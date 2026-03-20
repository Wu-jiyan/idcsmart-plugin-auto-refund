<script src="/plugins/addons/auto_refund/assets/layer.js"></script>
<link rel="stylesheet" href="https://cdnjs.25y.cn/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
    table {
        border-collapse: collapse;
        width: 100%;
    }

    th, td {
        border: 1px solid #ddd;
        text-align: left;
        padding: 8px;
    }

    th {
        background-color: #f2f2f2;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    .center {
        text-align: center;
    }

    .discount-label {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
    }

    .ren-discount {
        background-color: #4b4d50;
        color: white;
    }

    .zi-discount {
        background-color: #ef0e24;
        color: white;
    }

    .api-discount {
        background-color: #1890ff;
        color: white;
    }

    .ali-discount {
        background-color: #007bff;
        color: #fff;
    }

    .wx-discount {
        background-color: green;
        color: white;
    }

    .unknown-discount {
        background-color: #999;
        color: white;
    }

    .pagination {
        text-align: right;
        margin-top: 20px;
    }

    .pagination-link {
        margin: 0 5px;
        text-decoration: none;
        color: #333;
        border: 1px solid #ccc;
        padding: 5px 10px;
        border-radius: 5px;
    }

    .jump-input {
        width: 40px;
        padding: 2px;
        text-align: center;
    }

    .jump-button {
        padding: 2px 8px;
        background-color: #007bff;
        border: none;
        color: #fff;
        border-radius: 5px;
        cursor: pointer;
    }

    .jump-button:hover {
        background-color: #0056b3;
    }

    .search-box {
        margin: 15px 0;
        display: flex;
        gap: 10px;
        align-items: center;
    }

    .page-size-selector {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .config-info-cell {
        max-width: 400px;
        padding: 10px;
        border: 1px solid #ccc;
        font-size: 14px;
        color: #333;
        border-radius: 6px;
        display: inline-block;
        word-wrap: break-word;
    }

    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
    }

    .modal-content {
        background: white;
        margin: 5% auto;
        padding: 20px;
        border-radius: 4px;
        width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    }

    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eee;
    }

    .modal-title {
        font-size: 18px;
        font-weight: bold;
    }

    .close {
        font-size: 24px;
        cursor: pointer;
        color: #999;
    }

    .close:hover {
        color: #333;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        margin-bottom: 5px;
        font-weight: 500;
    }

    .help-text {
        color: #888;
        font-size: 12px;
        margin-top: 4px;
    }
</style>

<body>
    <section class="admin-main">
        <div class="container-fluid">
            <div class="page-container">
                <div class="card">
                    <div class="card-body">
                        <div class="card-title row">
                            <div style="padding:0 15px;">{$Title}</div>
                            <div class="col-lg-8 col-md-12 col-sm-12">
                                {foreach $PluginsAdminMenu as $admin}
                                {if $admin['custom']}
                                <span class="ml-2"><a class="h5" href="{$admin.url}" target="_blank">{$admin.name}</a></span>
                                {else/}
                                <span class="ml-2"> <a class="h5" href="{$admin.url}">{$admin.name}</a></span>
                                {/if}
                                {/foreach}
                            </div>
                        </div>

                        <!-- 添加按钮 -->
                        <div class="search-box">
                            <button type="button" class="btn btn-primary" onclick="openModal()">
                                <i class="fas fa-plus"></i> 添加上游配置
                            </button>
                        </div>

                        <table>
                            <thead>
                                <tr>
                                    <th class="center">ID</th>
                                    <th class="center">类型</th>
                                    <th class="center">配置名称</th>
                                    <th class="center">上游地址</th>
                                    <th class="center">审核方式</th>
                                    <th class="center">创建时间</th>
                                    <th class="center">更新时间</th>
                                    <th class="center">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                {volist name='data' id='item'}
                                <tr>
                                    <td class="center">{$item.id}</td>
                                    <td class="center">
                                        {if $item.type == 'plugin'}
                                        <span class="discount-label api-discount">插件间对接</span>
                                        {else}
                                        <span class="discount-label ali-discount">API工单</span>
                                        {/if}
                                    </td>
                                    <td class="center">
                                        <span class="config-info-cell">{$item.name}</span>
                                    </td>
                                    <td class="center">
                                        <span class="config-info-cell">{$item.hostname}</span>
                                    </td>
                                    <td class="center">
                                        {if $item.api_audit_type == 1}
                                        <span class="discount-label ren-discount">人工审核</span>
                                        {else}
                                        <span class="discount-label zi-discount">自动入账</span>
                                        {/if}
                                    </td>
                                    <td class="center">
                                        <span class="config-info-cell">
                                            <?php
                                            if (is_numeric($item['created_at'])) {
                                                echo date('Y-m-d H:i:s', $item['created_at']);
                                            } else {
                                                echo $item['created_at'];
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td class="center">
                                        <span class="config-info-cell">
                                            <?php
                                            if (is_numeric($item['updated_at'])) {
                                                echo date('Y-m-d H:i:s', $item['updated_at']);
                                            } else {
                                                echo $item['updated_at'];
                                            }
                                            ?>
                                        </span>
                                    </td>
                                    <td class="center">
                                        <button type="button" class="btn btn-primary btn-sm" onclick="editConfig({$item.id}, '{$item.name|addslashes}', '{$item.hostname|addslashes}', '{$item.username|addslashes}', {$item.api_audit_type}, {$item.ticket_department_id|default=2}, '{$item.ticket_title|addslashes|default='申请退款'}', '{$item.ticket_content|addslashes|default='申请产品无理由退款'}', '{$item.type}', '{$item.api_key|addslashes}')">
                                            <i class="fas fa-edit"></i> 编辑
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm" onclick="deleteConfig({$item.id})">
                                            <i class="fas fa-trash-alt"></i> 删除
                                        </button>
                                    </td>
                                </tr>
                                {/volist}
                            </tbody>
                        </table>

                        <div class="pagination">
                            当前页码：{$data->currentPage()}
                            总页码：{$data->lastPage()}
                            总记录：{$data->total()} 条
                            {$data->render()}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 添加/编辑配置模态框 -->
    <div id="configModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title" id="modalTitle">添加上游配置</span>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>

            <form id="configForm">
                <input type="hidden" id="configId" value="0">

                <div class="form-group">
                    <label>上游类型 <span style="color: red;">*</span></label>
                    <select id="configType" class="form-control" onchange="onConfigTypeChange()">
                        <option value="api">API工单退款</option>
                        <option value="plugin">插件间对接</option>
                    </select>
                    <div class="help-text">API工单：通过上游API提交退款工单<br>插件间对接：通过插件API直接对接上游退款功能</div>
                </div>

                <div class="form-group">
                    <label>配置名称 <span style="color: red;">*</span></label>
                    <input type="text" id="configName" class="form-control" placeholder="如：魔方云上游">
                    <div class="help-text">用于标识此上游配置的名称</div>
                </div>

                <!-- API工单配置区域 -->
                <div id="api-config-section">
                    <div class="form-group">
                        <label>API主机地址 <span style="color: red;">*</span></label>
                        <input type="text" id="configHostname" class="form-control" placeholder="如：https://api.example.com">
                        <div class="help-text">上游API的根地址，不需要带/v1路径</div>
                    </div>

                    <div class="form-group">
                        <label>API用户名 <span style="color: red;">*</span></label>
                        <input type="text" id="configUsername" class="form-control" placeholder="API登录用户名">
                    </div>

                    <div class="form-group">
                        <label>API密钥 <span style="color: red;" id="apiKeyRequired">*</span></label>
                        <input type="password" id="configApiKey" class="form-control" placeholder="API登录密钥">
                        <div class="help-text" id="apiKeyHelp">编辑时留空表示不修改</div>
                    </div>

                    <div class="form-group">
                        <label>工单部门ID</label>
                        <input type="number" id="configDepartmentId" class="form-control" value="2">
                        <div class="help-text">提交给上游的工单部门ID，默认为2</div>
                    </div>

                    <div class="form-group">
                        <label>工单标题</label>
                        <input type="text" id="configTicketTitle" class="form-control" value="申请退款">
                        <div class="help-text">支持变量：{product_name}产品名, {host_id}主机ID</div>
                    </div>

                    <div class="form-group">
                        <label>工单内容</label>
                        <textarea id="configTicketContent" class="form-control" rows="3">申请产品无理由退款</textarea>
                        <div class="help-text">支持变量：{product_name}产品名, {host_id}主机ID</div>
                    </div>
                </div>

                <!-- 插件间对接配置区域 -->
                <div id="plugin-config-section" style="display: none;">
                    <div class="form-group">
                        <label>上游插件地址 <span style="color: red;">*</span></label>
                        <input type="text" id="pluginHostname" class="form-control" placeholder="如：https://demo.example.com">
                        <div class="help-text">上游站点的完整地址，包含协议头</div>
                    </div>

                    <div class="form-group">
                        <label>API KEY <span style="color: red;" id="pluginApiKeyRequired">*</span></label>
                        <input type="text" id="pluginApiKey" class="form-control" placeholder="从上游站点获取的API KEY">
                        <div class="help-text" id="pluginApiKeyHelp">在上游站点的"产品退款 > API KEY管理"页面生成</div>
                    </div>
                </div>

                <div class="form-group">
                    <label>审核方式 <span style="color: red;">*</span></label>
                    <select id="configAuditType" class="form-control">
                        <option value="1">人工审核（提交后等待管理员审核）</option>
                        <option value="2">自动入账（提交后自动退款到用户余额）</option>
                    </select>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <button type="button" class="btn btn-secondary" onclick="closeModal()">取消</button>
                    <button type="button" class="btn btn-primary" onclick="saveConfig()">保存</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdnjs.25y.cn/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        function openModal() {
            $('#configId').val(0);
            $('#configForm')[0].reset();
            $('#modalTitle').text('添加上游配置');
            
            // 重置必填标记和提示
            $('#apiKeyRequired').show();
            $('#apiKeyHelp').hide();
            $('#pluginApiKeyRequired').show();
            $('#pluginApiKeyHelp').text('在上游站点的"产品退款 > API KEY管理"页面生成');
            
            $('#configType').val('api');
            onConfigTypeChange();
            $('#configModal').show();
        }

        function onConfigTypeChange() {
            var type = $('#configType').val();
            if (type == 'api') {
                $('#api-config-section').show();
                $('#plugin-config-section').hide();
            } else {
                $('#api-config-section').hide();
                $('#plugin-config-section').show();
            }
        }

        function closeModal() {
            $('#configModal').hide();
        }

        function editConfig(id, name, hostname, username, auditType, deptId, title, content, type, apiKey) {
            $('#configId').val(id);
            $('#configName').val(name);
            $('#configAuditType').val(auditType);
            
            // 设置类型
            if (type) {
                $('#configType').val(type);
            } else {
                $('#configType').val('api');
            }
            
            // 根据类型填充不同的字段
            if (type == 'plugin') {
                // 插件间对接
                $('#pluginHostname').val(hostname);
                $('#pluginApiKey').val(''); // API KEY编辑时留空，表示不修改
            } else {
                // API工单
                $('#configHostname').val(hostname);
                $('#configUsername').val(username);
                $('#configDepartmentId').val(deptId);
                $('#configTicketTitle').val(title);
                $('#configTicketContent').val(content);
                $('#configApiKey').val(''); // API密钥编辑时留空，表示不修改
            }
            
            onConfigTypeChange();

            $('#modalTitle').text('编辑上游配置');
            
            // 编辑时隐藏必填标记，显示提示文字
            if (type == 'plugin') {
                $('#pluginApiKeyRequired').hide();
                $('#pluginApiKeyHelp').text('编辑时留空表示不修改API KEY');
            } else {
                $('#apiKeyRequired').hide();
                $('#apiKeyHelp').show();
            }
            
            $('#configModal').show();
        }

        function saveConfig() {
            var configType = $('#configType').val();
            var data = {
                id: $('#configId').val(),
                type: configType,
                name: $('#configName').val().trim(),
                api_audit_type: $('#configAuditType').val()
            };

            // 根据类型添加不同的字段
            if (configType == 'api') {
                data.hostname = $('#configHostname').val().trim();
                data.username = $('#configUsername').val().trim();
                data.api_key = $('#configApiKey').val();
                data.ticket_department_id = $('#configDepartmentId').val();
                data.ticket_title = $('#configTicketTitle').val().trim();
                data.ticket_content = $('#configTicketContent').val().trim();
            } else {
                data.hostname = $('#pluginHostname').val().trim();
                data.api_key = $('#pluginApiKey').val().trim();
                data.username = '';
                data.ticket_department_id = 0;
                data.ticket_title = '';
                data.ticket_content = '';
            }

            // 验证
            if (!data.name) {
                layer.msg('配置名称不能为空', {
                    icon: 2
                });
                return;
            }
            if (!data.hostname) {
                layer.msg(configType == 'api' ? 'API主机地址不能为空' : '上游插件地址不能为空', {
                    icon: 2
                });
                return;
            }
            if (configType == 'api' && !data.username) {
                layer.msg('API用户名不能为空', {
                    icon: 2
                });
                return;
            }
            if (!data.api_key && data.id == 0) {
                layer.msg(configType == 'api' ? 'API密钥不能为空' : 'API KEY不能为空', {
                    icon: 2
                });
                return;
            }

            $.ajax({
                url: '{:shd_addon_url("AutoRefund://AdminIndex/saveApiConfig")}',
                type: 'POST',
                data: data,
                dataType: 'json',
                success: function(res) {
                    if (res.code == 200) {
                        layer.msg(res.msg, {
                            icon: 1
                        });
                        closeModal();
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
                    } else {
                        layer.msg(res.msg, {
                            icon: 2
                        });
                    }
                },
                error: function() {
                    layer.msg('请求失败', {
                        icon: 2
                    });
                }
            });
        }

        function deleteConfig(id) {
            layer.confirm('确定要删除此配置吗？', {
                btn: ['确定', '取消']
            }, function() {
                $.ajax({
                    url: '{:shd_addon_url("AutoRefund://AdminIndex/deleteApiConfig")}',
                    type: 'POST',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.code == 200) {
                            layer.msg(res.msg, {
                                icon: 1
                            });
                            setTimeout(function() {
                                location.reload();
                            }, 1000);
                        } else {
                            layer.msg(res.msg, {
                                icon: 2
                            });
                        }
                    },
                    error: function() {
                        layer.msg('请求失败', {
                            icon: 2
                        });
                    }
                });
            });
        }

        // 点击模态框外部关闭
        $(window).click(function(e) {
            if (e.target.id == 'configModal') {
                closeModal();
            }
        });
    </script>
</body>