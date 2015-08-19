<?php
namespace callmez\wechat\sdk\mp;

use callmez\wechat\sdk\components\WechatComponent;

/**
 * 摇一摇周边
 * @package callmez\wechat\components\mp
 */
class ShakeAround extends WechatComponent
{
    /**
     * 申请设备ID
     */
    const WECHAT_DEVICE_APPLY_ID_PREFIX = '/shakearound/device/applyid';
    /**
     * 申请设备ID
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deviceApplyId(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_DEVICE_APPLY_ID_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 编辑设备信息
     */
    const WECHAT_DEVICE_UPDATE_PREFIX = '/shakearound/device/update';
    /**
     * 编辑设备信息
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateDevice(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_DEVICE_UPDATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 配置设备与门店的关联关系
     */
    const WECHAT_DEVICE_LOCATION_BIND_PREFIX = '/shakearound/device/bindlocation';
    /**
     * 配置设备与门店的关联关系
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deviceBindLocation(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_DEVICE_LOCATION_BIND_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 查询设备列表
     */
    const WECHAT_DEVICE_SEARCH_PREFIX = '/shakearound/device/search';
    /**
     * 查询设备列表
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function searchDevice(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_DEVICE_SEARCH_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 新增页面
     */
    const WECHAT_PAGE_ADD_PREFIX = '/shakearound/page/add';
    /**
     * 新增页面
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addPage(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PAGE_ADD_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 编辑页面信息
     */
    const WECHAT_UPDATE_PREFIX= '/shakearound/page/update';
    /**
     * 编辑页面信息
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updatePage(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_UPDATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 查询页面列表
     */
    const WECHAT_PAGE_SEARCH_PREFIX = '/shakearound/page/search';
    /**
     * 查询页面列表
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function searchPage(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PAGE_SEARCH_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 删除页面
     */
    const WECHAT_PAGE_DELETE_PREFIX = '/shakearound/page/delete';
    /**
     * 删除页面
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deletePage(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PAGE_DELETE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 上传图片素材
     */
    const WECHAT_MATERIAL_ADD_PREFIX = '/shakearound/material/add';
    /**
     * 上传图片素材
     * @param $mediaPath
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addMaterial($mediaPath)
    {
        $result = $this->wechat->httpPost(self::WECHAT_MATERIAL_ADD_PREFIX, [
            'media' => $this->wechat->uploadFile($mediaPath)
        ], [
            'access_token' => $this->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 配置设备与页面的关联关系
     */
    const WECHAT_DEVICE_PAGE_BIND_PREFIX = '/shakearound/device/bindpage';
    /**
     * 配置设备与页面的关联关系
     * 配置设备与页面的关联关系
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function devicePageBind(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_DEVICE_PAGE_BIND_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 获取摇周边的设备及用户信息
     */
    const WECHAT_USER_SHAKE_INFO_GET_PREFIX = '/shakearound/user/getshakeinfo';
    /**
     * 获取摇周边的设备及用户信息
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getUserShakeInfo(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_USER_SHAKE_INFO_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 以设备为维度的数据统计接口
     */
    const WECHAT_DEVICE_STATISTICS_PREFIX = '/shakearound/statistics/device';
    /**
     * 以设备为维度的数据统计接口
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deviceStatistics(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_DEVICE_STATISTICS_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }

    /**
     * 以页面为维度的数据统计接口
     */
    const WECHAT_PAGE_STATISTICS_PREFIX = '/shakearound/statistics/page';
    /**
     * 以页面为维度的数据统计接口
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function pageStatistics(array $data)
    {
        $result = $this->wechat->httpPost(self::WECHAT_PAGE_STATISTICS_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errcode']) && !$result['errcode'] ? $result['data'] : false;
    }
}