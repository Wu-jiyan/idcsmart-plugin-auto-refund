<style>
    td .btn-sm {
        border-radius: 0 !important;
    }
    td .badge {
        font-size: 12px;
        padding: 0.4em 0.5em;
        border-radius: 0;
    }
</style>

<script src="/plugins/addons/auto_refund/assets/layer.js"></script>

<div class="container-fluid py-4">
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr class="text-center">
                            <th>主机ID</th>
                            <th>主机名</th>
                            <th>IP</th>
                            <th>产品名称</th>
                            <th>主机状态</th>
                            <th>开通时间</th>
                            <th>申请时间</th>
                            <th>使用时长</th>
                            <th>退款金额</th>
                            <th>申请规则</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        {volist name='$data' id='info'}
                        <tr class="text-center">
                            <td>{$info.id}</td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;"><a href="/servicedetail?id={$info.id}">{$info.domain}</a></span>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;">{$info.dedicatedip}</span>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;">{$info.name}</span>
                            </td>
                            <td>
                                {if $info.domainstatus == 'Active'}
                                    <span class="badge badge-success"><i class="fas fa-check-circle"></i> 已激活</span>
                                {elseif $info.domainstatus == 'Deleted'}
                                    <span class="badge badge-danger"><i class="fas fa-trash"></i> 已删除</span>
                                {elseif $info.domainstatus == 'Pending'}
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> 待开通</span>
                                {elseif $info.domainstatus == 'Cancelled'}
                                    <span class="badge badge-danger"><i class="fas fa-ban"></i> 已取消</span>
                                {elseif $info.domainstatus == 'Fraud'}
                                    <span class="badge badge-danger"><i class="fas fa-exclamation-triangle"></i> 有欺诈</span>
                                {elseif $info.domainstatus == 'Suspended'}
                                    <span class="badge badge-secondary"><i class="fas fa-pause-circle"></i> 已暂停</span>
                                {else}
                                    <span class="badge badge-secondary"><i class="fas fa-question-circle"></i> 未知状态</span>
                                {/if}
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;">{:date('Y-m-d H:i:s',$info['regdate'])}</span>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                    <?php
                                    $createdtime = $info['created_time'];
                                    if (!empty($createdtime)) {
                                        echo date('Y-m-d H:i:s',$info['created_time']);
                                    } else {
                                        echo "未申请";
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                    <?php
                                    $createdtime = $info['created_time'];
                                    if (empty($createdtime)) {
                                        $createdtime = time();
                                        $usageSeconds = $createdtime - $info['regdate'];
                                        $billingCycle = $info['billingcycle'];
                                        $totalSeconds = 0;

                                        switch($billingCycle) {
                                            case 'monthly':
                                                $totalSeconds = 30 * 24 * 3600;
                                                break;
                                            case 'quarterly':
                                                $totalSeconds = 90 * 24 * 3600;
                                                break;
                                            case 'semiannually':
                                                $totalSeconds = 180 * 24 * 3600;
                                                break;
                                            case 'annually':
                                                $totalSeconds = 365 * 24 * 3600;
                                                break;
                                            case 'biennially':
                                                $totalSeconds = 730 * 24 * 3600;
                                                break;
                                            case 'triennially':
                                                $totalSeconds = 1095 * 24 * 3600;
                                                break;
                                            default:
                                                $totalSeconds = 30 * 24 * 3600;
                                        }

                                        $unusedRatio = 1 - ($usageSeconds / $totalSeconds);
                                        $unusedRatio = max(0, min(1, $unusedRatio));
                                        $refundAmount = $info['amount'] * $unusedRatio;

                                        $days = floor($usageSeconds / 86400);
                                        $hours = floor(($usageSeconds % 86400) / 3600);
                                        $minutes = floor(($usageSeconds % 3600) / 60);
                                        $seconds = $usageSeconds % 60;

                                        $formattedTime = '';
                                        if ($days > 0) $formattedTime .= $days . '天';
                                        if ($hours > 0) $formattedTime .= $hours . '时';
                                        if ($minutes > 0) $formattedTime .= $minutes . '分';
                                        if ($seconds > 0 || $formattedTime === '') $formattedTime .= $seconds . '秒';

                                        echo "已用<br/>" . $formattedTime;
                                    } else {
                                        $usageSeconds = $createdtime - $info['regdate'];
                                        $billingCycle = $info['billingcycle'];
                                        $totalSeconds = 0;

                                        switch($billingCycle) {
                                            case 'monthly':
                                                $totalSeconds = 30 * 24 * 3600;
                                                break;
                                            case 'quarterly':
                                                $totalSeconds = 90 * 24 * 3600;
                                                break;
                                            case 'semiannually':
                                                $totalSeconds = 180 * 24 * 3600;
                                                break;
                                            case 'annually':
                                                $totalSeconds = 365 * 24 * 3600;
                                                break;
                                            case 'biennially':
                                                $totalSeconds = 730 * 24 * 3600;
                                                break;
                                            case 'triennially':
                                                $totalSeconds = 1095 * 24 * 3600;
                                                break;
                                            default:
                                                $totalSeconds = 30 * 24 * 3600;
                                        }

                                        $unusedRatio = 1 - ($usageSeconds / $totalSeconds);
                                        $unusedRatio = max(0, min(1, $unusedRatio));
                                        $refundAmount = $info['amount'] * $unusedRatio;

                                        $days = floor($usageSeconds / 86400);
                                        $hours = floor(($usageSeconds % 86400) / 3600);
                                        $minutes = floor(($usageSeconds % 3600) / 60);
                                        $seconds = $usageSeconds % 60;

                                        $formattedTime = '';
                                        if ($days > 0) $formattedTime .= $days . '天';
                                        if ($hours > 0) $formattedTime .= $hours . '时';
                                        if ($minutes > 0) $formattedTime .= $minutes . '分';
                                        if ($seconds > 0 || $formattedTime === '') $formattedTime .= $seconds . '秒';

                                        echo "使用<br/>" . $formattedTime;
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                    <?php
                                    $createdtime = $info['created_time'];
                                    if (empty($createdtime)) {
                                        // 未申请退款 - 显示可退金额
                                        $canApply = false;
                                        if ($info['within'] > 0) {
                                            $currentTime = time();
                                            $regTime = $info['regdate'];
                                            $timeDiff = $currentTime - $regTime;
                                            $hoursDiff = $timeDiff / 3600;
                                            if ($hoursDiff <= $info['within']) {
                                                $canApply = true;
                                            }
                                        } else {
                                            $canApply = true;
                                        }

                                        if ($canApply) {
                                            // 根据退款规则计算金额
                                            if ($info['rules'] == 3) {
                                                // 全额退
                                                echo "可退<br/>¥" . number_format($info['amount'], 2);
                                            } else {
                                                // 按时长或按月退 - 计算折算金额
                                                $createdtime = time();
                                                $usageSeconds = $createdtime - $info['regdate'];
                                                $billingCycle = $info['billingcycle'];
                                                $totalSeconds = 0;

                                                switch($billingCycle) {
                                                    case 'monthly':
                                                        $totalSeconds = 30 * 24 * 3600;
                                                        break;
                                                    case 'quarterly':
                                                        $totalSeconds = 90 * 24 * 3600;
                                                        break;
                                                    case 'semiannually':
                                                        $totalSeconds = 180 * 24 * 3600;
                                                        break;
                                                    case 'annually':
                                                        $totalSeconds = 365 * 24 * 3600;
                                                        break;
                                                    case 'biennially':
                                                        $totalSeconds = 730 * 24 * 3600;
                                                        break;
                                                    case 'triennially':
                                                        $totalSeconds = 1095 * 24 * 3600;
                                                        break;
                                                    default:
                                                        $totalSeconds = 30 * 24 * 3600;
                                                }

                                                $unusedRatio = 1 - ($usageSeconds / $totalSeconds);
                                                $unusedRatio = max(0, min(1, $unusedRatio));
                                                $refundAmount = $info['amount'] * $unusedRatio;
                                                echo "可退<br/>¥" . number_format($refundAmount, 2);
                                            }
                                        } else {
                                            echo "不可退款";
                                        }
                                    } else {
                                        // 已申请退款 - 显示实际退款金额
                                        if ($info['rules'] == 3) {
                                            // 全额退
                                            $refundAmount = $info['amount'];
                                        } else {
                                            // 按时长或按月退 - 计算折算金额
                                            $usageSeconds = $createdtime - $info['regdate'];
                                            $billingCycle = $info['billingcycle'];
                                            $totalSeconds = 0;

                                            switch($billingCycle) {
                                                case 'monthly':
                                                    $totalSeconds = 30 * 24 * 3600;
                                                    break;
                                                case 'quarterly':
                                                    $totalSeconds = 90 * 24 * 3600;
                                                    break;
                                                case 'semiannually':
                                                    $totalSeconds = 180 * 24 * 3600;
                                                    break;
                                                case 'annually':
                                                    $totalSeconds = 365 * 24 * 3600;
                                                    break;
                                                case 'biennially':
                                                    $totalSeconds = 730 * 24 * 3600;
                                                    break;
                                                case 'triennially':
                                                    $totalSeconds = 1095 * 24 * 3600;
                                                    break;
                                                default:
                                                    $totalSeconds = 30 * 24 * 3600;
                                            }

                                            $unusedRatio = 1 - ($usageSeconds / $totalSeconds);
                                            $unusedRatio = max(0, min(1, $unusedRatio));
                                            $refundAmount = $info['amount'] * $unusedRatio;
                                        }

                                        if ($info['status'] == 1) {
                                            echo "审核中<br/>¥" . number_format($refundAmount, 2);
                                        } elseif ($info['status'] == 2) {
                                            echo "已退款<br/>¥" . number_format($refundAmount, 2);
                                        } elseif ($info['status'] == 3) {
                                            echo "已拒绝<br/>¥" . number_format($refundAmount, 2);
                                        } else {
                                            echo "已取消<br/>¥" . number_format($refundAmount, 2);
                                        }
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;">
                                    <?php
                                    if (empty($info['created_time'])) {
                                        // 未申请 - 显示申请规则
                                        if ($info['request'] == 1) {
                                            echo '产品首次';
                                        } elseif ($info['request'] == 2) {
                                            echo '同类产品首次';
                                        } elseif ($info['request'] == 3) {
                                            echo '指定时间内';
                                        } else {
                                            echo '-';
                                        }
                                    } else {
                                        // 已申请 - 显示退款规则
                                        if ($info['rules'] == 1) {
                                            echo '按时长退';
                                        } elseif ($info['rules'] == 2) {
                                            echo '按月退';
                                        } elseif ($info['rules'] == 3) {
                                            echo '全额退';
                                        } else {
                                            echo '-';
                                        }
                                    }
                                    ?>
                                </span>
                            </td>
                            <td>
                                {if $info.status == 1}
                                    <span class="badge badge-warning">等待审核</span>
                                {elseif $info.status == 2}
                                    <span class="badge badge-success">已退款</span>
                                {elseif $info.status == 3}
                                    <span class="badge badge-danger">已拒绝</span>
                                {elseif $info.status == 4}
                                    <span class="badge badge-secondary">已取消</span>
                                {else}
                                    <span class="badge badge-info">未申请</span>
                                {/if}
                            </td>
                            <td>
                                {if $info.status == 1}
                                    <button type="button" class="btn btn-warning btn-sm">
                                        <i class="fas fa-hourglass"></i> 等待
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" onclick="cancellation('{$info.id}')">
                                        <i class="fas fa-times"></i> 取消
                                    </button>
                                {elseif ($info.request == 1 || $info.request == 2 || $info.request == 3) && ($info.status == null || $info.status == 4)}
                                    {if $info.domainstatus == 'Deleted'}
                                        <span class="text-muted">已删除</span>
                                    {else}
                                        <?php
                                        $canApply = false;
                                        if ($info['within'] > 0) {
                                            $currentTime = time();
                                            $regTime = $info['regdate'];
                                            $timeDiff = $currentTime - $regTime;
                                            $hoursDiff = $timeDiff / 3600;
                                            if ($hoursDiff <= $info['within']) {
                                                $canApply = true;
                                            }
                                        } else {
                                            $canApply = true;
                                        }
                                        ?>

                                        {if $canApply}
                                            <button type="button" class="btn btn-primary btn-sm"
                                                    onclick="receive('{$info.productid}', '{$info.orderid}', '{$info.id}')">
                                                <i class="fas fa-reply"></i> 申请
                                            </button>
                                        {else}
                                            <button type="button" class="btn btn-secondary btn-sm" disabled>
                                                <i class="fas fa-clock"></i> 超时
                                            </button>
                                        {/if}
                                    {/if}
                                {else}
                                    <span class="text-muted">无</span>
                                {/if}
                            </td>
                        </tr>
                        {/volist}

                        {if empty($data)}
                        <tr>
                            <td colspan="12" class="text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">您还没有订购任何产品</p>
                            </td>
                        </tr>
                        {/if}
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mt-3">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap">
            <div>
                {$currentPage = $fen->currentPage()}
                {$totalPages = $fen->lastPage()}
                <span class="text-muted">当前页：{$currentPage} / 共 {$totalPages} 页</span>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <a href="/addons?_plugin=auto_refund&_controller=index&_action=index&page={$currentPage - 1}" class="btn btn-primary mr-2{if $currentPage <= 1} disabled{/if}">上一页</a>
                <div class="input-group input-group" style="width: auto;">
                    <div class="input-group-prepend">
                        <span class="input-group-text">到</span>
                    </div>
                    <input type="number" id="jumpToPage" min="1" max="{$totalPages}" value="{$currentPage + 1}" class="form-control">
                    <div class="input-group-append">
                        <span class="input-group-text">页</span>
                        <button onclick="jumpToPage()" class="btn btn-primary">跳转</button>
                    </div>
                </div>
                <a href="/addons?_plugin=auto_refund&_controller=index&_action=index&page={$currentPage + 1}" class="btn btn-primary ml-2{if $currentPage >= $totalPages} disabled{/if}">下一页</a>
            </div>
        </div>
    </div>
</div>

<script>
    function jumpToPage() {
        var jumpInput = document.getElementById('jumpToPage');
        var targetPage = parseInt(jumpInput.value);
        var totalPages = {$totalPages};
        if (targetPage >= 1 && targetPage <= totalPages) {
            var targetUrl = "/addons?_plugin=auto_refund&_controller=index&_action=index&page=" + targetPage + "&languagesys=CN";
            window.location.href = targetUrl;
        }
    }
</script>

<script>
/**
 * 产品退款插件 - 客户端退款申请页面 JS
 * 已还原为可读代码
 */

/**
 * 申请退款 - 弹出确认框
 * @param {string} productid - 产品ID
 * @param {string} orderid - 订单ID
 * @param {string} id - 主机ID
 */
function receive(productid, orderid, id) {
    layer.prompt({
        title: '请输入退款理由',
        formType: 2
    }, function(reason, index) {
        layer.close(index);
        layer.confirm('如果申请通过，退款金额将退至您在本站的账户余额中，确定要申请退款吗？', {
            btn: ['确定', '取消'],
            icon: 3
        }, function() {
            submitRefund(productid, orderid, id, reason);
        });
    });
}

/**
 * 提交退款申请
 * @param {string} productid - 产品ID
 * @param {string} orderid - 订单ID
 * @param {string} id - 主机ID
 * @param {string} reason - 退款原因
 */
function submitRefund(productid, orderid, id, reason) {
    var loadIndex = layer.load();
    
    $.ajax({
        url: '/addons?_plugin=auto_refund&_controller=index&_action=check',
        type: 'post',
        data: {
            productid: productid,
            orderid: orderid,
            id: id,
            reason: reason
        },
        dataType: 'json',
        success: function(response) {
            layer.close(loadIndex);
            if (response.code == 200) {
                layer.alert(response.msg, {icon: 1}, function() {
                    location.reload();
                });
            } else {
                layer.alert(response.msg, {icon: 2});
            }
        },
        error: function() {
            layer.close(loadIndex);
            layer.msg('请求失败，请重试');
        }
    });
}

/**
 * 取消退款申请
 * @param {string} id - 主机ID
 */
function cancellation(id) {
    layer.confirm('确定要取消退款申请吗？', {
        btn: ['确定', '取消'],
        icon: 3
    }, function() {
        var loadIndex = layer.load();
        
        $.ajax({
            url: '/addons?_plugin=auto_refund&_controller=index&_action=cancellation',
            type: 'post',
            data: {
                id: id
            },
            dataType: 'json',
            success: function(response) {
                layer.close(loadIndex);
                if (response.code == 200) {
                    layer.alert(response.msg, {icon: 1}, function() {
                        location.reload();
                    });
                } else {
                    layer.alert(response.msg, {icon: 2});
                }
            },
            error: function() {
                layer.close(loadIndex);
                layer.msg('请求失败，请重试');
            }
        });
    });
}
</script>
