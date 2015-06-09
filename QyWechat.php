<?php
namespace callmez\wechat\sdk;

use callmez\wechat\sdk\components\BaseWechat;

/**
 * 微信企业号操作SDK
 * @package calmez\wechat\sdk
 */
class QyWechat extends BaseWechat
{
    /**
     * 微信接口基本地址
     */
    const WECHAT_BASE_URL = 'https://qyapi.weixin.qq.com';
    /**
     * 数据缓存前缀
     * @var string
     */
    public $cachePrefix = 'cache_wechat_sdk_qy';
    /**
     * 企业号的唯一标识
     * @var string
     */
    public $corpId;
    /**
     * 管理组凭证密钥
     * @var string
     */
    public $secret;

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

    /* =================== 建立连接 =================== */

    /**
     * access token获取
     */
    const WECHAT_ACCESS_TOKEN_PREFIX = '/cgi-bin/token';
    /**
     * 请求服务器access_token
     * @return array|false
     */
    protected function requestAccessToken()
    {
        $result = $this->httpGet(self::WECHAT_ACCESS_TOKEN_PREFIX, [
            'corpid' => $this->corpId,
            'corpsecret' => $this->secret
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

    /* =================== 管理通讯录 =================== */

    /**
     * 二次验证
     */
    const WECHAT_USER_AUTH_SUCCESS_PREFIX = '/cgi-bin/user/authsucc';
    /**
     * 二次验证
     * @param $userId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function userAuthSuccess($userId)
    {
        $result = $this->httpGet(self::WECHAT_USER_AUTH_SUCCESS_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'userid' => $userId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 创建部门
     */
    const WECHAT_DEPARTMENT_CREATE_PREFIX = '/cgi-bin/department/create';
    /**
     * 创建部门
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function createDepartment(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_DEPARTMENT_CREATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['id'] : false;
    }

    /**
     * 创建部门
     */
    const WECHAT_DEPARTMENT_UPDATE_PREFIX = '/cgi-bin/department/update';
    /**
     * 创建部门
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateDepartment(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_DEPARTMENT_CREATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 删除部门
     */
    const WECHAT_DEPARTMENT_DELETE_PREFIX = '/cgi-bin/department/delete';
    /**
     * 删除部门
     * @param $id
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteDepartment($id)
    {
        $result = $this->httpGet(self::WECHAT_DEPARTMENT_DELETE_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'id' => $id
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 获取部门列表
     */
    const WECHAT_DEPARTMENT_LIST = '/cgi-bin/department/list';
    /**
     * 获取部门列表
     * @param null $id 部门id。获取指定部门id下的子部门
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getDepartmentList($id = null)
    {
        $result = $this->httpGet(self::WECHAT_DEPARTMENT_DELETE_PREFIX, [
            'access_token' => $this->getAccessToken(),
        ] + ($id === null ? [] : [
            'id' => $id
        ]));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['department'] : false;
    }

    /**
     * 创建成员
     */
    const WECHAT_USER_CREATE_PREFIX = '/cgi-bin/user/create';
    /**
     * 创建成员
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function createUser(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USER_CREATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 创建成员
     */
    const WECHAT_USER_UPDATE_PREFIX = '/cgi-bin/user/create';
    /**
     * 创建成员
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateUser(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USER_UPDATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 删除成员
     */
    const WECHAT_USER_DELETE_PREFIX = '/cgi-bin/user/delete';
    /**
     * 删除成员
     * @param $userId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteUser($userId)
    {
        $result = $this->httpGet(self::WECHAT_USER_DELETE_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'userid' => $userId
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 批量删除成员
     */
    const WECHAT_USER_BATCH_DELETE_PREFIX = '/cgi-bin/user/batchdelete';
    /**
     * 批量删除成员
     * @param array $userIdList
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function batchDeleteUser(array $userIdList)
    {
        $result = $this->httpRaw(self::WECHAT_USER_BATCH_DELETE_PREFIX, [
            'useridlist' => $userIdList
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 获取部门成员(详情)
     */
    const WECHAT_USER_GET_PREFIX = '/cgi-bin/user/get';
    /**
     * 获取部门成员(详情)
     * @param $userId
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getUser($userId)
    {
        $result = $this->httpGet(self::WECHAT_USER_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'userid' => $userId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result : false;
    }

    /**
     * 获取部门成员
     */
    const WECHAT_DEPARTMENT_USER_LIST_GET_PREFIX = '/cgi-bin/user/simplelist';
    /**
     * 获取部门成员
     * @param $departmentId
     * @param int $fetchChild
     * @param int $status
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getDepartmentUserList($departmentId, $fetchChild = 0, $status = 0)
    {
        $result = $this->httpGet(self::WECHAT_DEPARTMENT_USER_LIST_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'department_id' => $departmentId,
            'fetch_child' => $fetchChild,
            'status' => $status,
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['userlist'] : false;
    }

    /**
     * 获取部门成员(详情)
     */
    const WECHAT_DEPARTMENT_USERS_INFO_LIST_GET_PREFIX = '/cgi-bin/user/list';
    /**
     * 获取部门成员(详情)
     * @param $departmentId
     * @param int $fetchChild
     * @param int $status
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getDepartmentUserInfoList($departmentId, $fetchChild = 0, $status = 0)
    {
        $result = $this->httpGet(self::WECHAT_DEPARTMENT_USERS_INFO_LIST_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'department_id' => $departmentId,
            'fetch_child' => $fetchChild,
            'status' => $status,
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['userlist'] : false;
    }

    /**
     * 邀请成员关注
     */
    const WECHAT_USER_INVITE_PREFIX = '/cgi-bin/invite/send';
    /**
     * 邀请成员关注
     * @param $userId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function inviteUser($userId)
    {
        $result = $this->httpRaw(self::WECHAT_USER_INVITE_PREFIX, [
            'userid' => $userId
        ], [
            'access_token' => $this->getAccessToken(),
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['type'] : false;
    }

    /**
     * 创建标签
     */
    const WECHAT_TAG_CREATE_PREFIX = '/cgi-bin/tag/create';
    /**
     * 创建标签
     * @param $tagName
     * @return int|bool
     * @throws \yii\web\HttpException
     */
    public function createTag($tagName)
    {
        $result = $this->httpRaw(self::WECHAT_TAG_CREATE_PREFIX, [
            'tagname' => $tagName
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['tagid'] : false;
    }

    /**
     * 更新标签名字
     */
    const WECHAT_TAG_NAME_UPDATE_PREFIX = '/cgi-bin/tag/update';
    /**
     * 更新标签名字
     * @param $tagId
     * @param $tagName
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateTagName($tagId, $tagName)
    {
        $result = $this->httpRaw(self::WECHAT_TAG_CREATE_PREFIX, [
            'tagid' => $tagId,
            'tagname' => $tagName
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 删除标签
     */
    const WECHAT_TAG_DELETE_PREFIX = '/cgi-bin/tag/delete';
    /**
     * 删除标签
     * @param $tagId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteTag($tagId)
    {
        $result = $this->httpGet(self::WECHAT_TAG_DELETE_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'tagid' => $tagId
        ]);
        return isset($result['errcode']) && !$result['errcode'];
    }

    /**
     * 获取标签成员
     */
    const WECHAT_TAG_USER_LIST_GET_PREFIX = '/cgi-bin/tag/get';
    /**
     * 获取标签成员
     * @param $tagId
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getTagUserList($tagId)
    {
        $result = $this->httpGet(self::WECHAT_TAG_USER_LIST_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'tagid' => $tagId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result : false;
    }

    /**
     * 增加标签成员
     */
    const WECHAT_TAG_USERS_ADD_PREFIX = '/cgi-bin/tag/addtagusers';
    /**
     * 增加标签成员
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addTagUsers(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_TAG_USERS_ADD_PREFIX, $data, [
            'access_token' => $this->getAccessToken(),
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 删除标签成员
     */
    const WECHAT_TAG_USERS_DELETE_PREFIX = '/cgi-bin/tag/deltagusers';
    /**
     * 删除标签成员
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteTagUsers(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_TAG_USERS_DELETE_PREFIX, $data, [
            'access_token' => $this->getAccessToken(),
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取标签列表
     */
    const WECHAT_TAG_LIST_GET_PREFIX = '/cgi-bin/tag/list';
    /**
     * 获取标签列表
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getTagList()
    {
        $result = $this->httpGet(self::WECHAT_TAG_LIST_GET_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['taglist'] : false;
    }

    /**
     * 邀请成员关注
     */
    const WECHAT_USER_BATCH_INVITE_PREFIX = '/cgi-bin/batch/inviteuser';
    /**
     * 邀请成员关注
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function batchInviteUser(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_TAG_USERS_DELETE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['jobid'] : false;
    }

    /**
     * 增量更新成员
     */
    const WECHAT_USER_BATCH_SYNC_PREFIX = '/cgi-bin/batch/syncuser';
    /**
     * 增量更新成员
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function batchSyncUser(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USER_BATCH_SYNC_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['jobid'] : false;
    }

    /**
     * 全量覆盖成员
     */
    const WECHAT_USER_BATCH_REPLACE_PREFIX = '/cgi-bin/batch/replaceuser';
    /**
     * 全量覆盖成员
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function batchReplaceUser(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_USER_BATCH_REPLACE_PREFIX, $data, [
            'access_token' => $this->getAccessToken(),
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['jobid'] : false;
    }

    /**
     * 全量覆盖部门
     */
    const WECHAT_PARTY_BATCH_REPLACE_PREFIX = '/cgi-bin/batch/replaceparty';
    /**
     * 全量覆盖部门
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function batchReplaceParty(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_PARTY_BATCH_REPLACE_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['jobid'] : false;
    }

    /**
     * 获取异步任务结果
     */
    const WECHAT_BATCH_RESULT_GET_PREFIX = '/cgi-bin/batch/getresult';
    /**
     * 获取异步任务结果
     * @param $jobId
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getBatchResult($jobId)
    {
        $result = $this->httpGet(self::WECHAT_BATCH_RESULT_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'jobid' => $jobId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result : false;
    }

    /* =================== 管理多媒体文件 =================== */

    /**
     * 上传媒体文件
     */
    const WECHAT_MEDIA_UPLOAD_PREFIX = '/cgi-bin/media/upload';
    /**
     * 上传媒体文件
     * @param $mediaPath
     * @param $type
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function updateMedia($mediaPath, $type)
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
     * 获取媒体文件
     */
    const WECHAT_MEDIA_GET_PREFIX = '/cgi-bin/media/get';
    /**
     * 获取媒体文件
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
        return !isset($result['errcode']) ? $result : false;
    }

    /* =================== 管理企业号应用 =================== */

    /**
     * 获取企业号应用
     */
    const WECHAT_AGENT_GET_PREFIX = '/cgi-bin/agent/get';
    /**
     * 获取企业号应用
     * @param $agentId
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getAgent($agentId)
    {
        $result = $this->httpGet(self::WECHAT_AGENT_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'agent_id' => $agentId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result : false;
    }

    /**
     * 设置企业号应用
     */
    const WECHAT_AGENT_SET_PREFIX = '/cgi-bin/agent/set';
    /**
     * 设置企业号应用
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function setAgent(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_AGENT_SET_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取应用概况列表
     */
    const WECHAT_AGENT_LIST_GET_PREFIX = '/cgi-bin/agent/list';
    /**
     * 获取应用概况列表
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getAgentList()
    {
        $result = $this->httpGet(self::WECHAT_AGENT_SET_PREFIX, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['agentlist'] : false;
    }

    /* =================== 发送消息 =================== */

    /**
     * 发送消息
     */
    const WECHAT_MESSAGE_SEND_PREFIX = '/cgi-bin/message/send';
    /**
     * 发送消息
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function sendMessage(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_CUSTOM_MESSAGE_SEND_PREFIX, $data, [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result : false;
    }

    /* =================== 自定义菜单 =================== */

    /**
     * 创建应用菜单
     */
    const WECHAT_MENU_CREATE_PREFIX = '/cgi-bin/menu/create';
    /**
     * 创建应用菜单
     * @param $agentId
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function createMenu($agentId, array $data)
    {
        $result = $this->httpRaw(self::WECHAT_MENU_CREATE_PREFIX, $data, [
            'access_token' => $this->getAccessToken(),
            'agentid' => $agentId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 删除菜单
     */
    const WECHAT_MENU_DELETE_PREFIX = '/cgi-bin/menu/delete';
    /**
     * 删除菜单
     * @param $agentId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteMenu($agentId)
    {
        $result = $this->httpGet(self::WECHAT_MENU_DELETE_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'agentid' => $agentId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取菜单列表
     */
    const WECHAT_MENU_GET_PREFIX = '/cgi-bin/menu/get';
    /**
     * 获取菜单列表
     * @param $agentId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getMenu($agentId)
    {
        $result = $this->httpGet(self::WECHAT_MENU_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'agentid' => $agentId
        ]);
        return isset($result['menu']['button']) ? $result['menu']['button'] : false;
    }

    /* =================== OAuth2验证接口 =================== */

    /**
     * 企业获取code
     */
    const WECHAT_OAUTH2_AUTHORIZE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize';
    /**
     * 企业获取code:第
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
            'appid' => $this->corpId,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
        ]) . '#wechat_redirect';
    }

    /**
     * 根据code获取成员信息
     */
    const WECHAT_USER_IFNO_GET_PREFIX = '/cgi-bin/user/getuserinfo';
    /**
     * 根据code获取成员信息
     * @param $agentId
     * @param $code
     * @return bool|mixed
     * @throws \yii\web\HttpException
     */
    public function getUserInfo($agentId, $code)
    {
        $result = $this->httpGet(self::WECHAT_USER_IFNO_GET_PREFIX, [
            'access_token' => $this->getAccessToken(),
            'code' => $code,
            'agentid' => $agentId
        ]);
        return !isset($result['errcode']) ? $result : false;
    }

    /* =================== 微信JS接口 =================== */

    /**
     * js api ticket 获取
     */
    const WECHAT_JS_API_TICKET_PREFIX = '/cgi-bin/get_jsapi_ticket';
    /**
     * 请求服务器jsapi_ticket
     * @return array
     */
    protected function requestJsApiTicket()
    {
        return $this->httpGet(self::WECHAT_JS_API_TICKET_PREFIX, [
            'access_token' => $this->getAccessToken(),
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
            'noncestr' => Yii::$app->security->generateRandomString(16),
            'timestamp' => $_SERVER['REQUEST_TIME'],
            'url' => explode('#', Yii::$app->request->getAbsoluteUrl())[0]
        ];
        return array_merge([
            'debug' => YII_DEBUG,
            'appId' => $this->corpId,
            'timestamp' => $data['timestamp'],
            'nonceStr' => $data['noncestr'],
            'signature' => sha1(urldecode(http_build_query($data))),
            'jsApiList' => [
                'onMenuShareTimeline',
                'onMenuShareAppMessage',
                'onMenuShareQQ',
                'onMenuShareWeibo',
                'startRecord',
                'stopRecord',
                'onVoiceRecordEnd',
                'playVoice',
                'pauseVoice',
                'stopVoice',
                'onVoicePlayEnd',
                'uploadVoice',
                'downloadVoice',
                'chooseImage',
                'previewImage',
                'uploadImage',
                'downloadImage',
                'translateVoice',
                'getNetworkType',
                'openLocation',
                'getLocation',
                'hideOptionMenu',
                'showOptionMenu',
                'hideMenuItems',
                'showMenuItems',
                'hideAllNonBaseMenuItem',
                'showAllNonBaseMenuItem',
                'closeWindow',
                'scanQRCode'
            ]
        ], $config);
    }

    /* =================== 第三方应用授权 =================== */



}