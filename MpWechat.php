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
            'access_token' => $this->getAccessToken()
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
            'access_token' => $this->getAccessToken()
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
            'access_token' => $this->getAccessToken()
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
            'access_token' => $this->getAccessToken(),
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
            'access_token' => $this->getAccessToken()
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
        $result = $this->httpRaw(self::WECHAT_ARTICLES_UPLOAD_URL, [
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
     * @param $mediaPath
     * @param $type
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function addMaterial($mediaPath, $type)
    {
        $result = $this->httpPost(self::WECHAT_MATERIAL_ADD_PREFIX, [
            'media' => $this->uploadFile($mediaPath)
        ], [
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
    const WECHAT_MEMBER_GROUP_ID_GET_PREFIX = '/cgi-bin/groups/getid';
    /**
     * 查询用户所在分组
     * @param $openId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getMemberGroupId($openId)
    {
        $result = $this->httpRaw(self::WECHAT_MEMBER_GROUP_ID_GET_PREFIX, [
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
    const WECHAT_MEMBER_GROUP_UPDATE_PREFIX = '/cgi-bin/groups/members/update';
    /**
     * 移动用户分组
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateMemberGroup(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_MEMBER_GROUP_UPDATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 批量移动用户分组
     */
    const WECHAT_MEMBERS_GROUP_UPDATE_PREFIX = '/cgi-bin/groups/members/batchupdate';
    /**
     * 批量移动用户分组
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateMmembersGroup(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_MEMBER_GROUP_UPDATE_PREFIX, $data, [
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
     * @param $gorupId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deletGroup($gorupId)
    {
        $result = $this->httpRaw(self::WECHAT_MEMBER_GROUP_UPDATE_PREFIX, [
            'group' => [
                'id' => $gorupId
            ]
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 设置用户备注名
     */
    const WEHCAT_MEMBER_MARK_UPDATE = '/cgi-bin/user/info/updateremark';
    /**
     * 设置用户备注名
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateMemberMark(array $data)
    {
        $result = $this->httpRaw(self::WEHCAT_MEMBER_MARK_UPDATE, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取用户基本信息(UnionID机制)
     */
    const WECHAT_MEMBER_INFO_GET = '/cgi-bin/user/info';
    /**
     * 获取用户基本信息(UnionID机制)
     * @param $openId
     * @param string $lang
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getMemberInfo($openId, $lang = 'zh_CN')
    {
        $result = $this->httpGet(self::WECHAT_MEMBER_INFO_GET, [
            'access_token' => $this->getAccessToken(),
            'openid' => $openId,
            'lang' => $lang
        ]);
        return array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 获取用户列表
     */
    const WECHAT_MEMBER_LIST_GET_PREFIX = '/cgi-bin/user/get';
    /**
     * 获取用户列表
     * @param $nextOpenId
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getMemberList($nextOpenId)
    {
        $result = $this->httpGet(self::WECHAT_MEMBER_LIST_GET_PREFIX, [
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
    const WEHCAT_SNS_MEMBER_INFO_PREFIX = '/sns/userinfo';
    /**
     * 拉取用户信息(需scope为 snsapi_userinfo):第四步
     * @param $openId
     * @param string $oauth2AccessToken
     * @param string $lang
     * @return array|bool
     */
    public function getSnsMemberInfo($openId, $oauth2AccessToken, $lang = 'zh_CN')
    {
        $result = $this->httpGet(self::WEHCAT_SNS_MEMBER_INFO_PREFIX, [
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
        $result = $this->httpGet(self::WECHAT_SNS_AUTH_URL, [
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
    const WECHAT_QRCODE_CREATE_PREFIX = '/cgi-bin/qrcode/create';
    /**
     * 创建二维码ticket
     * @param arary $data
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function createQrcode(arary $data)
    {
        $result = $this->httpRaw(self::WECHAT_QRCODE_CREATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return !array_key_exists('errcode', $result) ? $result : false;
    }

    /**
     * 通过ticket换取二维码
     */
    const WECHAT_QRCODE_SHOW_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode';
    /**
     * 通过ticket换取二维码
     * ticket正确情况下，http 返回码是200，是一张图片，可以直接展示或者下载。
     * @param $ticket
     * @return string
     */
    public function qrcodeUrl($ticket)
    {
        return $this->httpBuildQuery(self::WECHAT_QRCODE_SHOW_URL, ['ticket' => $ticket]);
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
    public function createShortUrl($longUrl)
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
     * 获取用户增减数据
     */
    const WECHAT_USER_SUMMARY_GET_PREFIX = '/datacube/getusersummary';
    /**
     * 获取用户增减数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserSummary(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USER_SUMMARY_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取累计用户数据
     */
    const WECHAT_USER_CUMULATE_GET_PREFIX = '/datacube/getusercumulate';
    /**
     * 获取累计用户数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserCumulate(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USER_CUMULATE_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文群发每日数据
     */
    const WECHAT_ARTICLES_SUMMARY_GET_PREFIX = '/datacube/getarticlesummary';
    /**
     * 获取图文群发每日数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getArticleSummary(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_ARTICLES_SUMMARY_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文群发总数据
     */
    const WECHAT_ARTICLES_TOTAL_GET_PREFIX = '/datacube/getarticletotal';
    /**
     * 获取图文群发总数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getArticleTotal(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_ARTICLES_TOTAL_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文统计数据
     */
    const WECHAT_USER_READ_GET_PREFIX = '/datacube/getuserread';
    /**
     * 获取图文统计数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserRead(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USER_READ_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文统计分时数据
     */
    const WECHAT_USER_READ_HOUR_GET_PREFIX = '/datacube/getuserreadhour';
    /**
     * 获取图文统计分时数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserReadHour(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USER_READ_HOUR_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文分享转发数据
     */
    const WECHAT_USER_SHARE_GET_PREFIX = '/datacube/getusershare';
    /**
     * 获取图文分享转发数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserShare(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USER_SHARE_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取图文分享转发分时数据
     */
    const WECHAT_USER_SHARE_HOUR_GET_PREFIX = '/datacube/getusersharehour';
    /**
     * 获取图文分享转发分时数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserShareUour(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USER_SHARE_HOUR_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送概况数据
     */
    const WECHAT_UP_STREAM_MESSAGE_GET_PREFIX = '/datacube/getupstreammsg';
    /**
     * 获取消息发送概况数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessage(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息分送分时数据
     */
    const WECHAT_UP_STREAM_MESSAGE_HOUR_GET_PREFIX = '/datacube/getupstreammsghour';
    /**
     * 获取消息分送分时数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageHour(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_HOUR_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送周数据
     */
    const WECHAT_UP_STREAM_MESSAGE_WEEK_GET_PREFIX = '/datacube/getupstreammsgweek';
    /**
     * 获取消息发送周数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageWeek(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_WEEK_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送月数据
     */
    const WECHAT_UP_STREAM_MESSAGE_MONTH_GET_PREFIX = '/datacube/getupstreammsgmonth';
    /**
     * 获取消息发送月数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageMonth(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_MONTH_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送分布数据
     */
    const WECHAT_UP_STREAM_MESSAGE_DIST_GET_PREFIX = '/datacube/getupstreammsgdist';
    /**
     * 获取消息发送分布数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageDist(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_DIST_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送分布周数据
     */
    const WECHAT_UP_STREAM_MESSAGE_DIST_WEEK_GET_PREFIX = '/datacube/getupstreammsgdistweek';
    /**
     * 获取消息发送分布周数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageDistWeek(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_DIST_WEEK_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取消息发送分布月数据
     */
    const WECHAT_UP_STREAM_MESSAGE_DIST_MONTH_GET_PREFIX = '/datacube/getupstreammsgdistmonth';
    /**
     * 获取消息发送分布月数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUpStreamMessageDistMonth(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_DIST_MONTH_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取接口分析数据
     */
    const WECHAT_INTERFACE_SUMMARY_GET_PREFIX = '/datacube/getinterfacesummary';
    /**
     * 获取接口分析数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getInterfaceSummary(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_INTERFACE_SUMMARY_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /**
     * 获取接口分析分时数据
     */
    const WECHAT_INTERFACE_SUMMARY_HOUR_GET_PREFIX = '/datacube/getinterfacesummaryhour';
    /**
     * 获取接口分析分时数据
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getInterfaceSummaryHour(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_INTERFACE_SUMMARY_HOUR_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }

    /* =================== 微信JS-SDK =================== */

    /**
     * js api ticket 获取
     */
    const WECHAT_JS_API_TICKET_PREFIX = '/cgi-bin/ticket/getticket';
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
            'noncestr' => Yii::$app->getSecurity()->generateRandomString(16),
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'url' => explode('#', Yii::$app->getRequest()->getAbsoluteUrl())[0]
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
     * 增加商品
     */
    const WECHAT_SHOP_PRODUCT_CREATE_PREFIX  = '/merchant/create';
    /**
     * 增加商品
     * @param array $data 商品详细信息
     * ~~~
     * $data = [
     *     'product_base' => [
     *         'category_id' => [537074298],
     *         'property' => [
     *             [
     *                 'id' => 1075741879,
     *                 'vid' => 1079749967
     *             ],
     *             [
     *                 'id' => 1075754127,
     *                 'vid' => 1079795198
     *            ],
     *             [
     *                 'id' => 1075777334,
     *                 'vid' => 1079837440
     *             ]
     *         ],
     *         'name' => 'testaddproduct',
     *         'sku_info' => [
     *             [
     *                 'id' => 1075741873,
     *                'vid' => [1079742386, 1079742363]
     *             ]
     *         ],
     *         'main_img' => 'http://mmbiz.qpic.cn/mmbiz/4whpV1VZl2iccsvYbHvnphkyGtnvjD3ulEKogfsiaua49pvLfUS8Ym0GSYjViaLic0FD3vN0V8PILcibEGb2fPfEOmw/0',
     *         'img' => ['http://mmbiz.qpic.cn/mmbiz/4whpV1VZl2iccsvYbHvnphkyGtnvjD3ulEKogfsiaua49pvLfUS8Ym0GSYjViaLic0FD3vN0V8PILcibEGb2fPfEOmw/0'],
     *         'detail' => [
     *             [
     *                'text' => 'test first'
     *             ],
     *             [
     *                'img' => 'http://mmbiz.qpic.cn/mmbiz/4whpV1VZl2iccsvYbHvnphkyGtnvjD3ul1UcLcwxrFdwTKYhH9Q5YZoCfX4Ncx655ZK6ibnlibCCErbKQtReySaVA/0'
     *            ],
     *            [
     *                'text' => 'test again'
     *             ]
     *         ],
     *         'buy_limit' => 10
     *     ],
     *     'sku_list' => [
     *         [
     *             'sku_id' => '1075741873:1079742386',
     *             'price' => 30,
     *             'icon_url' => 'http://mmbiz.qpic.cn/mmbiz/4whpV1VZl28bJj62XgfHPibY3ORKicN1oJ4CcoIr4BMbfA8LqyyjzOZzqrOGz3f5KWq1QGP3fo6TOTSYD3TBQjuw/0',
     *             'product_code' => 'testing',
     *             'ori_price' => 9000000,
     *             'quantity' => 800
     *         ],
     *         [
     *             'sku_id' => '1075741873:1079742363',
     *             'price' => 30,
     *             'icon_url' => 'http://mmbiz.qpic.cn/mmbiz/4whpV1VZl28bJj62XgfHPibY3ORKicN1oJ4CcoIr4BMbfA8LqyyjzOZzqrOGz3f5KWq1QGP3fo6TOTSYD3TBQjuw/0',
     *             'product_code' => 'testingtesting',
     *             'ori_price' => 9000000,
     *             'quantity' => 800,
     *         ]
     *     ],
     *     'attrext' => [
     *         'location' => [
     *             'country' => '中国',
     *             'province' => '广东省',
     *             'city' => '广州市',
     *             'address' => 'T.I.T创意园',
     *         ],
     *         'isPostFree' => 0,
     *         'isHasReceipt' => 1,
     *         'isUnderGuaranty' => 0,
     *         'isSupportReplace' => 0,
     *     ],
     *     'delivery_info' => [
     *         'delivery_type' => 0,
     *         'template_id' => 0,
     *         'express' => [
     *             [
     *                 'id' => 10000027,
     *                 'price' => 100,
     *             ],
     *             [
     *                 'id' => 10000028,
     *                 'price' => 100
     *             ],
     *             '2' => [
     *                 'id' => 10000029,
     *                 'price' => 100
     *             ]
     *         ]
     *     ]
     * ];
     * ~~~
     * @return array|bool
     * @throws \yii\web\HttpException
     */
    public function createProduct(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_CREATE_URL, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['product_id'] : false;
    }

    /**
     * 商品删除
     */
    const WECHAT_SHOP_PRODUCT_DELETE_PREFIX = '/merchant/del';
    /**
     * 删除商品
     * @param $productId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteProduct($productId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_DELETE_PREFIX, [
            'product_id' => $productId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 商品修改
     */
    const WECHAT_SHOP_PRODUCT_UPDATE_PREFIX = '/merchant/update';
    /**
     * 商品修改
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateProduct(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_UPDATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 查询商品信息
     */
    const WECHAT_SHOP_PRODUCT_GET_PREFIX = '/merchant/del';
    /**
     * 查询商品信息
     * @param $productId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getProduct($productId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_GET_PREFIX, [
            'product_id' => $productId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['product_info'] : false;
    }

    /**
     * 获取指定状态的商品
     * @param $status 商品状态(0-全部, 1-上架, 2-下架)
     * @return array|bool
     */
    public function getProductByStatus($status)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_STATUS_PRODUCT_GET_URL, [
            'status' => $status
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['product_info'] : false;
    }

    /**
     * 更改商品状态(上下架)
     */
    const WECHAT_SHOP_PRODUCT_STATUS_UPDATE_PREFIX = '/merchant/modproductstatus';
    /**
     * 更改商品状态(上下架)
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateProductStatus(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_STATUS_UPDATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取指定分类的所有子分类
     * @param $catId 分类ID
     * @return array|bool
     */
    public function getCategorySubCategory($cateId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_CATEGORY_SUB_GET_URL, [
            'cate_id' => $cateId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['cate_list'] : false;
    }

    /**
     * 获取指定子分类的所有SKU
     */
    const WECHAT_SHOP_CATEGORY_SKU_LIST_GET_PREFIX = '/merchant/category/getsku';
    /**
     * 获取指定分类的单品
     * @param $cateId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getCategorySkuList($cateId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_CATEGORY_SKU_LIST_GET_PREFIX, [
            'cate_id' => $cateId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['sku_table'] : false;
    }

    /**
     * 获取指定分类的所有属性
     */
    const WECHAT_SHOP_CATEGORY_PROPERTY_GET_PREFIX = '/merchant/category/getproperty';
    /**
     * 获取指定分类的所有属性
     * @param $cateId 分类ID
     * @return array|bool
     */
    public function getCategoryProperty($cateId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_CATEGORY_PROPERTY_GET_PREFIX, [
            'cate_id' => $cateId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['properties'] : false;
    }

    /**
     * 商品增加库存
     */
    const WECHAT_SHOP_PRODUCT_STOCK_ADD_PREFIX = '/merchant/stock/add';
    /**
     * 增加库存
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addProductStock(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_STOCK_ADD_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 减少库存
     */
    const WECHAT_SHOP_PRODUCT_STOCK_REDUCE_URL = '/merchant/stock/reduce';
    /**
     * 减少库存
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function reduceProductStock(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_STOCK_REDUCE_URL, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 增加邮费模板
     */
    const WECHAT_SHOP_DELIVERY_TEMPLATE_ADD_PREFIX = '/merchant/express/add?';
    /**
     * @param array $deliveryTemplate
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addDeliveryTemplate(array $deliveryTemplate)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_DELIVERY_TEMPLATE_ADD_PREFIX, [
            'delivery_template' => $deliveryTemplate
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['template_id'] : false;
    }

    /**
     * 删除邮费模板
     */
    const WECHAT_SHOP_DELIVERY_TEMPLATE_DELETE_PREFIX = '/merchant/express/del?';
    /**
     * 删除邮费模板
     * @param int $templateId 邮费模板ID
     * @return bool
     */
    public function deleteDeliverTemplate($templateId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_DELIVERY_TEMPLATE_DELETE_PREFIX, [
            'template_id' => $templateId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改邮费模板
     */
    const WECHAT_SHOP_DELIVERY_TEMPLATE_UPDATE_PREFIX = '/merchant/express/update?';
    /**
     * 修改邮费模板
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateDeliverTemplate(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_DELIVERY_TEMPLATE_UPDATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取指定ID的邮费模板
     */
    const WECHAT_SHOP_DELIVERY_TEMPLATE_ID_GET_PREFIX = '/merchant/express/getbyid?';
    /**
     * 获取指定ID的邮费模板
     * @param $templateId
     * @return bool
     */
    public function getDeliverTemplate($templateId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_DELIVERY_TEMPLATE_ID_GET_PREFIX, [
            'template_id' => $templateId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['template_info'] : false;
    }

    /**
     * 获取所有邮费模板
     */
    const WECHAT_SHOP_DELIVERY_TEMPLATE_LIST_GET_PREFIX = '/merchant/express/getall';
    /**
     * 获取所有邮费模板
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getDeliverTemplateList()
    {
        $result = $this->httpGet(self::WECHAT_SHOP_DELIVERY_TEMPLATE_LIST_GET_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['templates_info'] : false;
    }

    /**
     * 增加分组
     */
    const WECHAT_SHOP_GROUP_ADD_PREFIX = '/merchant/group/add';
    /**
     * 增加店铺分组
     * @param array $groupDetail
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addShopGroup(array $groupDetail)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_ADD_URL, [
            'group_detail' => $groupDetail
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['group_id'] : false;
    }

    /**
     * 删除分组
     */
    const WECHAT_SHOP_GROUP_DELETE_PREFIX = '/merchant/group/del?';
    /**
     * 删除店铺分组
     * @param $groupId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteShopGroup($groupId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_DELETE_PREFIX, [
            'group_id' => $groupId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改分组属性
     */
    const WECHAT_SHOP_GROUP_UPDATE_PREFIX = '/merchant/group/propertymod';
    /**
     * 修改分组属性
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateShopGroup(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_UPDATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改分组商品
     */
    const WECHAT_SHOP_GROUP_PRODUCT_UPDATE_PREFIX = '/merchant/group/productmod';
    /**
     * 修改分组商品
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateShopGroupProduct(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_PRODUCT_UPDATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取所有分组
     */
    const WECHAT_SHOP_GROUP_LIST_PREFIX = '/merchant/group/getall';
    /**
     * 获取所有分组
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getShopGroupList()
    {
        $result = $this->httpGet(self::WECHAT_SHOP_GROUP_LIST_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['groups_detail'] : false;
    }

    /**
     * 根据分组ID获取分组信息
     */
    const WECHAT_SHOP_GROUP_ID_GET_PREFIX = '/merchant/group/getbyid';
    /**
     * 根据分组ID获取分组信息
     * @param $groupId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getShopGroup($groupId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_ID_GET_PREFIX, [
            'group_id' => $groupId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['groups_detail'] : false;
    }

    /**
     * 增加货架
     */
    const WECHAT_SHOP_SHELF_ADD_PREFIX = '/merchant/shelf/add';
    /**
     * 增加货架
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addShelf(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_SHELF_ADD_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['shelf_id'] : false;
    }

    /**
     * 删除货架
     */
    const WECHAT_SHOP_SHELF_DELETE_PREFIX = '/merchant/shelf/del';
    /**
     * 删除货架
     * @param $shelfId
     * @return bool
     */
    public function deleteShelf($shelfId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_SHELF_DELETE_PREFIX, [
            'shelf_id' => $shelfId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改货架
     */
    const WECHAT_SHOP_SHELF_UPDATE_PREFIX = '/merchant/shelf/mod';
    /**
     * 修改货架
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateShelf(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_SHELF_UPDATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取所有货架
     */
    const WECHAT_SHOP_SHELF_LIST_PREFIX = '/merchant/shelf/getall';
    /**
     * 获取所有货架
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getShelfList()
    {
        $result = $this->httpGet(self::WECHAT_SHOP_SHELF_LIST_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['shelves'] : false;
    }

    /**
     * 根据货架ID获取货架信息
     */
    const WECHAT_SHOP_SHELF_ID_GET_PREFIX = '/merchant/shelf/getbyid';
    /**
     * 根据货架ID获取货架信息
     * @param $shelfId
     * @return array|bool
     */
    public function getShelf($shelfId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_SHELF_ID_GET_PREFIX, [
            'shelf_id' => $shelfId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['shelf_info'] : false;
    }

    /**
     * 根据订单ID获取订单详情
     */
    const WECHAT_SHOP_ORDER_GET_PREFIX = '/merchant/order/getbyid?';
    /**
     * 根据订单ID获取订单详情
     * @param $orderId
     * @return bool
     * @throws HttpException
     */
    public function getOrder($orderId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_ORDER_GET_PREFIX, [
            'order_id' => $orderId
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['order'] : false;
    }

    /**
     * 根据订单状态/创建时间获取订单详情
     */
    const WECHAT_SHOP_ORDER_FILTER_GET_PREFIX = '/merchant/order/getbyfilter';
    /**
     * 根据订单状态/创建时间获取订单详情
     * @param array $data
     * ~~~
     * $data = [
     *     'status' => 2, // 订单状态(不带该字段-全部状态, 2-待发货, 3-已发货, 5-已完成, 8-维权中, )
     *     'begintime' => 1397130460, // 订单创建时间起始时间(不带该字段则不按照时间做筛选)
     *     'endtime' => 1397130470 // 订单创建时间终止时间(不带该字段则不按照时间做筛选)
     * ];
     * ~~~
     * @return bool
     */
    public function getOrderByFilter(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_ORDER_FILTER_GET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['order_list'] : false;
    }

    /**
     * 设置订单发货信息
     */
    const WECHAT_SHOP_ORDER_DELIVERY_SET_PREFIX = '/merchant/order/setdelivery';
    /**
     * 设置订单发货信息
     * 注: 物流公司ID
     *    邮政EMS	Fsearch_code
     *    申通快递	002shentong
     *    中通速递	066zhongtong
     *    圆通速递	056yuantong
     *    天天快递	042tiantian
     *    顺丰速运	003shunfeng
     *    韵达快运	059Yunda
     *    宅急送	    064zhaijisong
     *    汇通快运	020huitong
     *    易迅快递	zj001yixun
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function setOrderDelivery(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_ORDER_DELIVERY_SET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['order_list'] : false;
    }

    /**
     * 关闭订单
     */
    const WECHAT_SHOP_ORDER_CLOSE_PREFIX = '/merchant/order/close';
    /**
     * 关闭订单
     * @param $orderId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function closeOrder($orderId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_ORDER_CLOSE_PREFIX, [
            'order_id' => $orderId,
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 上传图片(小店接口)
     * @param $filePath 文件完整路径
     * @param null $fileName 文件名 如不填写.则使用文件路径里的名称
     * @return bool
     */
    public function uploadShopImage($filePath, $fileName = null)
    {
        $fileName === null && $fileName = pathinfo($filePath, PATHINFO_BASENAME);
        $result = $this->httpRaw(self::WECHAT_SHOP_IMAGE_UPLOAD_URL .
            'access_token=' . $this->getAccessToken() . '&filename=' . $fileName, file_get_contents($filePath));
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['image_url'] : false;
    }

    /* =================== 微信卡卷接口(文档v2.0) =================== */

    /* ==== 门店管理(文档V2.2.3) ===== */

    /* =================== 微信智能接口 =================== */

    /* =================== 多客服功能 =================== */

    /* =================== 摇一摇周边 =================== */
}