<style>
    td .btn-sm {
        border-radius: 0 !important;
    }
    td .badge {
        font-size: 12px;
        padding: 0.4em 0.5em;
        border-radius: 0;
    }
    .api-key-box {
        background: #f8f9fa;
        border: 1px dashed #dee2e6;
        padding: 10px;
        font-family: 'Courier New', monospace;
        word-break: break-all;
    }
</style>

<script src="/plugins/addons/auto_refund/assets/layer.js"></script>

<div class="container-fluid py-4">

    <div class="card">
        <div class="card-body p-0">
            {if $apiKey}
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped mb-0">
                    <thead class="thead-light">
                        <tr class="text-center">
                            <th>API KEY</th>
                            <th>上游地址</th>
                            <th>创建时间</th>
                            <th>状态</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="text-center">
                            <td>
                                <div class="api-key-box">{$apiKey.api_key}</div>
                            </td>
                            <td>
                                <div class="api-key-box">{$apiEndpoint}</div>
                            </td>
                            <td>{$apiKey.created_at}</td>
                            <td>
                                <span class="badge badge-success"><i class="fas fa-check-circle"></i> 正常</span>
                            </td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" onclick="generateApiKey()">
                                    <i class="fas fa-sync-alt mr-1"></i>重新生成
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" onclick="deleteApiKey()">
                                    <i class="fas fa-trash-alt mr-1"></i>删除
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-3">
                <div class="alert alert-info mb-0">
                    <h6><i class="fas fa-info-circle mr-2"></i>使用说明</h6>
                    <p class="mb-0">如果您是上游供应商，请将以下信息提供给下游站点用于插件间对接：</p>
                    <ul class="mb-0 mt-2">
                        <li><strong>上游地址：</strong>{$apiEndpoint}</li>
                        <li><strong>API KEY：</strong>{$apiKey.api_key}</li>
                    </ul>
                    <p class="mt-2 mb-0 text-muted">下游站点将在其后台【上游API配置】中添加以上信息，实现插件间退款对接。</p>
                </div>
            </div>
            {else}
            <div class="text-center py-5">
                <i class="fas fa-key fa-4x text-muted mb-4"></i>
                <h5 class="text-muted">您还没有生成API KEY</h5>
                <p class="text-muted">生成API KEY后，您可以在其他站点使用本插件时，通过插件间对接方式处理退款申请</p>
                <button type="button" class="btn btn-primary mt-3" onclick="generateApiKey()">
                    <i class="fas fa-plus mr-2"></i>生成API KEY
                </button>
            </div>
            {/if}
        </div>
    </div>
</div>

<script>
    /**
     * 生成API KEY
     */
    function generateApiKey() {
        layer.confirm('确定要生成新的API KEY吗？旧的KEY将立即失效，已配置的其他站点将无法继续使用。', {
            btn: ['确定', '取消'],
            icon: 3
        }, function() {
            var loadIndex = layer.load();

            $.ajax({
                url: '/addons?_plugin=auto_refund&_controller=index&_action=generateApiKey',
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    layer.close(loadIndex);
                    if (res.code === 200) {
                        layer.alert('API KEY生成成功！', {icon: 1}, function() {
                            location.reload();
                        });
                    } else {
                        layer.alert(res.msg || '生成失败', {icon: 2});
                    }
                },
                error: function() {
                    layer.close(loadIndex);
                    layer.msg('请求失败，请稍后重试');
                }
            });
        });
    }

    /**
     * 删除API KEY
     */
    function deleteApiKey() {
        layer.confirm('确定要删除API KEY吗？删除后其他站点将无法通过插件间对接方式提交退款申请。', {
            btn: ['确定', '取消'],
            icon: 3
        }, function() {
            var loadIndex = layer.load();

            $.ajax({
                url: '/addons?_plugin=auto_refund&_controller=index&_action=deleteApiKey',
                type: 'POST',
                dataType: 'json',
                success: function(res) {
                    layer.close(loadIndex);
                    if (res.code === 200) {
                        layer.alert('API KEY已删除', {icon: 1}, function() {
                            location.reload();
                        });
                    } else {
                        layer.alert(res.msg || '删除失败', {icon: 2});
                    }
                },
                error: function() {
                    layer.close(loadIndex);
                    layer.msg('请求失败，请稍后重试');
                }
            });
        });
    }
</script>
