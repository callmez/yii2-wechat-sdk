<?php
namespace callmez\wechat\sdk;

use Yii;
use yii\base\InvalidConfigException;

/**
 * 微信公众号操作SDK
 * @package calmez\wechat\sdk
 */
class MpWechat extends BaseWechat
{
    /**
     * 微信接口基本地址
     */
    const WECHAT_BASE_URL = 'https://api.weixin.qq.com';
    /**
     * js api ticket 获取
     */
    const WECHAT_JS_API_TICKET_PREFIX = '/cgi-bin/ticket/getticket';
    /**
     * 数据缓存前缀
     * @var string
     */
    public $cachePrefix = 'cache_wechat_sdk_mp';
    /**
     * 公众号appId
     * @var string
     */
    public $appId;
    /**
     * 公众号appSecret
     * @var string
     */
    public $appSecret;
    /**
     * 公众号接口验证token,可由您来设定. 并填写在微信公众平台->开发者中心
     * @var string
     */
    public $token;
    /**
     * 公众号消息加密键值
     * @var string
     */
    public $encodingAesKey;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        if ($this->appId === null) {
            throw new InvalidConfigException('The "appId" property must be set.');
        } elseif ($this->appSecret === null) {
            throw new InvalidConfigException('The "appSecret" property must be set.');
        } elseif ($this->token === null) {
            throw new InvalidConfigException('The "token" property must be set.');
        } elseif ($this->encodingAesKey === null) {
            throw new InvalidConfigException('The "encodingAesKey" property must be set.');
        }
    }

    /**
     * 创建消息加密类
     * @return object
     */
    protected function createMessageCrypt()
    {
        return Yii::createObject([
            'class' => MessageCrypt::className(),
        ], $this->token, $this->encodingAesKey, $this->appId);
    }

    /**
     * 获取缓存键值
     * @param $name
     * @return string
     */
    protected function getCacheKey($name)
    {
        return $this->cachePrefix . '_' . $this->appId . '_' . $name;
    }

    /**
     * 增加微信基本链接
     * @inheritdoc
     */
    protected function httpBuildQuery($url, array $options)
    {
        if (stripos($url, 'http://') === false && stripos($url, 'https://') === false) {
            $url = self::WECHAT_BASE_URL . $url;
        }
        return parent::httpBuildQuery($url, $options);
    }

    /**
     * @inheritdoc
     * @param bool $force 是否强制获取access_token, 该设置会在access_token使用错误时, 是否再获取一次access_token并再重新提交请求
     */
    public function parseHttpRequest(callable $callable, $url, $postOptions = null, $force = true)
    {
        $result = call_user_func_array($callable, [$url, $postOptions]);
        if (isset($result['errcode'])) {
            switch ($result ['errcode']) {
                case 40001: //access_token 失效,强制更新access_token, 并更新地址重新执行请求
                    if ($force) {
                        $url = preg_replace_callback("/access_token=([^&]*)/i", function(){
                            return 'access_token=' . $this->getAccessToken(true);
                        }, $url);
                        $result = $this->parseHttpResult($url, $url, $postOptions, false); // 仅重新获取一次,否则容易死循环
                    }
                    break;
            }
        }
        return $result;
    }

    /* =================== 基础接口 =================== */

    /**
     * access token获取
     */
    const WECHAT_ACCESS_TOKEN_PREFIX = '/cgi-bin/token';
    /**
     * 请求服务器access_token
     * @param string $grantType
     * @return array
     */
    protected function requestAccessToken($grantType = 'client_credential')
    {
        return $this->httpGet(self::WECHAT_ACCESS_TOKEN_PREFIX, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => $grantType
        ]);
    }

    /**
     * 获取微信服务器IP地址
     */
    const WECHAT_IP_PREFIX = '/cgi-bin/getcallbackip';
    /**
     * 获取微信服务器IP地址
     * @return array|bool
     * @throws \yii\web\HttpException
     */
    public function getIp()
    {
        return $this->httpGet(self::WECHAT_IP_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
    }

    /* =================== 接收消息 =================== */

    /**
     * 微信服务器请求签名检测
     * @param string $signature 微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
     * @param string $timestamp 时间戳
     * @param string $nonce 随机数
     * @return bool
     */
    public function checkSignature($signature = null, $timestamp = null, $nonce = null)
    {
        $signature === null && isset($_GET['signature']) && $signature = $_GET['signature'];
        $timestamp === null && isset($_GET['timestamp']) && $timestamp = $_GET['timestamp'];
        $nonce === null && isset($_GET['nonce']) && $nonce = $_GET['nonce'];
        $tmpArr = [$this->token, $timestamp, $nonce];
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        return sha1($tmpStr) == $signature;
    }

    /* =================== 发送消息 =================== */

    /**
     * 添加客服帐号
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_ADD_PREFIX = '/customservice/kfaccount/add';
    /**
     * 添加客服帐号
     * @param array $account
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addCustomServeiceAccount(array $account)
    {
        $result = $this->httpRaw(self::WECHAT_CUSTOM_MESSAGE_SEND_PREFIX, $account, [
            'access_token=' . $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 修改客服帐号
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_UPDATE_PREFIX = '/customservice/kfaccount/update';
    /**
     * 修改客服帐号
     * @param array $account
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateCustomServeiceAccount(array $account)
    {
        $result = $this->httpRaw(self::WECHAT_CUSTOM_MESSAGE_UPDATE_PREFIX, $account, [
            'access_token=' . $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 删除客服帐号
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_DELETE_PREFIX = '/customservice/kfaccount/del';
    /**
     * 删除客服帐号
     * @param array $account
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteCustomServeiceAccount(array $account)
    {
        $result = $this->httpRaw(self::WECHAT_CUSTOM_MESSAGE_DELETE_PREFIX, $account, [
            'access_token=' . $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 设置客服账号头像
     */
    const WECHAT_CUSTOM_SERVICE_ACCOUNT_AVATAR_SET_PREFIX = '/customservice/kfaccount/uploadheadimg';
    /**
     * 设置客服账号头像
     * @param string $accountName
     * @param string $avatarPath
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function setCustomServiceAccountAvatar($accountName, $avatarPath)
    {
        $result = $this->httpPost(self::WECHAT_CUSTOM_SERVICE_ACCOUNT_AVATAR_SET_PREFIX, [
            'media' => $this->uploadFile($avatarPath)
        ], [
            'access_token=' . $this->getAccessToken(),
            'kf_account' => $accountName
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取所有客服账号
     */
    const CUSTOM_SERVICE_ACCOUNT_LIST_PREFIX = '/cgi-bin/customservice/getkflist';
    /**
     * 获取所有客服账号
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getCustomServiceAccountList()
    {
        $result = $this->httpGet(self::CUSTOM_SERVICE_ACCOUNT_LIST_PREFIX, [
            'access_token=' . $this->getAccessToken()
        ]);
        return isset($result['kf_list']) ? $result['kf_list'] : false;
    }

    /**
     * 发送客服消息
     */
    const WECHAT_CUSTOM_MESSAGE_SEND_PREFIX = '/cgi-bin/message/custom/send';
    /**
     * 发送客服消息
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function sendCustomMessage(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_CUSTOM_MESSAGE_SEND_PREFIX, $data, [
            'access_token=' . $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 消息上传
     */
    const WECHAT_ARTICLES_UPLOAD_PREFIX = '/cgi-bin/media/uploadnews';
    /**
     * 上传图文消息素材【订阅号与服务号认证后均可用】
     * @param array $articles
     * ~~~
     * $articles = [
     *     [
     *         'thumb_media_id' => 'qI6_Ze_6PtV7svjolgs-rN6stStuHIjs9_DidOHaj0Q-mwvBelOXCFZiq2OsIU-p',
     *         'author' => 'xxx',
     *         'title' => 'Happy Day',
     *         'content_source_url' => 'www.qq.com',
     *         'content' => 'content',
     *         'digest' => 'digest',
     *         'show_cover_pic' => '1'
     *     ]
     *     ...
     * ];
     *
     * ~~~
     * @return array|bool
     */
    public function uploadArticles(array $articles)
    {
        $result = $this->httpRaw(self::WECHAT_ARTICLES_UPLOAD_URL, [
            'articles' => $articles
        ], [
            'access_token=' . $this->getAccessToken()
        ]);
        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 上传视频
     */
    const WECHAT_VIDEO_UPLOAD_URL = 'https://file.api.weixin.qq.com/cgi-bin/media/uploadvideo';
    /**
     * 上传视频
     * @param $videoPath
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function uploadVideo($videoPath)
    {
        $result = $this->httpPost(self::WECHAT_VIDEO_UPLOAD_URL, [
            'media' => $this->uploadFile($videoPath)
        ], [
            'access_token=' . $this->getAccessToken()
        ]);
        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 高级群发接口
     */
    const WECHAT_SEND_ALL_PREFIX = '/cgi-bin/message/mass/sendall';
    /**
     * 高级群发接口
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function sendAll(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SEND_ALL_PREFIX, $data, [
            'access_token=' . $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 删除群发【订阅号与服务号认证后均可用】
     */
    const WECHAT_SENDED_ALL_DELETE_PREFIX = '/cgi-bin/message/mass/delete';
    /**
     * 删除群发【订阅号与服务号认证后均可用】
     * @param $messageId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteSendedAll($messageId)
    {
        $result = $this->httpRaw(self::WECHAT_SENDED_ALL_DELETE_PREFIX, [
            'msgid' => $messageId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 群发预览接口【订阅号与服务号认证后均可用】
     */
    const WECHAT_SEND_ALL_PREVIEW_PREFIX = '/cgi-bin/message/mass/preview';
    /**
     * 群发预览接口【订阅号与服务号认证后均可用】
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function previewSendAll(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SEND_ALL_PREVIEW_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 查询群发消息发送状态【订阅号与服务号认证后均可用】
     */
    const WECHAT_SEND_ALL_STATUS = '/cgi-bin/message/mass/get';
    /**
     * 查询群发消息发送状态【订阅号与服务号认证后均可用】
     * @param $messageId
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getSendAllStatus($messageId)
    {
        $result = $this->httpRaw(self::WECHAT_SEND_ALL_STATUS, [
            'msgid' => $messageId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['msg_status']) ? $result : false;
    }

    /**
     * 设置所属行业
     */
    const WECHAT_TEMPLATE_INDUSTRY_SET_PREFIX = '/cgi-bin/template/api_set_industry';
    /**
     * 设置所属行业
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function setTemplateIndustry(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_TEMPLATE_INDUSTRY_SET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && $result['errcode'];
    }

    /**
     *获得模板ID
     */
    const WECHAT_TEMPLATE_ID_GET_PREFIX = '/cgi-bin/template/api_add_template';
    /**
     * 获得模板ID
     * @param $shortId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getTemplateId($shortId)
    {
        $result = $this->httpRaw(self::WECHAT_TEMPLATE_ID_GET_PREFIX, [
            'template_id_short' => $shortId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['template_id']) ? $result['template_id'] : false;
    }

    /**
     * 发送模板消息
     */
    const WECHAT_TEMPLATE_MESSAGE_SEND_PREFIX = '/cgi-bin/message/template/send';
    /**
     * 发送模板消息
     * @param array $data 模板需要的数据
     * @return int|bool
     */
    public function sendTemplateMessage(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_TEMPLATE_MESSAGE_SEND_URL, [
            'url' => null,
            'topcolor' => '#FF0000'
        ] + $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['msgid']) ? $result['msgid'] : false;
    }

    /**
     * 获取自动回复规则
     */
    const WECHAT_AUTO_REPLY_INFO_GET_PREFIX = '/cgi-bin/get_current_autoreply_info';
    /**
     * 获取自动回复规则
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getAutoReplyInfo()
    {
        $result = $this->httpGet(self::WECHAT_AUTO_REPLY_INFO_GET_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /* =================== 素材管理 =================== */

    /* =================== 用户管理 =================== */

    /* =================== 自定义管理 =================== */

    /* =================== 账号管理 =================== */

    /* =================== 数据统计接口 =================== */

    /* =================== 微信JS-SDK =================== */

    /**
     * 请求服务器jsapi_ticket
     * @param string $type
     * @return array
     */
    protected function requestJsApiTicket($type = 'jsapi')
    {
        return $this->httpGet(self::WECHAT_JS_API_TICKET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'type' => $type
        ]);
    }

    /* =================== 微信小店接口 =================== */

    /* =================== 微信卡卷接口 =================== */

    /* =================== 微信智能接口 =================== */

    /* =================== 多客服功能 =================== */

    /* =================== 摇一摇周边 =================== */
}