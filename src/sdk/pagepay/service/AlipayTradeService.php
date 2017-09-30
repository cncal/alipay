<?php

namespace Cncal\Alipay\Sdk\Pagepay\Service;

use Exception;
use Cncal\Alipay\Sdk\Aop\AopClient;
use Cncal\Alipay\Sdk\Aop\Request\AlipayTradePagePayRequest;
use Cncal\Alipay\Sdk\Aop\Request\AlipayTradeQueryRequest;
use Cncal\Alipay\Sdk\Aop\Request\AlipayTradeRefundRequest;
use Cncal\Alipay\Sdk\Aop\Request\AlipayTradeCloseRequest;
use Cncal\Alipay\Sdk\Aop\Request\AlipayTradeFastpayRefundQueryRequest;
use Cncal\Alipay\Sdk\Aop\Request\AlipayDataDataserviceBillDownloadurlQueryRequest;

class AlipayTradeService
{
    public $appid;

    public $private_key;

    public $token = NULL;

    public $format = "json";

    public $signtype = "RSA2";

    public $charset = "UTF-8";

    public $alipay_public_key;

    public $gateway_url = "https://openapi.alipay.com/gateway.do";

    function __construct($config)
    {
        $this->gateway_url = $config['gateway_url'];
        $this->appid = $config['app_id'];
        $this->private_key = $config['merchant_private_key'];
        $this->alipay_public_key = $config['alipay_public_key'];
        $this->charset = $config['charset'];
        $this->signtype = $config['sign_type'];

        if (empty($this->appid) || trim($this->appid) == "") {
            throw new Exception("appid should not be NULL!");
        }

        if (empty($this->private_key) || trim($this->private_key) == "") {
            throw new Exception("private_key should not be NULL!");
        }

        if (empty($this->alipay_public_key) || trim($this->alipay_public_key) == "") {
            throw new Exception("alipay_public_key should not be NULL!");
        }

        if (empty($this->charset) || trim($this->charset) == "") {
            throw new Exception("charset should not be NULL!");
        }

        if (empty($this->gateway_url) || trim($this->gateway_url) == "") {
            throw new Exception("gateway_url should not be NULL!");
        }
    }

    /**
     * alipay.trade.page.pay
     * @param $builder
     * @param $return_url
     * @param $notify_url
     * @return $response
     */
    function pagePay($builder, $return_url, $notify_url)
    {
        $biz_content = $builder->getBizContent();
        $this->writeLog($biz_content);

        $request = new AlipayTradePagePayRequest();
        $request->setNotifyUrl($notify_url);
        $request->setReturnUrl($return_url);
        $request->setBizContent($biz_content);

        // 首先调用支付api
        $response = $this->aopclientRequestExecute($request, TRUE);
        // $response = $response->alipay_trade_wap_pay_response;
        return $response;
    }

    /**
     * sdkClient
     * @param $request
     * @param $ispage
     * @return $response
     */
    function aopclientRequestExecute($request, $is_page = FALSE)
    {
        $aop = new AopClient();
        $aop->gatewayUrl = $this->gateway_url;
        $aop->appId = $this->appid;
        $aop->rsaPrivateKey = $this->private_key;
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $aop->apiVersion = "1.0";
        $aop->postCharset = $this->charset;
        $aop->format = $this->format;
        $aop->signType = $this->signtype;
        $aop->debugInfo = TRUE;

        if ($is_page) {
            $result = $aop->pageExecute($request, "post");
            echo $result;
        } else {
            $result = $aop->execute($request);
        }

        //打开后，将报文写入log文件
        $this->writeLog("response: " . var_export($result, TRUE));
        return $result;
    }

    /**
     * alipay.trade.query (统一收单线下交易查询)
     * @param $builder
     * @return $response
     */
    function Query($builder)
    {
        $biz_content = $builder->getBizContent();
        $this->writeLog($biz_content);

        $request = new AlipayTradeQueryRequest();
        $request->setBizContent($biz_content);

        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_query_response;

        return $response;
    }

    /**
     * alipay.trade.refund (统一收单交易退款接口)
     * @param $builder
     * @return $response
     */
    function Refund($builder)
    {
        $biz_content = $builder->getBizContent();
        $this->writeLog($biz_content);

        $request = new AlipayTradeRefundRequest();
        $request->setBizContent($biz_content);

        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_refund_response;

        return $response;
    }

    /**
     * alipay.trade.close (统一收单交易关闭接口)
     * @param $builder
     * @return $response
     */
    function Close($builder)
    {
        $biz_content = $builder->getBizContent();
        $this->writeLog($biz_content);

        $request = new AlipayTradeCloseRequest();
        $request->setBizContent($biz_content);

        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_trade_close_response;

        return $response;
    }

    /**
     * alipay.trade.fastpay.refund.query (统一收单交易退款查询)
     * @param $builder
     * @return $response
     */
    function refundQuery($builder)
    {
        $biz_content = $builder->getBizContent();
        $this->writeLog($biz_content);

        $request = new AlipayTradeFastpayRefundQueryRequest();
        $request->setBizContent($biz_content);

        $response = $this->aopclientRequestExecute($request);

        return $response;
    }

    /**
     * alipay.data.dataservice.bill.downloadurl.query (查询对账单下载地址)
     * @param $builder
     * @return $response
     */
    function downloadurlQuery($builder)
    {
        $biz_content = $builder->getBizContent();
        $this->writeLog($biz_content);

        $request = new AlipayDataDataserviceBillDownloadurlQueryRequest();
        $request->setBizContent($biz_content);

        $response = $this->aopclientRequestExecute($request);
        $response = $response->alipay_data_dataservice_bill_downloadurl_query_response;

        return $response;
    }

    /**
     * 验签方法
     * @param $arr
     * @return boolean
     */
    function check($arr)
    {
        $aop = new AopClient();
        $aop->alipayrsaPublicKey = $this->alipay_public_key;
        $result = $aop->rsaCheckV1($arr, $this->alipay_public_key, $this->signtype);

        return $result;
    }

    /**
     * 打印日志
     */
    function writeLog($text)
    {
        file_put_contents(storage_path('logs/alipay.log'), date("Y-m-d H:i:s")."  ".$text."\r\n", FILE_APPEND);
    }
}