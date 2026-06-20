<?php
namespace addons\auto_refund\controller;

class AdminIndexController extends \app\admin\controller\PluginAdminBaseController
{
    public function products()
    {
        $page = input("page", 1);
        $pageSize = input("pageSize", 20);
        $search = input("search", "");
        
        // 限制分页大小范围
        $pageSize = intval($pageSize);
        if ($pageSize < 1) {
            $pageSize = 20;
        } elseif ($pageSize > 200) {
            $pageSize = 200;
        }
        
        // 构建查询
        $query = \Think\Db::name("product_refund");
        
        // 如果有搜索关键词，进行搜索
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where("productid", "like", "%" . $search . "%")
                  ->whereOr("id", "like", "%" . $search . "%");
            });
        }
        
        $lists = $query->order("id", "desc")->paginate($pageSize, false, ["page" => $page]);
        $resultWithNames = [];
        foreach ($lists as $record) {
            $productid = $record["productid"];
            $hostid = $record["id"];
            $name = \Think\Db::name("products")->where("id", $productid)->find();
            $name1 = \Think\Db::name("products")->where(["id" => $productid, "api_type" => "zjmf_api"])->find();
            $zjmf_finance_api = \Think\Db::name("zjmf_finance_api")->where(["id" => $name1["server_group"]])->find();
            if ($name !== NULL) {
                $record["name"] = $name["name"];
                $record["hosttype"] = $name["type"];
                $record["api_name"] = $zjmf_finance_api["name"];
                $record["hidden"] = $name["hidden"];
                $resultWithNames[] = $record;
            }
        }
        
        // 如果有搜索，需要过滤结果
        if (!empty($search)) {
            $resultWithNames = array_filter($resultWithNames, function($item) use ($search) {
                return strpos(strval($item["id"]), $search) !== false || 
                       strpos(strval($item["productid"]), $search) !== false ||
                       (isset($item["name"]) && strpos($item["name"], $search) !== false);
            });
        }
        
        $this->assign("Title", "产品列表");
        $this->assign("fen", $lists);
        $this->assign("pageSize", $pageSize);
        $this->assign("search", $search);
        return $this->fetch("/products", ["data" => $resultWithNames]);
    }
    
    public function batchDelete()
    {
        if (request()->isPost()) {
            $ids = input("post.ids/a");
            if (empty($ids) || !is_array($ids)) {
                return json(["code" => 400, "msg" => "请选择要删除的项目"]);
            }
            
            // 批量删除
            $result = \Think\Db::name("product_refund")->whereIn("id", $ids)->delete();
            if ($result !== false) {
                return json(["code" => 200, "msg" => "成功删除 " . $result . " 项"]);
            }
            return json(["code" => 500, "msg" => "删除失败"]);
        }
        return json(["code" => 405, "msg" => "请求方式错误"]);
    }
    public function deletelists()
    {
        if (request()->isPost()) {
            $id = input("post.id");
            $result = \Think\Db::name("product_refund")->where("id", $id)->delete();
            if ($result !== false) {
                return json(["code" => 200, "msg" => "活动删除成功"]);
            }
            return json(["code" => 500, "msg" => "活动删除失败"]);
        }
    }
    public function setting()
    {
        $configurationsModel = \Think\Db::name("product_refund_setting");
        $configData = $configurationsModel->find();
        $isMismatch = false;
        $mismatchMessage = "恭喜您，授权成功";
        $this->assign("Title", "功能设置");
        $this->assign("Data", $configData);
        $this->assign("IsMismatch", $isMismatch);
        $this->assign("MismatchMessage", $mismatchMessage);
        return $this->fetch("/setting");
    }
    public function submit()
    {
        $data = $this->request->post();
        $zzemail = isset($data["zzemail"]) ? $data["zzemail"] : NULL;
        $zzqq = isset($data["zzqq"]) ? $data["zzqq"] : NULL;
        $mfauth = isset($data["mfauth"]) ? $data["mfauth"] : NULL;
        $dailiday = isset($data["day"]) ? $data["day"] : NULL;
        $displaytime = isset($data["displaytime"]) ? $data["displaytime"] : NULL;
        $feishuurl = isset($data["feishuurl"]) ? $data["feishuurl"] : NULL;
        $dingurl = isset($data["dingurl"]) ? $data["dingurl"] : NULL;
        $wechaturl = isset($data["wechaturl"]) ? $data["wechaturl"] : NULL;
        $webname = isset($data["webname"]) ? $data["webname"] : NULL;
        $feishuswitch = isset($data["feishuswitch"]) ? $data["feishuswitch"] : NULL;
        $dingswitch = isset($data["dingswitch"]) ? $data["dingswitch"] : NULL;
        $wechatswitch = isset($data["wechatswitch"]) ? $data["wechatswitch"] : NULL;
        $tgswitch = isset($data["tgswitch"]) ? $data["tgswitch"] : NULL;
        $tgtoken = isset($data["tgtoken"]) ? $data["tgtoken"] : NULL;
        $tgchatid = isset($data["tgchatid"]) ? $data["tgchatid"] : NULL;
        $agent = isset($data["agent"]) ? $data["agent"] : NULL;
        $open = isset($data["open"]) ? $data["open"] : NULL;
        if (!isset($data["day"]) || $data["day"] < 0) {
            if (empty($data["day"])) {
                $msg = "代理可退时间不能为空。";
            } else {
                $msg = "代理可退时间必须大于等于0。";
            }
            $response = ["code" => 400, "msg" => $msg];
            return json($response);
        }
        if (!isset($data["displaytime"]) || $data["displaytime"] < 1) {
            if (empty($data["displaytime"])) {
                $msg = "过期订单显示时间不能为空，单位为天。";
            } else {
                $msg = "过期订单显示时间必须大于1。";
            }
            $response = ["code" => 400, "msg" => $msg];
            return json($response);
        }
        $dbConfig = ["webname" => $webname, "zzemail" => $zzemail, "zzqq" => $zzqq, "day" => $dailiday, "displaytime" => $displaytime, "mfauth" => $mfauth, "feishuurl" => $feishuurl, "dingurl" => $dingurl, "wechaturl" => $wechaturl, "feishuswitch" => $feishuswitch, "dingswitch" => $dingswitch, "wechatswitch" => $wechatswitch, "tgswitch" => $tgswitch, "tgtoken" => $tgtoken, "tgchatid" => $tgchatid, "agent" => $agent, "open" => $open, "weburl" => $_SERVER["HTTP_HOST"]];
        $result = \Think\Db::name("product_refund_setting")->where("id", 1)->find();
        if ($result) {
            $updateResult = \Think\Db::name("product_refund_setting")->where("id", 1)->update($dbConfig);
            if ($updateResult !== false) {
                $response = ["code" => 200, "msg" => "设置已成功更新。"];
            } else {
                $response = ["code" => 500, "msg" => "更新设置时出错。"];
            }
        } else {
            $dbConfig["id"] = 1;
            $insertResult = \Think\Db::name("product_refund_setting")->insert($dbConfig);
            if ($insertResult !== false) {
                $response = ["code" => 200, "msg" => "设置首次配置已成功。"];
            } else {
                $response = ["code" => 500, "msg" => "设置首次配置时出错。"];
            }
        }
        return json($response);
    }
    public function index()
    {
        $products = \Think\Db::name("products")->alias("p")
            ->field("p.id, p.gid, p.name, p.server_group, p.hidden as product_hidden, g.name as group_name, g.hidden as group_hidden")
            ->join("product_groups g", "p.gid = g.id", "LEFT")
            ->order("p.gid ASC")
            ->order("p.id ASC")
            ->select();
        $filteredProducts = [];
        foreach ($products as $key => $product) {
            $fanProducts = \Think\Db::name("product_refund")->where("productid", $product["id"])->find();
            $name1 = \Think\Db::name("products")->where(["id" => $product["id"], "api_type" => "zjmf_api"])->find();
            $zjmf_finance_api = \Think\Db::name("zjmf_finance_api")->where(["id" => $name1["server_group"]])->find();
            if (!$fanProducts) {
                $product["api_name"] = $zjmf_finance_api["name"];
                $product["api_id"] = $name1["server_group"];
                $product["hidden"] = $product["product_hidden"];
                $product["group_hidden"] = $product["group_hidden"];
                $filteredProducts[] = $product;
            }
        }
        $this->assign("keproducts", $filteredProducts);
        $this->assign("Title", "添加产品");
        return $this->fetch("/index");
    }
    public function submitActivitys()
    {
        try {
            $data = $this->request->post();
            
            // 调试日志
            trace("退款配置提交数据：" . json_encode($data), "info");
            
            $productids = isset($data["selected_products"]) ? $data["selected_products"] : NULL;
            $type = isset($data["type"]) ? $data["type"] : NULL;
            $request = isset($data["request"]) ? $data["request"] : NULL;
            $within = isset($data["within"]) ? $data["within"] : NULL;
            $rules = isset($data["rules"]) ? $data["rules"] : NULL;
            $created_time = isset($data["created_time"]) ? $data["created_time"] : NULL;
            
            // API退款相关参数（新的方式：使用api_config_id）
            $api_config_id = isset($data["api_config_id"]) ? intval($data["api_config_id"]) : 0;
            
            // 验证基本参数
            if (empty($type)) {
                return json(["code" => 400, "msg" => "请选择退款方式。"]);
            }
            
            // API退款类型和插件间对接类型额外验证
            if ($type == '3' || $type == '4') {
                if (empty($api_config_id)) {
                    return json(["code" => 400, "msg" => "API退款/插件间对接方式需要选择上游配置。"]);
                }
                // 验证配置是否存在
                $apiConfig = \Think\Db::name("product_refund_api_config")
                    ->where("id", $api_config_id)
                    ->find();
                if (!$apiConfig) {
                    return json(["code" => 400, "msg" => "选择的配置不存在或已被删除。"]);
                }
            }
            
            // 支持单个或多个产品（处理数组或逗号分隔的字符串）
            if (is_string($productids) && strpos($productids, ',') !== false) {
                $productids = explode(',', $productids);
            } elseif (!is_array($productids)) {
                $productids = [$productids];
            }
            
            // 过滤空值
            $productids = array_filter($productids, function($v) {
                return !empty($v);
            });
            
            if (empty($productids)) {
                return json(["code" => 400, "msg" => "请选择至少一个产品。"]);
            }
            
            if (empty($data["within"]) || $data["within"] <= 1) {
                return json(["code" => 400, "msg" => "请输入支持的退款时间，单位为小时，且必须大于1。"]);
            }
            
            // 加载加密工具类
            $encryptUtilLoaded = false;
            $libPath = __DIR__ . "/../lib/EncryptUtil.php";
            if (file_exists($libPath)) {
                require_once $libPath;
                $encryptUtilLoaded = class_exists('addons\auto_refund\lib\EncryptUtil');
                trace("EncryptUtil加载成功，路径：" . $libPath, "info");
            } else {
                trace("EncryptUtil文件不存在，路径：" . $libPath, "error");
                // 尝试使用ADDON_PATH
                if (defined('ADDON_PATH')) {
                    $addonLibPath = ADDON_PATH . "auto_refund" . DS . "lib" . DS . "EncryptUtil.php";
                    if (file_exists($addonLibPath)) {
                        require_once $addonLibPath;
                        $encryptUtilLoaded = class_exists('addons\auto_refund\lib\EncryptUtil');
                        trace("EncryptUtil从ADDON_PATH加载成功", "info");
                    } else {
                        trace("EncryptUtil从ADDON_PATH加载失败，路径：" . $addonLibPath, "error");
                    }
                } else {
                    // 手动定义路径
                    $addonPath = dirname(__DIR__) . "/";
                    $manualLibPath = $addonPath . "lib/EncryptUtil.php";
                    if (file_exists($manualLibPath)) {
                        require_once $manualLibPath;
                        $encryptUtilLoaded = class_exists('addons\auto_refund\lib\EncryptUtil');
                        trace("EncryptUtil从手动路径加载成功", "info");
                    } else {
                        trace("EncryptUtil从手动路径加载失败，路径：" . $manualLibPath, "error");
                    }
                }
            }
            
            $successCount = 0;
            $failCount = 0;
            $existCount = 0;
            $errorDetails = [];
            
            foreach ($productids as $productid) {
                if (empty($productid)) {
                    continue;
                }
                
                try {
                    $producttype = \Think\Db::name("products")->where("id", $productid)->value("type");
                    $productresult = \Think\Db::name("product_refund")->where(["productid" => $productid])->find();
                    
                    if ($productresult) {
                        $existCount++;
                        continue;
                    }
                    
                    $dbData = [
                        "productid" => $productid, 
                        "producttype" => $producttype ?: '', 
                        "type" => $type, 
                        "request" => $request, 
                        "within" => $within, 
                        "rules" => $rules, 
                        "created_time" => time()
                    ];
                    
                    // API退款类型和插件间对接类型存储api_config_id
                    if ($type == '3' || $type == '4') {
                        $dbData["api_config_id"] = $api_config_id;
                    }
                    
                    // 过滤NULL值，但保留空字符串
                    $dbData = array_filter($dbData, function ($value) {
                        return $value !== NULL;
                    });
                    
                    trace("准备插入数据，产品ID：" . $productid . "，数据：" . json_encode($dbData), "info");
                    
                    // 确保所有必要字段都有值
                    if (empty($dbData["productid"])) {
                        $failCount++;
                        $errorDetails[] = "产品ID为空";
                        continue;
                    }
                    
                    $result = \Think\Db::name("product_refund")->insert($dbData);
                    if ($result) {
                        $successCount++;
                        trace("产品配置插入成功，产品ID：" . $productid, "info");
                    } else {
                        $failCount++;
                        $errorDetails[] = "产品ID " . $productid . " 插入失败，数据库返回false";
                        trace("产品配置插入失败，产品ID：" . $productid . "，数据库返回false", "error");
                    }
                } catch (\Exception $e) {
                    $failCount++;
                    $errorMsg = "产品ID " . $productid . " 异常：" . $e->getMessage();
                    $errorDetails[] = $errorMsg;
                    trace("退款配置插入异常：" . $errorMsg, "error");
                    trace("异常堆栈：" . $e->getTraceAsString(), "error");
                }
            }
            
            // 构建响应消息
            if ($successCount > 0 && $failCount == 0 && $existCount == 0) {
                $response = ["code" => 200, "msg" => "成功添加 " . $successCount . " 个产品配置。"];
            } elseif ($successCount > 0) {
                $msg = "成功添加 " . $successCount . " 个产品配置。";
                if ($existCount > 0) {
                    $msg .= " " . $existCount . " 个产品已存在。";
                }
                if ($failCount > 0) {
                    $msg .= " " . $failCount . " 个产品添加失败。";
                    if (!empty($errorDetails)) {
                        $msg .= " 错误详情：" . implode("; ", array_slice($errorDetails, 0, 3));
                    }
                }
                $response = ["code" => 200, "msg" => $msg];
            } elseif ($existCount > 0 && $failCount == 0) {
                $response = ["code" => 400, "msg" => "选择的 " . $existCount . " 个产品已存在列表中。"];
            } else {
                $msg = "添加失败，请重试。";
                if (!empty($errorDetails)) {
                    $msg .= " 错误详情：" . implode("; ", array_slice($errorDetails, 0, 3));
                }
                $response = ["code" => 400, "msg" => $msg];
            }
            
            trace("退款配置提交结果：" . json_encode($response), "info");
            return json($response);
            
        } catch (\Exception $e) {
            $errorMsg = "系统异常：" . $e->getMessage();
            trace("submitActivitys方法异常：" . $errorMsg, "error");
            trace("异常堆栈：" . $e->getTraceAsString(), "error");
            return json(["code" => 500, "msg" => $errorMsg]);
        }
    }
    public function upgrade()
    {
        try {
            $tableName = 'shd_product_refund';
            
            // 检查表是否存在
            $tableExists = \Think\Db::query("SHOW TABLES LIKE '{$tableName}'");
            if (empty($tableExists)) {
                return json(["code" => 400, "msg" => "表 {$tableName} 不存在"]);
            }
            
            // 获取现有字段
            $columns = \Think\Db::query("SHOW COLUMNS FROM {$tableName}");
            $existingFields = array_column($columns, 'Field');
            
            // 需要添加的字段
            $fieldsToAdd = [
                'api_refund' => "TINYINT DEFAULT 0 COMMENT '是否API退款'",
                'api_hostname' => "VARCHAR(255) DEFAULT '' COMMENT 'API主机地址'",
                'api_username' => "VARCHAR(255) DEFAULT '' COMMENT 'API用户名'",
                'api_key' => "TEXT COMMENT 'API密钥(加密存储)'",
                'api_audit_type' => "TINYINT DEFAULT 1 COMMENT 'API审核类型：1人工审核，2自动入账'",
                'ticket_department_id' => "INT DEFAULT 2 COMMENT '工单部门ID'",
                'ticket_title' => "VARCHAR(255) DEFAULT '申请退款' COMMENT '工单标题'",
                'ticket_content' => "TEXT COMMENT '工单内容'",
            ];
            
            $addedCount = 0;
            $existingCount = 0;
            
            foreach ($fieldsToAdd as $fieldName => $fieldDef) {
                if (!in_array($fieldName, $existingFields)) {
                    $sql = "ALTER TABLE {$tableName} ADD COLUMN {$fieldName} {$fieldDef}";
                    \Think\Db::execute($sql);
                    $addedCount++;
                } else {
                    $existingCount++;
                }
            }
            
            return json([
                "code" => 200, 
                "msg" => "升级完成！新增 {$addedCount} 个字段，已存在 {$existingCount} 个字段"
            ]);
            
        } catch (\Exception $e) {
            return json([
                "code" => 500, 
                "msg" => "升级失败: " . $e->getMessage()
            ]);
        }
    }
    
    /**
     * 数据迁移：将旧版API配置迁移到新的表结构
     * 访问地址：/addons?_plugin=auto_refund&_controller=admin_index&_action=migrate
     */
    public function migrate()
    {
        try {
            $output = [];
            $output[] = "=== 开始数据迁移 ===";
            
            // 检查新表是否存在
            $tableExists = \Think\Db::query("SHOW TABLES LIKE 'shd_product_refund_api_config'");
            if (empty($tableExists)) {
                return json([
                    "code" => 400, 
                    "msg" => "错误：新表 shd_product_refund_api_config 不存在，请先安装或升级插件"
                ]);
            }
            
            // 检查旧表是否有API配置数据
            $columns = \Think\Db::query("SHOW COLUMNS FROM shd_product_refund");
            $existingFields = array_column($columns, 'Field');
            
            $hasOldApiFields = in_array('api_hostname', $existingFields) && 
                               in_array('api_username', $existingFields) &&
                               in_array('api_key', $existingFields);
            
            if (!$hasOldApiFields) {
                $output[] = "提示：旧表中没有找到API配置字段，检查 api_config_id 字段...";
                
                if (!in_array('api_config_id', $existingFields)) {
                    return json([
                        "code" => 400, 
                        "msg" => "错误：api_config_id 字段也不存在，请检查数据库结构"
                    ]);
                }
                
                return json([
                    "code" => 200, 
                    "msg" => "已经是新结构，无需迁移",
                    "detail" => implode("\n", $output)
                ]);
            }
            
            $output[] = "步骤1：查询需要迁移的API退款配置...";
            
            // 查询所有有API配置的产品
            $apiProducts = \Think\Db::name("product_refund")
                ->where("type", '3')
                ->where("api_hostname", '<>', '')
                ->where("api_key", '<>', '')
                ->select();
            
            if (empty($apiProducts)) {
                return json([
                    "code" => 200, 
                    "msg" => "没有找到需要迁移的API配置数据",
                    "detail" => implode("\n", $output)
                ]);
            }
            
            $output[] = "找到 " . count($apiProducts) . " 条需要迁移的配置";
            
            // 按上游分组
            $upstreamGroups = [];
            foreach ($apiProducts as $product) {
                $key = md5($product['api_hostname'] . $product['api_username']);
                if (!isset($upstreamGroups[$key])) {
                    $upstreamGroups[$key] = [
                        'hostname' => $product['api_hostname'],
                        'username' => $product['api_username'],
                        'api_key' => $product['api_key'],
                        'api_audit_type' => $product['api_audit_type'] ?: 1,
                        'ticket_department_id' => $product['ticket_department_id'] ?: 2,
                        'ticket_title' => $product['ticket_title'] ?: '申请退款',
                        'ticket_content' => $product['ticket_content'] ?: '申请产品无理由退款',
                        'products' => []
                    ];
                }
                $upstreamGroups[$key]['products'][] = $product;
            }
            
            $output[] = "步骤2：创建上游API配置...";
            
            $configMap = []; // 存储配置ID映射
            $createdCount = 0;
            
            foreach ($upstreamGroups as $key => $group) {
                // 检查是否已存在相同的配置
                $existingConfig = \Think\Db::name("product_refund_api_config")
                    ->where("hostname", $group['hostname'])
                    ->where("username", $group['username'])
                    ->find();
                
                if ($existingConfig) {
                    $output[] = "  - 配置已存在: {$group['hostname']} ({$group['username']}) -> ID: {$existingConfig['id']}";
                    $configMap[$key] = $existingConfig['id'];
                } else {
                    // 创建新配置
                    $configData = [
                        'name' => '上游配置-' . ($createdCount + 1),
                        'hostname' => $group['hostname'],
                        'username' => $group['username'],
                        'api_key' => $group['api_key'],
                        'api_audit_type' => $group['api_audit_type'],
                        'ticket_department_id' => $group['ticket_department_id'],
                        'ticket_title' => $group['ticket_title'],
                        'ticket_content' => $group['ticket_content'],
                        'created_at' => time(),
                        'updated_at' => time()
                    ];
                    
                    $configId = \Think\Db::name("product_refund_api_config")
                        ->insertGetId($configData);
                    
                    $output[] = "  - 创建配置: {$group['hostname']} ({$group['username']}) -> ID: {$configId}";
                    $configMap[$key] = $configId;
                    $createdCount++;
                }
            }
            
            $output[] = "步骤3：更新产品配置...";
            
            $updatedCount = 0;
            foreach ($upstreamGroups as $key => $group) {
                $configId = $configMap[$key];
                
                foreach ($group['products'] as $product) {
                    \Think\Db::name("product_refund")
                        ->where("id", $product['id'])
                        ->update(['api_config_id' => $configId]);
                    
                    $output[] = "  - 更新产品ID: {$product['productid']} -> 配置ID: {$configId}";
                    $updatedCount++;
                }
            }
            
            $output[] = "=== 迁移完成 ===";
            $output[] = "创建配置数: {$createdCount}";
            $output[] = "更新产品数: {$updatedCount}";
            
            return json([
                "code" => 200, 
                "msg" => "迁移完成！创建配置数: {$createdCount}, 更新产品数: {$updatedCount}",
                "detail" => implode("\n", $output)
            ]);
            
        } catch (\Exception $e) {
            return json([
                "code" => 500, 
                "msg" => "迁移失败: " . $e->getMessage(),
                "detail" => $e->getTraceAsString()
            ]);
        }
    }
    
    /**
     * 上游API配置列表
     */
    public function apiConfig()
    {
        $page = input("page", 1);
        $pageSize = input("pageSize", 20);
        
        $lists = \Think\Db::name("product_refund_api_config")
            ->order("id", "desc")
            ->paginate($pageSize, false, ["page" => $page]);
        
        $this->assign("Title", "上游API配置");
        $this->assign("data", $lists);
        return $this->fetch("/api_config");
    }
    
    /**
     * 保存上游API配置
     */
    public function saveApiConfig()
    {
        try {
            $data = $this->request->post();
            
            $id = isset($data["id"]) ? intval($data["id"]) : 0;
            $type = isset($data["type"]) ? trim($data["type"]) : "api";
            $name = isset($data["name"]) ? trim($data["name"]) : "";
            $hostname = isset($data["hostname"]) ? trim($data["hostname"]) : "";
            $username = isset($data["username"]) ? trim($data["username"]) : "";
            $api_key = isset($data["api_key"]) ? $data["api_key"] : "";
            $api_audit_type = isset($data["api_audit_type"]) ? intval($data["api_audit_type"]) : 1;
            $ticket_department_id = isset($data["ticket_department_id"]) ? intval($data["ticket_department_id"]) : 2;
            $ticket_title = isset($data["ticket_title"]) ? trim($data["ticket_title"]) : "申请退款";
            $ticket_content = isset($data["ticket_content"]) ? trim($data["ticket_content"]) : "申请产品无理由退款";
            
            // 验证
            if (empty($name)) {
                return json(["code" => 400, "msg" => "配置名称不能为空"]);
            }
            if (empty($hostname)) {
                return json(["code" => 400, "msg" => $type == 'api' ? "API主机地址不能为空" : "上游插件地址不能为空"]);
            }
            if ($type == 'api' && empty($username)) {
                return json(["code" => 400, "msg" => "API用户名不能为空"]);
            }
            if (empty($api_key) && $id == 0) {
                return json(["code" => 400, "msg" => $type == 'api' ? "API密钥不能为空" : "API KEY不能为空"]);
            }
            
            // 加载加密工具类
            $libPath = __DIR__ . "/../lib/EncryptUtil.php";
            if (file_exists($libPath)) {
                require_once $libPath;
            }
            $encryptUtilLoaded = class_exists('addons\auto_refund\lib\EncryptUtil');
            
            $dbData = [
                "type" => $type,
                "name" => $name,
                "hostname" => $hostname,
                "username" => $username,
                "api_audit_type" => $api_audit_type,
                "ticket_department_id" => $ticket_department_id,
                "ticket_title" => $ticket_title,
                "ticket_content" => $ticket_content,
            ];
            
            // 加密存储API KEY
            if (!empty($api_key)) {
                if ($encryptUtilLoaded) {
                    $dbData["api_key"] = \addons\auto_refund\lib\EncryptUtil::encrypt($api_key);
                } else {
                    $dbData["api_key"] = base64_encode($api_key);
                }
            }
            
            if ($id > 0) {
                // 更新
                if (empty($api_key)) {
                    // 如果不填写API KEY，保留原值
                    unset($dbData["api_key"]);
                }
                $dbData["updated_at"] = date('Y-m-d H:i:s');
                \Think\Db::name("product_refund_api_config")
                    ->where("id", $id)
                    ->update($dbData);
                return json(["code" => 200, "msg" => "更新成功"]);
            } else {
                // 新增
                $dbData["created_at"] = date('Y-m-d H:i:s');
                $dbData["updated_at"] = date('Y-m-d H:i:s');
                \Think\Db::name("product_refund_api_config")
                    ->insert($dbData);
                return json(["code" => 200, "msg" => "添加成功"]);
            }
            
        } catch (\Exception $e) {
            return json(["code" => 500, "msg" => "操作失败: " . $e->getMessage()]);
        }
    }
    
    /**
     * 删除上游API配置
     */
    public function deleteApiConfig()
    {
        try {
            $id = input("id", 0);
            if ($id <= 0) {
                return json(["code" => 400, "msg" => "参数错误"]);
            }
            
            // 检查是否有产品在使用此配置
            $usedCount = \Think\Db::name("product_refund")
                ->where("api_config_id", $id)
                ->count();
            
            if ($usedCount > 0) {
                return json(["code" => 400, "msg" => "该配置正在被 {$usedCount} 个产品使用，无法删除"]);
            }
            
            \Think\Db::name("product_refund_api_config")
                ->where("id", $id)
                ->delete();
            
            return json(["code" => 200, "msg" => "删除成功"]);
            
        } catch (\Exception $e) {
            return json(["code" => 500, "msg" => "删除失败: " . $e->getMessage()]);
        }
    }
    
    /**
     * 获取上游API配置列表（用于下拉选择）
     */
    public function getApiConfigList()
    {
        try {
            $type = input("type", "");
            
            $query = \Think\Db::name("product_refund_api_config")
                ->field("id, name, hostname, username, api_audit_type, ticket_department_id, ticket_title, ticket_content, type as config_type")
                ->order("id", "desc");
            
            // 如果指定了类型，则过滤
            if (!empty($type)) {
                $query->where("type", $type);
            }
            
            $list = $query->select();
            
            return json(["code" => 200, "data" => $list]);
            
        } catch (\Exception $e) {
            return json(["code" => 500, "msg" => "获取失败: " . $e->getMessage()]);
        }
    }
    
    public function lists()
    {
        $page = input("page", 1);
        $pageSize = 10;
        $lists = \Think\Db::name("product_refund_list")->order("id", "desc")->paginate($pageSize, false, ["page" => $page]);
        $url = $_SERVER["REQUEST_URI"];
        $domain = dirname($url);
        $this->assign("Title", "申请列表");
        $this->assign("domain", $domain);
        $this->assign("clients", $clients);
        return $this->fetch("/lists", ["data" => $lists]);
    }
    public function agreewith()
    {
        $data = $this->request->post();
        $id = isset($data["id"]) ? $data["id"] : NULL;
        $refundlist = \Think\Db::name("product_refund_list")->where("id", $id)->find();
        $clients = \Think\Db::name("clients")->where("id", $refundlist["user_id"])->find();
        $productsid = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
        $admin_id = cmf_get_current_admin_id();
        $adminuser = \Think\Db::name("user")->where("id", $admin_id)->value("user_nickname");
        $request = $refundlist["request"];
        $rules = $refundlist["rules"];
        $days = $product["within"] / 24;
        $currentTimestamp = time();
        $client_ip = $_SERVER["REMOTE_ADDR"];
        $client_port = $_SERVER["REMOTE_PORT"];
        if ($request == 1) {
            if ($rules == 1) {
                $refundamount = $refundlist["amount"];
                $newcredit = $clients["credit"] + $refundamount;
                $productstime = $productsid["nextduedate"] - $productsid["regdate"];
                $usagetime = $currentTimestamp - $productsid["regdate"];
                $hours = floor($usagetime / 3600);
                $minutes = floor($usagetime % 3600 / 60);
                $seconds = $usagetime % 60;
                $timeFormat = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                $dataaccounts = ["uid" => $refundlist["user_id"], "currency" => "CNY", "gateway" => "退款至余额【主机ID：" . $refundlist["hostid"] . " 】", "create_time" => time(), "pay_time" => time(), "description" => "【人工审核产品首次按时长退款】订单号：" . $refundlist["orderid"] . ", 主机ID：" . $refundlist["hostid"] . ", 开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "amount_out" => $refundamount, "rate" => "1.00000", "invoice_id" => $refundlist["invoices"]];
                \Think\Db::name("accounts")->insert($dataaccounts);
                $datacredit = ["uid" => $refundlist["user_id"], "create_time" => time(), "description" => "Credit from Refund of Invoice ID " . $refundlist["invoices"], "amount" => $refundamount, "notes" => "订单号：" . $refundlist["orderid"] . "，主机ID：" . $refundlist["hostid"] . "，账单号：" . $refundlist["invoices"] . "，首付金额：" . $productsid["firstpaymentamount"] . "元，人工审核产品首次按时长退款【退款金额：" . $refundamount . " 元】，开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "balance" => $newcredit];
                \Think\Db::name("credit")->insert($datacredit);
                \Think\Db::name("clients")->where("id", $refundlist["user_id"])->update(["credit" => $newcredit]);
                $datainvoices = ["status" => "Refunded"];
                \Think\Db::name("invoices")->where("id", $refundlist["invoices"])->update($datainvoices);
                $dataactivity_log = ["create_time" => time(), "description" => "账单退款 - User ID:" . $refundlist["user_id"] . " - Invoice ID:" . $refundlist["invoices"] . " - 首付金额：" . $productsid["firstpaymentamount"] . "元，退款金额: " . $refundamount . " 交易明细处，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => $refundlist["invoices"]];
                \Think\Db::name("activity_log")->insert($dataactivity_log);
                $updatetime = time();
                \Think\Db::name("host")->where("id", $refundlist["hostid"])->update(["nextduedate" => $updatetime]);
                $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核通过】退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " ，更新到期时间：" . date("Y-m-d H:i:s", $updatetime) . "  ，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                \Think\Db::name("activity_log")->insert($dataupdatetime);
                $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "2", "reason" => "审核通过"];
                \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                $originalData = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
                $newNotes = "人工按时长退款\n原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "\n" . "更新后到期时间：" . date("Y-m-d H:i:s", $updatetime);
                $originalNotes = $originalData["notes"] ?? "";
                $updatedNotes = $originalNotes . "\n" . $newNotes;
                $datahost = ["notes" => $updatedNotes];
                \Think\Db::name("host")->where("id", $refundlist["hostid"])->update($datahost);
                $response = ["code" => 200, "msg" => "【审核通过】，按天退款。"];
            } else {
                if ($rules == 2) {
                    $refundamount = $refundlist["amount"];
                    $newcredit = $clients["credit"] + $refundamount;
                    $productstime = $productsid["nextduedate"] - $productsid["regdate"];
                    $usagetime = $currentTimestamp - $productsid["regdate"];
                    $hours = floor($usagetime / 3600);
                    $minutes = floor($usagetime % 3600 / 60);
                    $seconds = $usagetime % 60;
                    $timeFormat = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                    $dataaccounts = ["uid" => $refundlist["user_id"], "currency" => "CNY", "gateway" => "退款至余额【主机ID：" . $refundlist["hostid"] . " 】", "create_time" => time(), "pay_time" => time(), "description" => "【人工审核产品首次按月退款】订单号：" . $refundlist["orderid"] . ", 主机ID：" . $refundlist["hostid"] . ", 开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "amount_out" => $refundamount, "rate" => "1.00000", "invoice_id" => $refundlist["invoices"]];
                    \Think\Db::name("accounts")->insert($dataaccounts);
                    $datacredit = ["uid" => $refundlist["user_id"], "create_time" => time(), "description" => "Credit from Refund of Invoice ID " . $refundlist["invoices"], "amount" => $refundamount, "notes" => "订单号：" . $refundlist["orderid"] . "，主机ID：" . $refundlist["hostid"] . "，账单号：" . $refundlist["invoices"] . "，首付金额：" . $productsid["firstpaymentamount"] . "元，人工审核产品首次按月退款【退款金额：" . $refundamount . " 元】，开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "balance" => $newcredit];
                    \Think\Db::name("credit")->insert($datacredit);
                    \Think\Db::name("clients")->where("id", $refundlist["user_id"])->update(["credit" => $newcredit]);
                    $datainvoices = ["status" => "Refunded"];
                    \Think\Db::name("invoices")->where("id", $refundlist["invoices"])->update($datainvoices);
                    $dataactivity_log = ["create_time" => time(), "description" => "账单退款 - User ID:" . $refundlist["user_id"] . " - Invoice ID:" . $refundlist["invoices"] . " - 首付金额：" . $productsid["firstpaymentamount"] . "元，退款金额: " . $refundamount . " 交易明细处，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，来源：人工审核产品首次按月退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => $refundlist["invoices"]];
                    \Think\Db::name("activity_log")->insert($dataactivity_log);
                    $updatetime = time();
                    \Think\Db::name("host")->where("id", $refundlist["hostid"])->update(["nextduedate" => $updatetime]);
                    $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核通过】用户申请退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " ，更新到期时间：" . date("Y-m-d H:i:s", $updatetime) . "  ，来源：人工审核产品首次按月退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                    \Think\Db::name("activity_log")->insert($dataupdatetime);
                    $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "2", "reason" => "审核通过"];
                    \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                    $originalData = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
                    $newNotes = "人工按时月退款\n原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "\n" . "更新后到期时间：" . date("Y-m-d H:i:s", $updatetime);
                    $originalNotes = $originalData["notes"] ?? "";
                    $updatedNotes = $originalNotes . "\n" . $newNotes;
                    $datahost = ["notes" => $updatedNotes];
                    \Think\Db::name("host")->where("id", $refundlist["hostid"])->update($datahost);
                    $response = ["code" => 200, "msg" => "【审核通过】，按月退款。"];
                } else {
                    if ($rules == 3) {
                        $refundamount = $refundlist["amount"];
                        $newcredit = $clients["credit"] + $refundamount;
                        $productstime = $productsid["nextduedate"] - $productsid["regdate"];
                        $usagetime = $currentTimestamp - $productsid["regdate"];
                        $hours = floor($usagetime / 3600);
                        $minutes = floor($usagetime % 3600 / 60);
                        $seconds = $usagetime % 60;
                        $timeFormat = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                        $dataaccounts = ["uid" => $refundlist["user_id"], "currency" => "CNY", "gateway" => "退款至余额【主机ID：" . $refundlist["hostid"] . " 】", "create_time" => time(), "pay_time" => time(), "description" => "【人工审核产品首次按全额退款】订单号：" . $refundlist["orderid"] . ", 主机ID：" . $refundlist["hostid"] . ", 开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "amount_out" => $refundamount, "rate" => "1.00000", "invoice_id" => $refundlist["invoices"]];
                        \Think\Db::name("accounts")->insert($dataaccounts);
                        $datacredit = ["uid" => $refundlist["user_id"], "create_time" => time(), "description" => "Credit from Refund of Invoice ID " . $refundlist["invoices"], "amount" => $refundamount, "notes" => "订单号：" . $refundlist["orderid"] . "，主机ID：" . $refundlist["hostid"] . "，账单号：" . $refundlist["invoices"] . "，首付金额：" . $productsid["firstpaymentamount"] . "元，人工审核产品首次按全额退款【退款金额：" . $refundamount . " 元】，开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "balance" => $newcredit];
                        \Think\Db::name("credit")->insert($datacredit);
                        \Think\Db::name("clients")->where("id", $refundlist["user_id"])->update(["credit" => $newcredit]);
                        $datainvoices = ["status" => "Refunded"];
                        \Think\Db::name("invoices")->where("id", $refundlist["invoices"])->update($datainvoices);
                        $dataactivity_log = ["create_time" => time(), "description" => "账单退款 - User ID:" . $refundlist["user_id"] . " - Invoice ID:" . $refundlist["invoices"] . " - 首付金额：" . $productsid["firstpaymentamount"] . "元，退款金额: " . $refundamount . " 交易明细处，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，来源：人工审核产品首次按全额退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => $refundlist["invoices"]];
                        \Think\Db::name("activity_log")->insert($dataactivity_log);
                        $updatetime = time();
                        \Think\Db::name("host")->where("id", $refundlist["hostid"])->update(["nextduedate" => $updatetime]);
                        $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核通过】用户申请退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " ，更新到期时间：" . date("Y-m-d H:i:s", $updatetime) . "  ，来源：人工审核产品首次按全额退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                        \Think\Db::name("activity_log")->insert($dataupdatetime);
                        $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "2", "reason" => "审核通过"];
                        \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                        $originalData = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
                        $newNotes = "人工按全额退款\n原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "\n" . "更新后到期时间：" . date("Y-m-d H:i:s", $updatetime);
                        $originalNotes = $originalData["notes"] ?? "";
                        $updatedNotes = $originalNotes . "\n" . $newNotes;
                        $datahost = ["notes" => $updatedNotes];
                        \Think\Db::name("host")->where("id", $refundlist["hostid"])->update($datahost);
                        $response = ["code" => 200, "msg" => "【审核通过】，全额退款。"];
                    } else {
                        $response = ["code" => 400, "msg" => "不支持的退款规则。"];
                    }
                }
            }
        } else {
            if ($request == 2) {
                if ($rules == 1) {
                    $refundamount = $refundlist["amount"];
                    $newcredit = $clients["credit"] + $refundamount;
                    $productstime = $productsid["nextduedate"] - $productsid["regdate"];
                    $usagetime = $currentTimestamp - $productsid["regdate"];
                    $hours = floor($usagetime / 3600);
                    $minutes = floor($usagetime % 3600 / 60);
                    $seconds = $usagetime % 60;
                    $timeFormat = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                    $dataaccounts = ["uid" => $refundlist["user_id"], "currency" => "CNY", "gateway" => "退款至余额【主机ID：" . $refundlist["hostid"] . " 】", "create_time" => time(), "pay_time" => time(), "description" => "【人工审核同类产品首次按时长退款】订单号：" . $refundlist["orderid"] . ", 主机ID：" . $refundlist["hostid"] . ", 开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "amount_out" => $refundamount, "rate" => "1.00000", "invoice_id" => $refundlist["invoices"]];
                    \Think\Db::name("accounts")->insert($dataaccounts);
                    $datacredit = ["uid" => $refundlist["user_id"], "create_time" => time(), "description" => "Credit from Refund of Invoice ID " . $refundlist["invoices"], "amount" => $refundamount, "notes" => "订单号：" . $refundlist["orderid"] . "，主机ID：" . $refundlist["hostid"] . "，账单号：" . $refundlist["invoices"] . "，首付金额：" . $productsid["firstpaymentamount"] . "元，人工审核同类产品首次按时长退款【退款金额：" . $refundamount . " 元】，开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "balance" => $newcredit];
                    \Think\Db::name("credit")->insert($datacredit);
                    \Think\Db::name("clients")->where("id", $refundlist["user_id"])->update(["credit" => $newcredit]);
                    $datainvoices = ["status" => "Refunded"];
                    \Think\Db::name("invoices")->where("id", $refundlist["invoices"])->update($datainvoices);
                    $dataactivity_log = ["create_time" => time(), "description" => "账单退款 - User ID:" . $refundlist["user_id"] . " - Invoice ID:" . $refundlist["invoices"] . " - 首付金额：" . $productsid["firstpaymentamount"] . "元，退款金额: " . $refundamount . " 交易明细处，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，来源：人工审核同类产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => $refundlist["invoices"]];
                    \Think\Db::name("activity_log")->insert($dataactivity_log);
                    $updatetime = time();
                    \Think\Db::name("host")->where("id", $refundlist["hostid"])->update(["nextduedate" => $updatetime]);
                    $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核通过】用户申请退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " ，更新到期时间：" . date("Y-m-d H:i:s", $updatetime) . "  ，来源：人工审核同类产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                    \Think\Db::name("activity_log")->insert($dataupdatetime);
                    $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "2", "reason" => "审核通过"];
                    \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                    $originalData = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
                    $newNotes = "人工按时长退款\n原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "\n" . "更新后到期时间：" . date("Y-m-d H:i:s", $updatetime);
                    $originalNotes = $originalData["notes"] ?? "";
                    $updatedNotes = $originalNotes . "\n" . $newNotes;
                    $datahost = ["notes" => $updatedNotes];
                    \Think\Db::name("host")->where("id", $refundlist["hostid"])->update($datahost);
                    $response = ["code" => 200, "msg" => "【审核通过】，按天退款。"];
                } else {
                    if ($rules == 2) {
                        $refundamount = $refundlist["amount"];
                        $newcredit = $clients["credit"] + $refundamount;
                        $productstime = $productsid["nextduedate"] - $productsid["regdate"];
                        $usagetime = $currentTimestamp - $productsid["regdate"];
                        $hours = floor($usagetime / 3600);
                        $minutes = floor($usagetime % 3600 / 60);
                        $seconds = $usagetime % 60;
                        $timeFormat = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                        $dataaccounts = ["uid" => $refundlist["user_id"], "currency" => "CNY", "gateway" => "退款至余额【主机ID：" . $refundlist["hostid"] . " 】", "create_time" => time(), "pay_time" => time(), "description" => "【人工审核同类产品首次按全月退款】订单号：" . $refundlist["orderid"] . ", 主机ID：" . $refundlist["hostid"] . ", 开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "amount_out" => $refundamount, "rate" => "1.00000", "invoice_id" => $refundlist["invoices"]];
                        \Think\Db::name("accounts")->insert($dataaccounts);
                        $datacredit = ["uid" => $refundlist["user_id"], "create_time" => time(), "description" => "Credit from Refund of Invoice ID " . $refundlist["invoices"], "amount" => $refundamount, "notes" => "订单号：" . $refundlist["orderid"] . "，主机ID：" . $refundlist["hostid"] . "，账单号：" . $refundlist["invoices"] . "，首付金额：" . $productsid["firstpaymentamount"] . "元，人工审核同类产品首次按全月退款【退款金额：" . $refundamount . " 元】，开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "balance" => $newcredit];
                        \Think\Db::name("credit")->insert($datacredit);
                        \Think\Db::name("clients")->where("id", $refundlist["user_id"])->update(["credit" => $newcredit]);
                        $datainvoices = ["status" => "Refunded"];
                        \Think\Db::name("invoices")->where("id", $refundlist["invoices"])->update($datainvoices);
                        $dataactivity_log = ["create_time" => time(), "description" => "账单退款 - User ID:" . $refundlist["user_id"] . " - Invoice ID:" . $refundlist["invoices"] . " - 首付金额：" . $productsid["firstpaymentamount"] . "元，退款金额: " . $refundamount . " 交易明细处，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，来源：人工审核同类产品首次按全月退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => $refundlist["invoices"]];
                        \Think\Db::name("activity_log")->insert($dataactivity_log);
                        $updatetime = time();
                        \Think\Db::name("host")->where("id", $refundlist["hostid"])->update(["nextduedate" => $updatetime]);
                        $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核通过】用户申请退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " ，更新到期时间：" . date("Y-m-d H:i:s", $updatetime) . "  ，来源：人工审核同类产品首次按全月退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                        \Think\Db::name("activity_log")->insert($dataupdatetime);
                        $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "2", "reason" => "审核通过"];
                        \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                        $originalData = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
                        $newNotes = "人工按月退款\n原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "\n" . "更新后到期时间：" . date("Y-m-d H:i:s", $updatetime);
                        $originalNotes = $originalData["notes"] ?? "";
                        $updatedNotes = $originalNotes . "\n" . $newNotes;
                        $datahost = ["notes" => $updatedNotes];
                        \Think\Db::name("host")->where("id", $refundlist["hostid"])->update($datahost);
                        $response = ["code" => 200, "msg" => "【审核通过】，按月退款。"];
                    } else {
                        if ($rules == 3) {
                            $refundamount = $refundlist["amount"];
                            $newcredit = $clients["credit"] + $refundamount;
                            $productstime = $productsid["nextduedate"] - $productsid["regdate"];
                            $usagetime = $currentTimestamp - $productsid["regdate"];
                            $hours = floor($usagetime / 3600);
                            $minutes = floor($usagetime % 3600 / 60);
                            $seconds = $usagetime % 60;
                            $timeFormat = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                            $dataaccounts = ["uid" => $refundlist["user_id"], "currency" => "CNY", "gateway" => "退款至余额【主机ID：" . $refundlist["hostid"] . " 】", "create_time" => time(), "pay_time" => time(), "description" => "【人工审核同类产品首次按全额退款】订单号：" . $refundlist["orderid"] . ", 主机ID：" . $refundlist["hostid"] . ", 开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "amount_out" => $refundamount, "rate" => "1.00000", "invoice_id" => $refundlist["invoices"]];
                            \Think\Db::name("accounts")->insert($dataaccounts);
                            $datacredit = ["uid" => $refundlist["user_id"], "create_time" => time(), "description" => "Credit from Refund of Invoice ID " . $refundlist["invoices"], "amount" => $refundamount, "notes" => "订单号：" . $refundlist["orderid"] . "，主机ID：" . $refundlist["hostid"] . "，账单号：" . $refundlist["invoices"] . "，首付金额：" . $productsid["firstpaymentamount"] . "元，人工审核同类产品首次按全额退款【退款金额：" . $refundamount . " 元】，开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "balance" => $newcredit];
                            \Think\Db::name("credit")->insert($datacredit);
                            \Think\Db::name("clients")->where("id", $refundlist["user_id"])->update(["credit" => $newcredit]);
                            $datainvoices = ["status" => "Refunded"];
                            \Think\Db::name("invoices")->where("id", $refundlist["invoices"])->update($datainvoices);
                            $dataactivity_log = ["create_time" => time(), "description" => "账单退款 - User ID:" . $refundlist["user_id"] . " - Invoice ID:" . $refundlist["invoices"] . " - 首付金额：" . $productsid["firstpaymentamount"] . "元，退款金额: " . $refundamount . " 交易明细处，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，来源：人工审核同类产品首次按全额退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => $refundlist["invoices"]];
                            \Think\Db::name("activity_log")->insert($dataactivity_log);
                            $updatetime = time();
                            \Think\Db::name("host")->where("id", $refundlist["hostid"])->update(["nextduedate" => $updatetime]);
                            $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核通过】用户申请退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " ，更新到期时间：" . date("Y-m-d H:i:s", $updatetime) . "  ，来源：人工审核同类产品首次按全额退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                            \Think\Db::name("activity_log")->insert($dataupdatetime);
                            $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "2", "reason" => "审核通过"];
                            \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                            $originalData = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
                            $newNotes = "人工按全额退款\n原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "\n" . "更新后到期时间：" . date("Y-m-d H:i:s", $updatetime);
                            $originalNotes = $originalData["notes"] ?? "";
                            $updatedNotes = $originalNotes . "\n" . $newNotes;
                            $datahost = ["notes" => $updatedNotes];
                            \Think\Db::name("host")->where("id", $refundlist["hostid"])->update($datahost);
                            $response = ["code" => 200, "msg" => "【审核通过】，全额退款。"];
                        } else {
                            $response = ["code" => 400, "msg" => "不支持的退款规则。"];
                        }
                    }
                }
            } else {
                if ($request == 3) {
                    if ($rules == 1) {
                        $refundamount = $refundlist["amount"];
                        $newcredit = $clients["credit"] + $refundamount;
                        $productstime = $productsid["nextduedate"] - $productsid["regdate"];
                        $usagetime = $currentTimestamp - $productsid["regdate"];
                        $hours = floor($usagetime / 3600);
                        $minutes = floor($usagetime % 3600 / 60);
                        $seconds = $usagetime % 60;
                        $timeFormat = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                        $dataaccounts = ["uid" => $refundlist["user_id"], "currency" => "CNY", "gateway" => "退款至余额【主机ID：" . $refundlist["hostid"] . " 】，退款金额" . $refundamount . "元， 使用时长：" . $timeFormat , "create_time" => time(), "pay_time" => time(), "description" => "【人工审核X小时内按时长退款】订单号：" . $refundlist["orderid"] . ", 主机ID：" . $refundlist["hostid"] . ", 开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "amount_out" => $refundamount, "rate" => "1.00000", "invoice_id" => $refundlist["invoices"]];
                        \Think\Db::name("accounts")->insert($dataaccounts);
                        $datacredit = ["uid" => $refundlist["user_id"], "create_time" => time(), "description" => "Credit from Refund of Invoice ID " . $refundlist["invoices"], "amount" => $refundamount, "notes" => "订单号：" . $refundlist["orderid"] . "，主机ID：" . $refundlist["hostid"] . "，账单号：" . $refundlist["invoices"] . "，首付金额：" . $productsid["firstpaymentamount"] . "元，人工审核X小时内按时长退款【退款金额：" . $refundamount . " 元】，开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "balance" => $newcredit];
                        \Think\Db::name("credit")->insert($datacredit);
                        \Think\Db::name("clients")->where("id", $refundlist["user_id"])->update(["credit" => $newcredit]);
                        $datainvoices = ["status" => "Refunded"];
                        \Think\Db::name("invoices")->where("id", $refundlist["invoices"])->update($datainvoices);
                        $dataactivity_log = ["create_time" => time(), "description" => "账单退款 - User ID:" . $refundlist["user_id"] . " - Invoice ID:" . $refundlist["invoices"] . " - 首付金额：" . $productsid["firstpaymentamount"] . "元，退款金额: " . $refundamount . " 交易明细处，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，来源：人工审核X小时内按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => $refundlist["invoices"]];
                        \Think\Db::name("activity_log")->insert($dataactivity_log);
                        $updatetime = time();
                        \Think\Db::name("host")->where("id", $refundlist["hostid"])->update(["nextduedate" => $updatetime]);
                        $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核通过】用户申请退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " ，更新到期时间：" . date("Y-m-d H:i:s", $updatetime) . "  ，来源：人工审核X小时内按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                        \Think\Db::name("activity_log")->insert($dataupdatetime);
                        $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "2", "reason" => "审核通过"];
                        \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                        $originalData = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
                        $newNotes = "人工按时长退款\n原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "\n" . "更新后到期时间：" . date("Y-m-d H:i:s", $updatetime);
                        $originalNotes = $originalData["notes"] ?? "";
                        $updatedNotes = $originalNotes . "\n" . $newNotes;
                        $datahost = ["notes" => $updatedNotes];
                        \Think\Db::name("host")->where("id", $refundlist["hostid"])->update($datahost);
                        $response = ["code" => 200, "msg" => "【审核通过】，按天退款。"];
                    } else {
                        if ($rules == 2) {
                            $refundamount = $refundlist["amount"];
                            $newcredit = $clients["credit"] + $refundamount;
                            $productstime = $productsid["nextduedate"] - $productsid["regdate"];
                            $usagetime = $currentTimestamp - $productsid["regdate"];
                            $hours = floor($usagetime / 3600);
                            $minutes = floor($usagetime % 3600 / 60);
                            $seconds = $usagetime % 60;
                            $timeFormat = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                            $dataaccounts = ["uid" => $refundlist["user_id"], "currency" => "CNY", "gateway" => "退款至余额【主机ID：" . $refundlist["hostid"] . " 】", "create_time" => time(), "pay_time" => time(), "description" => "【人工审核X小时内按月退款】订单号：" . $refundlist["orderid"] . ", 主机ID：" . $refundlist["hostid"] . ", 开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "amount_out" => $refundamount, "rate" => "1.00000", "invoice_id" => $refundlist["invoices"]];
                            \Think\Db::name("accounts")->insert($dataaccounts);
                            $datacredit = ["uid" => $refundlist["user_id"], "create_time" => time(), "description" => "Credit from Refund of Invoice ID " . $refundlist["invoices"], "amount" => $refundamount, "notes" => "订单号：" . $refundlist["orderid"] . "，主机ID：" . $refundlist["hostid"] . "，账单号：" . $refundlist["invoices"] . "，首付金额：" . $productsid["firstpaymentamount"] . "元，人工审核X小时内按月退款【退款金额：" . $refundamount . " 元】，开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "balance" => $newcredit];
                            \Think\Db::name("credit")->insert($datacredit);
                            \Think\Db::name("clients")->where("id", $refundlist["user_id"])->update(["credit" => $newcredit]);
                            $datainvoices = ["status" => "Refunded"];
                            \Think\Db::name("invoices")->where("id", $refundlist["invoices"])->update($datainvoices);
                            $dataactivity_log = ["create_time" => time(), "description" => "账单退款 - User ID:" . $refundlist["user_id"] . " - Invoice ID:" . $refundlist["invoices"] . " - 首付金额：" . $productsid["firstpaymentamount"] . "元，退款金额: " . $refundamount . " 交易明细处，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，来源：人工审核X小时内按月退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => $refundlist["invoices"]];
                            \Think\Db::name("activity_log")->insert($dataactivity_log);
                            $updatetime = time();
                            \Think\Db::name("host")->where("id", $refundlist["hostid"])->update(["nextduedate" => $updatetime]);
                            $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核通过】用户申请退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " ，更新到期时间：" . date("Y-m-d H:i:s", $updatetime) . "  ，来源：人工审核X小时内按月退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                            \Think\Db::name("activity_log")->insert($dataupdatetime);
                            $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "2", "reason" => "审核通过"];
                            \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                            $originalData = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
                            $newNotes = "人工按月退款\n原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "\n" . "更新后到期时间：" . date("Y-m-d H:i:s", $updatetime);
                            $originalNotes = $originalData["notes"] ?? "";
                            $updatedNotes = $originalNotes . "\n" . $newNotes;
                            $datahost = ["notes" => $updatedNotes];
                            \Think\Db::name("host")->where("id", $refundlist["hostid"])->update($datahost);
                            $response = ["code" => 200, "msg" => "【审核通过】，按月退款。"];
                        } else {
                            if ($rules == 3) {
                                $refundamount = $refundlist["amount"];
                                $newcredit = $clients["credit"] + $refundamount;
                                $productstime = $productsid["nextduedate"] - $productsid["regdate"];
                                $usagetime = $currentTimestamp - $productsid["regdate"];
                                $hours = floor($usagetime / 3600);
                                $minutes = floor($usagetime % 3600 / 60);
                                $seconds = $usagetime % 60;
                                $timeFormat = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);
                                $dataaccounts = ["uid" => $refundlist["user_id"], "currency" => "CNY", "gateway" => "退款至余额【主机ID：" . $refundlist["hostid"] . " 】", "create_time" => time(), "pay_time" => time(), "description" => "【人工审核X小时内按全额退款】订单号：" . $refundlist["orderid"] . ", 主机ID：" . $refundlist["hostid"] . ", 开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "amount_out" => $refundamount, "rate" => "1.00000", "invoice_id" => $refundlist["invoices"]];
                                \Think\Db::name("accounts")->insert($dataaccounts);
                                $datacredit = ["uid" => $refundlist["user_id"], "create_time" => time(), "description" => "Credit from Refund of Invoice ID " . $refundlist["invoices"], "amount" => $refundamount, "notes" => "订单号：" . $refundlist["orderid"] . "，主机ID：" . $refundlist["hostid"] . "，账单号：" . $refundlist["invoices"] . "，首付金额：" . $productsid["firstpaymentamount"] . "元，人工审核X小时内按全额退款【退款金额：" . $refundamount . " 元】，开通时间：" . date("Y-m-d H:i:s", $productsid["regdate"]) . ", 到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " 使用时长：" . $timeFormat . " ", "balance" => $newcredit];
                                \Think\Db::name("credit")->insert($datacredit);
                                \Think\Db::name("clients")->where("id", $refundlist["user_id"])->update(["credit" => $newcredit]);
                                $datainvoices = ["status" => "Refunded"];
                                \Think\Db::name("invoices")->where("id", $refundlist["invoices"])->update($datainvoices);
                                $dataactivity_log = ["create_time" => time(), "description" => "账单退款 - User ID:" . $refundlist["user_id"] . " - Invoice ID:" . $refundlist["invoices"] . " - 首付金额：" . $productsid["firstpaymentamount"] . "元，退款金额: " . $refundamount . " 交易明细处，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，来源：人工审核X小时内按全额退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => $refundlist["invoices"]];
                                \Think\Db::name("activity_log")->insert($dataactivity_log);
                                $updatetime = time();
                                \Think\Db::name("host")->where("id", $refundlist["hostid"])->update(["nextduedate" => $updatetime]);
                                $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核通过】用户申请退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . " ，更新到期时间：" . date("Y-m-d H:i:s", $updatetime) . "  ，来源：人工审核X小时内按全额退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                                \Think\Db::name("activity_log")->insert($dataupdatetime);
                                $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "2", "reason" => "审核通过"];
                                \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                                $originalData = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
                                $newNotes = "人工按全额退款\n原到期时间：" . date("Y-m-d H:i:s", $productsid["nextduedate"]) . "\n" . "更新后到期时间：" . date("Y-m-d H:i:s", $updatetime);
                                $originalNotes = $originalData["notes"] ?? "";
                                $updatedNotes = $originalNotes . "\n" . $newNotes;
                                $datahost = ["notes" => $updatedNotes];
                                \Think\Db::name("host")->where("id", $refundlist["hostid"])->update($datahost);
                                $response = ["code" => 200, "msg" => "【审核通过】，全额退款。"];
                            } else {
                                $response = ["code" => 400, "msg" => "不支持的退款规则。"];
                            }
                        }
                    }
                } else {
                    $response = ["code" => 400, "msg" => "不支持的请求类型。"];
                }
            }
        }
        return json($response);
    }
    public function refuse()
    {
        $data = $this->request->post();
        $id = isset($data["id"]) ? $data["id"] : NULL;
        $reason = isset($data["reason"]) ? $data["reason"] : NULL;
        $refundlist = \Think\Db::name("product_refund_list")->where("id", $id)->find();
        $clients = \Think\Db::name("clients")->where("id", $refundlist["user_id"])->find();
        $productsid = \Think\Db::name("host")->where("id", $refundlist["hostid"])->find();
        $admin_id = cmf_get_current_admin_id();
        $adminuser = \Think\Db::name("user")->where("id", $admin_id)->value("user_nickname");
        $request = $refundlist["request"];
        $rules = $refundlist["rules"];
        $days = $product["within"] / 24;
        $currentTimestamp = time();
        $client_ip = $_SERVER["REMOTE_ADDR"];
        $client_port = $_SERVER["REMOTE_PORT"];
        if ($request == 1) {
            if ($rules == 1) {
                $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核不通过】拒绝退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，拒绝原因：" . $reason . " ，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                \Think\Db::name("activity_log")->insert($dataupdatetime);
                $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "3", "reason" => $reason];
                \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                $response = ["code" => 200, "msg" => $adminuser . " 【审核已拒绝】"];
            } else {
                if ($rules == 2) {
                    $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核不通过】拒绝退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，拒绝原因：" . $reason . " ，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                    \Think\Db::name("activity_log")->insert($dataupdatetime);
                    $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "3", "reason" => $reason];
                    \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                    $response = ["code" => 200, "msg" => $adminuser . " 【审核已拒绝】"];
                } else {
                    if ($rules == 3) {
                        $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核不通过】拒绝退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，拒绝原因：" . $reason . " ，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                        \Think\Db::name("activity_log")->insert($dataupdatetime);
                        $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "3", "reason" => $reason];
                        \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                        $response = ["code" => 200, "msg" => $adminuser . " 【审核已拒绝】"];
                    } else {
                        $response = ["code" => 400, "msg" => "不支持的退款规则。"];
                    }
                }
            }
        } else {
            if ($request == 2) {
                if ($rules == 1) {
                    $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核不通过】拒绝退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，拒绝原因：" . $reason . " ，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                    \Think\Db::name("activity_log")->insert($dataupdatetime);
                    $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "3", "reason" => $reason];
                    \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                    $response = ["code" => 200, "msg" => $adminuser . " 【审核已拒绝】"];
                } else {
                    if ($rules == 2) {
                        $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核不通过】拒绝退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，拒绝原因：" . $reason . " ，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                        \Think\Db::name("activity_log")->insert($dataupdatetime);
                        $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "3", "reason" => $reason];
                        \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                        $response = ["code" => 200, "msg" => $adminuser . " 【审核已拒绝】"];
                    } else {
                        if ($rules == 3) {
                            $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核不通过】拒绝退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，拒绝原因：" . $reason . " ，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                            \Think\Db::name("activity_log")->insert($dataupdatetime);
                            $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "3", "reason" => $reason];
                            \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                            $response = ["code" => 200, "msg" => $adminuser . " 【审核已拒绝】"];
                        } else {
                            $response = ["code" => 400, "msg" => "不支持的退款规则。"];
                        }
                    }
                }
            } else {
                if ($request == 3) {
                    if ($rules == 1) {
                        $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核不通过】拒绝退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，拒绝原因：" . $reason . " ，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                        \Think\Db::name("activity_log")->insert($dataupdatetime);
                        $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "3", "reason" => $reason];
                        \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                        $response = ["code" => 200, "msg" => $adminuser . " 【审核已拒绝】"];
                    } else {
                        if ($rules == 2) {
                            $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核不通过】拒绝退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，拒绝原因：" . $reason . " ，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                            \Think\Db::name("activity_log")->insert($dataupdatetime);
                            $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "3", "reason" => $reason];
                            \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                            $response = ["code" => 200, "msg" => $adminuser . " 【审核已拒绝】"];
                        } else {
                            if ($rules == 3) {
                                $dataupdatetime = ["create_time" => time(), "description" => "【管理 " . $adminuser . " (" . $admin_id . " )审核不通过】拒绝退款 <a href=\"#/customer-view/abstract?id=" . $refundlist["user_id"] . "\">User ID:" . $refundlist["user_id"] . "</a>，订单号：" . $refundlist["orderid"] . " ，<a href=\"#/customer-view/product-innerpage?hid={" . $refundlist["hostid"] . "}&id=" . $refundlist["user_id"] . "\">Host ID:" . $refundlist["hostid"] . "</a>，拒绝原因：" . $reason . " ，来源：人工审核产品首次按时长退款", "user" => $adminuser, "uid" => $refundlist["user_id"], "ipaddr" => $client_ip, "type" => "6", "activeid" => $admin_id, "usertype" => "Admin", "port" => $client_port, "type_data_id" => ""];
                                \Think\Db::name("activity_log")->insert($dataupdatetime);
                                $dbConfig = ["audittime" => time(), "adminid" => $admin_id, "reviewed" => $adminuser, "status" => "3", "reason" => $reason];
                                \Think\Db::name("product_refund_list")->where("id", $refundlist["id"])->update($dbConfig);
                                $response = ["code" => 200, "msg" => $adminuser . " 【审核已拒绝】"];
                            } else {
                                $response = ["code" => 400, "msg" => "不支持的退款规则。"];
                            }
                        }
                    }
                } else {
                    $response = ["code" => 400, "msg" => "不支持的请求类型。"];
                }
            }
        }
        return json($response);
    }
    public function helplist()
    {
        $this->assign("Title", "Demo样式6");
        return $this->fetch("/helplist");
    }
}

?>