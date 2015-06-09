<?php
namespace callmez\wechat\sdk\mp;

use callmez\wechat\sdk\components\WechatComponent;

/**
 * 卡卷组件(文档v2.0)
 * @package callmez\wechat\sdk\components\mp
 */
class Card extends WechatComponent
{

    /**
     * 上传图片
     */
    const WECHAT_IMG_UPLOAD_PREFIX = '/cgi-bin/media/uploadimg';
    /**
     * 上传图片(门卡和卡卷通用)
     * @param $filePath
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function uploadImage($filePath)
    {
        $result = $this->wechat->httpPost(self::WECHAT_IMG_UPLOAD_PREFIX, [
            'buffer' => class_exists('\CURLFile') ? new \CURLFile($filePath) : '@' . $filePath
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);

        return isset($result['url']) ? $result['url'] : false;
    }

    /* ==== 门店管理(文档V2.2.3) ===== */

    /**
     * 创建门店
     */
    const WECHAT_POI_ADD_PREFIX = '/cgi-bin/poi/addpoi';
    /**
     * 创建门店
     * @param array $data
     * @return bool
     */
    public function addPoi(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_POI_ADD_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 查询单个门店
     */
    const WECHAT_POI_GET_PREFIX = '/cgi-bin/poi/getpoi';
    /**
     * 查询单个门店
     * @param $poiId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getPoi($poiId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_POI_GET_PREFIX, [
            'poi_id' => $poiId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['business'] : false;
    }

    /**
     * 查询门店列表
     */
    const WECHAT_POI_LIST_GET_PREFIX = '/cgi-bin/poi/getpoilist';
    /**
     * 查询门店列表
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getPoiList(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_POI_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['business_list'] : false;
    }

    /**
     * 删除门店
     */
    const WECHAT_POI_DELETE_PREFIX = '/cgi-bin/poi/delpoi';
    /**
     * 删除门店
     * @param $poiId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deletePoi($poiId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_POI_GET_PREFIX, [
            'poi_id' => $poiId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 更新门店信息
     */
    const WECHAT_POI_UPDATE_PREFIX = '/cgi-bin/poi/updatepoi';
    /**
     * 更新门店信息
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updatePoi(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_POI_UPDATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /* ==== 卡卷管理 ===== */

    /**
     * 获取微信卡券颜色列表
     */
    const WECHAT_CARD_COLORS_GET_PREFIX = '/card/getcolors';
    /**
     * 获取微信卡券颜色列表
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getColorList()
    {
        $result = $this->wechat->httpGet(self::WECHAT_CARD_COLORS_GET_PREFIX, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['colors'] : false;
    }

    /**
     * 创建卡券
     */
    const WECHAT_CARD_CREATE_PREFIX = '/card/create';
    /**
     * 创建卡券
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function createCard(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_CARD_CREATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['card_id'] : false;
    }

    /**
     * 创建二维码
     */
    const WECHAT_QR_CODE_CREATE_PREFIX = '/card/qrcode/create';
    /**
     * 创建二维码
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function createQrCode(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_QR_CODE_CREATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['ticket'] : false;
    }

    /**
     * JS API更新后事件
     */
    const EVENT_AFTER_JS_API_TICKET_UPDATE = 'afterJsApiTicketUpdate';
    /**
     * @var array
     */
    private $_jsApiTicket;
    /**
     * 获取JsApiTicket(添加卡券JS-SDK)
     * 超时后会自动重新获取JsApiTicket并触发self::EVENT_AFTER_JS_API_TICKET_UPDATE事件
     * @param bool $force 是否强制获取
     * @return mixed
     * @throws HttpException
     */
    public function getJsApiTicket($force = false)
    {
        $time = time(); // 为了更精确控制.取当前时间计算
        $type = 'wx_card';
        if ($this->_jsApiTicket === null || $this->_jsApiTicket['expire'] < $time || $force) {
            $result = $this->_jsApiTicket === null && !$force ? $this->wechat->getCache('card_js_api_ticket', false) : false;
            if ($result === false) {
                if (!($result = $this->wechat->requestJsApiTicket($type))) {
                    throw new HttpException(500, 'Fail to get card jsapi_ticket from wechat server.');
                }
                $result['expire'] = $time + $result['expires_in'];
                $this->trigger(self::EVENT_AFTER_JS_API_TICKET_UPDATE, new Event(['data' => $result]));
                $this->setCache('card_js_api_ticket', $result, $result['expires_in']);
            }
            $this->setJsApiTicket($result);
        }
        return $this->_jsApiTicket['ticket'];
    }

    /**
     * 设置JsApiTicket(添加卡券JS-SDK)
     * @param array $jsApiTicket
     */
    public function setJsApiTicket(array $jsApiTicket)
    {
        $this->_jsApiTicket = $jsApiTicket;
    }

    /**
     * 核销卡券
     */
    const WECHAT_CODE_CONSUME_PREFIX = '/card/code/consume';

    /**
     * 核销卡券
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function consumeCode(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_QR_CODE_CREATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['ticket'] : false;
    }
    /**
     * CODE解码
     */
    const WECHAT_CARD_CODE_DECRYPT_URL = '/card/code/decrypt';
    /**
     * CODE解码
     * @param $encryptCode
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function decryptCode($encryptCode)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_QR_CODE_CREATE_PREFIX, [
            'encrypt_code' => $encryptCode
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result['ticket'] : false;
    }

    /**
     * 删除卡券
     */
    const WECHAT_CARD_DELETE_PREFIX = '/card/delete';
    /**
     * 删除卡券
     * @param $cardId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteCard($cardId)
    {
        $result = $this->wechat->httpPost(self::WECHAT_DELETE_CARD_URL, [
            'card_id' => $cardId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? true : false;
    }

    /**
     * 查询code
     */
    const WECHAT_CODE_GET_PREFIX = '/card/code/get';
    /**
     * 查询code
     * @param $code
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getCode($code)
    {
        $result = $this->wechat->httpPost(self::WECHAT_DELETE_CARD_URL, [
            'code' => $code
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 批量查询卡列表
     */
    const WECHAT_CARD_LIST_GET_PREFIX = '/card/batchget';
    /**
     * 批量查询卡列表
     * @param array $data
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getCardList(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 查询卡券详情
     */
    const WECHAT_CARD_GET_PREFIX = '/card/get';
    /**
     * 查询卡券详情
     * @param $cardId
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getCard($cardId)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, [
            'card_id' => $cardId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 更改code
     */
    const WECHAT_CODE_UPDATE_PREFIX = '/card/code/update';
    /**
     * 更改code
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateCode(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 设置卡券失效接口
     */
    const WECHAT_CODE_UNAVAILABLE_SET_PREFIX = '/card/code/unavailable';
    public function setCodeUnavailable(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 更改卡券信息接口
     */
    const WECHAT_CARD_UPDATE_PREFIX = '/card/update';
    /**
     * 更改卡券信息接口
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateCard(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 库存修改接口
     */
    const WECHAT_CARD_STOCK_UPDATE = '/card/modifystock';
    /**
     *  库存修改接口
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateCardStock(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 激活/绑定会员卡
     */
    const WECHAT_MEMBER_CARD_ACTIVATE_PREFIX = '/card/membercard/activate';
    /**
     * 激活/绑定会员卡
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function activateMemberCard(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 会员卡交易
     */
    const WECHAT_MEMBER_CARD_USER_UPDATE_PREFIX = '/card/membercard/updateuser';

    /**
     * 会员卡交易
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function userUpdateMemberCard(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 更新电影票
     */
    const WECHAT_USER_UPDATE_MOVIE_TICKET_PREFIX = '/card/movieticket/updateuser';
    /**
     * 更新电影票
     * @param array $data
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function userUpdateMovieTicket(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok' ? $result : false;
    }

    /**
     * 飞机票
     */
    const WECHAT_BOARDINGPASS_CHECKIN_PREFIX = '/card/boardingpass/checkin';
    /**
     * 飞机票
     * @param array $data
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function checkinBoardingpass(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 会议门票
     */
    const WECHAT_METTING_TICKET_USER_UPDATE_PREFIX = '/card/meetingticket/updateuser';
    /**
     * 会议门票
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function userUpateMeetingTicket(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }

    /**
     * 设置测试用户白名单
     */
    const WECHAT_TEST_WHITE_LIST_SET_PREFIX = '/card/testwhitelist/set';
    /**
     * 设置测试用户白名单
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function setTestWhiteList(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_CARD_LIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] === 'ok';
    }
}