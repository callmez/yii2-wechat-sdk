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
     * @return mixed
     */
    protected function requestAccessToken()
    {
        return $this->httpGet(self::WECHAT_ACCESS_TOKEN_PREFIX, [
            'corpid' => $this->corpId,
            'corpsecret' => $this->secret
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
}