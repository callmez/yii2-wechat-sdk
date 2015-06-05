<?php
namespace callmez\wechat\sdk\mp;

use callmez\wechat\sdk\components\WechatComponent;

/**
 * 数据统计组件
 * @package callmez\wechat\sdk\components\mp
 */
class DataCube extends WechatComponent
{
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
        $result = $this->wechat->httpRaw(self::WECHAT_USER_SUMMARY_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_USER_CUMULATE_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_ARTICLES_SUMMARY_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_ARTICLES_TOTAL_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_USER_READ_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_USER_READ_HOUR_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_USER_SHARE_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_USER_SHARE_HOUR_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_HOUR_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_WEEK_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_MONTH_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_DIST_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_DIST_WEEK_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_UP_STREAM_MESSAGE_DIST_MONTH_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_INTERFACE_SUMMARY_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
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
        $result = $this->wechat->httpRaw(self::WECHAT_INTERFACE_SUMMARY_HOUR_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['list']) ? $result['list'] : false;
    }
}