<?php
namespace addons\auto_refund\lib;

/**
 * API客户端工具类
 * 用于调用上游API接口
 */
class ApiClient
{
    /**
     * 登录获取JWT令牌
     * @param string $hostname 上游API地址
     * @param string $username 用户名
     * @param string $apiKey API密钥
     * @return array ['success' => bool, 'jwt' => string, 'msg' => string]
     */
    public static function login($hostname, $username, $apiKey)
    {
        $url = rtrim($hostname, '/') . '/v1/login_api';
        
        $data = [
            'account' => $username,
            'password' => $apiKey
        ];
        
        $result = self::httpPost($url, $data);
        
        // API返回格式：{"jwt":"...","status":200,"msg":"login successful"}
        // JWT在根级别，不是data里面
        if (!empty($result['jwt'])) {
            return [
                'success' => true,
                'jwt' => $result['jwt'],
                'msg' => $result['msg'] ?: '登录成功'
            ];
        }
        
        return [
            'success' => false,
            'jwt' => '',
            'msg' => $result['msg'] ?: '登录失败'
        ];
    }
    
    /**
     * 提交工单
     * @param string $hostname 上游API地址
     * @param string $jwt JWT令牌
     * @param array $ticketData 工单数据
     * @return array ['success' => bool, 'ticket_id' => int, 'msg' => string]
     */
    public static function createTicket($hostname, $jwt, $ticketData)
    {
        $url = rtrim($hostname, '/') . '/submitticket?step=2&dptid=' . $ticketData['department_id'];
        
        // 使用multipart/form-data格式提交工单
        $boundary = '----WebKitFormBoundary' . uniqid();
        
        // 构建表单数据
        $postData = [
            'dptid' => $ticketData['department_id'],
            'hostid' => $ticketData['host_id'] ?? '',
            'priority' => 'medium',
            'title' => $ticketData['title'],
            'content' => $ticketData['content']
        ];
        
        $body = '';
        foreach ($postData as $name => $value) {
            $body .= "--" . $boundary . "\r\n";
            $body .= "Content-Disposition: form-data; name=\"" . $name . "\"\r\n\r\n";
            $body .= $value . "\r\n";
        }
        
        // 添加附件字段（空）
        $body .= "--" . $boundary . "\r\n";
        $body .= "Content-Disposition: form-data; name=\"attachments[]\"; filename=\"\"\r\n";
        $body .= "Content-Type: application/octet-stream\r\n\r\n\r\n";
        
        $body .= "--" . $boundary . "--\r\n";
        
        $headers = [
            'Content-Type: multipart/form-data; boundary=' . $boundary,
            'Origin: ' . $hostname,
            'Referer: ' . $hostname . '/submitticket?step=2&dptid=' . $ticketData['department_id'],
            'Authorization: Bearer ' . $jwt,
            'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36 Edg/145.0.0.0',
            'Upgrade-Insecure-Requests: 1'
        ];
        
        $result = self::httpPostRaw($url, $body, $headers);
        
        // 检查是否重定向到supporttickets页面（表示成功）
        if ($result['http_code'] == 302 && strpos($result['redirect_url'], 'supporttickets') !== false) {
            return [
                'success' => true,
                'ticket_id' => 0, // 前端接口不返回ticket_id
                'msg' => '工单提交成功'
            ];
        }
        
        // 检查是否200且页面包含成功信息
        if ($result['http_code'] == 200) {
            if (strpos($result['response'], '工单提交成功') !== false || 
                strpos($result['response'], 'supporttickets') !== false) {
                return [
                    'success' => true,
                    'ticket_id' => 0,
                    'msg' => '工单提交成功'
                ];
            }
        }
        
        return [
            'success' => false,
            'ticket_id' => 0,
            'msg' => '工单提交失败 (HTTP: ' . $result['http_code'] . ')'
        ];
    }
    
    /**
     * 发送HTTP POST请求（JSON格式）
     * @param string $url 请求地址
     * @param array $data 请求数据
     * @param array $headers 请求头
     * @return array ['code' => int, 'data' => array, 'msg' => string]
     */
    private static function httpPost($url, $data, $headers = [])
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        
        // 如果没有传入headers，使用默认的Content-Type
        if (empty($headers)) {
            $headers = ['Content-Type: application/json'];
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'code' => 500,
                'data' => [],
                'msg' => '请求错误：' . $error
            ];
        }
        
        $result = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'code' => $httpCode,
                'data' => [],
                'msg' => '响应解析失败：' . substr($response, 0, 200)
            ];
        }
        
        return [
            'code' => $result['code'] ?? $result['status'] ?? $httpCode,
            'data' => $result['data'] ?? [],
            'msg' => $result['msg'] ?? '',
            'jwt' => $result['jwt'] ?? ''  // 保留jwt字段
        ];
    }
    
    /**
     * 发送HTTP POST请求（原始数据格式）
     * @param string $url 请求地址
     * @param string $body 请求体
     * @param array $headers 请求头
     * @return array ['http_code' => int, 'response' => string, 'redirect_url' => string]
     */
    private static function httpPostRaw($url, $body, $headers = [])
    {
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false); // 不跟随重定向，以便获取重定向URL
        curl_setopt($ch, CURLOPT_HEADER, true); // 获取响应头
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $redirectUrl = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'http_code' => 500,
                'response' => '',
                'redirect_url' => '',
                'error' => $error
            ];
        }
        
        // 分离响应头和响应体
        $parts = explode("\r\n\r\n", $response, 2);
        $responseBody = $parts[1] ?? $response;
        
        return [
            'http_code' => $httpCode,
            'response' => $responseBody,
            'redirect_url' => $redirectUrl,
            'error' => ''
        ];
    }
}
