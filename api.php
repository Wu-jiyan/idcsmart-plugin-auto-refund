<?php
/**
 * 插件间对接退款接收接口（独立文件，无需框架路由和登录认证）
 * 部署位置：/public/plugins/addons/auto_refund/ 目录下
 * 下游调用地址：https://上游域名/plugins/addons/auto_refund/api.php
 */

header('Content-Type: application/json; charset=utf-8');

$configPaths = [
    __DIR__ . '/../../../../app/config/database.php',
    __DIR__ . '/../../../../config/database.php',
    __DIR__ . '/../../../../application/database.php',
];

$dbConfig = null;
foreach ($configPaths as $path) {
    if (file_exists($path)) {
        $dbConfig = include $path;
        break;
    }
}

if (!$dbConfig) {
    exit(json_encode(['code' => 500, 'msg' => '无法加载数据库配置，尝试路径：' . implode(', ', $configPaths)]));
}

$dbHost   = $dbConfig['hostname'] ?? $dbConfig['host']   ?? '127.0.0.1';
$dbPort   = $dbConfig['hostport'] ?? $dbConfig['port']   ?? 3306;
$dbName   = $dbConfig['database'] ?? $dbConfig['dbname'] ?? '';
$dbUser   = $dbConfig['username'] ?? $dbConfig['user']   ?? 'root';
$dbPass   = $dbConfig['password'] ?? $dbConfig['pass']   ?? '';
$dbPrefix = $dbConfig['prefix']   ?? 'shd_';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    exit(json_encode(['code' => 500, 'msg' => '数据库连接失败: ' . $e->getMessage()]));
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $queryApiKey = trim($_GET['api_key'] ?? '');
    $queryHostId = intval($_GET['host_id'] ?? 0);

    if (empty($queryApiKey) || $queryHostId <= 0) {
        exit(json_encode(['code' => 400, 'msg' => '缺少必要参数（api_key、host_id）']));
    }

    $stmt = $pdo->prepare("SELECT * FROM `{$dbPrefix}product_refund_plugin_api` WHERE api_key = ? AND status = 1 LIMIT 1");
    $stmt->execute([$queryApiKey]);
    $apiCfg = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$apiCfg) {
        exit(json_encode(['code' => 401, 'msg' => 'API KEY 无效或已被禁用']));
    }

    $stmt = $pdo->prepare("SELECT id, status, reason, amount, audittime, reviewed, created_time FROM `{$dbPrefix}product_refund_list` WHERE hostid = ? AND type = '4' ORDER BY id DESC LIMIT 1");
    $stmt->execute([$queryHostId]);
    $refundRecord = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$refundRecord) {
        exit(json_encode(['code' => 404, 'msg' => '未找到该主机的退款申请记录']));
    }

    $statusMap = ['1' => '待审核', '2' => '已通过', '3' => '已拒绝', '4' => '已取消'];
    exit(json_encode([
        'code' => 200,
        'data' => [
            'id'         => $refundRecord['id'],
            'status'     => $refundRecord['status'],
            'status_text'=> $statusMap[$refundRecord['status']] ?? '未知',
            'amount'     => $refundRecord['amount'],
            'reason'     => $refundRecord['reason'],
            'reviewed'   => $refundRecord['reviewed'],
            'audittime'  => $refundRecord['audittime'],
            'created_time' => $refundRecord['created_time'],
        ]
    ]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['code' => 405, 'msg' => 'Method Not Allowed']));
}

$apiKey            = trim($_POST['api_key']             ?? '');
$userId            = trim($_POST['user_id']             ?? '');
$username          = trim($_POST['username']            ?? '');
$upstreamProductId = trim($_POST['upstream_product_id'] ?? '');
$amount            = floatval($_POST['amount']          ?? 0);
$reason            = trim($_POST['reason']              ?? '');
$productName       = trim($_POST['product_name']        ?? '');
$timestamp         = intval($_POST['timestamp']         ?? 0);

if (empty($apiKey) || empty($userId) || empty($upstreamProductId)) {
    exit(json_encode(['code' => 400, 'msg' => '缺少必要参数（api_key、user_id、upstream_product_id）']));
}

if ($timestamp === 0 || abs(time() - $timestamp) > 300) {
    exit(json_encode(['code' => 400, 'msg' => '请求已过期，请检查两端服务器时间是否同步（允许误差5分钟）']));
}

if ($amount <= 0) {
    exit(json_encode(['code' => 400, 'msg' => '退款金额必须大于0']));
}

$stmt = $pdo->prepare("SELECT * FROM `{$dbPrefix}product_refund_plugin_api` WHERE api_key = ? AND status = 1 LIMIT 1");
$stmt->execute([$apiKey]);
$apiConfig = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$apiConfig) {
    exit(json_encode(['code' => 401, 'msg' => 'API KEY 无效或已被禁用']));
}

$stmt = $pdo->prepare("SELECT username FROM `{$dbPrefix}clients` WHERE id = ? LIMIT 1");
$stmt->execute([$apiConfig['user_id']]);
$localUser = $stmt->fetch(PDO::FETCH_ASSOC);
$localUsername = $localUser ? $localUser['username'] : ('用户ID:' . $apiConfig['user_id']);

$stmt = $pdo->prepare("SELECT * FROM `{$dbPrefix}host` WHERE id = ? LIMIT 1");
$stmt->execute([$upstreamProductId]);
$hostInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$hostInfo) {
    exit(json_encode(['code' => 404, 'msg' => '未找到对应产品记录（upstream_product_id=' . $upstreamProductId . '）']));
}

$localHostId    = $hostInfo['id'];
$localProductId = $hostInfo['productid'];

$stmt = $pdo->prepare("SELECT * FROM `{$dbPrefix}orders` WHERE id = ? LIMIT 1");
$stmt->execute([$hostInfo['orderid']]);
$orderInfo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$orderInfo) {
    exit(json_encode(['code' => 404, 'msg' => '未找到对应订单记录（orderid=' . $hostInfo['orderid'] . '）']));
}

$invoiceId = $orderInfo['invoiceid'];

$stmt = $pdo->prepare("SELECT * FROM `{$dbPrefix}products` WHERE id = ? LIMIT 1");
$stmt->execute([$localProductId]);
$productInfo      = $stmt->fetch(PDO::FETCH_ASSOC);
$localProductName = $productInfo ? $productInfo['name'] : ($productName ?: '未知产品');

$stmt = $pdo->prepare("SELECT id FROM `{$dbPrefix}product_refund_list` WHERE hostid = ? AND status IN (1,2) LIMIT 1");
$stmt->execute([$localHostId]);
if ($stmt->fetch()) {
    exit(json_encode(['code' => 400, 'msg' => '该产品已有进行中的退款申请（hostid=' . $localHostId . '）']));
}

$stmt = $pdo->prepare("SELECT * FROM `{$dbPrefix}product_refund` WHERE productid = ? LIMIT 1");
$stmt->execute([$localProductId]);
$refundConfig = $stmt->fetch(PDO::FETCH_ASSOC);

$upstreamRefundResult = null;
$upstreamApiConfig = null;

if ($refundConfig && ($refundConfig['type'] == '3' || $refundConfig['type'] == '4') && !empty($refundConfig['api_config_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM `{$dbPrefix}product_refund_api_config` WHERE id = ? LIMIT 1");
    $stmt->execute([$refundConfig['api_config_id']]);
    $upstreamApiConfig = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($upstreamApiConfig) {
        $upstreamProductIdForApi = $hostInfo['dcimid'] ?: ($hostInfo['upstream_id'] ?? '');
        
        if (!empty($upstreamProductIdForApi)) {
            $apiKeyDecrypted = '';
            if (!empty($upstreamApiConfig['api_key'])) {
                $encryptedKey = $upstreamApiConfig['api_key'];
                $apiKeyDecrypted = decryptApiKey($encryptedKey, $dbPass);
                if (empty($apiKeyDecrypted)) {
                    $apiKeyDecrypted = $encryptedKey;
                }
            }
            
            if ($upstreamApiConfig['type'] == 'api') {
                $upstreamRefundResult = callUpstreamApiRefund($upstreamApiConfig, $apiKeyDecrypted, $upstreamProductIdForApi, $amount, $reason);
            } else {
                $upstreamRefundResult = callUpstreamPluginRefund($upstreamApiConfig, $apiKeyDecrypted, $upstreamProductIdForApi, $amount, $reason, $localUsername);
            }
        }
    }
}

$reasonFull = $reason . ' [下游用户ID:' . $userId . '，下游用户名:' . $username . '，上游产品ID:' . $upstreamProductId . ']';

$initialStatus = '1';
$reviewedBy = '';
$auditTime = 0;

if ($upstreamRefundResult && $upstreamRefundResult['success']) {
    if (isset($upstreamApiConfig['api_audit_type']) && $upstreamApiConfig['api_audit_type'] == 2) {
        $initialStatus = '2';
        $reviewedBy = 'System(UpstreamAuto)';
        $auditTime = time();
    }
    $reasonFull .= ' [上游调用:' . $upstreamRefundResult['msg'] . ']';
} elseif ($upstreamRefundResult) {
    $reasonFull .= ' [上游调用失败:' . $upstreamRefundResult['msg'] . ']';
}

$stmt = $pdo->prepare("
    INSERT INTO `{$dbPrefix}product_refund_list`
        (user_id, username, productid, productname, orderid, producttype,
         hostid, invoices, type, request, rules, amount, created_time, reasonrefund, status, reviewed, audittime)
    VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, '4', '3', '1', ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $apiConfig['user_id'],
    $localUsername,
    $localProductId,
    $localProductName,
    $hostInfo['orderid'],
    $productInfo ? $productInfo['type'] : 'server',
    $localHostId,
    $invoiceId,
    $amount,
    time(),
    $reasonFull,
    $initialStatus,
    $reviewedBy,
    $auditTime,
]);

$logDesc = '【插件间对接】收到下游退款申请 - 下游用户：' . $username . '（ID:' . $userId . '）'
    . '，上游用户：' . $localUsername . '（ID:' . $apiConfig['user_id'] . '）'
    . '，上游产品ID:' . $upstreamProductId
    . '，本地HostID:' . $localHostId . '，金额:' . $amount;
if ($upstreamRefundResult) {
    $logDesc .= ' [上游调用:' . ($upstreamRefundResult['success'] ? '成功' : '失败') . ' - ' . $upstreamRefundResult['msg'] . ']';
}
$stmt = $pdo->prepare("
    INSERT INTO `{$dbPrefix}activity_log`
        (create_time, description, user, uid, ipaddr, type, activeid, usertype, port, type_data_id)
    VALUES (?, ?, 'PluginAPI', ?, ?, '6', 0, 'System', '', 0)
");
$stmt->execute([time(), $logDesc, $apiConfig['user_id'], $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0']);

if ($initialStatus == '2') {
    $localUserId = $apiConfig['user_id'];

    $stmt = $pdo->prepare("SELECT credit FROM `{$dbPrefix}clients` WHERE id = ? LIMIT 1");
    $stmt->execute([$localUserId]);
    $clientRow = $stmt->fetch(PDO::FETCH_ASSOC);
    $oldCredit = floatval($clientRow['credit'] ?? 0);
    $newCredit = $oldCredit + $amount;

    $stmt = $pdo->prepare("UPDATE `{$dbPrefix}clients` SET credit = ? WHERE id = ?");
    $stmt->execute([$newCredit, $localUserId]);

    $stmt = $pdo->prepare("
        INSERT INTO `{$dbPrefix}accounts`
            (uid, currency, gateway, create_time, pay_time, description, amount_out, rate, invoice_id)
        VALUES (?, 'CNY', ?, ?, ?, ?, ?, '1.00000', ?)
    ");
    $stmt->execute([
        $localUserId,
        '退款至余额【主机ID：' . $localHostId . ' 】',
        time(), time(),
        '【插件间对接自动退款】下游用户ID:' . $userId . '，上游产品ID:' . $upstreamProductId,
        $amount,
        $invoiceId
    ]);

    $stmt = $pdo->prepare("
        INSERT INTO `{$dbPrefix}credit`
            (uid, create_time, description, amount, notes, balance)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $localUserId,
        time(),
        'Credit from Refund of Invoice ID ' . $invoiceId,
        $amount,
        '订单号：' . $hostInfo['orderid'] . '，首付金额：' . ($hostInfo['firstpaymentamount'] ?? '0') . '元，【插件间对接自动退款】下游用户ID:' . $userId . '【退款金额：' . $amount . ' 元】',
        $newCredit
    ]);

    $stmt = $pdo->prepare("UPDATE `{$dbPrefix}invoices` SET status = 'Refunded' WHERE id = ?");
    $stmt->execute([$invoiceId]);

    $updatetime = time();
    $stmt = $pdo->prepare("UPDATE `{$dbPrefix}host` SET nextduedate = ? WHERE id = ?");
    $stmt->execute([$updatetime, $localHostId]);

    $oldNotes = $hostInfo['notes'] ?? '';
    $newNotes = $oldNotes . "\n【插件间对接自动退款】下游用户ID:" . $userId . "\n原到期：" . date("Y-m-d H:i:s", $hostInfo['nextduedate']) . "\n更新到期：" . date("Y-m-d H:i:s", $updatetime);
    $stmt = $pdo->prepare("UPDATE `{$dbPrefix}host` SET notes = ? WHERE id = ?");
    $stmt->execute([$newNotes, $localHostId]);

    $stmt = $pdo->prepare("
        INSERT INTO `{$dbPrefix}activity_log`
            (create_time, description, user, uid, ipaddr, type, activeid, usertype, port, type_data_id)
        VALUES (?, ?, 'System', ?, ?, '6', 0, 'System', '', 0)
    ");
    $stmt->execute([
        time(),
        '【插件间对接自动退款】下游用户ID:' . $userId . '，Host ID:' . $localHostId . '，原到期：' . date("Y-m-d H:i:s", $hostInfo['nextduedate']) . '，更新到期：' . date("Y-m-d H:i:s", $updatetime),
        $localUserId,
        $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'
    ]);
}

exit(json_encode(['code' => 200, 'msg' => '退款申请提交成功' . ($upstreamRefundResult ? '（' . $upstreamRefundResult['msg'] . '）' : '')]));

function callUpstreamApiRefund($config, $apiKey, $upstreamProductId, $amount, $reason)
{
    $hostname = rtrim($config['hostname'], '/');
    
    $loginUrl = $hostname . '/v1/login_api';
    $loginData = json_encode(['account' => $config['username'], 'password' => $apiKey]);
    
    $ch = curl_init($loginUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $loginData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $loginResponse = curl_exec($ch);
    curl_close($ch);
    
    $loginResult = json_decode($loginResponse, true);
    if (empty($loginResult['jwt'])) {
        return ['success' => false, 'msg' => '上游登录失败:' . ($loginResult['msg'] ?? '未知错误')];
    }
    
    $ticketUrl = $hostname . '/submitticket?step=2&dptid=' . ($config['ticket_department_id'] ?: 2);
    $boundary = '----WebKitFormBoundary' . uniqid();
    
    $ticketTitle = str_replace(['{product_name}', '{host_id}'], [$config['name'] ?? '产品', $upstreamProductId], $config['ticket_title'] ?: '申请退款');
    $ticketContent = str_replace(['{product_name}', '{host_id}'], [$config['name'] ?? '产品', $upstreamProductId], $config['ticket_content'] ?: '申请产品无理由退款');
    
    $postData = [
        'dptid' => $config['ticket_department_id'] ?: 2,
        'hostid' => $upstreamProductId,
        'priority' => 'medium',
        'title' => $ticketTitle,
        'content' => $ticketContent . "\n\n[退款金额: {$amount}元，原因: {$reason}]"
    ];
    
    $body = '';
    foreach ($postData as $name => $value) {
        $body .= "--" . $boundary . "\r\n";
        $body .= "Content-Disposition: form-data; name=\"" . $name . "\"\r\n\r\n";
        $body .= $value . "\r\n";
    }
    $body .= "--" . $boundary . "\r\n";
    $body .= "Content-Disposition: form-data; name=\"attachments[]\"; filename=\"\"\r\n";
    $body .= "Content-Type: application/octet-stream\r\n\r\n\r\n";
    $body .= "--" . $boundary . "--\r\n";
    
    $headers = [
        'Content-Type: multipart/form-data; boundary=' . $boundary,
        'Origin: ' . $hostname,
        'Referer: ' . $hostname . '/submitticket?step=2&dptid=' . ($config['ticket_department_id'] ?: 2),
        'Authorization: Bearer ' . $loginResult['jwt'],
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ];
    
    $ch = curl_init($ticketUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $ticketResponse = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);
    
    if ($httpCode == 302 && strpos($redirectUrl, 'supporttickets') !== false) {
        return ['success' => true, 'msg' => '上游工单提交成功'];
    }
    
    return ['success' => false, 'msg' => '上游工单提交失败(HTTP:' . $httpCode . ')'];
}

function callUpstreamPluginRefund($config, $apiKey, $upstreamProductId, $amount, $reason, $username)
{
    $hostname = rtrim($config['hostname'], '/');
    $url = $hostname . '/plugins/addons/auto_refund/api.php';
    
    $requestData = [
        'api_key' => $apiKey,
        'user_id' => 0,
        'username' => $username,
        'upstream_product_id' => $upstreamProductId,
        'amount' => $amount,
        'reason' => $reason,
        'product_name' => '',
        'timestamp' => time()
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($requestData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode != 200) {
        return ['success' => false, 'msg' => '上游接口返回HTTP:' . $httpCode];
    }
    
    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['success' => false, 'msg' => '上游返回格式错误'];
    }
    
    if (isset($result['code']) && $result['code'] == 200) {
        return ['success' => true, 'msg' => $result['msg'] ?? '上游处理成功'];
    } else {
        return ['success' => false, 'msg' => $result['msg'] ?? '上游处理失败'];
    }
}

function decryptApiKey($data, $dbPassword)
{
    if (empty($data)) {
        return '';
    }
    
    $key = hash('sha256', $dbPassword, true);
    
    $decoded = base64_decode($data);
    if ($decoded === false || strlen($decoded) < 16) {
        return '';
    }
    
    $iv = substr($decoded, 0, 16);
    $encrypted = substr($decoded, 16);
    
    $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    
    if ($decrypted === false) {
        return '';
    }
    
    return $decrypted;
}