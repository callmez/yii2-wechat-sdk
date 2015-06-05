<?php
namespace callmez\wechat\sdk\mp;

use callmez\wechat\sdk\components\WechatComponent;

/**
 * 多客服组件
 * @package callmez\wechat\sdk\components\mp
 */
class CustomService extends WechatComponent
{
    /**
     * 添加客服帐号
     */
    const WECHAT_ACCOUNT_ADD_PREFIX = '/customservice/kfaccount/add';
    /**
     * 添加客服帐号
     * @param array $account
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addAccount(array $account)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_ACCOUNT_ADD_PREFIX, $account, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 修改客服帐号
     */
    const WECHAT_ACCOUNT_UPDATE_PREFIX = '/customservice/kfaccount/update';
    /**
     * 修改客服帐号
     * @param array $account
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateAccount(array $account)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_ACCOUNT_UPDATE_PREFIX, $account, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 删除客服帐号
     */
    const WECHAT_ACCOUNT_DELETE_PREFIX = '/customservice/kfaccount/del';
    /**
     * 删除客服帐号
     * @param array $account
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteAccount(array $account)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_ACCOUNT_DELETE_PREFIX, $account, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 设置客服账号头像
     */
    const WECHAT_ACCOUNT_AVATAR_SET_PREFIX = '/customservice/kfaccount/uploadheadimg';
    /**
     * 设置客服账号头像
     * @param string $accountName
     * @param string $avatarPath
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function setAccountAvatar($accountName, $avatarPath)
    {
        $result = $this->wechat->httpPost(self::WECHAT_ACCOUNT_AVATAR_SET_PREFIX, [
            'media' => $this->wechat->uploadFile($avatarPath)
        ], [
            'access_token' => $this->wechat->getAccessToken(),
            'kf_account' => $accountName
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取所有客服账号
     */
    const WECHAT_ACCOUNT_LIST_GET_PREFIX = '/cgi-bin/customservice/getkflist';
    /**
     * 获取所有客服账号
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getAccountList()
    {
        $result = $this->wechat->httpGet(self::WECHAT_ACCOUNT_LIST_GET_PREFIX, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['kf_list']) ? $result['kf_list'] : false;
    }

    /**
     * 获取客服聊天记录
     */
    const WECHAT_MESSAGE_RECORD_GET_PREFIX = '/customservice/msgrecord/getrecord';
    /**
     * 获取客服聊天记录
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getMessageRecord(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_MESSAGE_RECORD_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['recordlist'] : false;
    }
}