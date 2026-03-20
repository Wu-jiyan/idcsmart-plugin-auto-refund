

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="https://www.layuicdn.com/layer/layer.css">
  <link rel="stylesheet" href="https://cdnjs.25y.cn/ajax/libs/select2/4.0.13/css/select2.min.css">
  <style>
    .form-group.row {
      display: flex;
      align-items: center;
    }

    .form-group.row label {
      flex: 0 0 120px;
    }

    .form-group.row .col-sm-4 {
      flex: 1;
    }
    .tip-text {
      margin-left: 10px;
      color: #888;
      font-size: 12px;
    }
    
    /* 自定义产品选择器样式 */
    .product-selector {
      border: 1px solid #d9d9d9;
      border-radius: 4px;
      max-height: 400px;
      overflow-y: auto;
      background: #fff;
    }
    
    .product-group {
      border-bottom: 1px solid #f0f0f0;
    }
    
    .product-group:last-child {
      border-bottom: none;
    }
    
    .group-header {
      padding: 10px 15px;
      background: #fafafa;
      font-weight: bold;
      color: #333;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: background 0.2s;
    }
    
    .group-header:hover {
      background: #f0f0f0;
    }
    
    .group-header .group-name {
      font-size: 14px;
    }
    
    .group-header .select-group-btn {
      font-size: 12px;
      color: #1890ff;
      padding: 2px 8px;
      border: 1px solid #1890ff;
      border-radius: 3px;
      background: #fff;
    }
    
    .group-header .select-group-btn:hover {
      background: #1890ff;
      color: #fff;
    }
    
    .group-products {
      padding: 5px 0;
    }
    
    .product-item {
      padding: 8px 15px 8px 30px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: space-between;
      transition: background 0.2s;
    }
    
    .product-item:hover {
      background: #e6f7ff;
    }
    
    .product-item.selected {
      background: #e6f7ff;
      color: #1890ff;
    }
    
    .product-item .product-info {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .product-item .product-name {
      font-size: 13px;
    }
    
    .product-item .product-tags {
      display: flex;
      gap: 5px;
    }
    
    .product-item .tag {
      font-size: 11px;
      padding: 1px 5px;
      border-radius: 3px;
    }
    
    .product-item .tag-local {
      background: #52c41a;
      color: #fff;
    }
    
    .product-item .tag-api {
      background: #1890ff;
      color: #fff;
    }
    
    .product-item .tag-hidden {
      background: #ff4d4f;
      color: #fff;
    }
    
    .product-item .tag-normal {
      background: #52c41a;
      color: #fff;
    }
    
    .product-item .check-icon {
      display: none;
      color: #1890ff;
    }
    
    .product-item.selected .check-icon {
      display: block;
    }
    
    .selected-products-bar {
      margin-top: 10px;
      padding: 8px 12px;
      background: #f6ffed;
      border: 1px solid #b7eb8f;
      border-radius: 4px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }
    
    .selected-products-bar .count {
      color: #52c41a;
      font-weight: bold;
    }
    
    .selected-products-bar .clear-btn {
      color: #999;
      cursor: pointer;
      font-size: 12px;
    }
    
    .selected-products-bar .clear-btn:hover {
      color: #666;
    }
    
    .search-input {
      width: 100%;
      padding: 8px 12px;
      border: 1px solid #d9d9d9;
      border-radius: 4px;
      margin-bottom: 10px;
      font-size: 13px;
    }
    
    .search-input:focus {
      outline: none;
      border-color: #1890ff;
    }
  </style>
</head>

<body>
  <section class="admin-main">
    <div class="container-fluid">
      <div class="page-container">
        <div class="card">
          <div class="card-body">
            <div class="card-title row">
              <div style="padding:0 15px;">{$Title}</div>
              <div class="col-lg-8 col-md-12 col-sm-12">
                          <div class="col-lg-8 col-md-12 col-sm-12">
              {foreach $PluginsAdminMenu as $v}
                {if $v['custom']}
                  <span class="ml-2"><a class="h5" href="{$v.url}" target="_blank">{$v.name}</a></span>
                {else/}
                  <span class="ml-2"><a class="h5" href="{$v.url}">{$v.name}</a></span>
                {/if}
              {/foreach}
            </div>
                <!-- Your navigation links here -->
              </div>
            </div>

            <div class="tab-content mt-4">
              <div class="table-body">
                <form class="form" id="config">
                  <div class="form-group row">

                    <label class="require">退款方式</label>
                    <div class="col-sm-4">
                      <select class="form-control" name="type" id="refund-type" onchange="onRefundTypeChange()">
                        <option value="1">人工审核</option>
                        <option value="2">自动退款</option>
                        <option value="3">自动API工单退款(Beta)</option>
                        <option value="4">插件间对接</option>
                      </select>
                      
                      <div style="border: 1px solid green; padding: 5px; color: red;">请选择退款方式，自营产品可选人工或自动退，对接产品建议选择人工审核</div>
                    </div>
                    
                    <label class="require">退款要求</label>
                    <div class="col-sm-4">
                      <select class="form-control" name="request">
                        <option value="1">首次订购（限制次数）</option>
                        
                        <option value="3">订购X小时内（不限次数）</option>
                      </select>
                      
                      <div style="border: 1px solid green; padding: 5px; color: red;">请选择退款要求，首次订购‘同一个商品限退一次’，X小时内‘同一个商品不同订单不限次数’</div>
                    </div>
                    </div>
                    <div class="form-group row">
                    <label class="require">退款时间</label>
                    <div class="col-sm-4">
                      <input class="form-control" type="text" name="within">
                      <div style="border: 1px solid green; padding: 5px; color: red;">请输入退款时间，单位小时，订购后的规定时间内可申请退款</div>
                    </div>
                    <label class="require">退款规则</label>
                    <div class="col-sm-4">
                      <select class="form-control" name="rules">
                        <option value="1">按时长退（按申请时间至到期时间剩余时间计算退款金额）</option>
                        <option value="2">按月退款（按月不足一个月按一个月计算，超过一个月不足2个月按2个月计算，以此类推）</option>
                        <option value="3">全额退款（按订购金额全额退款）</option>
                      </select>
                      
                      <div style="border: 1px solid green; padding: 5px; color: red;">请选择退款规则</div>
                    </div>
                    </div>
                    
                                
                      <!-- API退款设置区域 - 仅对接产品显示 -->
                      <div class="form-group row" id="api-refund-section" style="display: none;">
                          <label class="require">API退款设置</label>
                          <div class="col-sm-8">
                              <div style="border: 1px solid #d9d9d9; padding: 15px; border-radius: 4px; background: #fafafa;">
                                  <div style="margin-bottom: 15px;">
                                      <label style="font-weight: bold; margin-bottom: 5px; display: block;">选择上游配置 <span style="color: red;">*</span></label>
                                      <select class="form-control" name="api_config_id" id="api-config-select" style="width: 400px;" onchange="onApiConfigChange()">
                                          <option value="">请选择上游API配置</option>
                                      </select>
                                      <div style="margin-top: 10px;">
                                          <a href="{:shd_addon_url('AutoRefund://AdminIndex/apiConfig')}" target="_blank" class="btn btn-sm btn-primary">管理上游配置</a>
                                          <span style="color: #888; font-size: 12px; margin-left: 10px;">如果没有合适的配置，请先创建上游API配置</span>
                                      </div>
                                  </div>
                                  
                                  <div id="api-config-info" style="display: none; margin-bottom: 15px; padding: 10px; background: #f0f0f0; border-radius: 4px;">
                                      <div style="margin-bottom: 5px;">
                                          <span style="color: #666; display: inline-block; width: 80px;">配置类型：</span>
                                          <span id="info-type" style="font-weight: 500;"></span>
                                      </div>
                                      <div style="margin-bottom: 5px;">
                                          <span style="color: #666; display: inline-block; width: 80px;">上游地址：</span>
                                          <span id="info-hostname" style="font-weight: 500;"></span>
                                      </div>
                                      <div style="margin-bottom: 5px;">
                                          <span style="color: #666; display: inline-block; width: 80px;">用户名：</span>
                                          <span id="info-username" style="font-weight: 500;"></span>
                                      </div>
                                      <div>
                                          <span style="color: #666; display: inline-block; width: 80px;">审核方式：</span>
                                          <span id="info-audit-type" style="font-weight: 500;"></span>
                                      </div>
                                  </div>
                              </div>
                              <div style="border: 1px solid green; padding: 5px; color: red; margin-top: 5px;">对接产品退款功能仅适用于对接产品，选择后会使用配置好的上游配置自动处理退款</div>
                          </div>
                      </div>

                      <div class="form-group row">
                          <label class="require">选择产品</label>
                          <div class="col-sm-8">
                              <!-- 搜索框 -->
                              <input type="text" class="search-input" id="product-search" placeholder="搜索产品名称...">
                              
                              <!-- 自定义产品选择器 -->
                              <div class="product-selector" id="product-selector">
                                  {php}
                                      $groupedProducts = [];
                                      $apiProducts = [];
                                      foreach($keproducts as $product) {
                                          $groupName = $product['group_name'] ?: '未分组';
                                          if(!isset($groupedProducts[$groupName])) {
                                              $groupedProducts[$groupName] = [];
                                          }
                                          $groupedProducts[$groupName][] = $product;
                                          // 收集对接产品信息
                                          if(!empty($product['api_name']) && !empty($product['api_id'])) {
                                              $apiInfo = \Think\Db::name('zjmf_finance_api')->where('id', $product['api_id'])->find();
                                              if($apiInfo) {
                                                  $apiProducts[$product['id']] = [
                                                      'hostname' => $apiInfo['hostname'],
                                                      'username' => $apiInfo['username']
                                                  ];
                                              }
                                          }
                                      }
                                  {/php}
                                  <!-- 存储对接产品API信息 -->
                                  <script>
                                      apiProductsInfo = {:json_encode($apiProducts)};
                                  </script>
                                  {foreach $groupedProducts as $groupName => $products}
                                  <div class="product-group" data-group="{$groupName}">
                                      <div class="group-header" onclick="toggleGroup('{$groupName}')">
                                          <span class="group-name">{$groupName}</span>
                                          <button type="button" class="select-group-btn" onclick="event.stopPropagation();selectGroup('{$groupName}')">全选</button>
                                      </div>
                                      <div class="group-products" id="group-{$groupName}">
                                          {foreach $products as $product}
                                          <div class="product-item" 
                                               data-id="{$product.id}" 
                                               data-name="{$product.name}"
                                               data-group="{$groupName}"
                                               onclick="toggleProduct({$product.id})">
                                              <div class="product-info">
                                                  <span class="product-name">{$product.name}</span>
                                                  <span class="product-tags">
                                                      {if empty($product.api_name)}
                                                      <span class="tag tag-local">本地接口</span>
                                                      {else}
                                                      <span class="tag tag-api">对接:{$product.api_name}</span>
                                                      {/if}
                                                      {if $product.hidden == '1'}
                                                      <span class="tag tag-hidden">隐藏</span>
                                                      {else}
                                                      <span class="tag tag-normal">正常</span>
                                                      {/if}
                                                  </span>
                                              </div>
                                              <i class="fas fa-check check-icon"></i>
                                          </div>
                                          {/foreach}
                                      </div>
                                  </div>
                                  {/foreach}
                              </div>
                              
                              <!-- 已选择产品显示 -->
                              <div class="selected-products-bar" id="selected-bar" style="display: none;">
                                  <span>已选择 <span class="count" id="selected-count">0</span> 个产品</span>
                                  <span class="clear-btn" onclick="clearAllSelection()">清空选择</span>
                              </div>
                              
                              <!-- 隐藏的选择输入框 -->
                              <input type="hidden" name="products[]" id="selected-products-input">
                              
                              <div style="border: 1px solid green; padding: 5px; color: red; margin-top: 5px;">点击分组名称可展开/收起，点击"全选"按钮可选择该分组下所有产品</div>
                          </div>
                      </div>

                  <div class="form-group row">
                    <div class="col-sm-10">
                      <button type="button" onclick="submitForm()" class="btn btn-primary w-md">保存更改</button>
                    </div>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
  <script src="/plugins/addons/auto_refund/assets/layer.js"></script>
  <link rel="stylesheet" href="https://cdnjs.25y.cn/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <script>
/**
 * 产品退款插件 - 添加产品页面 JS
 * 已还原为可读代码
 */

// 存储选中的产品ID
var selectedProducts = [];
var selectedApiProduct = null;

// 对接产品API信息（从产品选择器区域传递过来）
var apiProductsInfo = apiProductsInfo || {};

// 退款类型切换
function onRefundTypeChange() {
    var type = document.getElementById('refund-type').value;
    var apiSection = document.getElementById('api-refund-section');
    var configLabel = document.querySelector('#api-refund-section label.require');
    var configSelect = document.getElementById('api-config-select');
    
    if (type == '3' || type == '4') {
        apiSection.style.display = 'flex';
        
        // 根据类型更新标签和加载对应的配置列表
        if (type == '3') {
            configLabel.textContent = 'API退款设置';
            configSelect.options[0].text = '请选择API工单配置';
        } else {
            configLabel.textContent = '插件间对接设置';
            configSelect.options[0].text = '请选择插件间对接配置';
        }
        
        // 清空并重新加载配置列表
        configSelect.innerHTML = '<option value="">请选择上游配置</option>';
        loadApiConfigList(type);
    } else {
        apiSection.style.display = 'none';
    }
}

// 存储API配置列表
var apiConfigList = [];

// 加载上游API配置列表
function loadApiConfigList(refundType) {
    var select = document.getElementById('api-config-select');
    
    // 根据退款类型确定配置类型
    var configType = '';
    if (refundType == '3') {
        configType = 'api';
    } else if (refundType == '4') {
        configType = 'plugin';
    }
    
    $.ajax({
        url: '{:shd_addon_url("AutoRefund://AdminIndex/getApiConfigList")}',
        type: 'GET',
        data: {type: configType},
        dataType: 'json',
        success: function(res) {
            if (res.code == 200) {
                apiConfigList = res.data || [];
                // 清空并重新填充下拉框
                select.innerHTML = '<option value="">' + (configType == 'api' ? '请选择API工单配置' : '请选择插件间对接配置') + '</option>';
                for (var i = 0; i < apiConfigList.length; i++) {
                    var config = apiConfigList[i];
                    var option = document.createElement('option');
                    option.value = config.id;
                    option.text = config.name + ' (' + config.hostname + ')';
                    select.appendChild(option);
                }
            } else {
                layer.msg('加载配置列表失败：' + res.msg, {icon: 2});
            }
        },
        error: function() {
            layer.msg('加载配置列表失败', {icon: 2});
        }
    });
}

// API配置选择变化
function onApiConfigChange() {
    var select = document.getElementById('api-config-select');
    var configId = select.value;
    var infoDiv = document.getElementById('api-config-info');
    
    if (!configId) {
        infoDiv.style.display = 'none';
        return;
    }
    
    // 查找选中的配置
    var config = null;
    for (var i = 0; i < apiConfigList.length; i++) {
        if (apiConfigList[i].id == configId) {
            config = apiConfigList[i];
            break;
        }
    }
    
    if (config) {
        document.getElementById('info-hostname').textContent = config.hostname;
        document.getElementById('info-username').textContent = config.username || '-';
        document.getElementById('info-audit-type').textContent = 
            config.api_audit_type == 1 ? '人工审核' : '自动入账';
        
        // 根据配置类型显示不同的标签
        var typeLabel = document.getElementById('info-type');
        if (typeLabel) {
            typeLabel.textContent = config.config_type == 'plugin' ? '插件间对接' : 'API工单';
        }
        
        infoDiv.style.display = 'block';
    }
}

// 更新API信息显示
function updateApiInfo() {
    var hostnameInput = document.getElementById('api-hostname');
    var usernameInput = document.getElementById('api-username');
    
    if (selectedProducts.length === 0) {
        // 没有选择产品时清空
        hostnameInput.value = '';
        usernameInput.value = '';
        selectedApiProduct = null;
        return;
    }
    
    // 检查所有选中产品的API来源
    var firstApiInfo = null;
    var hasLocalProduct = false;
    var hasApiProduct = false;
    var differentApi = false;
    
    for (var i = 0; i < selectedProducts.length; i++) {
        var productId = selectedProducts[i];
        var apiInfo = apiProductsInfo[productId];
        
        if (apiInfo) {
            // 是对接产品
            hasApiProduct = true;
            if (firstApiInfo === null) {
                firstApiInfo = apiInfo;
            } else {
                // 检查是否同一个上游
                if (apiInfo.hostname !== firstApiInfo.hostname || apiInfo.username !== firstApiInfo.username) {
                    differentApi = true;
                }
            }
        } else {
            // 是本地产品
            hasLocalProduct = true;
        }
    }
    
    // 验证逻辑
    if (hasLocalProduct && hasApiProduct) {
        // 同时选择了本地产品和对接产品
        hostnameInput.value = '';
        usernameInput.value = '';
        selectedApiProduct = null;
        layer.msg('错误：不能同时选择本地接口产品和对接产品', {icon: 2, time: 3000});
        return;
    }
    
    if (differentApi) {
        // 选择了不同上游的对接产品
        hostnameInput.value = '';
        usernameInput.value = '';
        selectedApiProduct = null;
        layer.msg('错误：不能同时选择不同上游的对接产品', {icon: 2, time: 3000});
        return;
    }
    
    // 验证通过，显示上游信息
    if (firstApiInfo) {
        hostnameInput.value = firstApiInfo.hostname || '';
        usernameInput.value = firstApiInfo.username || '';
        selectedApiProduct = selectedProducts[0];
    } else {
        // 全是本地产品
        hostnameInput.value = '';
        usernameInput.value = '';
        selectedApiProduct = null;
    }
}

// 搜索功能 - 简单实现
function filterProducts() {
    var keyword = document.getElementById('product-search').value.toLowerCase();
    var items = document.getElementsByClassName('product-item');
    var groups = document.getElementsByClassName('product-group');
    
    // 过滤产品
    for (var i = 0; i < items.length; i++) {
        var name = items[i].getAttribute('data-name');
        var groupName = items[i].getAttribute('data-group');
        var matchName = name && name.toLowerCase().indexOf(keyword) >= 0;
        var matchGroup = groupName && groupName.toLowerCase().indexOf(keyword) >= 0;
        if (matchName || matchGroup) {
            items[i].style.display = 'flex';
        } else {
            items[i].style.display = 'none';
        }
    }
    
    // 更新分组显示
    for (var j = 0; j < groups.length; j++) {
        var groupName = groups[j].getAttribute('data-group');
        var matchGroupName = groupName && groupName.toLowerCase().indexOf(keyword) >= 0;
        var visibleItems = groups[j].querySelectorAll('.product-item[style*="flex"], .product-item:not([style*="none"])');
        if (visibleItems.length === 0 && !matchGroupName && keyword !== '') {
            groups[j].style.display = 'none';
        } else {
            groups[j].style.display = 'block';
            // 如果匹配分组名，展开该分组
            if (matchGroupName) {
                groups[j].querySelector('.group-products').style.display = 'block';
            }
        }
    }
}

document.getElementById('product-search').addEventListener('input', filterProducts);

// 切换分组展开/收起
function toggleGroup(groupName) {
    var groupProducts = $('#group-' + groupName);
    groupProducts.slideToggle(200);
}

// 选择/取消选择单个产品
function toggleProduct(productId) {
    var index = selectedProducts.indexOf(productId);
    var productItem = $('.product-item[data-id="' + productId + '"]');
    
    // 检查是否是API退款方式且尝试选择本地接口产品
    var refundType = document.getElementById('refund-type').value;
    if (index === -1 && refundType == '3') {
        // 尝试选择新产品，检查是否是本地接口
        if (!apiProductsInfo[productId]) {
            layer.msg('错误：API退款方式只能选择对接产品，不能选择本地接口产品', {icon: 2, time: 3000});
            return;
        }
    }
    
    if (index === -1) {
        // 添加选择
        selectedProducts.push(productId);
        productItem.addClass('selected');
    } else {
        // 取消选择
        selectedProducts.splice(index, 1);
        productItem.removeClass('selected');
    }
    
    updateSelectedDisplay();
    updateApiInfo();
}

// 全选/取消全选分组
function selectGroup(groupName) {
    var groupDiv = document.getElementById('group-' + groupName);
    var items = groupDiv.getElementsByClassName('product-item');
    var ids = [];
    var allSelected = true;
    var refundType = document.getElementById('refund-type').value;
    
    // 收集该分组所有产品ID
    for (var i = 0; i < items.length; i++) {
        var id = parseInt(items[i].getAttribute('data-id'));
        ids.push(id);
        if (selectedProducts.indexOf(id) === -1) {
            allSelected = false;
        }
    }
    
    // 检查是否是API退款方式且分组包含本地接口产品
    if (!allSelected && refundType == '3') {
        var hasLocalProduct = false;
        for (var idx = 0; idx < ids.length; idx++) {
            if (!apiProductsInfo[ids[idx]]) {
                hasLocalProduct = true;
                break;
            }
        }
        if (hasLocalProduct) {
            layer.msg('错误：API退款方式只能选择对接产品，该分组包含本地接口产品', {icon: 2, time: 3000});
            return;
        }
    }
    
    if (allSelected) {
        // 取消全选
        for (var j = 0; j < ids.length; j++) {
            var idx = selectedProducts.indexOf(ids[j]);
            if (idx > -1) {
                selectedProducts.splice(idx, 1);
            }
        }
        for (var k = 0; k < items.length; k++) {
            items[k].classList.remove('selected');
        }
    } else {
        // 全选
        for (var m = 0; m < ids.length; m++) {
            if (selectedProducts.indexOf(ids[m]) === -1) {
                selectedProducts.push(ids[m]);
            }
        }
        for (var n = 0; n < items.length; n++) {
            items[n].classList.add('selected');
        }
    }
    
    updateSelectedDisplay();
    updateApiInfo();
}

// 更新已选择显示
function updateSelectedDisplay() {
    var count = selectedProducts.length;
    $('#selected-count').text(count);
    $('#selected-products-input').val(selectedProducts.join(','));
    
    if (count > 0) {
        $('#selected-bar').show();
    } else {
        $('#selected-bar').hide();
    }
}

// 清空所有选择
function clearAllSelection() {
    selectedProducts = [];
    $('.product-item').removeClass('selected');
    updateSelectedDisplay();
    updateApiInfo();
}

/**
 * 提交产品配置表单
 */
function submitForm() {
    var refundType = $('select[name="type"]').val();
    
    // 获取表单数据
    var formData = {
        type: refundType,
        request: $('select[name="request"]').val(),
        within: $('input[name="within"]').val(),
        rules: $('select[name="rules"]').val(),
        selected_products: selectedProducts
    };
    
    // API退款类型和插件间对接类型额外验证
    if (refundType == '3' || refundType == '4') {
        // 检查是否选择了上游配置
        var apiConfigId = $('#api-config-select').val();
        if (!apiConfigId) {
            var msg = refundType == '3' ? '请选择上游API配置' : '请选择插件间对接配置';
            layer.msg(msg, {icon: 2, time: 2000});
            return;
        }
        
        formData.api_config_id = apiConfigId;
    }

    // 表单验证
    if (!selectedProducts || selectedProducts.length === 0) {
        layer.msg('请选择至少一个产品', {icon: 2, time: 2000});
        return;
    }
    if (!formData.within || formData.within <= 1) {
        layer.msg('请输入支持的退款时间，单位为小时，且必须大于1', {icon: 2, time: 2000});
        return;
    }

    // 显示加载层
    var loadIndex = layer.load(2);

    // 发送AJAX请求
    $.ajax({
        url: '{:shd_addon_url(\'AutoRefund://AdminIndex/submitActivitys\')}',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(response) {
            layer.close(loadIndex);
            if (response.code === 200) {
                layer.msg(response.msg, {icon: 1, time: 2000}, function() {
                    window.location.reload();
                });
            } else {
                layer.msg(response.msg || '添加失败', {icon: 2, time: 2000});
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
  

  
  
</body>

</html>
