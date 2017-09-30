<?php

return [
    /**
     * Gateway.
     */
    'gateway_url' => "https://openapi.alipay.com/gateway.do",

    /**
     * The app_id you get after creating your application.
     */
    'app_id' => "",

    /**
     * The alipay public key corresponding to the app_id.
     */
    'alipay_public_key' => "",

    /**
     * Merchant private key.
     */
    'merchant_private_key' => "",

    /**
     * Only GBK and UTF-8 is supported.
     */
    'charset' => "UTF-8",

    /**
     * The signature algorithm type is used to generate merchant keys.
     * Only RSA and RSA2 is supported so far, and RSA2 is recommended.
     */
    'sign_type'=>"RSA2",

    /**
     * The url which will be called by alipay server asynchronously
     * via POST method when your customers pay successfully.
     */
    'notify_url' => "",

    /**
     * The url which will be called by alipay server synchronously via GET method.
     */
    'return_url' => "",
];
