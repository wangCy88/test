<?php
return[
    //商户编号
    'customerNumber' => 'C1800372744',

    //公钥
    'publicKey' => app_path() . '/Utils/HeLiBao/Keys/pub.key',

    //私钥
    'privateKey' => app_path() . '/Utils/HeLiBao/Keys/pri.key',

    //rsa pem文件
    'rsaPem' => app_path() . '/Utils/HeLiBao/Keys/gounihua.pem',

    //helipay文件
    'enPem' => app_path() . '/Utils/HeLiBao/Keys/helipay.pem',

    //代付结果查询密钥
    'secretKey' => 'ixoc7PSBxMi2QjBTi3BHgC4chh6OVRiw',

    //md5快捷密钥
    'quickKey' => '1XBEHZQVRyMfhE7SltNCJt0cdpQu6rvT',

    //代付请求url
    //'transferUrl' => 'http://test.trx.helipay.com/trx/transfer/interface.action',
    'transferUrl' => 'http://transfer.trx.helipay.com/trx/transfer/interface.action',

    //快捷支付url
    //'quickUrl' => 'http://test.trx.helipay.com/trx/quickPayApi/interface.action',
    'quickUrl' => 'http://pay.trx.helipay.com/trx/quickPayApi/interface.action',

    //代付回调url
    'notifyTransferUrl' => 'https://cv.liangziloan.com/transferNotify',

    //快捷支付回调url
    'serverCallbackUrl' => 'https://cv.liangziloan.com/confirmPay',

    //快捷支付展期回调url
    'serverCallbackExtensionUrl' => 'https://cv.liangziloan.com/confirmPayExtension',

    //快捷支付信用回调url
    'serverCallbackCreditUrl' => 'https://cv.liangziloan.com/confirmPayCredit'
];
