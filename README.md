# Alipay(web) for laravel
[支付宝官方文档](https://docs.open.alipay.com/270/105898/)   
[English Document](https://github.com/cncal/alipay/wiki)

## 安装
* 执行命名：
```sh
$ composer require cncal/alipay
```

* 如果 Laravel 版本小于5.5：  
    * 添加 `AlipayServiceProvider` 至 `config/app` 的 `providers`：
    ```php
    Cncal\Alipay\AlipayServiceProvider::class,
    ```

    * 添加 Facade 至 `config/app` 的 `aliases`：
    ```php
    'Alipay' => Cncal\Alipay\Facades\Alipay::class,
    ```

* 发布资源文件 `config/alipay.php` 和 `storage/logs/alipay.log`：
```sh
$ php artisan vendor:publish --provider="Cncal\Alipay\AlipayServiceProvider"
```

## 配置
在 `config/alipay.php` 中配置支付宝信息：
```php
return [
    'gateway_url' => "https://openapi.alipay.com/gateway.do",

    'app_id' => "",

    'alipay_public_key' => "",

    'merchant_private_key' => "",

    'charset' => "UTF-8",

    'sign_type' => "RSA2",

    'notify_url' => "",

    'return_url' => "",
];
```
> `merchant_private_key` 和 `merchant_public_key` 可由支付宝开放平台提供的[RSA签名验签工具](https://docs.open.alipay.com/291/105971)生成。 
   生成之后在平台配置密钥，即可获取 `alipay_public_key`。如有问题，请查阅[参考文档](https://docs.open.alipay.com/200/105310)。

* `gateway_url`：支付宝网关
* `app_id`：创建应用后获取的 app_id
* `alipay_public_key`：与 app_id 对应的支付宝公钥，[查看地址](https://openhome.alipay.com/platform/keyManage.htm) 
* `merchant_private_key`：RSA 签名验签工具生成的商家私钥
* `charset`：仅支持 GBK 和 UTF-8
* `sign_type`：生成商家公钥和私钥的签名方式，目前仅支持 RSA 和 RSA2, 推荐使用 RSA2
* `notify_url`：支付成功后的异步通知地址，支付宝用 POST 方式请求该地址，所以确保该地址可以被访问到，尤其当你的网站有认证机制的时候
* `return_url`：支付成功后的同步回调地址，尽量不要在该地址后添加任何参数，例如 `?date=***`。如果必须这样做，在 `Alipay::check` 之前 `unset` 它们

## 使用

```php
<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Alipay;

class AlipayController extends Controller 
{
    /**
     * 支付.
     * https://docs.open.alipay.com/api_1/alipay.trade.pay
     */
    public function pay(Request $request)
    {
       $this->validate($request, [
            // 商户订单号,64个字符以内、可包含字母、数字、下划线；需保证在商户端不重复
           'out_trade_no' => 'required|unique:orders,out_trade_no|max:64|alpha_num',
           // 订单标题
           'title' => 'required',
           // 订单描述
           'description' => 'nullable',
           // 订单总金额，单位为元，精确到小数点后两位，取值范围[0.01,100000000] 
           'total_amount' => 'required|numeric|between:0.01,100000000',
       ]);
       
       $data = $request->only(['out_trade_no', 'title', 'description', 'total_amount']);
       
       Alipay::pay($data);
    }
    
    /**
     * 交易查询
     * https://docs.open.alipay.com/api_1/alipay.trade.query 
     */
    public function query(Request $request)
    {
       $this->validate($request, [
           // 订单支付时传入的商户订单号，和支付宝交易号不能同时为空 
           // trade_no, out_trade_no 如果同时存在优先取 trade_no
           'out_trade_no' => 'required_without:trade_no',
       ]);
       
       $data = $request->only(['trade_no', 'out_trade_no']);
       
       $response = Alipay::query($data);
    }
    
    /**
     * 退款.
     * https://docs.open.alipay.com/api_1/alipay.trade.refund
     */
    public function refund(Request $request)
    {
       $this->validate($request, [
           'out_trade_no' => 'required_without:trade_no',
           // 标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传
           'out_request_no' => 'nullable|max:64',
           'refund_reason' => 'nullable',
           // 需要退款的金额，该金额不能大于订单金额，单位为元，支持两位小数
           'refund_amount' => 'required',
       ]);
       
       $data = $request->only(['trade_no', 'out_trade_no', 'out_request_no', 'refund_reason', 'refund_amount']);

       $response = Alipay::refund($data);
    } 
    
    /**
     * 退款查询.
     * https://docs.open.alipay.com/api_1/alipay.trade.fastpay.refund.query
     */
    public function refundQuery(Request $request)
    {
       $this->validate($request, [
           'out_trade_no' => 'required_without:trade_no',
           // 请求退款接口时，传入的退款请求号，如果在退款请求时未传入，则该值为创建交易时的外部交易号
           'out_request_no' => 'required',
       ]);
       
       $data = $request->only(['trade_no', 'out_trade_no', 'out_request_no']);
       
       $response = Alipay::refundQuery($data);
    }
    
    /**
     * 关闭交易
     * https://docs.open.alipay.com/api_1/alipay.trade.close
     */
    public function close(Request $request)
    {
       $this->validate($request, [
           'out_trade_no' => 'required_without:trade_no',
       ]);
       
       $data = $request->only(['trade_no', 'out_trade_no']);
       
       $response = Alipay::close($data);
    }
    
    /**
     * 异步通知方法
     * https://docs.open.alipay.com/270/105902/
     */
    public function paidNotify()
    {
        if (Alipay::check($_POST)) {
            // 支付是否成功以异步通知为准，所以可以在此方法中进行一些操作，例如更新订单状态，通知用户支付结果
            $out_trade_no = $_POST['out_trade_no'];
            $trade_no = $_POST['trade_no'];
            $trade_status = $_POST['trade_status'];
            
            if ($_POST['trade_status'] == 'TRADE_FINISHED') {
                // 交易已完成，不能退款
            } else if ($_POST['trade_status'] == 'TRADE_SUCCESS') {
                // 支付成功
            }
            
            // 不要修改或删除此行
            echo "success";
        } else {
            echo "fail";
        }
    }
    
    /**
     * 同步回调方法
     */
    public function paidReturn()
    {
        // 如果配置的同步回调地址有附加参数，例如 `?date=***`，unset 它们 (unset($_GET['data']))
        if (Alipay::check($_GET)) {
            // 一般地，跳转到支付成功页面
            $out_trade_no = $_GET['out_trade_no'];
            $trade_no = $_GET['trade_no'];
            
            return view('trade_success');
        } else {
            echo "fail";
        }
    }
}
?> 
```