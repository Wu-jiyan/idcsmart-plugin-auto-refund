<?php
namespace addons\auto_refund;

class AutoRefundPlugin extends \app\admin\lib\Plugin
{
    public $info = ["name" => "AutoRefund", "title" => "产品退款", "description" => "产品退款管理插件，支持自动退款，人工审核退款，支持按时长退款，按月退款，全额退款，支持自定义退款时间，支持插件间对接", "status" => 1, "author" => "二五云", "version" => "1.3.0", "module" => "addons", "lang" => ["chinese" => "产品退款", "chinese_tw" => "產品退款", "english" => "Product Refund"]];
    
    public function install()
    {
        // 上游API配置表 - 新增
        $fieldsToAddTable0 = [
            ["name" => "id", "type" => "INT AUTO_INCREMENT PRIMARY KEY", "comment" => "ID"],
            ["name" => "type", "type" => "VARCHAR(50) DEFAULT 'api'", "comment" => "配置类型：api-API工单，plugin-插件间对接"],
            ["name" => "name", "type" => "VARCHAR(255)", "comment" => "配置名称"],
            ["name" => "hostname", "type" => "VARCHAR(255)", "comment" => "API主机地址"],
            ["name" => "username", "type" => "VARCHAR(255)", "comment" => "API用户名"],
            ["name" => "api_key", "type" => "TEXT", "comment" => "API密钥(加密存储)"],
            ["name" => "api_audit_type", "type" => "TINYINT DEFAULT 1", "comment" => "审核类型：1人工审核，2自动入账"],
            ["name" => "ticket_department_id", "type" => "INT DEFAULT 2", "comment" => "工单部门ID"],
            ["name" => "ticket_title", "type" => "VARCHAR(255) DEFAULT '申请退款'", "comment" => "工单标题"],
            ["name" => "ticket_content", "type" => "TEXT", "comment" => "工单内容"],
            ["name" => "created_at", "type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP", "comment" => "创建时间"],
            ["name" => "updated_at", "type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "comment" => "更新时间"]
        ];
        
        // 产品退款配置表 - 简化版，使用api_config_id关联上游配置
        $fieldsToAddTable1 = [
            ["name" => "id", "type" => "INT AUTO_INCREMENT PRIMARY KEY", "comment" => "ID"],
            ["name" => "productid", "type" => "VARCHAR(255)", "comment" => "产品ID"],
            ["name" => "producttype", "type" => "VARCHAR(255)", "comment" => "产品类型"],
            ["name" => "type", "type" => "VARCHAR(255)", "comment" => "退款类型：1人工审核，2自动退款，3API退款，4插件间对接"],
            ["name" => "request", "type" => "VARCHAR(255)", "comment" => "退款要求"],
            ["name" => "within", "type" => "INT", "comment" => "退款时间限制(小时)"],
            ["name" => "rules", "type" => "VARCHAR(255)", "comment" => "退款规则"],
            ["name" => "created_time", "type" => "VARCHAR(255)", "comment" => "创建时间"],
            ["name" => "api_config_id", "type" => "INT DEFAULT 0", "comment" => "API配置ID，关联product_refund_api_config表"],
            ["name" => "allow_plugin_api", "type" => "TINYINT DEFAULT 0", "comment" => "是否开放插件间对接API：0否，1是"],
            ["name" => "created_at", "type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP", "comment" => "创建时间"],
            ["name" => "updated_at", "type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "comment" => "更新时间"]
        ];
        
        // 插件间对接API配置表 - 新增
        $fieldsToAddTable4 = [
            ["name" => "id", "type" => "INT AUTO_INCREMENT PRIMARY KEY", "comment" => "ID"],
            ["name" => "user_id", "type" => "INT", "comment" => "用户ID"],
            ["name" => "api_key", "type" => "VARCHAR(255)", "comment" => "API密钥"],
            ["name" => "status", "type" => "TINYINT DEFAULT 1", "comment" => "状态：0禁用，1启用"],
            ["name" => "created_at", "type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP", "comment" => "创建时间"],
            ["name" => "updated_at", "type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "comment" => "更新时间"]
        ];
        
        // 插件间对接配置表 - 下游使用
        $fieldsToAddTable5 = [
            ["name" => "id", "type" => "INT AUTO_INCREMENT PRIMARY KEY", "comment" => "ID"],
            ["name" => "name", "type" => "VARCHAR(255)", "comment" => "配置名称"],
            ["name" => "hostname", "type" => "VARCHAR(255)", "comment" => "上游插件地址"],
            ["name" => "api_key", "type" => "TEXT", "comment" => "API密钥(加密存储)"],
            ["name" => "api_audit_type", "type" => "TINYINT DEFAULT 1", "comment" => "审核类型：1人工审核，2自动入账"],
            ["name" => "status", "type" => "TINYINT DEFAULT 1", "comment" => "状态：0禁用，1启用"],
            ["name" => "created_at", "type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP", "comment" => "创建时间"],
            ["name" => "updated_at", "type" => "TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP", "comment" => "更新时间"]
        ];
        
        $fieldsToAddTable2 = [["name" => "id", "type" => "INT AUTO_INCREMENT PRIMARY KEY"], ["name" => "webname", "type" => "VARCHAR(255)"], ["name" => "weburl", "type" => "VARCHAR(255)"], ["name" => "open", "type" => "VARCHAR(255)"], ["name" => "zzqq", "type" => "VARCHAR(255)"], ["name" => "zzemail", "type" => "VARCHAR(255)"], ["name" => "day", "type" => "VARCHAR(255)"], ["name" => "agent", "type" => "VARCHAR(255)"], ["name" => "displaytime", "type" => "VARCHAR(255)"], ["name" => "mfauth", "type" => "VARCHAR(255)"], ["name" => "feishuswitch", "type" => "VARCHAR(255)"], ["name" => "feishuurl", "type" => "VARCHAR(255)"], ["name" => "dingswitch", "type" => "VARCHAR(255)"], ["name" => "dingurl", "type" => "VARCHAR(255)"], ["name" => "wechatswitch", "type" => "VARCHAR(255)"], ["name" => "wechaturl", "type" => "VARCHAR(255)"], ["name" => "tgswitch", "type" => "VARCHAR(255)"], ["name" => "tgtoken", "type" => "VARCHAR(255)"], ["name" => "tgchatid", "type" => "VARCHAR(255)"]];
        $fieldsToAddTable3 = [["name" => "id", "type" => "INT AUTO_INCREMENT PRIMARY KEY"], ["name" => "user_id", "type" => "INT"], ["name" => "username", "type" => "VARCHAR(255)"], ["name" => "productid", "type" => "INT"], ["name" => "productname", "type" => "VARCHAR(255)"], ["name" => "orderid", "type" => "INT"], ["name" => "producttype", "type" => "VARCHAR(255)"], ["name" => "hostid", "type" => "INT"], ["name" => "invoices", "type" => "INT"], ["name" => "type", "type" => "VARCHAR(255)"], ["name" => "request", "type" => "VARCHAR(255)"], ["name" => "rules", "type" => "VARCHAR(255)"], ["name" => "amount", "type" => "VARCHAR(255)"], ["name" => "reasonrefund", "type" => "VARCHAR(255)"], ["name" => "created_time", "type" => "VARCHAR(255)"], ["name" => "audittime", "type" => "VARCHAR(255)"], ["name" => "adminid", "type" => "VARCHAR(255)"], ["name" => "reviewed", "type" => "VARCHAR(255)"], ["name" => "status", "type" => "VARCHAR(255)"], ["name" => "reason", "type" => "VARCHAR(255)"]];
        
        // 创建上游API配置表
        $tableName0 = "shd_product_refund_api_config";
        $tableExists0 = \Think\Db::query("SHOW TABLES LIKE '" . $tableName0 . "'");
        if (empty($tableExists0)) {
            $sql0 = "
                    CREATE TABLE " . $tableName0 . " (
                        " . implode(",\n", array_map(function ($fieldInfo) {
                return $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "'";
            }, $fieldsToAddTable0)) . "
                    );
                ";
            \Think\Db::execute($sql0);
        } else {
            foreach ($fieldsToAddTable0 as $fieldInfo) {
                $fieldName = $fieldInfo["name"];
                $fieldExists = \Think\Db::query("SHOW COLUMNS FROM " . $tableName0 . " LIKE '" . $fieldName . "'");
                if (empty($fieldExists)) {
                    $sql0 = "
                            ALTER TABLE " . $tableName0 . "
                            ADD COLUMN " . $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "';
                        ";
                    \Think\Db::execute($sql0);
                }
            }
        }
        
        // 创建产品退款配置表
        $tableName1 = "shd_product_refund";
        $tableExists1 = \Think\Db::query("SHOW TABLES LIKE '" . $tableName1 . "'");
        if (empty($tableExists1)) {
            $sql1 = "
                    CREATE TABLE " . $tableName1 . " (
                        " . implode(",\n", array_map(function ($fieldInfo) {
                return $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "'";
            }, $fieldsToAddTable1)) . "
                    );
                ";
            \Think\Db::execute($sql1);
        } else {
            foreach ($fieldsToAddTable1 as $fieldInfo) {
                $fieldName = $fieldInfo["name"];
                $fieldExists = \Think\Db::query("SHOW COLUMNS FROM " . $tableName1 . " LIKE '" . $fieldName . "'");
                if (empty($fieldExists)) {
                    $sql1 = "
                            ALTER TABLE " . $tableName1 . "
                            ADD COLUMN " . $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "';
                        ";
                    \Think\Db::execute($sql1);
                }
            }
        }
        
        // 创建插件间对接API配置表
        $tableName4 = "shd_product_refund_plugin_api";
        $tableExists4 = \Think\Db::query("SHOW TABLES LIKE '" . $tableName4 . "'");
        if (empty($tableExists4)) {
            $sql4 = "
                    CREATE TABLE " . $tableName4 . " (
                        " . implode(",\n", array_map(function ($fieldInfo) {
                return $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "'";
            }, $fieldsToAddTable4)) . "
                    );
                ";
            \Think\Db::execute($sql4);
        } else {
            foreach ($fieldsToAddTable4 as $fieldInfo) {
                $fieldName = $fieldInfo["name"];
                $fieldExists = \Think\Db::query("SHOW COLUMNS FROM " . $tableName4 . " LIKE '" . $fieldName . "'");
                if (empty($fieldExists)) {
                    $sql4 = "
                            ALTER TABLE " . $tableName4 . "
                            ADD COLUMN " . $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "';
                        ";
                    \Think\Db::execute($sql4);
                }
            }
        }
        
        // 创建插件间对接配置表
        $tableName5 = "shd_product_refund_plugin_config";
        $tableExists5 = \Think\Db::query("SHOW TABLES LIKE '" . $tableName5 . "'");
        if (empty($tableExists5)) {
            $sql5 = "
                    CREATE TABLE " . $tableName5 . " (
                        " . implode(",\n", array_map(function ($fieldInfo) {
                return $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "'";
            }, $fieldsToAddTable5)) . "
                    );
                ";
            \Think\Db::execute($sql5);
        } else {
            foreach ($fieldsToAddTable5 as $fieldInfo) {
                $fieldName = $fieldInfo["name"];
                $fieldExists = \Think\Db::query("SHOW COLUMNS FROM " . $tableName5 . " LIKE '" . $fieldName . "'");
                if (empty($fieldExists)) {
                    $sql5 = "
                            ALTER TABLE " . $tableName5 . "
                            ADD COLUMN " . $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "';
                        ";
                    \Think\Db::execute($sql5);
                }
            }
        }
        
        // 创建设置表
        $tableName2 = "shd_product_refund_setting";
        $tableExists2 = \Think\Db::query("SHOW TABLES LIKE '" . $tableName2 . "'");
        if (empty($tableExists2)) {
            $sql2 = "
                    CREATE TABLE " . $tableName2 . " (
                        " . implode(",\n", array_map(function ($fieldInfo) {
                return $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "'";
            }, $fieldsToAddTable2)) . "
                    );
                ";
            \Think\Db::execute($sql2);
        } else {
            foreach ($fieldsToAddTable2 as $fieldInfo) {
                $fieldName = $fieldInfo["name"];
                $fieldExists = \Think\Db::query("SHOW COLUMNS FROM " . $tableName2 . " LIKE '" . $fieldName . "'");
                if (empty($fieldExists)) {
                    $sql2 = "
                            ALTER TABLE " . $tableName2 . "
                            ADD COLUMN " . $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "';
                        ";
                    \Think\Db::execute($sql2);
                }
            }
        }
        
        // 创建退款列表表
        $tableName3 = "shd_product_refund_list";
        $tableExists3 = \Think\Db::query("SHOW TABLES LIKE '" . $tableName3 . "'");
        if (empty($tableExists3)) {
            $sql3 = "
                    CREATE TABLE " . $tableName3 . " (
                        " . implode(",\n", array_map(function ($fieldInfo) {
                return $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "'";
            }, $fieldsToAddTable3)) . "
                    );
                ";
            \Think\Db::execute($sql3);
        } else {
            foreach ($fieldsToAddTable3 as $fieldInfo) {
                $fieldName = $fieldInfo["name"];
                $fieldExists = \Think\Db::query("SHOW COLUMNS FROM " . $tableName3 . " LIKE '" . $fieldName . "'");
                if (empty($fieldExists)) {
                    $sql3 = "
                            ALTER TABLE " . $tableName3 . "
                            ADD COLUMN " . $fieldInfo["name"] . " " . $fieldInfo["type"] . " COMMENT '" . $fieldInfo["comment"] . "';
                        ";
                    \Think\Db::execute($sql3);
                }
            }
        }
        return true;
    }
    
    public function uninstall()
    {
        // 可选：卸载时删除表（默认保留数据）
        // 如需删除表，取消下面注释
        /*
        $sql = [
            "DROP TABLE IF EXISTS shd_product_refund_api_config",
            "DROP TABLE IF EXISTS shd_product_refund_plugin_api",
            "DROP TABLE IF EXISTS shd_product_refund_plugin_config",
            "DROP TABLE IF EXISTS shd_product_refund",
            "DROP TABLE IF EXISTS shd_product_refund_setting",
            "DROP TABLE IF EXISTS shd_product_refund_list"
        ];
        foreach ($sql as $v) {
            \think\Db::execute($v);
        }
        */
        return true;
    }
    
    public function afterCron()
    {
        $result = \Think\Db::name("product_refund_setting")->where("id", 1)->find();
        if ($result["open"] == 1) {
            $lists = \Think\Db::name("product_refund")->select();
            $count = 0;
            foreach ($lists as $list) {
                $hidden = \Think\Db::name("products")->where("id", $list["productid"])->value("hidden");
                if ($hidden == 1) {
                    $jie = \Think\Db::name("product_refund")->where("id", $list["id"])->delete();
                    if ($jie) {
                        $count++;
                    }
                }
            }
            echo "产品退款(插件)清理已下架产品：" . $count . "个:" . date("Y-m-d H:i:s") . PHP_EOL . "";
        }
    }
}
