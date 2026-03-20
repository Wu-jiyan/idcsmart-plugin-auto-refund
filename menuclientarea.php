<?php
return [[
    "name" => "产品退款",
    "url" => "",
    "fa_icon" => "bx bx-slider-alt",
    "lang" => [
        "chinese" => "产品退款",
        "chinese_tw" => "產品退款",
        "english" => "Product Refund"
    ],
    "child" => [
        [
            "name" => "自助退款",
            "url" => "AutoRefund://Index/index",
            "fa_icon" => "bx bx-highlight",
            "lang" => [
                "chinese" => "申请退款",
                "chinese_tw" => "申請退款",
                "english" => "Request refund"
            ],
            "child" => []
        ],
        [
            "name" => "API KEY管理",
            "url" => "AutoRefund://Index/apikey",
            "fa_icon" => "bx bx-key",
            "lang" => [
                "chinese" => "API KEY管理",
                "chinese_tw" => "API KEY管理",
                "english" => "API Key Management"
            ],
            "child" => []
        ]
    ]
]];
