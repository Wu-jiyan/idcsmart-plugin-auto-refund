<script src="/plugins/addons/auto_refund/assets/layer.js"></script>
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


    </style>

<body>
  <section class="admin-main">
    <div class="container-fluid">
      <div class="page-container">
        <div class="card">
          <div class="card-body">
            <div class="card-title row"> <div style="padding:0 15px;">{$Title}</div>
              <div class="col-lg-8 col-md-12 col-sm-12">
                {foreach $PluginsAdminMenu as $Admin}
                  {if $Admin['custom']}
                    <span  class="ml-2"><a  class="h5" href="{$Admin.url}" target="_blank">{$Admin.name}</a></span>
                  {else/}
                    <span  class="ml-2"> <a  class="h5" href="{$Admin.url}">{$Admin.name}</a></span>
                  {/if}
                {/foreach}
              </div>
            </div>
    <table>
        <thead>
            <tr>
                <th class="center t1">ID</th>
                <th class="center">用户名</th>
                <th class="center">商品ID</th>
                <th class="center">主机ID</th>
                <th class="center">退款类型</th>
                <th class="center">退款规则</th>
                <th class="center">退款原因</th>
                <th class="center">退款金额</th>
                <th class="center">申请时间</th>
                <th class="center">审核人</th>
                <th class="center">状态</th>
                <th class="center">操作</th>
            </tr>
        </thead>
        <tbody>
            {volist name='data' id='info'}
            <tr>
                <td class="center t1">{$info.id}</td>
                <td class="center">
                    <span class="username-cell"><span class="product-names-cell"><a href="{$domain}/#/customer-view/abstract?id={$info.user_id}">{$info.username}</span></span>
                </td>
                
                
                <td class="center">{$info.productname}【ID：{$info.productid}】</td>

                <td class="center">
                    <span class="product-names-cell"><a href="{$domain}/#/customer-view/product-innerpage?id={$info.user_id}&hid={$info.hostid}">{$info.hostid}</span>
                </td>
                
                <td class="discount-types">
                    {if $info.type == '1'}
                    <span class="discount-label ali-discount">人工审核</span>
                    {elseif $info.type == '2'}
                    <span class="discount-label wx-discount">自动退款</span>
                    {elseif $info.type == '3'}
                    <span class="discount-label qq-discount">API工单退款</span>
                    {elseif $info.type == '4'}
                    <span class="discount-label api-discount">插件间对接</span>
                    {else}
                    <span class="discount-label unknown-discount">未知类型</span>
                    {/if}
                </td>
                
                <td class="discount-types">
                    {if $info.rules == '1'}
                    <span class="discount-label ali-discount">按时长退</span>
                    {elseif $info.rules == '2'}
                    <span class="discount-label wx-discount">按月退</span>
                    {elseif $info.rules == '3'}
                    <span class="discount-label qq-discount">全额退</span>
                    {else}
                    <span class="discount-label unknown-discount">未知类型</span>
                    {/if}
                </td>
                <td class="center">
                    <span class="product-names-cell">{$info.reasonrefund}</span>
                </td>
                <td class="center">
                    <span class="product-names-cell">{$info.amount}</span>
                </td>
                
                <td class="center">
                    <span class="product-names-cell">{:date('Y-m-d H:i:s',$info['created_time'])}</span>
                </td>
                
                
                <td class="center">
                <span class="product-names-cell">
                    <?php
                    $reviewed = $info['reviewed'];
                    if (!empty($reviewed)) {
                        echo $reviewed;
                    } else {
                        echo "待审核"; 
                    }
                    ?>
                    </span>
                </td>
                
                <td class="center">
                  {if $info.status == 1}
                    <span class="status-label pending" data-tooltip="待审核">
                      <i class="fas fa-clock"></i> 待审核
                    </span>
                  {elseif $info.status == 2}
                    <span class="status-label approved" data-tooltip="{$info.reason}">
                      <i class="fas fa-check-circle"></i> 审核通过
                    </span>
                  {elseif $info.status == 3}
                    <span class="status-label rejected" data-tooltip="{$info.reason}">
                      <i class="fas fa-times-circle""></i> 驳回申请
                    </span>
                  {elseif $info.status == 4}
                    <span class="status-label circle" data-tooltip="{$info.reason}">
                      <i class="fas fa-times-circle""></i> 取消申请
                    </span>
                  {else}
                    <span class="status-label circle" data-tooltip="{$info.reason}">
                      <i class="fas fa-exclamation-circle"></i> 未知类型
                    </span>
                  {/if}
                </td>
                <td class="center">
                  {if $info.status == 1}
                    <button type="button" class="btn btn-success btn-sm" onclick="agree('{$info.id}')">
                      <i class="fas fa-check-circle"></i> 同意
                    </button>
                    <button type="button" class="btn btn-danger btn-sm" onclick="reject('{$info.id}')">
                      <i class="fas fa-times-circle"></i> 驳回
                    </button>
                  {else}
                    <span class="text-muted">无</span>
                  {/if}
                </td>
            </tr>
            {/volist}
        </tbody>
        
    </table>
            
            <div class="pagination">
            当前页码：{$currentPage = $data->currentPage()}
            总页码：{$totalPages = $data->lastPage()}
            {if $currentPage > 1}
                <a href="{:shd_addon_url('AutoRefund://AdminIndex/lists')}&page={$currentPage - 1}&languagesys=CN" class="pagination-link">上一页</a>
            {/if}
            {if $currentPage < $totalPages}
                <a href="{:shd_addon_url('AutoRefund://AdminIndex/lists')}&page={$currentPage + 1}&languagesys=CN" class="pagination-link">下一页</a>
            {/if}
            <div>
                跳转到第
                <input type="number" id="jumpToPage" min="1" max="{$totalPages}" value="{$currentPage + 1}" style="width: 40px;">页
                <button onclick="jumpToPage()" class="jump-button">跳转</button> 
            </div>
        </div>
    </div>
</div>

</section>



<script>
    function jumpToPage() {
        var jumpInput = document.getElementById('jumpToPage');
        var targetPage = parseInt(jumpInput.value); 
        if (targetPage >= 1 && targetPage <= <?php echo $totalPages; ?>) {
            var targetUrl = "{:shd_addon_url('AutoRefund://AdminIndex/lists')}&page=" + targetPage + "&languagesys=CN";
            window.location.href = targetUrl;
        }
    }
</script>


<script>
/**
 * 产品退款插件 - 申请列表页面 JS
 * 已还原为可读代码
 */

/**
 * 同意退款申请
 * @param {string|number} id - 申请记录ID
 */
function agree(id) {
    layer.confirm('确定要同意该退款申请吗？', {
        btn: ['确定', '取消'],
        title: '提示'
    }, function(index) {
        var loadIndex = layer.load(2);
        
        $.ajax({
            url: '{:shd_addon_url(\'AutoRefund://AdminIndex/agreewith\')}',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                layer.close(loadIndex);
                if (response.code === 200) {
                    layer.msg(response.msg, {icon: 1, time: 2000}, function() {
                        window.location.reload();
                    });
                } else {
                    layer.msg(response.msg || '操作失败', {icon: 2, time: 2000});
                }
            },
            error: function(xhr, status, error) {
                layer.close(loadIndex);
                layer.msg('请求失败，请稍后重试', {icon: 2, time: 2000});
                console.error('Agree request error:', error);
            }
        });
        layer.close(index);
    }, function(index) {
        layer.close(index);
    });
}

/**
 * 驳回退款申请
 * @param {string|number} id - 申请记录ID
 */
function reject(id) {
    layer.prompt({
        formType: 2,
        value: '',
        title: '请输入驳回原因',
        area: ['300px', '100px']
    }, function(value, promptIndex) {
        if (!value || value.trim() === '') {
            layer.msg('请输入驳回原因', {icon: 2, time: 2000});
            return;
        }
        
        var loadIndex = layer.load(2);
        
        $.ajax({
            url: '{:shd_addon_url(\'AutoRefund://AdminIndex/refuse\')}',
            type: 'POST',
            data: { 
                id: id,
                reason: value
            },
            dataType: 'json',
            success: function(response) {
                layer.close(loadIndex);
                layer.close(promptIndex);
                if (response.code === 200) {
                    layer.msg(response.msg, {icon: 1, time: 2000}, function() {
                        window.location.reload();
                    });
                } else {
                    layer.msg(response.msg || '操作失败', {icon: 2, time: 2000});
                }
            },
            error: function(xhr, status, error) {
                layer.close(loadIndex);
                layer.msg('请求失败，请稍后重试', {icon: 2, time: 2000});
                console.error('Reject request error:', error);
            }
        });
    });
}
</script>
    
</body>
</html>