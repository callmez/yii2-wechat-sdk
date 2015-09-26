<?php
namespace callmez\wechat\sdk;

use Yii;
use yii\base\InvalidConfigException;
use callmez\wechat\sdk\mp\Card;
use callmez\wechat\sdk\mp\Shop;
use callmez\wechat\sdk\mp\ShakeAround;
use callmez\wechat\sdk\mp\DataCube;
use callmez\wechat\sdk\mp\CustomService;
use callmez\wechat\sdk\components\BaseWechat;
use callmez\wechat\sdk\components\MessageCrypt;

/**
 * 微信公众号操作SDK
 * 注:部分功能因API的整体和功能性, 拆分为单独的类调用请查看compoents/mp文件夹
 * @package calmez\wechat\sdk
 */
class MpWechat extends BaseWechat
{
    /**
     * 微信接口基本地址
     */
    const WECHAT_BASE_URL = 'https://api.weixin.qq.com';
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
        if (isset($result['errcode']) && $result['errcode']) {
            $this->lastError = $result;
            Yii::warning([
                'url' => $url,
                'result' => $result,
                'postOptions' => $postOptions
            ], __METHOD__);
            switch ($result ['errcode']) {
                case 40001: //access_token 失效,强制更新access_token, 并更新地址重新执行请求
                    if ($force) {
                        $url = preg_replace_callback("/access_token=([^&]*)/i", function(){
                            return 'access_token=' . $this->getAccessToken(true);
                        }, $url);
                        $result = $this->parseHttpRequest($callable, $url, $postOptions, false); // 仅重新获取一次,否则容易死循环
                    }
                    break;
            }
        }
        return $result;
    }

    /**
     * 解析微信服务器请求的xml数据, 如果是加密数据直接自动解密
     * @param string $xml 微信请求的XML信息主体, 默认取$_GET数据
     * @param string $messageSignature 加密签名, 默认取$_GET数据
     * @param string $timestamp 加密时间戳, 默认取$_GET数据
     * @param string $nonce 加密随机串, 默认取$_GET数据
     * @param string $encryptType 加密类型, 默认取$_GET数据
     * @return array
     */
    public function parseRequestXml($xml = null, $messageSignature = null, $timestamp = null , $nonce = null, $encryptType = null)
    {
        $xml === null && $xml = Yii::$app->request->getRawBody();
        $return = [];
        if (!empty($xml)) {
            $messageSignature === null && isset($_GET['msg_signature']) && $messageSignature = $_GET['msg_signature'];
            $encryptType === null && isset($_GET['encrypt_type']) && $encryptType = $_GET['encrypt_type'];
            if ($messageSignature !== null && $encryptType == 'aes') { // 自动解密
                $timestamp === null && isset($_GET['timestamp']) && $timestamp = $_GET['timestamp'];
                $nonce === null && isset($_GET['nonce']) && $nonce = $_GET['nonce'];
                $xml = $this->decryptXml($xml, $messageSignature, $timestamp, $nonce);
                if ($xml === false) {
                    return $return;
                }
            }
            $return = (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return $return;
    }

    /**
     * 创建消息加密类
     * @return object
     */
    protected function createMessageCrypt()
    {
        return Yii::createObject(MessageCrypt::className(), [$this->token, $this->encodingAesKey, $this->appId]);
    }

    /* =================== 基础接口 =================== */

    /**
     * access token获取
     */
    const WECHAT_ACCESS_TOKEN_PREFIX = '/cgi-bin/token';
    /**
     * 请求服务器access_token
     * @param string $grantType
     * @return array|bool
     */
    protected function requestAccessToken($grantType = 'client_credential')
    {
        $result = $this->httpGet(self::WECHAT_ACCESS_TOKEN_PREFIX, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => $grantType
        ]);
        return isset($result['access_token']) ? $result : false;
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
        $result = $this->httpGet(self::WECHAT_IP_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['ip_list']) ? $result['ip_list'] : false;
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

    // 多客服部分 @see self::getCustomService()

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
    public function sendMessage(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_CUSTOM_MESSAGE_SEND_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
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
        $result = $this->httpRaw(self::WECHAT_ARTICLES_UPLOAD_PREFIX, [
            'articles' => $articles
        ], [
            'access_token' => $this->getAccessToken()
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
            'access_token' => $this->getAccessToken()
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
            'access_token' => $this->getAccessToken()
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
        return isset($result['errcode']) && !$result['errcode'];
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
        $result = $this->httpRaw(self::WECHAT_TEMPLATE_MESSAGE_SEND_PREFIX, array_merge([
            'url' => null,
            'topcolor' => '#FF0000'
        ], $data), [
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

    /**
     * 新增临时素材(上传临时多媒体文件)
     */
    const WECHAT_MEDIA_UPLOAD_PREFIX = '/cgi-bin/media/upload';
    /**
     * 新增临时素材(上传临时多媒体文件)
     * @param $mediaPath
     * @param $type
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function uploadMedia($mediaPath, $type)
    {
        $result = $this->httpPost(self::WECHAT_MEDIA_UPLOAD_PREFIX, [
            'media' => $this->uploadFile($mediaPath)
        ], [
            'access_token' => $this->getAccessToken(),
            'type' => $type
        ]);
        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 获取临时素材(下载多媒体文件)
     */
    const WECHAT_MEDIA_GET_PREFIX = '/cgi-bin/media/get';
    /**
     * 获取临时素材(下载多媒体文件)
     * @param $mediaId
     * @return bool|string
     * @throws \yii\web\HttpException
     */
    public function getMedia($mediaId)
    {
        $result = $this->httpGet(self::WECHAT_MEDIA_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'media_id' => $mediaId
        ]);
        return is_string($result) ? $result : false;
    }

    /**
     * 新增永久图文素材
     */
    const WECHAT_NEWS_MATERIAL_ADD_PREFIX = '/cgi-bin/material/add_news';
    /**
     * 新增永久图文素材
     * @param array $articles
     * @return string|bool
     * @throws \yii\web\HttpException
     */
    public function addNewsMaterial(array $articles)
    {
        $result = $this->httpRaw(self::WECHAT_NEWS_MATERIAL_ADD_PREFIX, [
            'articles' => $articles
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['media_id']) ? $result['media_id'] : false;
    }

    /**
     * 新增其他类型永久素材
     */
    const WECHAT_MATERIAL_ADD_PREFIX = '/cgi-bin/material/add_material';
    /**
     * 新增其他类型永久素材
     * @param string $mediaPath
     * @param string $type
     * @param array $data 视频素材需要description
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function addMaterial($mediaPath, $type, $data = [])
    {
        $result = $this->httpPost(self::WECHAT_MATERIAL_ADD_PREFIX, array_merge($data, [
            'media' => $this->uploadFile($mediaPath)
        ]), [
            'access_token' => $this->getAccessToken(),
            'type' => $type
        ]);
        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 获取永久素材
     */
    const WECHAT_MATERIAL_GET_PREFIX = '/cgi-bin/material/get_material';
    /**
     * 获取永久素材
     * @param $mediaId
     * @return bool|string
     * @throws \yii\web\HttpException
     */
    public function getMaterial($mediaId)
    {
        $result = $this->httpGet(self::WECHAT_MATERIAL_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'media_id' => $mediaId
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 删除永久素材
     */
    const WECHAT_MATERIAL_DELETE_PREFIX = '/cgi-bin/material/del_material';
    /**
     * 删除永久素材
     * @param $mediaId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteMaterial($mediaId)
    {
        $result = $this->httpRaw(self::WECHAT_MATERIAL_DELETE_PREFIX, [
            'media_id' => $mediaId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 修改永久图文素材
     */
    const WECHAT_NEWS_MATERIAL_UPDATE_PREFIX = '/cgi-bin/material/update_news';
    /**
     * 修改永久图文素材
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateNewsMaterial(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_MATERIAL_DELETE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 获取素材总数
     */
    const WECHAT_MATERIAL_COUNT_GET_PREFIX = '/cgi-bin/material/get_materialcount';
    /**
     * 获取素材总数
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getMaterialCount()
    {
        $result = $this->httpGet(self::WECHAT_MATERIAL_COUNT_GET_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
        return !array_key_exists('errorcode', $result) ? $result : false;
    }

    /**
     * 获取素材列表
     */
    const WECHAT_MATERIAL_LIST_GET_PREFIX = '/cgi-bin/material/batchget_material';
    /**
     * 获取素材列表
     * @param $data
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getMaterialList($data)
    {
        $result = $this->httpRaw(self::WECHAT_MATERIAL_LIST_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return !isset($result['errodcode']) ? $result : false;
    }

    /* =================== 用户管理 =================== */

    /**
     * 创建分组
     */
    const WECHAT_GROUP_CREATE_PREFIX = '/cgi-bin/groups/create';
    /**
     * 创建分组
     * @param $group
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function createGroup($group)
    {
        $result = $this->httpRaw(self::WECHAT_GROUP_CREATE_PREFIX, [
            'group' => $group
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['group']) ? $result['group'] : false;
    }

    /**
     * 查询所有分组
     */
    const WECHAT_GROUP_LIST_GET_PREFIX = '/cgi-bin/groups/get';
    /**
     * 查询所有分组
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getGroupList()
    {
        $result = $this->httpGet(self::WECHAT_GROUP_LIST_GET_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['groups']) ? $result['groups'] : false;
    }

    /**
     * 查询用户所在分组
     */
    const WECHAT_USER_GROUP_ID_GET_PREFIX = '/cgi-bin/groups/getid';
    /**
     * 查询用户所在分组
     * @param $openId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserGroupId($openId)
    {
        $result = $this->httpRaw(self::WECHAT_USER_GROUP_ID_GET_PREFIX, [
            'openid' => $openId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['groupid']) ? $result['groupid'] : false;
    }

    /**
     * 修改分组名
     */
    const WECHAT_GROUP_UPDATE_PREFIX = '/cgi-bin/groups/update';
    /**
     * 修改分组名
     * @param array $group
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateGroup(array $group)
    {
        $result = $this->httpRaw(self::WECHAT_GROUP_UPDATE_PREFIX, [
            'group' => $group
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 移动用户分组
     */
    const WECHAT_USER_GROUP_UPDATE_PREFIX = '/cgi-bin/groups/members/update';
    /**
     * 移动用户分组
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateUserGroup(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_MEMBER_GROUP_UPDATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 批量移动用户分组
     */
    const WECHAT_USERS_GROUP_UPDATE_PREFIX = '/cgi-bin/groups/members/batchupdate';
    /**
     * 批量移动用户分组
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateUsersGroup(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USERS_GROUP_UPDATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 删除分组
     */
    const WECHAT_GROUP_DELETE_PREFIX = '/cgi-bin/groups/delete';
    /**
     * 删除分组
     * @param $groupId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deletGroup($groupId)
    {
        $result = $this->httpRaw(self::WECHAT_GROUP_DELETE_PREFIX, [
            'group' => [
                'id' => $groupId
            ]
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 设置用户备注名
     */
    const WEHCAT_USER_MARK_UPDATE = '/cgi-bin/user/info/updateremark';
    /**
     * 设置用户备注名
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateUserMark(array $data)
    {
        $result = $this->httpRaw(self::WEHCAT_USER_MARK_UPDATE, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取用户基本信息(UnionID机制)
     */
    const WECHAT_USER_INFO_GET = '/cgi-bin/user/info';
    /**
     * 获取用户基本信息(UnionID机制)
     * @param $openId
     * @param string $lang
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getUserInfo($openId, $lang = 'zh_CN')
    {
        $result = $this->httpGet(self::WECHAT_USER_INFO_GET, [
            'access_token' => $this->getAccessToken(),
            'openid' => $openId,
            'lang' => $lang
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 获取用户列表
     */
    const WECHAT_USER_LIST_GET_PREFIX = '/cgi-bin/user/get';
    /**
     * 获取用户列表
     * @param $nextOpenId
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getUserList($nextOpenId)
    {
        $result = $this->httpGet(self::WECHAT_USER_LIST_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'next_openid' => $nextOpenId,
        ]);
        return array_key_exists('errcode', $result) ? $result : false;
    }

    /* ==== 网页授权 ===== */

    /**
     * 用户同意授权，获取code
     */
    const WECHAT_OAUTH2_AUTHORIZE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    /**
     * 用户同意授权，获取code:第一步
     * 通过此函数生成授权url
     * @param $redirectUrl 授权后重定向的回调链接地址，请使用urlencode对链接进行处理
     * @param string $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
     * @param string $scope 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），
     * snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
     * @return string
     */
    public function getOauth2AuthorizeUrl($redirectUrl, $state = 'authorize', $scope = 'snsapi_base')
    {
        return $this->httpBuildQuery(self::WECHAT_OAUTH2_AUTHORIZE_URL, [
            'appid' => $this->appId,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
        ]) . '#wechat_redirect';
    }

    /**
     * 通过code换取网页授权access_token
     */
    const WECHAT_OAUTH2_ACCESS_TOKEN_PREFIX = '/sns/oauth2/access_token';
    /**
     * 通过code换取网页授权access_token:第二步
     * 通过跳转到getOauth2AuthorizeUrl返回的授权code获取用户资料 (该函数和getAccessToken函数作用不同.请参考文档)
     * @param $code
     * @param string $grantType
     * @return array
     */
    public function getOauth2AccessToken($code, $grantType = 'authorization_code')
    {
        $result = $this->httpGet(self::WECHAT_OAUTH2_ACCESS_TOKEN_PREFIX, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'code' => $code,
            'grant_type' => $grantType
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 刷新access_token
     */
    const WECHAT_OAUTH2_ACCESS_TOKEN_REFRESH_PREFIX = '/sns/oauth2/refresh_token';
    /**
     * 刷新access_token:第三步(非必须)
     * 由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新
     * refresh_token拥有较长的有效期（7天、30天、60天、90天），当refresh_token失效的后，需要用户重新授权。
     * @param $refreshToken
     * @param string $grantType
     * @return array|bool
     */
    public function refreshOauth2AccessToken($refreshToken, $grantType = 'refresh_token')
    {
        $result = $this->httpGet(self::WECHAT_OAUTH2_ACCESS_TOKEN_REFRESH_PREFIX, [
            'appid' => $this->appId,
            'grant_type' => $grantType,
            'refresh_token' => $refreshToken
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 拉取用户信息(需scope为 snsapi_userinfo)
     */
    const WEHCAT_SNS_USER_INFO_PREFIX = '/sns/userinfo';
    /**
     * 拉取用户信息(需scope为 snsapi_userinfo):第四步
     * @param $openId
     * @param string $oauth2AccessToken
     * @param string $lang
     * @return array|bool
     */
    public function getSnsUserInfo($openId, $oauth2AccessToken, $lang = 'zh_CN')
    {
        $result = $this->httpGet(self::WEHCAT_SNS_USER_INFO_PREFIX, [
            'access_token' => $oauth2AccessToken,
            'openid' => $openId,
            'lang' => $lang
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 检验授权凭证（access_token）是否有效
     */
    const WECHAT_SNS_AUTH_PREFIX = '/sns/auth';
    /**
     * 检验授权凭证（access_token）是否有效
     * @param $accessToken
     * @param $openId
     * @return bool
     */
    public function checkOauth2AccessToken($accessToken, $openId)
    {
        $result = $this->httpGet(self::WECHAT_SNS_AUTH_PREFIX, [
            'access_token' => $accessToken,
            'openid' => $openId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /* =================== 自定义管理 =================== */

    /**
     * 自定义菜单创建接口
     */
    const WECHAT_MENU_CREATE_PREFIX = '/cgi-bin/menu/create';
    /**
     * 自定义菜单创建接口(创建菜单)
     * @param array $buttons 菜单结构字符串
     * ~~~
     *  $this->createMenu([
     *      [
     *           'type' => 'click',
     *           'name' => '今日歌曲',
     *           'key' => 'V1001_TODAY_MUSIC'
     *      ],
     *      [
     *           'type' => 'view',
     *           'name' => '搜索',
     *           'url' => 'http://www.soso.com'
     *      ]
     *      ...
     * ]);
     * ~~~
     * @return bool
     */
    public function createMenu(array $buttons)
    {
        $result = $this->httpRaw(self::WECHAT_MENU_CREATE_PREFIX, [
            'button' => $buttons
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 自定义菜单查询
     */
    const WECHAT_MENU_GET_PREFIX = '/cgi-bin/menu/get';
    /**
     * 自定义菜单查询接口(获取菜单)
     * @return bool
     */
    public function getMenu()
    {
        $result = $this->httpGet(self::WECHAT_MENU_GET_PREFIX, [
             'access_token' => $this->getAccessToken()
        ]);
        return isset($result['menu']['button']) ? $result['menu']['button'] : false;
    }

    /**
     * 自定义菜单删除接口(删除菜单)
     */
    const WECHAT_MENU_DELETE_PREFIX = '/cgi-bin/menu/delete';
    /**
     * 自定义菜单删除接口(删除菜单)
     * @return bool
     */
    public function deleteMenu()
    {
        $result = $this->httpGet(self::WECHAT_MENU_DELETE_PREFIX, [
             'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取自定义菜单配置接口
     */
    const WECHAT_MENU_INFO_GET_PREFIX = '/cgi-bin/get_current_selfmenu_info';
    /**
     * 获取自定义菜单配置接口
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getMenuInfo()
    {
        $result = $this->httpGet(self::WECHAT_MENU_INFO_GET_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /* =================== 账号管理 =================== */

    /**
     * 创建二维码ticket
     */
    const WECHAT_QR_CODE_CREATE_PREFIX = '/cgi-bin/qrcode/create';
    /**
     * 创建二维码ticket
     * @param arary $data
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function createQrCode(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_QR_CODE_CREATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 通过ticket换取二维码
     */
    const WECHAT_QR_CODE_GET_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';
    /**
     * 通过ticket换取二维码
     * ticket正确情况下，http 返回码是200，是一张图片，可以直接展示或者下载。
     * @param $ticket
     * @return string
     */
    public function getQrCode($ticket)
    {
        return $this->httpBuildQuery(self::WECHAT_QR_CODE_GET_URL, ['ticket' => $ticket]);
    }

    /**
     * 长链接转短链接接口
     */
    const WECHAT_SHORT_URL_CREATE_PREFIX = '/cgi-bin/shorturl';
    /**
     * 长链接转短链接接口
     * @param $longUrl 需要转换的长链接，支持http://、https://、weixin://wxpay 格式的url
     * @return bool
     */
    public function getShortUrl($longUrl)
    {
        $result = $this->httpRaw(self::WECHAT_SHORT_URL_CREATE_PREFIX, [
            'action' => 'long2short',
            'long_url' => $longUrl,
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['short_url'] : false;
    }

    /* =================== 数据统计接口 =================== */

    /**
     * @var object
     */
    private $_dataCube;
    /**
     * 数据统计组件
     * @return object
     */
    public function getDataCube()
    {
        if ($this->_dataCube === null) {
            $this->_dataCube = Yii::createObject(DataCube::className(), [$this]);
        }
        return $this->_dataCube;
    }

    /* =================== 微信JS-SDK =================== */

    /**
     * js api ticket 获取
     */
    const WECHAT_JS_API_TICKET_PREFIX = '/cgi-bin/ticket/getticket';
    /**
     * 请求服务器jsapi_ticket
     * @param string $type
     * @return array|bool
     */
    protected function requestJsApiTicket($type = 'jsapi')
    {
        $result = $this->httpGet(self::WECHAT_JS_API_TICKET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'type' => $type
        ]);
        return isset($result['ticket']) ? $result : false;
    }

    /**
     * 生成js 必需的config
     * 只需在视图文件输出JS代码:
     *  wx.config(<?= json_encode($wehcat->jsApiConfig()) ?>); // 默认全权限
     *  wx.config(<?= json_encode($wehcat->jsApiConfig([ // 只允许使用分享到朋友圈功能
     *      'jsApiList' => [
     *          'onMenuShareTimeline'
     *      ]
     *  ])) ?>);
     * @param array $config
     * @return array
     * @throws HttpException
     */
    public function jsApiConfig(array $config = [])
    {
        $data = [
            'jsapi_ticket' => $this->getJsApiTicket(),
            'noncestr' => Yii::$app->security->generateRandomString(16),
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'url' => explode('#', Yii::$app->request->getAbsoluteUrl())[0]
        ];
        return array_merge([
            'debug' => YII_DEBUG,
            'appId' => $this->appId,
            'timestamp' => $data['timestamp'],
            'nonceStr' => $data['noncestr'],
            'signature' => sha1(urldecode(http_build_query($data))),
            'jsApiList' => [
                'checkJsApi',
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo',
                'hideMenuItems',
                'showMenuItems',
                'hideAllNonBaseMenuItem',
                'showAllNonBaseMenuItem',
                'translateVoice',
                'startRecord',
                'stopRecord',
                'onRecordEnd',
                'playVoice',
                'pauseVoice',
                'stopVoice',
                'uploadVoice',
                'downloadVoice',
                'chooseImage',
                'previewImage',
                'uploadImage',
                'downloadImage',
                'getNetworkType',
                'openLocation',
                'getLocation',
                'hideOptionMenu',
                'showOptionMenu',
                'closeWindow',
                'scanQRCode',
                'chooseWXPay',
                'openProductSpecificView',
                'addCard',
                'chooseCard',
                'openCard'
            ]
        ], $config);
    }

    /* =================== 微信小店接口(基于手册V1.15) =================== */

    /**
     * @var object
     */
    private $_merchant;
    /**
     * 微信小店组件
     * @return object
     */
    public function getMerchant()
    {
        if ($this->_merchant === null) {
            $this->_merchant = Yii::createObject(Merchant::className(), [$this]);
        }
        return $this->_merchant;
    }

    /* =================== 微信卡卷接口 =================== */

    /**
     * @var object
     */
    private $_card;
    /**
     * @return object
     */
    public function getCard()
    {
        if ($this->_card === null) {
            $this->_card = Yii::createObject(Card::className(), [$this]);
        }
        return $this->_card;
    }

    /* =================== 微信智能接口 =================== */

    /**
     * 语义理解
     */
    const WECHAT_SEMANTIC_SEMPROXY_PREFIX = '/semantic/semproxy/search';
    /**
     * 语义理解
     * @param array $data
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function searchSemantic(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SEMANTIC_SEMPROXY_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result : false;
    }

    /* =================== 设备功能(物联网, 欢迎PR) =================== */

    /* =================== 多客服功能(部分功能实现在[发送消息]区域内) =================== */

    /**
     * @var object
     */
    private $_customService;
    /**
     * 多客服组件
     * @return object
     */
    public function getCustomService()
    {
        if ($this->_customService === null) {
            $this->_customService = Yii::createObject(CustomService::className(), [$this]);
        }
        return $this->_customService;
    }

    /* =================== 摇一摇周边 =================== */

    /**
     * @var object
     */
    private $_shakeAround;
    /**
     * 摇一摇组件
     * @return object
     */
    public function getShakeAround()
    {
        if ($this->_shakeAround === null) {
            $this->_shakeAround = Yii::createObject(ShakeAround::className(), [$this]);
        }
        return $this->_shakeAround;
    }
}