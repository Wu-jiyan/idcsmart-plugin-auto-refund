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
.img-fluid {
    max-height: 100%; 
    max-width: 100%; 
    display: block; 
    margin: 0 auto; 
}
.pending {
    background-color: #FFD700; 
    color: #333; 
    padding: 5px 10px; 
    border-radius: 5px; 
    display: inline-block; 
}
.approved {
    background-color: #008000; 
    color: #fff; 
    padding: 5px 10px;
    border-radius: 5px;
    display: inline-block;
}


.rejected {
    background-color: #FF0000; 
    color: #fff; 
    padding: 5px 10px;
    border-radius: 5px;
    display: inline-block;
}
.circle {
    background-color: #800080; 
    color: #fff; 
    padding: 5px 10px;
    border-radius: 5px;
    display: inline-block;
}

    .center {
        text-align: center;
    }

    .coupon-code-cell {
        padding: 10px;
        border: 1px solid #ccc;
        font-size: 14px;
        color: #333;
        background-color: #f5f5f5;
        border-radius: 6px;
        display: inline-block;
        transition: transform 0.3s, background-color 0.3s;
        cursor: pointer;
    }

    .coupon-code-cell:hover {
        transform: scale(1.05);
        background-color: #f0f0f0;
    }

    .center {
        text-align: center;
    }
    .product-names-cell {
        max-width: 500px;
        padding: 10px;
        border: 1px solid #ccc;
        font-size: 14px;
        color: #333;
        border-radius: 6px;
        display: inline-block;
        word-wrap: break-word;
    }
.discount-type {
    font-weight: bold;
    text-align: center; 
    padding: 10px;
    border: 1px solid #ccc;
    display: flex; 
    justify-content: center; 
    align-items: center; 
}


.discount-types {
    font-weight: bold;
    text-align: center; 
}
    .discount-cell {
        font-weight: bold;
        text-align: center;
        padding: 10px;
        border: 1px solid #ccc;
    }
    .discount-label {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        font-size: 14px;
    }
    .percent-discount {
        background-color: #ffbc21;
        color: #333;
    }
    .fixed-discount {
        background-color: green;
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
    .qq-discount {
        background-color: red;
        color: white;
    }
    .bank-discount {
        background-color: #d0b03e;
        color: white;
    }
    .shichang {
        background-color: #00cc64;
        color: #333;
    }
    .yue {
        background-color: #ffd3ad;
        color: #333;
    }
    .quane {
        background-color: #FFD700;
        color: #333;
    }
    .shou-discount {
        background-color: #007bff;
        color: #fff;
    }
    .tong-discount {
        background-color: green;
        color: white;
    }
    .x-discount {
        background-color: red;
        color: white;
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

.status-label {
  position: relative;
}

.status-label::before {
  content: attr(data-tooltip); 
  position: absolute;
  background-color: #333;
  color: white;
  padding: 5px;
  border-radius: 5px;
  display: none;
  z-index: 1;
  top: 100%; 
  left: 50%;
  transform: translateX(-50%);
}
.status-label:hover::before {
  display: block; 
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
    .batch-actions {
        margin: 15px 0;
        padding: 10px;
        background-color: #f8f9fa;
        border-radius: 5px;
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
    .select-all-checkbox {
        cursor: pointer;
    }
    .item-checkbox {
        cursor: pointer;
    }
    </style>

<body>

            
  <section class="admin-main">
    <div class="container-fluid">
      <div class="page-container">
        <div class="card">
          <div class="card-body">
            <div class="card-title row"> <div style="padding:0 15px;">{$Title}</div>
              <div class="col-lg-8 col-md-12 col-sm-12">
                {foreach $PluginsAdminMenu as $admin}
                  {if $admin['custom']}
                    <span  class="ml-2"><a  class="h5" href="{$admin.url}" target="_blank">{$admin.name}</a></span>
                  {else/}
                    <span  class="ml-2"> <a  class="h5" href="{$admin.url}">{$admin.name}</a></span>
                  {/if}
                {/foreach}
              </div>
          </div>
          
          <!-- 搜索和批量操作区域 -->
          <div class="search-box">
              <input type="text" id="searchInput" class="form-control" placeholder="搜索产品ID、名称..." style="width: 300px;">
              <button type="button" class="btn btn-primary" onclick="searchProducts()">搜索</button>
              <button type="button" class="btn btn-secondary" onclick="clearSearch()">清空</button>
              
              <div class="page-size-selector" style="margin-left: 20px;">
                  <label>每页显示：</label>
                  <select id="pageSize" class="form-control" style="width: 80px;" onchange="changePageSize()">
                      <option value="20" {if $pageSize == 20}selected{/if}>20</option>
                      <option value="50" {if $pageSize == 50}selected{/if}>50</option>
                      <option value="100" {if $pageSize == 100}selected{/if}>100</option>
                      <option value="200" {if $pageSize == 200}selected{/if}>200</option>
                  </select>
                  <span>条</span>
              </div>
          </div>
          
          <!-- 批量操作按钮 -->
          <div class="batch-actions">
              <button type="button" class="btn btn-danger btn-sm" onclick="batchDelete()">
                  <i class="fas fa-trash-alt"></i> 批量删除
              </button>
              <span style="margin-left: 10px; color: #666;">已选择 <span id="selectedCount">0</span> 项</span>
          </div>
        
    <table>
        <thead>
            <tr>
                <th class="center"><input type="checkbox" class="select-all-checkbox" onclick="toggleSelectAll()"></th>
                <th class="center t1">ID</th>
                <th class="center">商品ID</th>
                <th class="center">商品状态</th>
                <th class="center">商品接口</th>
                <th class="center">商品类型</th>
                <th class="center">退款类型</th>
                <th class="center">退款方式</th>
                <th class="center">退款要求</th>
                <th class="center">可退款时间</th>
                <th class="center">操作</th>
            </tr>
        </thead>
        <tbody>
            {volist name='data' id='info'}
            <tr>
                <td class="center"><input type="checkbox" class="item-checkbox" value="{$info.id}" onclick="updateSelectedCount()"></td>
                <td class="center t1">{$info.id}</td>
                <td class="center"><span class="product-names-cell">[商品ID:{$info.productid}]{$info.name}</span></td>
                
                <td class="discount-types">
                    {if $info.hidden == '0'}
                    <span class="discount-label ren-discount">正常</span>
                    {elseif $info.hidden == '1'}
                    <span class="discount-label zi-discount">隐藏</span>
                    {else}
                    <span class="discount-label unknown-discount">未知类型</span>
                    {/if}
                </td>
                
                <td class="center t1">
                <span class="product-names-cell">
                    {if empty($info.api_name)}
                    <span class="discount-label ali-discount">
                        自动化资源
                        </span>
                    {else}
                    <span class="discount-label wx-discount">
                        对接:{$info.api_name}
                        </span>
                    {/if}
                    </span>
                </td>
                
                <td class="discount-types">
                    {if $info.hosttype == 'hostingaccount'}
                    <span class="discount-label ali-discount">虚拟主机</span>
                    {elseif $info.hosttype == 'cloud'}
                    <span class="discount-label wx-discount">云服务器</span>
                    {elseif $info.hosttype == 'dcimcloud'}
                    <span class="discount-label qq-discount">魔方云</span>
                    {elseif $info.hosttype == 'cdn'}
                    <span class="discount-label ali-discount">CDN</span>
                    {elseif $info.hosttype == 'server'}
                    <span class="discount-label wx-discount">独立服务器</span>
                    {elseif $info.hosttype == 'dcim'}
                    <span class="discount-label qq-discount">魔方DCIM</span>
                    {elseif $info.hosttype == 'bareMetal'}
                    <span class="discount-label ali-discount">裸金属</span>
                    {elseif $info.hosttype == 'software'}
                    <span class="discount-label wx-discount">软件产品</span>
                    {elseif $info.hosttype == 'ssl'}
                    <span class="discount-label qq-discount">SSL证书</span>
                    {elseif $info.hosttype == 'domain'}
                    <span class="discount-label ali-discount">域名</span>
                    {elseif $info.hosttype == 'sms'}
                    <span class="discount-label wx-discount">短信</span>
                    {else}
                    <span class="discount-label unknown-discount">未知类型</span>
                    {/if}
                </td>
                
                <td class="discount-types">
                    {if $info.type == '1'}
                    <span class="discount-label ren-discount">人工审核</span>
                    {elseif $info.type == '2'}
                    <span class="discount-label zi-discount">自动退款</span>
                    {elseif $info.type == '3'}
                    <span class="discount-label api-discount">API工单退款</span>
                    {elseif $info.type == '4'}
                    <span class="discount-label api-discount">插件间对接</span>
                    {else}
                    <span class="discount-label unknown-discount">未知类型</span>
                    {/if}
                </td>
                
                <td class="discount-types">
                    {if $info.rules == '1'}
                    <span class="discount-label shichang"><i class="fas fa-clock"></i>按时长退</span>
                    {elseif $info.rules == '2'}
                    <span class="discount-label yue"><i class="fas fa-calendar-alt"></i>按月退</span>
                    {elseif $info.rules == '3'}
                    <span class="discount-label quane"><i class="fas fa-money-bill-wave"></i>全额退</span>
                    {else}
                    <span class="discount-label unknown-discount">未知类型</span>
                    {/if}
                </td>
                
                <td class="discount-types">
                    {if $info.request == '1'}
                    <span class="discount-label shou-discount"><i class="fas fa-star"></i>产品首次</span>
                    {elseif $info.request == '2'}
                    <span class="discount-label tong-discount"><i class="fas fa-check-circle"></i>同类产品首次</span>
                    {elseif $info.request == '3'}
                    <span class="discount-label x-discount"><i class="fas fa-clock"></i>指定时间内</span>
                    {else}
                    <span class="discount-label unknown-discount">未知类型</span>
                    {/if}
                </td>
                
                

                <td class="center">
                    <span class="product-names-cell">订购{$info.within}小时内</span>
                </td>

                <td class="center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="del('{$info.id}')">
                      <i class="fas fa-times-circle"></i> 删除
                    </button>
                </td>

            </tr>
            {/volist}
        </tbody>
        
    </table>
            
            <div class="pagination">
            当前页码：{$currentPage = $fen->currentPage()}
            总页码：{$totalPages = $fen->lastPage()}
            总记录：{$fen->total()} 条
            {if $currentPage > 1}
                <a href="{:shd_addon_url('AutoRefund://AdminIndex/products')}&page={$currentPage - 1}&pageSize={$pageSize}&search={$search|urlencode}&languagesys=CN" class="pagination-link">上一页</a>
            {/if}
            {if $currentPage < $totalPages}
                <a href="{:shd_addon_url('AutoRefund://AdminIndex/products')}&page={$currentPage + 1}&pageSize={$pageSize}&search={$search|urlencode}&languagesys=CN" class="pagination-link">下一页</a>
            {/if}
            <div>
                跳转到第
                <input type="number" id="jumpToPage" min="1" max="{$totalPages}" value="{$currentPage + 1}" style="width: 40px;"> 页
                <button onclick="jumpToPage()" class="jump-button">跳转</button> 
            </div>
        </div>
    </div>
</div>

</section>



<script src="https://cdnjs.25y.cn/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    // 当前分页参数
    var currentPageSize = {$pageSize|default=20};
    var currentSearch = '{$search|default=""}';
    
    function jumpToPage() {
        var jumpInput = document.getElementById('jumpToPage');
        var targetPage = parseInt(jumpInput.value); 
        if (targetPage >= 1 && targetPage <= <?php echo $totalPages; ?>) {
            var targetUrl = "{:shd_addon_url('AutoRefund://AdminIndex/products')}&page=" + targetPage + "&pageSize=" + currentPageSize + "&search=" + encodeURIComponent(currentSearch) + "&languagesys=CN";
            window.location.href = targetUrl;
        }
    }
    
    // 搜索功能
    function searchProducts() {
        var searchValue = document.getElementById('searchInput').value.trim();
        var targetUrl = "{:shd_addon_url('AutoRefund://AdminIndex/products')}&page=1&pageSize=" + currentPageSize + "&search=" + encodeURIComponent(searchValue) + "&languagesys=CN";
        window.location.href = targetUrl;
    }
    
    // 清空搜索
    function clearSearch() {
        document.getElementById('searchInput').value = '';
        var targetUrl = "{:shd_addon_url('AutoRefund://AdminIndex/products')}&page=1&pageSize=" + currentPageSize + "&languagesys=CN";
        window.location.href = targetUrl;
    }
    
    // 改变每页显示数量
    function changePageSize() {
        var pageSize = document.getElementById('pageSize').value;
        var targetUrl = "{:shd_addon_url('AutoRefund://AdminIndex/products')}&page=1&pageSize=" + pageSize + "&search=" + encodeURIComponent(currentSearch) + "&languagesys=CN";
        window.location.href = targetUrl;
    }
    
    // 全选/取消全选
    function toggleSelectAll() {
        var selectAllCheckbox = document.querySelector('.select-all-checkbox');
        var itemCheckboxes = document.querySelectorAll('.item-checkbox');
        itemCheckboxes.forEach(function(checkbox) {
            checkbox.checked = selectAllCheckbox.checked;
        });
        updateSelectedCount();
    }
    
    // 更新已选择数量
    function updateSelectedCount() {
        var selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
        document.getElementById('selectedCount').textContent = selectedCheckboxes.length;
    }
    
    // 批量删除
    function batchDelete() {
        var selectedCheckboxes = document.querySelectorAll('.item-checkbox:checked');
        if (selectedCheckboxes.length === 0) {
            layer.msg('请至少选择一项', {icon: 2, time: 2000});
            return;
        }
        
        var ids = [];
        selectedCheckboxes.forEach(function(checkbox) {
            ids.push(checkbox.value);
        });
        
        layer.confirm('确定要删除选中的 ' + ids.length + ' 项吗？', {
            btn: ['确定', '取消'],
            title: '提示'
        }, function(index) {
            var loadIndex = layer.load(2);
            $.ajax({
                url: '{:shd_addon_url(\'AutoRefund://AdminIndex/batchDelete\')}',
                type: 'POST',
                data: { ids: ids },
                dataType: 'json',
                success: function(response) {
                    layer.close(loadIndex);
                    if (response.code === 200) {
                        layer.msg(response.msg, {icon: 1, time: 2000}, function() {
                            window.location.reload();
                        });
                    } else {
                        layer.msg(response.msg || '删除失败', {icon: 2, time: 2000});
                    }
                },
                error: function(xhr, status, error) {
                    layer.close(loadIndex);
                    layer.msg('请求失败，请稍后重试', {icon: 2, time: 2000});
                    console.error('Batch delete error:', error);
                }
            });
            layer.close(index);
        }, function(index) {
            layer.close(index);
        });
    }
    
    // 页面加载时设置搜索框值
    $(document).ready(function() {
        document.getElementById('searchInput').value = currentSearch;
    });
</script>


<script>
/**
 * 产品退款插件 - 产品列表页面 JS
 * 已还原为可读代码
 */

/**
 * 显示图片弹窗
 * @param {string} imageSrc - 图片地址
 */
function showImage(imageSrc) {
    var imageHtml = '<img src="' + imageSrc + '" class="img-fluid">';
    layer.open({
        type: 1,
        title: false,
        closeBtn: 1,
        shadeClose: true,
        area: ['auto', 'auto'],
        content: imageHtml
    });
}

/**
 * 删除产品退款配置
 * @param {string|number} id - 产品退款配置ID
 */
function del(id) {
    layer.confirm('确定要删除吗？', {
        btn: ['确定', '取消'],
        title: '提示'
    }, function(index) {
        $.ajax({
            url: '{:shd_addon_url(\'AutoRefund://AdminIndex/deletelists\')}',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if (response.code === 200) {
                    layer.msg(response.msg, {icon: 1, time: 2000});
                    setTimeout(function() {
                        window.location.reload();
                    }, 1500);
                } else {
                    layer.msg(response.msg || '删除失败', {icon: 2, time: 2000});
                }
            },
            error: function(xhr, status, error) {
                layer.msg('请求失败，请稍后重试', {icon: 2, time: 2000});
                console.error('Delete request error:', error);
            }
        });
        layer.close(index);
    }, function(index) {
        layer.close(index);
    });
}
</script>


