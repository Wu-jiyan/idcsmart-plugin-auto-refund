<?php
namespace addons\auto_refund\lib;

/**
 * 退款服务 - 使用系统 Invoices 逻辑计算正确的可退金额
 * 
 * 解决漏洞：原插件直接 insert accounts/credit 表记录来退款，
 * 完全绕过了系统账单的支付验证，导致未支付也能退款（刷余额漏洞）。
 * 
 * 现改为通过系统 Invoices::creditRefund() 和 refundAndApply() 
 * 计算真实的已支付可退金额，最多只退系统计算出的可退金额。
 */
class RefundService
{
    /**
     * @var \app\common\logic\Invoices
     */
    private $invoiceLogic;

    public function __construct()
    {
        $this->invoiceLogic = new \app\common\logic\Invoices();
        $this->invoiceLogic->is_admin = true;
    }

    /**
     * 获取账单可退金额明细
     *
     * @param int $invoiceId 账单ID
     * @return array [
     *   'total_refundable'   => 总可退金额,
     *   'credit_refundable'  => 余额支付可退金额,
     *   'gateway_refundable' => 网关交易可退金额,
     *   'already_refunded'   => 已退款金额,
     *   'can_refund'         => 剩余可退金额,
     *   'invoice'            => 账单信息
     * ]
     */
    public function getRefundable(int $invoiceId): array
    {
        $invoice = \think\Db::name("invoices")->where("id", $invoiceId)->find();
        if (!$invoice) {
            return $this->emptyResult("账单不存在");
        }

        if ($invoice['status'] !== 'Paid') {
            return $this->emptyResult("账单未支付，不可退款（状态：" . $invoice['status'] . "）");
        }

        if ($invoice['type'] === 'credit_limit') {
            return $this->emptyResult("信用额还款账单不可退款");
        }

        // 余额支付可退金额（系统逻辑）：
        // Credit Applied to Invoice #ID
        // + Credit Applied to Renew Invoice #ID  
        // + Credit Removed from Invoice #ID
        $creditRefundable = $this->invoiceLogic->creditRefund($invoiceId);

        // 总已支付金额（网关 + 余额）
        $totalPaid = $this->invoiceLogic->refundAndApply($invoiceId);

        // 网关支付金额 = 总已付 - 余额已付
        $gatewayRefundable = max(0, bcsub($totalPaid, $creditRefundable, 2));

        // 已经退款了多少（通过 accounts 表 refund 关联）
        $alreadyRefunded = $this->getAlreadyRefunded($invoiceId);

        $canRefund = max(0, bcsub($totalPaid, $alreadyRefunded, 2));

        return [
            'success'             => true,
            'msg'                 => 'OK',
            'total_refundable'    => floatval($totalPaid),
            'credit_refundable'   => floatval($creditRefundable),
            'gateway_refundable'  => floatval($gatewayRefundable),
            'already_refunded'    => floatval($alreadyRefunded),
            'can_refund'          => floatval($canRefund),
            'invoice'             => $invoice,
        ];
    }

    /**
     * 执行退款（按系统 InvoiceController::refund() 逻辑）
     * 
     * 先退网关支付部分（通过 accounts 退款），再退余额支付部分
     *
     * @param int    $invoiceId   账单ID
     * @param float  $refundAmount 请求退款金额
     * @param int    $hostId      主机ID（用于日志）
     * @param string $adminUser   操作管理员（空字符串表示系统自动）
     * @param int    $adminId     管理员ID
     * @return array [success, msg, refunded_amount]
     */
    public function refund(int $invoiceId, float $refundAmount, int $hostId = 0, string $adminUser = '', int $adminId = 0): array
    {
        $refundInfo = $this->getRefundable($invoiceId);
        if (!$refundInfo['success']) {
            return ['success' => false, 'msg' => $refundInfo['msg'], 'refunded_amount' => 0];
        }

        $canRefund = $refundInfo['can_refund'];
        $invoice   = $refundInfo['invoice'];

        if ($refundAmount <= 0) {
            return ['success' => false, 'msg' => '退款金额必须大于0', 'refunded_amount' => 0];
        }

        // 最多只退系统计算出的可退金额
        $actualRefund = min($refundAmount, $canRefund);

        if ($actualRefund <= 0) {
            return ['success' => false, 'msg' => '无可退金额（已退完或未支付）', 'refunded_amount' => 0];
        }

        $uid = $invoice['uid'];
        $remaining = $actualRefund;

        \think\Db::startTrans();
        try {
            // 第一步：退网关交易部分（通过 accounts 表退款记录）
            $gatewayRefundable = $refundInfo['gateway_refundable'];
            if ($gatewayRefundable > 0 && $remaining > 0) {
                $gatewayRefund = min($remaining, $gatewayRefundable);
                $this->refundGateway($invoiceId, $uid, $gatewayRefund);
                $remaining = bcsub($remaining, $gatewayRefund, 2);
            }

            // 第二步：退余额支付部分
            $creditRefundable = $refundInfo['credit_refundable'];
            if ($creditRefundable > 0 && $remaining > 0) {
                $creditRefund = min($remaining, $creditRefundable);
                $this->refundCredit($invoiceId, $uid, $creditRefund);
                $remaining = bcsub($remaining, $creditRefund, 2);
            }

            // 更新账单状态为 Refunded
            \think\Db::name("invoices")->where("id", $invoiceId)->update([
                "status"      => "Refunded",
                "update_time" => time()
            ]);

            // 记录活动日志
            $logDesc = "账单退款 - User ID:{$uid} - Invoice ID:{$invoiceId} - 退款金额: {$actualRefund} 元";
            if ($hostId) {
                $logDesc .= " - Host ID:{$hostId}";
            }
            $logDesc .= " - 来源：产品退款插件" . ($adminUser ? "（审核人：{$adminUser}）" : "（系统自动）");

            \think\Db::name("activity_log")->insert([
                "create_time"  => time(),
                "description"  => $logDesc,
                "user"         => $adminUser ?: "System",
                "uid"          => $uid,
                "ipaddr"       => $_SERVER["REMOTE_ADDR"] ?? "0.0.0.0",
                "type"         => "6",
                "activeid"     => $adminId,
                "usertype"     => $adminUser ? "Admin" : "System",
                "port"         => $_SERVER["REMOTE_PORT"] ?? "",
                "type_data_id" => $invoiceId,
            ]);

            \think\Db::commit();

            return [
                'success'         => true,
                'msg'             => "退款成功，实际退款 {$actualRefund} 元" . ($actualRefund < $refundAmount ? "（请求{$refundAmount}元，系统可退{$canRefund}元）" : ""),
                'refunded_amount' => $actualRefund,
            ];
        } catch (\Exception $e) {
            \think\Db::rollback();
            return ['success' => false, 'msg' => '退款失败：' . $e->getMessage(), 'refunded_amount' => 0];
        }
    }

    /**
     * 退网关支付部分 — 通过 accounts 退款记录
     */
    private function refundGateway(int $invoiceId, int $uid, float $amount): void
    {
        // 获取未退完的网关支付记录
        $accounts = \think\Db::name("accounts")
            ->where("invoice_id", $invoiceId)
            ->where("delete_time", 0)
            ->where("refund", 0)
            ->where("gateway", "<>", "")
            ->select()
            ->toArray();

        $remaining = $amount;
        foreach ($accounts as $account) {
            if ($remaining <= 0) break;

            // 计算该笔交易剩余可退金额
            $alreadyOut = \think\Db::name("accounts")
                ->where("refund", $account['id'])
                ->where("delete_time", 0)
                ->sum("amount_out");
            $available = bcsub($account['amount_in'], $alreadyOut, 2);
            if ($available <= 0) continue;

            $thisRefund = min($remaining, $available);

            // 插入退款记录（关联原交易）
            \think\Db::name("accounts")->insert([
                "uid"         => $uid,
                "currency"    => $account['currency'],
                "gateway"     => "退款至余额",
                "create_time" => time(),
                "pay_time"    => time(),
                "description" => "产品退款 Transaction ID " . $account['trans_id'],
                "amount_out"  => $thisRefund,
                "invoice_id"  => $invoiceId,
                "refund"      => $account['id'],  // 关联原交易ID
            ]);

            // 退款入账到用户余额
            \think\Db::name("accounts")->insert([
                "uid"         => $uid,
                "currency"    => $account['currency'],
                "gateway"     => "退款至余额",
                "create_time" => time(),
                "pay_time"    => time(),
                "description" => "退款至余额入账 Invoice ID " . $invoiceId,
                "amount_in"   => $thisRefund,
                "invoice_id"  => $invoiceId,
            ]);

            // 增加用户余额
            \think\Db::name("clients")->where("id", $uid)->setInc("credit", $thisRefund);

            // 信用日志
            credit_log([
                "uid"    => $uid,
                "desc"   => "Credit from Refund of Invoice ID " . $invoiceId,
                "amount" => $thisRefund,
                "relid"  => $invoiceId,
            ]);

            $remaining = bcsub($remaining, $thisRefund, 2);
        }
    }

    /**
     * 退余额支付部分
     */
    private function refundCredit(int $invoiceId, int $uid, float $amount): void
    {
        // 余额退款直接入账到用户余额
        \think\Db::name("clients")->where("id", $uid)->setInc("credit", $amount);

        // 插入交易记录
        \think\Db::name("accounts")->insert([
            "uid"         => $uid,
            "currency"    => "CNY",
            "gateway"     => "退款至余额",
            "create_time" => time(),
            "pay_time"    => time(),
            "description" => "余额支付退款 Invoice ID " . $invoiceId,
            "amount_out"  => $amount,
            "invoice_id"  => $invoiceId,
        ]);

        // 退款入账
        \think\Db::name("accounts")->insert([
            "uid"         => $uid,
            "currency"    => "CNY",
            "gateway"     => "退款至余额",
            "create_time" => time(),
            "pay_time"    => time(),
            "description" => "退款至余额入账 Invoice ID " . $invoiceId,
            "amount_in"   => $amount,
            "invoice_id"  => $invoiceId,
        ]);

        // 信用日志
        credit_log([
            "uid"    => $uid,
            "desc"   => "Credit from Refund of Invoice ID " . $invoiceId,
            "amount" => $amount,
            "relid"  => $invoiceId,
        ]);
    }

    /**
     * 获取账单已退款总额
     */
    private function getAlreadyRefunded(int $invoiceId): float
    {
        // accounts 表中 refund > 0 表示是退款记录（关联原交易）
        $refundedFromAccounts = \think\Db::name("accounts")
            ->where("invoice_id", $invoiceId)
            ->where("delete_time", 0)
            ->where("refund", ">", 0)
            ->sum("amount_out");

        // 纯余额退款记录（refund = 0 且 amount_out > 0 且 gateway 含"退款"）
        $creditRefunded = \think\Db::name("accounts")
            ->where("invoice_id", $invoiceId)
            ->where("delete_time", 0)
            ->where("refund", 0)
            ->where("amount_out", ">", 0)
            ->where(function ($query) {
                $query->where("gateway", "like", "%退款%")
                      ->whereOr("description", "like", "%退款%");
            })
            ->sum("amount_out");

        return floatval(bcadd($refundedFromAccounts, $creditRefunded, 2));
    }

    private function emptyResult(string $msg): array
    {
        return [
            'success'             => false,
            'msg'                 => $msg,
            'total_refundable'    => 0,
            'credit_refundable'   => 0,
            'gateway_refundable'  => 0,
            'already_refunded'    => 0,
            'can_refund'          => 0,
            'invoice'             => null,
        ];
    }
}
