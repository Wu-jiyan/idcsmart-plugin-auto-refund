<script src="/plugins/addons/auto_refund/assets/layer.js"></script>
  <section class="admin-main">
    <div class="container-fluid">
      <div class="page-container">
        <div class="card">
          <div class="card-body">
            <div class="card-title row"> <div style="padding:0 15px;">{$Title}</div>
              <div class="col-lg-8 col-md-12 col-sm-12">
                {foreach $PluginsAdminMenu as $v}
                  {if $v['custom']}
                    <span  class="ml-2"><a  class="h5" href="{$v.url}" target="_blank">{$v.name}</a></span>
                  {else/}
                    <span  class="ml-2"> <a  class="h5" href="{$v.url}">{$v.name}</a></span>
                  {/if}
                {/foreach}
              </div>
            </div>
            <div class="tab-content mt-4">
              <div class="table-body">
                <form class="form" id="config">
                 <div class="form-group row d-none">
                    <label class="require">网站名称
                    </label>
                    <div class="col-sm-4">
                      <input class="form-control" type="text" name="webname" value="{$Data.webname}">
                        <div class="invalid-feedback">
                      </div>
                      <div style="border: 1px solid green; padding: 5px; color: red;">请输入网站名称，用于显示消息来源</div>
                    </div>
                    
                    <label class="require">魔方授权码
                    </label>
                    <div class="col-sm-4">
                      <input class="form-control" type="text" name="mfauth" value="{$Data.mfauth}">
                        <div class="invalid-feedback"></div>
                        <div style="border: 1px solid green; padding: 5px; color: red;">请输入正确的已购买插件的魔方系统授权码，授权码状态：{$MismatchMessage}</div>
                      </div>
                      </div>
                 <div class="form-group row d-none">
                    <label class="require">授权站长邮箱
                    </label>
                    <div class="col-sm-4">
                      <input class="form-control" type="text" name="zzemail" value="{$Data.zzemail}">
                        <div class="invalid-feedback">
                      </div>
                      <div style="border: 1px solid green; padding: 5px; color: red;">请输入站长邮箱</div>
                    </div>
                    <label class="require">授权QQ
                    </label>
                    <div class="col-sm-4">
                      <input class="form-control" type="text" name="zzqq" value="{$Data.zzqq}">
                        <div class="invalid-feedback"></div>
                        <div style="border: 1px solid green; padding: 5px; color: red;">请输入授权QQ</div>
                      </div>
                    </div>
                    
                 <div class="form-group row">
                    <label class="require">代理退款时间
                    </label>
                    <div class="col-sm-4">
                      <input class="form-control" type="text" name="day" value="{$Data.day}">
                        <div class="invalid-feedback">
                      </div>
                      <div style="border: 1px solid green; padding: 5px; color: red;">请输入代理可退款时间，在普通用户退款时间基础上增加，单位：天，如不额外增加，输入0</div>
                    </div>
                    <label class="require">可显示订单
                    </label>
                    <div class="col-sm-4">
                      <input class="form-control" type="text" name="displaytime" value="{$Data.displaytime}">
                        <div class="invalid-feedback"></div>
                        <div style="border: 1px solid green; padding: 5px; color: red;">请输入可显示订单的时间，产品到期后在退款列表显示的时间，单位：天</div>
                      </div>
                    </div>
                    
                 <div class="form-group row">
                    <label class="require">代理退款
                    </label>
                    <div class="col-sm-4">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="agent" value="0"> <!-- 默认值为0 -->
                            <input type="checkbox" class="custom-control-input" id="agent" name="agent" value="1" {if $Data.agent == 1}checked{/if}>
                            <label class="custom-control-label" for="agent">代理退款不限制首次</label>
                        </div>
                    </div>
                    
                   </div>
                    
                 <div class="form-group row">
                    <label class="require">自动清理
                    </label>
                    <div class="col-sm-4">
                        <div class="custom-control custom-switch">
                            <input type="hidden" name="open" value="0"> <!-- 默认值为0 -->
                            <input type="checkbox" class="custom-control-input" id="open" name="open" value="1" {if $Data.open == 1}checked{/if}>
                            <label class="custom-control-label" for="open">自动清理产品列表中已下架（隐藏）的产品设置</label>
                        </div>
                    </div>
                    
                    </div>
                  
                </form>
                <div class="form-group row">
                  <div class="col-sm-10">
                    <button type="button" onclick="submit()" class="btn btn-primary w-md">保存更改</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  





<script>
/**
 * 产品退款插件 - 功能设置页面 JS
 * 已还原为可读代码
 */

/**
 * 提交设置表单
 */
function submit() {
    // 获取表单数据
    var formData = {
        webname: $('input[name="webname"]').val(),
        mfauth: $('input[name="mfauth"]').val(),
        zzemail: $('input[name="zzemail"]').val(),
        zzqq: $('input[name="zzqq"]').val(),
        day: $('input[name="day"]').val(),
        displaytime: $('input[name="displaytime"]').val(),
        agent: $('input[name="agent"]:checked').val() || 0,
        open: $('input[name="open"]:checked').val() || 0
    };

    // 表单验证
    if (!formData.day || formData.day < 0) {
        layer.msg('代理可退时间必须大于等于0', {icon: 2, time: 2000});
        return;
    }
    if (!formData.displaytime || formData.displaytime < 1) {
        layer.msg('过期订单显示时间必须大于1', {icon: 2, time: 2000});
        return;
    }

    // 显示加载层
    var loadIndex = layer.load(2);

    // 发送AJAX请求
    $.ajax({
        url: '{:shd_addon_url(\'AutoRefund://AdminIndex/submit\')}',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            layer.close(loadIndex);
            if (response.code === 200) {
                layer.msg(response.msg, {icon: 1, time: 2000});
            } else {
                layer.msg(response.msg || '保存失败', {icon: 2, time: 2000});
            }
        },
        error: function(xhr, status, error) {
            layer.close(loadIndex);
            layer.msg('请求失败，请稍后重试', {icon: 2, time: 2000});
            console.error('Submit error:', error);
        }
    });
}
</script>

