<?php
return [
    'hocmai_url'           => env('HOCMAI_URL', 'https://hocmai.vn'),
    'HOCMAI_PAYMENT_PATH'  => env('HOCMAI_PAYMENT_PATH', 'payment/quickpay/?bill='),
    'enable_push_traffic'  => true,
    'PAYGATE_URL'          => env('PAYGATE_URL', 'https://paygate.hocmai.vn/payment/pgw?bill='),
    'COUPON_KEY'           => env('COUPON_KEY', '94a8b75b8bcd51b428a3beeaa6ab0cc3'),
    'API_COUPON'           => env('API_COUPON', 'https://hocmai.vn/api/coupon/event'),
    'TOKEN_SEND_SMS'       => env('TOKEN_SEND_SMS','7269b80c7a2c34c1efd4bf12a867820f'),
    'TOKEN_SEND_MAIL'      => env('TOKEN_SEND_MAIL','eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.8JElynQzkmljb4DS4vVtRSZszoBhQFo5vFrW1iWyox8'),
    'API_COUPON_CHECK'     => env('API_COUPON_CHECK', 'https://hocmai.vn/api/payment/get-cart-data'),
    'KEY_API_COUPON_CHECK' => env('KEY_API_COUPON_CHECK', 'c762fef66c04b79a15a95694fac3486b'),
    'VQMM_FAHASA'          => [
        'ADDRESS_ONSIDE'   => '',   /* Tỉnh thành ưu tiên */
        'ARRAY_GIFTS_HBTP' => '34,35,26,16,14',           /* ID các học bổng toàn phần và giải đặc biệt */
        'SPECIAL_GIFT'     => '34',                    /* ID giải đặc biệt */
        'START_TIME_FIRST' => '2023-05-08',            /* Thời gian bắt đầu sự kiện đợt 1 */
        'END_TIME_FIRST'   => '2023-05-19',            /* Thời gian kết thúc sự kiện đợt 1 */
        'START_TIME_FINAL' => '2023-05-29',            /* Thời gian bắt đầu sự kiện đợt cuối */
        'END_TIME_FINAL'   => '2023-06-15',            /* Thời gian kết thúc sự kiện đợt cuối */
        'ID_CBMM_FAHASA'   => '30',
        'IGNORE_GIFT'      => '34', /**Danh sách các quà không cho vào vòng quay */
        'START_GOAL_SPECIAL_GIFT' => '2023-07-31 20:00:00', /**Thời gian bắt đầu ra quà đặc biệt */
        'END_GOAL_SPECIAL_GIFT'   => '2023-07-31 23:40:00', /**Thời gian kết thúc ra quà đặc biệt */
    ],
    'ADMIN' => [
        'is_send'   => env('SEND_EMAIL_TO_ADMIN',false),
        'email'     => 'lamkh@hocmai.vn',
        'full_name' => 'Kiều Hải Lâm'
    ]
];