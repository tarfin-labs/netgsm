<?php

return [
    'credentials' => [
        'user_code' => env('NETGSM_USERCODE'),
        'secret'    => env('NETGSM_SECRET'),
    ],
    'defaults'    => [
        'language'           => env('NETGSM_LANGUAGE', 'tr'),
        'header'             => env('NETGSM_HEADER', null),
        'sms_sending_method' => env('NETGSM_SMS_SENDING_METHOD', 'get'),
        'base_uri'           => env('NETGSM_BASE_URI', 'https://api.netgsm.com.tr'),
        'timeout'            => env('NETGSM_TIMEOUT', 60),
    ],
];
