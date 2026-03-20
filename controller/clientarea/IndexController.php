<?php
namespace addons\auto_refund\controller\clientarea;

class IndexController extends \app\home\controller\PluginHomeBaseController
{
    public function index()
    {
        $page = input("page", 1);
        $pageSize = 10;
        $user_id = input("uid");
        $setting = \Think\Db::name("product_refund_setting")->where("id", 1)->find();
        $results = \Think\Db::name("host")->where("uid", $user_id)->where("nextduedate", ">", time() - $setting["displaytime"] * 86400)->order("id", "desc")->paginate($pageSize, false, ["page" => $page]);
        $resultWithNames = [];
        foreach ($results as $record) {
            $productid = $record["productid"];
            $hostid = $record["id"];
            $name = \Think\Db::name("products")->where("id", $productid)->value("name");
            $product = \Think\Db::name("product_refund_list")->where("hostid", $hostid)->order("id", "desc")->find();
            $products = \Think\Db::name("product_refund")->where("productid", $productid)->find();
            $record["name"] = $name;
            $record["created_time"] = $product["created_time"];
            $record["audittime"] = $product["audittime"];
            $record["status"] = $product["status"];
            $record["reason"] = $product["reason"];
            $record["request"] = $products["request"];
            $record["within"] = $products["within"];
            $record["rules"] = $products["rules"];
            $resultWithNames[] = $record;
        }
        $this->assign("data", $resultWithNames);
        $this->assign("fen", $results);
        $this->assign("Title", "申请退款");
        return $this->fetch("/index");
    }

    public function cancellation()
    {
        if (request()->isPost()) {
            $productid = input("post.productid");
            $orderid = input("post.orderid");
            $id = input("post.id");
            $user_id = request()->uid;
            if (empty($user_id)) {
                return json(["code" => 400, "msg" => "您未登录或登录已失效，请刷新页面"]);
            }
            $result = \Think\Db::name("product_refund_list")->where("hostid", $id)->where("status", 1)->update(["status" => 4, "reason" => date("Y-m-d H:i:s") . " 取消申请"]);
            $list = \Think\Db::name("product_refund_list")->where("hostid", $id)->find();
            $clients = \Think\Db::name("clients")->where("id", $user_id)->find();
            $status = \Think\Db::name("product_refund_list")->where("hostid", $id)->value("status");
            if ($result !== false && 0 < $result) {
                $Switch = \Think\Db::name("product_refund_setting")->where(["id" => 1])->find();
                $time = date("Y-m-d H:i:s", time());
                $message = "【取消退款申请】通知 \n用户编号：" . $user_id . " \n用户名称：" . $clients["username"] . " \n用户邮箱：" . $clients["email"] . "\n商品名称：" . $list["productname"] . "  \n订单编号：" . $list["orderid"] . "  \n主机编号：" . $id . " \n\n通知时间：" . $time . "  \n消息来源【" . $Switch["webname"] . " 】产品退款【机器人版】";
                $this->sendNotifications($Switch, $message);
                return json(["code" => 200, "msg" => "取消成功"]);
            }
            switch ($status) {
                case 2:
                    return json(["code" => 500, "msg" => "取消失败，原因：已退款成功"]);
                case 3:
                    return json(["code" => 500, "msg" => "取消失败，原因：申请已驳回"]);
                default:
                    return json(["code" => 500, "msg" => "取消失败"]);
            }
        }
    }

    public function check()
    {
        if (!request()->isPost()) {
            return;
        }

        $productid    = input("post.productid");
        $orderid      = input("post.orderid");
        $reasonrefund = input("post.reason");
        $id           = input("post.id");
        $user_id      = input("uid");

        if (empty($user_id)) {
            return json(["code" => 400, "msg" => "您未登录或登录已失效，请刷新页面"]);
        }

        $product      = \Think\Db::name("product_refund")->where("productid", $productid)->find();
        $productsid   = \Think\Db::name("host")->where("id", $id)->find();
        $orderidsid   = \Think\Db::name("orders")->where("id", $productsid["orderid"])->find();
        $invoices     = \Think\Db::name("invoices")->where("id", $orderidsid["invoiceid"])->find();
        $clients      = \Think\Db::name("clients")->where("id", $user_id)->find();
        $resultinvoice = \Think\Db::name("invoice_items")->where(["rel_id" => $id, "type" => "renew"])->find();
        $shengjiangji = \Think\Db::name("invoice_items")->where(["rel_id" => $id, "type" => "upgrade"])->find();
        $productstype = \Think\Db::name("products")->where("id", $productid)->find();
        $firstpaymentamount = \Think\Db::name("host")->where("id", $id)->value("firstpaymentamount");
        $resultlist   = \Think\Db::name("product_refund_list")->where("hostid", $id)->find();

        if (!$product)                                            return json(["code" => 400, "msg" => "当前产品不支持退款"]);
        if ($invoices["status"] === "Unpaid")                    return json(["code" => 400, "msg" => "当前产品订单未支付状态，不支持申请"]);
        if ($resultlist && $resultlist["status"] != 0 && $resultlist["status"] != 4) return json(["code" => 400, "msg" => "当前订单已经申请过退款，请勿重复再次申请"]);
        if ($productsid["nextduedate"] < time())                 return json(["code" => 400, "msg" => "当前产品已到期，不支持申请退款"]);
        if ($productsid["domainstatus"] == "Deleted")            return json(["code" => 400, "msg" => "已删除产品不允许退款"]);
        if ($productsid["domainstatus"] != "Active")             return json(["code" => 400, "msg" => "当前产品主机状态为非已激活状态，不支持申请"]);
        if ($resultinvoice)                                       return json(["code" => 400, "msg" => "已续费的产品不支持自助申请退款"]);
        if ($firstpaymentamount === "0.00")                      return json(["code" => 400, "msg" => "当前订单为免费订单，不支持退款申请"]);
        if ($shengjiangji)                                        return json(["code" => 400, "msg" => "产品升级或降级过，不支持自助申请退款"]);

        $type    = $product["type"];
        $rules   = $product["rules"];
        $request = $product["request"];
        $Switch  = \Think\Db::name("product_refund_setting")->where(["id" => 1])->find();
        $client_ip   = $_SERVER["REMOTE_ADDR"];
        $client_port = $_SERVER["REMOTE_PORT"];
        $currentTimestamp = time();

        // =====================================================================
        // TYPE=3: API工单退款 — 登录上游ZJMF + 提交工单
        // =====================================================================
        if ($type == '3' && !empty($product["api_config_id"])) {
            $apiConfig = \Think\Db::name("product_refund_api_config")
                ->where("id", $product["api_config_id"])
                ->where("type", "api")
                ->find();
            if (!$apiConfig) return json(["code" => 400, "msg" => "API配置不存在（需 type=api）"]);

            $this->loadLibs();
            $apiKey  = \addons\auto_refund\lib\EncryptUtil::decrypt($apiConfig["api_key"]);
            $hostRow = \Think\Db::name("host")->where("id", $id)->find();
            $dcimid  = $hostRow["dcimid"] ?: ($hostRow["upstream_id"] ?? '');
            if (empty($dcimid)) return json(["code" => 400, "msg" => "无法获取上游产品ID（dcimid/upstream_id 为空）"]);

            $loginResult = \addons\auto_refund\lib\ApiClient::login($apiConfig["hostname"], $apiConfig["username"], $apiKey);
            if (!$loginResult["success"]) return json(["code" => 400, "msg" => "上游API登录失败：" . $loginResult["msg"]]);
            if (empty($loginResult["jwt"]) || strlen($loginResult["jwt"]) < 10) return json(["code" => 400, "msg" => "获取的JWT令牌无效"]);

            $ticketTitle   = str_replace(["{product_name}", "{host_id}"], [$productstype["name"], $dcimid], $apiConfig["ticket_title"] ?: "申请退款");
            $ticketContent = str_replace(["{product_name}", "{host_id}"], [$productstype["name"], $dcimid], $apiConfig["ticket_content"] ?: "申请产品无理由退款");
            $ticketResult  = \addons\auto_refund\lib\ApiClient::createTicket($apiConfig["hostname"], $loginResult["jwt"], [
                "department_id" => intval($apiConfig["ticket_department_id"]) ?: 2,
                "title"         => $ticketTitle,
                "content"       => $ticketContent,
                "host_id"       => intval($dcimid)
            ]);
            if (!$ticketResult["success"]) return json(["code" => 400, "msg" => "提交工单失败：" . $ticketResult["msg"]]);

            $refundamount = $this->calcRefund($productsid, $rules);
            if ($refundamount <= 0) return json(["code" => 400, "msg" => "退款金额不能小于0"]);

            $apiAuditType = $apiConfig["api_audit_type"] ?: 1;
            $status       = $apiAuditType == 2 ? 2 : 1;
            \Think\Db::name("product_refund_list")->insert([
                "user_id" => $user_id, "username" => $clients["username"], "productid" => $productid,
                "productname" => $productstype["name"], "orderid" => $orderid, "producttype" => $productstype["type"],
                "hostid" => $id, "invoices" => $orderidsid["invoiceid"], "type" => $type,
                "request" => $request, "rules" => $rules, "amount" => $refundamount,
                "created_time" => time(), "reasonrefund" => $reasonrefund . " [上游产品ID:" . $dcimid . "]",
                "status" => $status
            ]);

            if ($apiAuditType == 2) {
                $this->doAutoRefund($user_id, $clients, $id, $orderidsid, $refundamount, "【API自动退款】上游产品ID：" . $dcimid);
                return json(["code" => 200, "msg" => "申请成功，工单已提交且已自动退款到余额。"]);
            }
            return json(["code" => 200, "msg" => "申请成功，等待管理员审核。"]);
        }

        // =====================================================================
        // TYPE=4: 插件间对接 — 调用上游同款插件的 apiRefund 公开接口
        // =====================================================================
        if ($type == '4' && !empty($product["api_config_id"])) {
            $apiConfig = \Think\Db::name("product_refund_api_config")
                ->where("id", $product["api_config_id"])
                ->where("type", "plugin")
                ->find();
            if (!$apiConfig) return json(["code" => 400, "msg" => "插件间对接配置不存在（需 type=plugin）"]);

            $this->loadLibs();
            $apiKey  = \addons\auto_refund\lib\EncryptUtil::decrypt($apiConfig["api_key"]);
            $hostRow = \Think\Db::name("host")->where("id", $id)->find();
            $dcimid  = $hostRow["dcimid"] ?: ($hostRow["upstream_id"] ?? '');
            if (empty($dcimid)) return json(["code" => 400, "msg" => "无法获取上游产品ID（dcimid/upstream_id 为空）"]);

            $refundamount = $this->calcRefund($productsid, $rules);
            if ($refundamount <= 0) return json(["code" => 400, "msg" => "退款金额不能小于0"]);

            $pluginApiResult = self::callPluginRefundApi($apiConfig["hostname"], $apiKey, [
                "user_id"             => $user_id,
                "username"            => $clients["username"],
                "upstream_product_id" => $dcimid,
                "amount"              => $refundamount,
                "reason"              => $reasonrefund,
                "product_name"        => $productstype["name"]
            ]);

            if (!$pluginApiResult["success"]) {
                return json(["code" => 400, "msg" => "向上游提交退款申请失败：" . $pluginApiResult["msg"]]);
            }

            $apiAuditType = $apiConfig["api_audit_type"] ?: 1;
            $localStatus  = $apiAuditType == 2 ? 2 : 1;
            \Think\Db::name("product_refund_list")->insert([
                "user_id" => $user_id, "username" => $clients["username"], "productid" => $productid,
                "productname" => $productstype["name"], "orderid" => $orderid, "producttype" => $productstype["type"],
                "hostid" => $id, "invoices" => $orderidsid["invoiceid"], "type" => $type,
                "request" => $request, "rules" => $rules, "amount" => $refundamount,
                "created_time" => time(), "reasonrefund" => $reasonrefund . " [上游产品ID:" . $dcimid . "]",
                "status" => $localStatus
            ]);

            if ($apiAuditType == 2) {
                $this->doAutoRefund($user_id, $clients, $id, $orderidsid, $refundamount, "【插件间对接自动退款】上游产品ID：" . $dcimid);
                return json(["code" => 200, "msg" => "申请成功，已向上游提交且已自动退款到余额。"]);
            }
            return json(["code" => 200, "msg" => "申请成功，已向上游提交退款申请，等待管理员审核。"]);
        }

        // =====================================================================
        // TYPE=1/2: 人工审核 / 自动退款（原逻辑不变，按 request+rules 分支处理）
        // =====================================================================
        // 以下保留原有人工审核和自动退款完整逻辑
        if ($type == 1 || $type == 2) {
            // 时间窗口检查
            $withinHours     = $product["within"];
            $days            = $Switch["day"];
            $clientsGroup    = $clients["groupid"];
            $extraSeconds    = ($clientsGroup == 0) ? 0 : ($days * 24 * 3600);
            $withinInSeconds = $withinHours * 3600 + $extraSeconds;
            $newTimestamp    = $productsid["regdate"] + $withinInSeconds;

            if ($request == 1 || $request == 2) {
                // 首次购买限制检查
                $setting = \Think\Db::name("product_refund_setting")->where("id", 1)->find();
                if ($clientsGroup == "0" || $setting["agent"] != 1) {
                    $dupCheck = \Think\Db::name("product_refund_list")
                        ->where("user_id", $user_id)
                        ->where("productid", $productid)
                        ->where("status", "in", [1, 2, 3])
                        ->find();
                    if ($dupCheck) {
                        $limitMsg = ($request == 1) ? "产品首次" : "同类产品首次";
                        return json(["code" => 400, "msg" => $productstype["name"] . "（" . $limitMsg . "）每人仅限一次退款，您已申请过"]);
                    }
                }
            }

            if ($request == 3) {
                // 指定时间内检查
                if ($newTimestamp < $currentTimestamp) {
                    return json(["code" => 400, "msg" => "当前产品支持开通后" . $product["within"] . "小时内退款，当前已经超过指定时间，不能执行操作。"]);
                }
            }

            $refundamount = $this->calcRefund($productsid, $rules);
            if ($refundamount <= 0) return json(["code" => 400, "msg" => "退款金额不能小于0"]);

            if ($type == 2) {
                // ---- 自动退款 ----
                $newcredit = $clients["credit"] + $refundamount;
                $usagetime = $currentTimestamp - $productsid["regdate"];
                $h = floor($usagetime / 3600); $m = floor($usagetime % 3600 / 60); $s = $usagetime % 60;
                $timeFormat = sprintf("%02d:%02d:%02d", $h, $m, $s);
                $ruleLabel  = ($rules == 1) ? "按时长" : (($rules == 2) ? "按月" : "全额");
                $reqLabel   = ($request == 1) ? "产品首次" : (($request == 2) ? "同类产品首次" : $product["within"] . "小时内");
                \Think\Db::name("accounts")->insert(["uid" => $user_id, "currency" => "CNY", "gateway" => "退款至余额【主机ID：" . $id . " 】", "create_time" => time(), "pay_time" => time(), "description" => "【" . $reqLabel . "自动" . $ruleLabel . "退款】订单号：" . $orderid . ", 主机ID：" . $id . ", 使用时长：" . $timeFormat, "amount_out" => $refundamount, "rate" => "1.00000", "invoice_id" => $orderidsid["invoiceid"]]);
                \Think\Db::name("credit")->insert(["uid" => $user_id, "create_time" => time(), "description" => "Credit from Refund of Invoice ID " . $orderidsid["invoiceid"], "amount" => $refundamount, "notes" => "订单号：" . $orderidsid["invoiceid"] . "，首付金额：" . $productsid["firstpaymentamount"] . "元，" . $reqLabel . "自动" . $ruleLabel . "退款【退款金额：" . $refundamount . " 元】，使用时长：" . $timeFormat, "balance" => $newcredit]);
                \Think\Db::name("clients")->where("id", $user_id)->update(["credit" => $newcredit]);
                \Think\Db::name("invoices")->where("id", $orderidsid["invoiceid"])->update(["status" => "Refunded"]);
                \Think\Db::name("activity_log")->insert(["create_time" => time(), "description" => "账单退款 - User ID:" . $user_id . " - Invoice ID:" . $orderidsid["invoiceid"] . " - 退款金额: " . $refundamount . " 元，来源：" . $reqLabel . "自动" . $ruleLabel . "退款", "user" => "System", "uid" => $user_id, "ipaddr" => "0.0.0.0", "type" => "6", "activeid" => 0, "usertype" => "System", "port" => "", "type_data_id" => $orderidsid["invoiceid"]]);
                \Think\Db::name("product_refund_list")->insert(["user_id" => $user_id, "username" => $clients["username"], "productid" => $productid, "productname" => $productstype["name"], "orderid" => $orderid, "producttype" => $productstype["type"], "hostid" => $id, "invoices" => $orderidsid["invoiceid"], "type" => $type, "request" => $request, "rules" => $rules, "amount" => $refundamount, "created_time" => time(), "reasonrefund" => $reasonrefund, "audittime" => time(), "adminid" => "System", "reviewed" => "System", "status" => "2", "reason" => "自动审核通过"]);
                $updatetime = time();
                \Think\Db::name("host")->where("id", $id)->update(["nextduedate" => $updatetime]);
                \Think\Db::name("activity_log")->insert(["create_time" => time(), "description" => "【自动退】用户申请退款 User ID:" . $user_id . "，Host ID:" . $id . "，原到期：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "，更新到期：" . date("Y-m-d H:i:s", $updatetime) . "，来源：" . $reqLabel . "自动" . $ruleLabel . "退款", "user" => "System", "uid" => $user_id, "ipaddr" => "0.0.0.0", "type" => "6", "activeid" => 0, "usertype" => "System", "port" => "", "type_data_id" => ""]);
                $originalData = \Think\Db::name("host")->where("id", $id)->find();
                \Think\Db::name("host")->where("id", $id)->update(["notes" => ($originalData["notes"] ?? "") . "\n自动" . $ruleLabel . "退款\n原到期：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "\n更新到期：" . date("Y-m-d H:i:s", $updatetime)]);
                $this->sendNotifications($Switch, $this->buildNotifyMsg($Switch, $product, $productstype, $clients, $orderidsid, $id, $user_id, $orderid, $firstpaymentamount, $refundamount, $reasonrefund));
                return json(["code" => 200, "msg" => "申请成功，系统审核中。"]);
            } else {
                // ---- 人工审核 ----
                \Think\Db::name("product_refund_list")->insert(["user_id" => $user_id, "username" => $clients["username"], "productid" => $productid, "productname" => $productstype["name"], "orderid" => $orderid, "producttype" => $productstype["type"], "hostid" => $id, "invoices" => $orderidsid["invoiceid"], "type" => $type, "request" => $request, "rules" => $rules, "amount" => $refundamount, "created_time" => time(), "reasonrefund" => $reasonrefund, "status" => "1"]);
                \Think\Db::name("activity_log")->insert(["create_time" => time(), "description" => "【待审核】用户申请退款 User ID:" . $user_id . "，Host ID:" . $id . "，申请时间：" . date("Y-m-d H:i:s", $currentTimestamp), "user" => $clients["username"], "uid" => $user_id, "ipaddr" => $client_ip, "type" => "6", "activeid" => $user_id, "usertype" => "System", "port" => $client_port, "type_data_id" => ""]);
                $this->sendNotifications($Switch, $this->buildNotifyMsg($Switch, $product, $productstype, $clients, $orderidsid, $id, $user_id, $orderid, $firstpaymentamount, $refundamount, $reasonrefund));
                return json(["code" => 200, "msg" => "申请成功，等待审核。"]);
            }
        }

        return json(["code" => 400, "msg" => "不支持的退款类型。"]);
    }

    // =====================================================================
    // 私有辅助方法
    // =====================================================================

    /** 加载加密/API工具库 */
    private function loadLibs()
    {
        $libPath = __DIR__ . "/../../lib/";
        if (file_exists($libPath . "EncryptUtil.php")) {
            require_once $libPath . "EncryptUtil.php";
            if (file_exists($libPath . "ApiClient.php")) {
                require_once $libPath . "ApiClient.php";
            }
        } elseif (defined('ADDON_PATH')) {
            $base = ADDON_PATH . "auto_refund" . DS . "lib" . DS;
            require_once $base . "EncryptUtil.php";
            if (file_exists($base . "ApiClient.php")) require_once $base . "ApiClient.php";
        } else {
            $base = dirname(dirname(__DIR__)) . "/lib/";
            require_once $base . "EncryptUtil.php";
            if (file_exists($base . "ApiClient.php")) require_once $base . "ApiClient.php";
        }
    }

    /** 按退款规则计算退款金额 */
    private function calcRefund($productsid, $rules)
    {
        $productstime = $productsid["nextduedate"] - $productsid["regdate"];
        $paymenttime  = $productsid["firstpaymentamount"] / $productstime;
        $usagetime    = time() - $productsid["regdate"];

        if ($rules == 1) {
            $paymentamount = $paymenttime * $usagetime;
            return round($productsid["firstpaymentamount"] - $paymentamount, 2);
        } elseif ($rules == 2) {
            $monthpayment  = $paymenttime * 2592000;
            $monthsUsed    = ceil($usagetime / 2592000);
            $paymentamount = $monthpayment * $monthsUsed;
            return round($productsid["firstpaymentamount"] - $paymentamount, 2);
        } else {
            return floatval($productsid["firstpaymentamount"]);
        }
    }

    /** 执行自动退款入账操作（credit/accounts/invoice） */
    private function doAutoRefund($user_id, $clients, $id, $orderidsid, $refundamount, $description)
    {
        $newcredit = $clients["credit"] + $refundamount;
        \Think\Db::name("accounts")->insert([
            "uid" => $user_id, "currency" => "CNY",
            "gateway" => "退款至余额【主机ID：" . $id . " 】",
            "create_time" => time(), "pay_time" => time(),
            "description" => $description,
            "amount_out" => $refundamount, "rate" => "1.00000",
            "invoice_id" => $orderidsid["invoiceid"]
        ]);
        \Think\Db::name("clients")->where("id", $user_id)->update(["credit" => $newcredit]);
        \Think\Db::name("invoices")->where("id", $orderidsid["invoiceid"])->update(["status" => "Refunded"]);
    }

    /** 构建通知消息文本 */
    private function buildNotifyMsg($Switch, $product, $productstype, $clients, $orderidsid, $id, $user_id, $orderid, $firstpaymentamount, $refundamount, $reasonrefund)
    {
        $typeMap    = ['1' => '人工审核', '2' => '自动退款', '3' => 'API工单退款', '4' => '插件间对接'];
        $reqMap     = ['1' => '产品首次', '2' => '同类产品首次', '3' => '指定时间内'];
        $rulesMap   = ['1' => '按时长退', '2' => '按月退', '3' => '全额退'];
        $producttype    = $typeMap[$product["type"]]   ?? '未知类型';
        $productrequest = $reqMap[$product["request"]] ?? '未知要求';
        $productrules   = $rulesMap[$product["rules"]] ?? '未知规则';
        return "【" . $producttype . " 】申请退款通知 \n用户编号：" . $user_id . " \n用户名称：" . $clients["username"] . " \n用户邮箱：" . $clients["email"] . "\n商品名称：" . $productstype["name"] . "  \n账单编号：" . $orderidsid["invoiceid"] . " \n订单编号：" . $orderid . " \n主机编号：" . $id . " \n退款类型：" . $producttype . " \n退款要求：" . $productrequest . " \n退款规则：" . $productrules . " \n首付金额：" . $firstpaymentamount . " 元 \n退款金额：" . $refundamount . " 元 \n退款原因：" . $reasonrefund . " \n\n通知时间：" . date("Y-m-d H:i:s", time()) . " \n消息来源【" . $Switch["webname"] . " 】产品退款【机器人版】";
    }

    /** 发送所有渠道通知 */
    private function sendNotifications($Switch, $message)
    {
        if ($Switch["feishuswitch"] == 1) {
            $this->sendToWebhook($Switch["feishuurl"], json_encode(["msg_type" => "text", "content" => ["text" => $message]]));
        }
        if ($Switch["dingswitch"] == 1) {
            $this->sendToWebhook($Switch["dingurl"], json_encode(["msgtype" => "text", "text" => ["content" => $message]]));
        }
        if ($Switch["wechatswitch"] == 1) {
            $this->sendToWebhook($Switch["wechaturl"], json_encode(["msgtype" => "text", "text" => ["content" => $message]]));
        }
        if ($Switch["tgswitch"] == 1) {
            $this->sendTelegramNotification("https://api.telegram.org/bot" . $Switch["tgtoken"] . "/sendMessage?chat_id=" . $Switch["tgchatid"] . "&text=" . urlencode($message));
        }
    }

    private function sendToWebhook($webhookUrl, $data)
    {
        $ch = curl_init($webhookUrl);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_exec($ch);
        curl_close($ch);
    }

    private function sendTelegramNotification($telegramUrl)
    {
        $ch = curl_init($telegramUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_exec($ch);
        curl_close($ch);
    }

    // =====================================================================
    // API KEY 管理页（代理用户）
    // =====================================================================
    public function apikey()
    {
        $user_id = request()->uid;
        if (empty($user_id)) {
            $this->redirect("/login");
            return;
        }

        $apiKey = \Think\Db::name("product_refund_plugin_api")
            ->where("user_id", $user_id)
            ->where("status", 1)
            ->find();

        $scheme = request()->isSsl() ? 'https://' : 'http://';
        $weburl = \Think\Db::name("product_refund_setting")->where("id", 1)->value("weburl");
        if (empty($weburl)) $weburl = request()->domain();
        if (!preg_match('/^https?:\/\//', $weburl)) $weburl = $scheme . $weburl;

        // 只显示域名部分（去掉协议头和路径）
        $apiEndpoint = rtrim($weburl, '/');

        $this->assign("apiKey", $apiKey);
        $this->assign("apiEndpoint", $apiEndpoint);
        $this->assign("Title", "API KEY管理");
        return $this->fetch("/apikey");
    }

    public function generateApiKey()
    {
        if (!request()->isPost()) return json(["code" => 400, "msg" => "请求方式错误"]);
        $user_id = request()->uid;
        if (empty($user_id)) return json(["code" => 401, "msg" => "未登录"]);

        $apiKey = "sk_" . md5(uniqid() . $user_id . time());

        \Think\Db::name("product_refund_plugin_api")->where("user_id", $user_id)->update(["status" => 0]);
        $result = \Think\Db::name("product_refund_plugin_api")->insert([
            "user_id" => $user_id, "api_key" => $apiKey,
            "status" => 1, "created_at" => date('Y-m-d H:i:s'), "updated_at" => date('Y-m-d H:i:s')
        ]);

        if ($result) return json(["code" => 200, "msg" => "生成成功", "data" => ["api_key" => $apiKey]]);
        return json(["code" => 500, "msg" => "生成失败"]);
    }

    public function deleteApiKey()
    {
        if (!request()->isPost()) return json(["code" => 400, "msg" => "请求方式错误"]);
        $user_id = request()->uid;
        if (empty($user_id)) return json(["code" => 401, "msg" => "未登录"]);

        $result = \Think\Db::name("product_refund_plugin_api")->where("user_id", $user_id)->update(["status" => 0]);
        if ($result !== false) return json(["code" => 200, "msg" => "删除成功"]);
        return json(["code" => 500, "msg" => "删除失败"]);
    }

    // =====================================================================
    // 插件间对接 - 下游发起方法（callPluginRefundApi）
    // =====================================================================
    /**
     * 向上游网站的 apiRefund 公开接口提交退款申请
     *
     * @param string $hostname  上游网站地址（含协议，如 https://upstream.example.com）
     * @param string $apiKey    上游颁发给本站代理账号的 API KEY
     * @param array  $data      退款数据
     * @return array ['success' => bool, 'msg' => string]
     */
    private static function callPluginRefundApi($hostname, $apiKey, $data)
    {
        $hostname = rtrim($hostname, '/');

        // 使用独立API文件地址（无需框架路由和登录认证）
        $url = $hostname . '/plugins/addons/auto_refund/api.php';

        $requestData = [
            'api_key'             => $apiKey,
            'user_id'             => $data['user_id'],
            'username'            => $data['username'],
            'upstream_product_id' => $data['upstream_product_id'],  // 上游dcimid/upstream_id
            'amount'              => $data['amount'],
            'reason'              => $data['reason'],
            'product_name'        => $data['product_name'],
            'timestamp'           => time()
        ];

        trace('插件间对接请求URL: ' . $url, 'info');
        trace('插件间对接请求数据: ' . json_encode($requestData, JSON_UNESCAPED_UNICODE), 'info');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);  // 不自动跟随重定向，便于捕获302

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        trace('插件间对接响应状态: ' . $httpCode, 'info');
        trace('插件间对接响应体: ' . $response, 'info');

        if ($error) {
            return ['success' => false, 'msg' => 'cURL错误：' . $error];
        }
        if ($httpCode == 302) {
            // 被跳转到登录页，说明 apiRefund 没有被加入免登录白名单
            return ['success' => false, 'msg' => '上游返回302重定向，请确认上游插件已正确配置 $noLoginAction 白名单'];
        }
        if ($httpCode != 200) {
            return ['success' => false, 'msg' => 'HTTP错误：' . $httpCode . '，响应：' . substr($response, 0, 300)];
        }

        $result = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'msg' => '响应解析失败（非JSON）：' . substr($response, 0, 200)];
        }

        if (isset($result['code']) && $result['code'] == 200) {
            return ['success' => true, 'msg' => $result['msg'] ?? '提交成功'];
        } else {
            return ['success' => false, 'msg' => $result['msg'] ?? '上游返回错误'];
        }
    }

    public function customerdetail2() { $this->assign("Title", "Demo样式3"); return $this->fetch("/customerdetail2"); }
    public function customerdetail3() { $this->assign("Title", "Demo样式4"); return $this->fetch("/customerdetail3"); }
    public function customerdetail4() { $this->assign("Title", "Demo样式5"); return $this->fetch("/customerdetail4"); }
    public function helplist()        { $this->assign("Title", "Demo样式6"); return $this->fetch("/helplist"); }
}
