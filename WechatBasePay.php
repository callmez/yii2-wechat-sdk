<?php
namespace callmez\wechat\sdk;

use Yii;
use callmez\wechat\sdk\WechatBasic;
use yii\log\Logger;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use yii\web\HttpException;

class WechatBasePay extends WechatBasic
{
    public $parameters; //请求参数，类型为关联数组
    public $response;//微信返回的响应
    public $result;//返回参数，类型为关联数组
    public $url;//接口链接
    public $curlTimeout;//curl超时时间
    public $payType;//支付类型 默认不写就是统一的支付类型
    public $prepayId;//使用统一支付接口得到的预支付id

    /**
     * @var string 受理商ID，身份标识
     */
    public $mchid;
    /**
     * @var string 商户支付密钥Key。审核通过后，在微信发送的邮件中查看
     */
    public $key;

    /**
     * 统一支付接口URL
     */
    const WECHAT_PAY_URL = 'https://api.mch.weixin.qq.com/pay';
    /**
     * 刷卡支付
     */
    const WECHAT_MICRO_PAY_URL = '/micropay';
    /**
     * 统一支付
     */
    const WECHAT_UNIFIED_PAY_URL = '/unifiedorder';

    /**
     *  作用：生成签名
     */
    public function getSign($Obj)
    {
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $string = $this->formatBizQueryParaMap($Parameters, false);
        //echo '【string1】'.$string.'</br>';
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $this->key;
        //echo "【string2】".$string."</br>";
        //签名步骤三：MD5加密
        $string = md5($string);
        //echo "【string3】 ".$string."</br>";
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($string);
        //echo "【result】 ".$result_."</br>";
        return $result_;
    }

    /**
     *  作用：格式化参数，签名过程需要使用
     */
    public function formatBizQueryParaMap($paraMap, $urlencode)
    {
        $buff = '';
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            //$buff .= strtolower($k) . "=" . $v . "&";
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = '';
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }


    /**
     *  作用：产生随机字符串，不长于32位
     */
    public function createNoncestr( $length = 32 )
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str.= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }

    /**
     *    作用：打印数组
     */
    public function printErr($wording = '', $err = '')
    {
        print_r('<pre>');
        echo $wording . "</br>";
        var_dump($err);
        print_r('</pre>');
    }

    /**
     * 设置参数
     */
    public function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    public function trimString($value)
    {
        $ret = null;
        if (null != $value) {
            $ret = $value;
            if (strlen($ret) == 0) {
                $ret = null;
            }
        }
        return $ret;
    }


    /**
     *  作用：post请求xml
     */
    public function postXml()
    {
        $xml = $this->createXml();
        // \Yii::getLogger()->log('Xml::::'.$xml, Logger::LEVEL_INFO);
        //echo "<textarea style='width:600px;height:400px;'>{$xml}</textarea>";
        $this->response = $this->postXmlCurl($xml, $this->url, $this->curlTimeout);
        return $this->response;
    }

    /**
     *  作用：设置标配的请求参数，生成签名，生成接口参数xml
     */
    public function createXml()
    {
        try
        {
            // 检测必填参数
            // if($this->parameters["out_trade_no"] == null)
            // {
            //     throw new SDKRuntimeException("缺少统一支付接口必填参数out_trade_no！"."<br>");
            // }elseif($this->parameters["body"] == null){
            //     throw new SDKRuntimeException("缺少统一支付接口必填参数body！"."<br>");
            // }elseif ($this->parameters["total_fee"] == null ) {
            //     throw new SDKRuntimeException("缺少统一支付接口必填参数total_fee！"."<br>");
            // }elseif ($this->parameters["notify_url"] == null) {
            //     throw new SDKRuntimeException("缺少统一支付接口必填参数notify_url！"."<br>");
            // }elseif ($this->parameters["trade_type"] == null) {
            //     throw new SDKRuntimeException("缺少统一支付接口必填参数trade_type！"."<br>");
            // }elseif ($this->parameters["trade_type"] == "JSAPI" &&
            //     $this->parameters["openid"] == NULL){
            //     throw new SDKRuntimeException("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！"."<br>");
            // }
            $this->parameters["appid"] = $this->appId; //公众账号ID
            $this->parameters["mch_id"] = $this->mchid; //商户号
            $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR'];//终端ip
            $this->parameters["nonce_str"] = $this->createNoncestr();//随机字符串
            $this->parameters["sign"] = $this->getSign($this->parameters);//签名
            return  $this->arrayToXml($this->parameters);
        }catch (SDKRuntimeException $e)
        {
            die($e->errorMessage());
        }
    }

    /**
     *    作用：array转xml
     */
    public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";

            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     *    作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xml, $url, $second = 30)
    {

         \Yii::getLogger()->log('xml::'.$xml, Logger::LEVEL_INFO);
         \Yii::getLogger()->log('url::'.$url, Logger::LEVEL_INFO);
        if (stripos($url, 'http://') === false && stripos($url, 'https://') === false) {
            $url = self::WECHAT_PAY_URL . $url;
        }
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //curl_close($ch);
        //返回结果
        \Yii::getLogger()->log('data::'.$data, Logger::LEVEL_INFO);
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }


    /**
     *    作用：将xml转为array
     */
    public function xmlToArray($xml)
    {
        //将XML转为array
        $array_data = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $array_data;
    }

    /**
     *  作用：使用证书post请求xml
     */
    public function postXmlSSL()
    {
        $xml = $this->createXml();
        $this->response = $this->postXmlSSLCurl($xml, $this->url, $this->curlTimeout);
        return $this->response;
    }

    /**
     *    作用：使用证书，以post方式提交xml到对应的接口url
     */
    public function postXmlSSLCurl($xml, $url, $second = 30)
    {
        if (stripos($url, 'http://') === false && stripos($url, 'https://') === false) {
            $url = self::WECHAT_PAY_URL . $url;
        }
        $ch = curl_init();
        //超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        //这里设置代理，如果有的话
        //curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        //curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //设置证书
        //使用证书：cert 与 key 分别属于两个.pem文件
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, WxPayConf_pub::SSLCERT_PATH);
        //默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, WxPayConf_pub::SSLKEY_PATH);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    /**
     * 获取prepay_id
     */
    public function getPrepayId()
    {
        $this->createXml();
        $this->result = $this->xmlToArray($xml);
        // echo "<textarea style='width:600px;height:400px;'>{$this->response}</textarea>";
        $prepayId = $this->result["prepay_id"];
        return $prepayId;
    }

    /**
     *  作用：设置prepay_id
     */
    public function setPrepayId($prepayId)
    {
        $this->prepayId = $prepayId;
    }

}