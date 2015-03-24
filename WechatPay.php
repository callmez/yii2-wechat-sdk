<?php
namespace callmez\wechat\sdk;

use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\base\InvalidParamException;
use Yii;
use callmez\wechat\sdk\WechatBasePay;
use yii\web\HttpException;
use yii\log\Logger;

/**
 * 微信公众号支付API类
 * 相关文档请参考 http://mp.weixin.qq.com/wiki 微信公众平台开发者文档
 *
 * @package callmez\wechat\components
 * @version 1.0.0alpha
 */
class WechatPay extends WechatBasePay
{

    

    /**
     *  作用：获取结果，默认不使用证书
     */
    private function getResult()
    {
        $this->postXml();
        $this->result = $this->xmlToArray($this->response);
        return $this->result;
    }

    /**
     * 刷卡支付
     * @return [type] [description]
     */
    public function getMicropay()
    {
        $this->url = self::WECHAT_MICRO_PAY_URL;
        return $this->getResult();
    }

    /**
     * 统一支付
     * @return [type] [description]
     */
    public function getpay()
    {
        $this->url = self::WECHAT_UNIFIED_PAY_URL;
        return $this->getResult();
    }
}
