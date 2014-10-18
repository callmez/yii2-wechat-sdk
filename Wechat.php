<?php
namespace yii\wechat\sdk;

use Yii;
use yii\base\Event;
use yii\base\Component;
use yii\web\HttpException;
use yii\base\InvalidParamException;
use yii\base\InvalidConfigException;

/**
 * 微信公众号API类
 * 相关文档请参考 http://mp.weixin.qq.com/wiki 微信公众平台开发者文档
 * @package yii\wechat\components
 * @version 1.0.0alpha
 */
class Wechat extends Component
{
    const EVENT_AFTER_ACCESS_TOKEN_UPDATE = 'afterAccessTokenUpdate';
    /**
     * 微信接口基本地址
     */
    const WECHAT_BASE_URL = 'https://api.weixin.qq.com';
    /**
     * access token获取
     */
    const WECHAT_ACCESS_TOKEN_URL = '/cgi-bin/token?';
    /**
     * 创建菜单
     */
    const WECHAT_MENU_CREATE_URL = '/cgi-bin/menu/create?';
    /**
     * 获取菜单
     */
    const WECHAT_MENU_GET_URL = '/cgi-bin/menu/get?';
    /**
     * 发送客服消息
     */
    const WECHAT_CUSTOM_MESSAGE_SEND_URL = '/cgi-bin/message/custom/send?';
    /**
     * 消息上传
     */
    const WECHAT_ARTICLES_UPLOAD_URL = '/cgi-bin/media/uploadnews?';
    /**
     * 消息发送
     */
    const WECHAT_ARTICLES_SEND_URL = '/cgi-bin/message/mass/sendall?';
    /**
     * 删除群发
     */
    const WECHAT_ARTICLES_SEND_CANCEL_URL = '/cgi-bin/message/mass/delete?';
    /**
     * video消息的上传
     */
    const WECHAT_MEDIA_VIDEO_UPLOAD_URL = '/cgi-bin/media/uploadvideo?';
    /**
     * 媒体文件上传
     */
    const WECHAT_MEDIA_UPLOAD_URL = 'http://file.api.weixin.qq.com/cgi-bin/media/upload?';
    /**
     * 媒体文件获取
     */
    const WECHAT_MEDIA_URL = 'http://file.api.weixin.qq.com/cgi-bin/media/get?';
    /**
     *  分组创建
     */
    const WECHAT_CREATE_GROUP_URL = '/cgi-bin/groups/create?';
    /**
     *  分组列表获取
     */
    const WECHAT_GROUP_GET_URL = '/cgi-bin/groups/get?';
    /**
     * 修改分组名
     */
    const WECHAT_UPDATE_GROUP_NAME_URL = '/cgi-bin/groups/update?';
    /**
     *  获取关注者所在分组ID
     */
    const WECHAT_GET_GROUP_ID_URL = '/cgi-bin/groups/getid?';
    /**
     * 修改关注者所在分组
     */
    const WECHAT_MEMBER_GROUP_UPDATE_URL = '/cgi-bin/groups/members/update?';
    /**
     * 修改关注者备注
     */
    const WECHAT_MEMBER_REMARK_UPDATE_URL = '/cgi-bin/user/info/updateremark?';
    /**
     * 关注者基本信息
     */
    const WECHAT_MEMBER_INFO_URL = '/cgi-bin/user/info?';
    /**
     * 关注者列表
     */
    const WECHAT_MEMBER_GET_URL = '/cgi-bin/user/get?';
    /**
     * 获取客服聊天记录
     */
    const WECHAT_CUSTOMER_SERVICE_RECORD_GET_URL = '/cgi-bin/customservice/getrecord?';
    /**
     * QR二维码创建
     */
    const WECHAT_CREATE_QRCODE_URL = '/cgi-bin/qrcode/create?';
    /**
     * QR二维码展示
     */
    const WECHAT_SHOW_QRCODE_URL = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?';
    /**
     * 短连接
     */
    const WECHAT_SHORT_URL_URL = '/cgi-bin/shorturl?';
    /**
     * 网页授权获取关注者信息
     */
    const WECHAT_OAUTH2_AUTHORIZE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
    /**
     * 标记客户的投诉处理状态
     */
    const WECHAT_PAY_FEEDBACK_URL = '/payfeedback/update?';
    /**
     * 商品创建
     */
    const WECHAT_SHOP_PRODUCT_CREATE_URL  = '/merchant/create?';
    /**
     * 商品删除
     */
    const WECHAT_SHOP_PRODUCT_DELETE_URL = '/merchant/del?';
    /**
     * 商品修改
     */
    const WECHAT_SHOP_PRODUCT_UPDATE_URL = '/merchant/update?';
    /**
     * 获取商品
     */
    const WECHAT_SHOP_PRODUCT_GET_URL = '/merchant/del?';
    /**
     * 获取指定状态的所有商品
     */
    const WECHAT_SHOP_STATUS_PRODUCT_UPDATE_URL = '/merchant/getbystatus?';
    /**
     * 商品上下架
     */
    const WECHAT_SHOP_STATUS_PRODUCT_GET_URL = '/merchant/modproductstatus?';
    /**
     * 商品增加库存
     */
    const WECHAT_SHOP_PRODUCT_STOCK_ADD_URL = '/merchant/stock/add?';
    /**
     * 商品减少库存
     */
    const WECHAT_SHOP_PRODUCT_STOCK_REDUCE_URL = '/merchant/stock/reduce?';
    /**
     * 增加分组
     */
    const WECHAT_SHOP_GROUP_ADD_URL = '/merchant/group/add?';
    /**
     * 删除分组
     */
    const WECHAT_SHOP_GROUP_DELETE_URL = '/merchant/group/del?';
    /**
     * 修改分组属性
     */
    const WECHAT_SHOP_GROUP_UPDATE_URL = '/merchant/group/propertymod?';
    /**
     * 修改分组商品
     */
    const WECHAT_SHOP_GROUP_PRODUCT_UPDATE_URL = '/merchant/group/productmod?';
    /**
     * 获取所有分组
     */
    const WECHAT_SHOP_GROUP_LIST_URL = '/merchant/group/getall?';
    /**
     * 根据分组ID获取分组信息
     */
    const WECHAT_SHOP_GROUP_ID_GET_URL = '/merchant/group/getbyid?';
    /**
     * 获取指定分类的所有子分类
     */
    const WECHAT_SHOP_CATEGORY_SUB_GET_URL = '/merchant/category/getsub?';
    /**
     * 获取指定子分类的所有SKU
     */
    const WECHAT_SHOP_CATEGORY_SKU_LIST_GET_URL = '/merchant/category/getsku?';
    /**
     * 获取指定分类的所有属性
     */
    const WECHAT_SHOP_CATEGORY_PROPERTY_GET_URL = '/merchant/category/getproperty?';
    /**
     * 增加邮费模板
     */
    const WECHAT_SHOP_DELIVERY_TEMPLATE_ADD_URL = '/merchant/express/add?';
    /**
     * 删除邮费模板
     */
    const WECHAT_SHOP_DELIVERY_TEMPLATE_DELETE_URL = '/merchant/express/del?';
    /**
     * 修改邮费模板
     */
    const WECHAT_SHOP_DELIVERY_TEMPLATE_UPDATE_URL = '/merchant/express/update?';
    /**
     * 获取指定ID的邮费模板
     */
    const WECHAT_SHOP_DELIVERY_TEMPLATE_ID_GET_URL = '/merchant/express/getbyid?';
    /**
     * 获取所有邮费模板
     */
    const WECHAT_SHOP_DELIVERY_TEMPLATE_LIST_GET_URL = '/merchant/express/getall?';
    /**
     * 增加货架
     */
    const WECHAT_SHOP_SHELF_ADD_URL = '/merchant/shelf/add?';
    /**
     * 删除货架
     */
    const WECHAT_SHOP_SHELF_DELETE_URL = '/merchant/shelf/del?';
    /**
     * 修改货架
     */
    const WECHAT_SHOP_SHELF_UPDATE_URL = '/merchant/shelf/mod?';
    /**
     * 获取所有货架
     */
    const WECHAT_SHOP_SHELF_LIST_URL = '/merchant/shelf/getall?';
    /**
     * 根据货架ID获取货架信息
     */
    const WECHAT_SHOP_SHELF_ID_GET_URL = '/merchant/shelf/getbyid?';
    /**
     * 根据订单ID获取订单详情
     */
    const WECHAT_SHOP_ORDER_GET_URL = '/merchant/order/getbyid?';
    /**
     * 根据订单状态/创建时间获取订单详情
     */
    const WECHAT_SHOP_ORDER_FILTER_GET_URL = '/merchant/order/getbyfilter?';
    /**
     * 设置订单发货信息
     */
    const WECHAT_SHOP_ORDER_DELIVERY_SET_URL = '/merchant/order/setdelivery?';
    /**
     * 关闭订单
     */
    const WECHAT_SHOP_ORDER_CLOSE_URL = '/merchant/order/close?';
    /**
     * 上传图片(小店接口)
     */
    const WECHAT_SHOP_IMAGE_UPLOAD_URL = '/merchant/common/upload_img?';
    /**
     * @var string 公众号appId
     */
    public $appId;
    /**
     * @var string 公众号appSecret
     */
    public $appSecret;
    /**
     * @var string 公众号支付请求中用于加密的密钥 Key，可验证商户唯一身份，PaySignKey 对应于支付场景中的 appKey 值。
     */
    public $paySignKey;
    /**
     * @var sting 财付通商户身份标识。
     */
    public $partnerId;
    /**
     * @var string 财付通商户权限密钥 Key
     */
    public $partnerKey;
    /**
     * @var string 公众号接口验证token,可由您来设定. 并填写在微信公众平台->开发者中心
     */
    public $token;
    /**
     * 数据缓存前缀
     * @var string
     */
    public $cacheKey = 'wechat_cache';
    /**
     * 数据缓存时长
     * @var int
     */
    public $cacheTime = 3600;
    /**
     * @var array 最后请求的错误信息
     */
    public $lastErrorInfo;
    /**
     * 操作ID(会化状态）定义
     * 可用于显示客服聊天记录的操作详情
     * @var array
     */
    public $operCode = [
        '1000' => '创建未接入会话',
        '1001' => '接入会话',
        '1002' => '主动发起会话',
        '1004' => '关闭会话',
        '1005' => '抢接会话',
        '2001' => '公众号收到消息',
        '2002' => '客服发送消息',
        '2003' => '客服收到消息',
    ];
    /**
     * 回调错误代码
     * 可用于检索用户返回错误详情
     * @var array
     */
    public $errorCode = [
        '-1' => '系统繁忙',
        '0' => '请求成功',
        '40001' => '获取access_token时AppSecret错误，或者access_token无效',
        '40002' => '不合法的凭证类型',
        '40003' => '不合法的OpenID',
        '40004' => '不合法的媒体文件类型',
        '40005' => '不合法的文件类型',
        '40006' => '不合法的文件大小',
        '40007' => '不合法的媒体文件id',
        '40008' => '不合法的消息类型',
        '40009' => '不合法的图片文件大小',
        '40010' => '不合法的语音文件大小',
        '40011' => '不合法的视频文件大小',
        '40012' => '不合法的缩略图文件大小',
        '40013' => '不合法的APPID',
        '40014' => '不合法的access_token',
        '40015' => '不合法的菜单类型',
        '40016' => '不合法的按钮个数',
        '40017' => '不合法的按钮个数',
        '40018' => '不合法的按钮名字长度',
        '40019' => '不合法的按钮KEY长度',
        '40020' => '不合法的按钮URL长度',
        '40021' => '不合法的菜单版本号',
        '40022' => '不合法的子菜单级数',
        '40023' => '不合法的子菜单按钮个数',
        '40024' => '不合法的子菜单按钮类型',
        '40025' => '不合法的子菜单按钮名字长度',
        '40026' => '不合法的子菜单按钮KEY长度',
        '40027' => '不合法的子菜单按钮URL长度',
        '40028' => '不合法的自定义菜单使用用户',
        '40029' => '不合法的oauth_code',
        '40030' => '不合法的refresh_token',
        '40031' => '不合法的openid列表',
        '40032' => '不合法的openid列表长度',
        '40033' => '不合法的请求字符，不能包含\uxxxx格式的字符',
        '40035' => '不合法的参数',
        '40038' => '不合法的请求格式',
        '40039' => '不合法的URL长度',
        '40050' => '不合法的分组id',
        '40051' => '分组名字不合法',
        '41001' => '缺少access_token参数',
        '41002' => '缺少appid参数',
        '41003' => '缺少refresh_token参数',
        '41004' => '缺少secret参数',
        '41005' => '缺少多媒体文件数据',
        '41006' => '缺少media_id参数',
        '41007' => '缺少子菜单数据',
        '41008' => '缺少oauth code',
        '41009' => '缺少openid',
        '42001' => 'access_token超时',
        '42002' => 'refresh_token超时',
        '42003' => 'oauth_code超时',
        '43001' => '需要GET请求',
        '43002' => '需要POST请求',
        '43003' => '需要HTTPS请求',
        '43004' => '需要接收者关注',
        '43005' => '需要好友关系',
        '44001' => '多媒体文件为空',
        '44002' => 'POST的数据包为空',
        '44003' => '图文消息内容为空',
        '44004' => '文本消息内容为空',
        '45001' => '多媒体文件大小超过限制',
        '45002' => '消息内容超过限制',
        '45003' => '标题字段超过限制',
        '45004' => '描述字段超过限制',
        '45005' => '链接字段超过限制',
        '45006' => '图片链接字段超过限制',
        '45007' => '语音播放时间超过限制',
        '45008' => '图文消息超过限制',
        '45009' => '接口调用超过限制',
        '45010' => '创建菜单个数超过限制',
        '45015' => '回复时间超过限制',
        '45016' => '系统分组，不允许修改',
        '45017' => '分组名字过长',
        '45018' => '分组数量超过上限',
        '46001' => '不存在媒体数据',
        '46002' => '不存在的菜单版本',
        '46003' => '不存在的菜单数据',
        '46004' => '不存在的用户',
        '47001' => '解析JSON/XML内容错误',
        '48001' => 'api功能未授权',
        '50001' => '用户未授权该api',
    ];
    public $templateMessageErrorCode = [
        ' -1' => '系统繁忙',
        '0' => '请求成功',
        '40001' => '验证失败',
        '40002' => '不合法的凭证类型',
        '40003' => '不合法的OpenID',
        '40004' => '不合法的媒体文件类型,',
        '40005' => '不合法的文件类型,',
        '40006' => '不合法的文件大小',
        '40007' => '不合法的媒体文件id',
        '40008' => '不合法的消息类型',
        '40009' => '不合法的图片文件大小',
        '40010' => '不合法的语音文件大小',
        '40011' => '不合法的视频文件大小',
        '40012' => '不合法的缩略图文件大小',
        '40013' => '不合法的APPID',
        '41001' => '缺少access_token参数',
        '41002' => '缺少appid参数',
        '41003' => '缺少refresh_token参数',
        '41004' => '缺少secret参数',
        '41005' => '缺少多媒体文件数据',
        '41006' => 'access_token超时',
        '42001' => '需要GET请求',
        '43002' => '需要POST请求',
        '43003' => '需要HTTPS请求',
        '44001' => '多媒体文件为空',
        '44002' => 'POST的数据包为空',
        '44003' => '图文消息内容为空',
        '45001' => '多媒体文件大小超过限制',
        '45002' => '消息内容超过限制',
        '45003' => '标题字段超过限制',
        '45004' => '描述字段超过限制',
        '45005' => '链接字段超过限制',
        '45006' => '图片链接字段超过限制',
        '45007' => '语音播放时间超过限制',
        '45008' => '图文消息超过限制',
        '45009' => '接口调用超过限制',
        '46001' => '不存在媒体数据',
        '47001' => '解析JSON/XML内容错误',
    ];
    /**
     * @var array
     */
    private $_accessToken;

    public function init()
    {
        if ($this->appId === null) {
            throw new InvalidConfigException('The appId property must be set.');
        } elseif ($this->appSecret === null) {
            throw new InvalidConfigException('The appSecret property must be set.');
        } elseif ($this->token === null) {
            throw new InvalidConfigException('The token property must be set.');
        }
    }

    /**
     * 微信服务器请求签名检测
     * @param string $signature 微信加密签名，signature结合了开发者填写的token参数和请求中的timestamp参数、nonce参数。
     * @param string $timestamp 时间戳
     * @param string $nonce 随机数
     * @return bool
     */
    public function checkSignature($signature = null, $timestamp = null, $nonce = null)
    {
        $signature === null && $signature = isset($_GET['signature']) ? $_GET['signature'] : '';
        $timestamp === null && $timestamp = isset($_GET['timestamp']) ? $_GET['timestamp'] : '';
        $nonce === null && $nonce = isset($_GET['nonce']) ? $_GET['nonce'] : '';
        $tmpArr = array($this->token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode($tmpArr);
        return sha1($tmpStr) == $signature;
    }

    /**
     * 解析微信服务器请求的xml数据
     * @param srting $xml 服务发送的xml数据
     * @return array
     */
    public function parseRequestData($xml = null)
    {
        $xml === null && $xml = file_get_contents("php://input");
        return empty($xml) ? [] : (array)simplexml_load_string($xml);
    }

    /**
     * 设置AccessToken
     * @param string @param array $data  ['token' => 'token 字符串', 'expire' => 'token 超时时间']
     */
    public function setAccessToken(array $data)
    {
        if (!isset($data['token'])) {
            throw new InvalidParamException('The wechat token must be set.');
        } elseif(!isset($data['expire'])) {
            throw new InvalidParamException('Wechat token expire time must be set.');
        }
        $this->_accessToken = [
            'token' => $data['token'],
            'expire' => $data['expire']
        ];
    }

    /**
     * 获取AccessToken
     * 会自动判断超时时间然后重新获取新的token
     * (会智能缓存accessToken)
     * @param bool $force 是否强制获取
     * @return string
     * @throws \yii\base\Exception
     */
    public function getAccessToken($force = false)
    {
        if ($force || $this->_accessToken === null || $this->_accessToken['expire'] < YII_BEGIN_TIME) {
            $result = false;
            if (!$force && $this->_accessToken === null) {
                $result = $this->getCache('access_token', false);
            }
            if ($result === false) {
                $result = $this->httpGet(static::WECHAT_ACCESS_TOKEN_URL, [
                    'appid' => $this->appId,
                    'secret' => $this->appSecret,
                    'grant_type' => 'client_credential'
                ]);
                if (!isset($result['access_token']) || !isset($result['expires_in'])) {
                    throw new HttpException('Fail to get accessToken form wechat server.');
                }
                $result['expire'] = $result['expires_in'] + time();
                $this->trigger(self::EVENT_AFTER_ACCESS_TOKEN_UPDATE, new Event(['data' => $result]));
                $this->setCache('access_token', $result);
            }
            $this->setAccessToken($result['access_token'], $result['expire']);
        }
        return $this->_accessToken['token'];
    }

    /**
     * 创建菜单
     * @param array $buttons 菜单结构字符串
     * ~~~
     *  $this->createMenu([
     *      [
     *           'type' => 'click',
     *           'name' => '今日歌曲',
     *           'key' => 'V1001_TODAY_MUSIC'
     *      ]
     *      [
     *           'type' => 'click',
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
        $result = $this->httpRaw(self::WECHAT_MENU_CREATE_URL . 'access_token=' . $this->getAccessToken(), json_encode([
            'button' => $buttons
        ]));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取菜单列表
     * @return bool
     */
    public function getMenuList()
    {
        $result = $this->httpRaw(self::WECHAT_MENU_GET_URL . 'access_token=' . $this->getAccessToken());
        return isset($result['menu']['button']) ? $result['menu']['button'] : fasle;
    }

    /**
     * 删除菜单
     * @return bool
     */
    public function deleteMenu()
    {
        $result = $this->httpGet(self::WECHAT_MENU_DELETE_URL . 'access_token=' . $this->getAccessToken());
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 发送模板消息给关注者
     * @param $toUser 关注者openID
     * @param $templateId 模板ID(模板需在公众平台模板消息中挑选)
     * @param array $data 模板需要的数据
     * @return int|bool
     */
    public function sendTemplateMessage($toUser, $templateId, array $data)
    {
        $data = [
            'url' => null,
            'topcolor' => '#FF0000'
        ] + $data;
        $result = $this->httpRaw(self::WECHAT_TEMPLATE_MESSAGE_SEND_URL . 'access_token=' . $this->getAccessToken(),
            json_encode($data, JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok' ? $result['msgid'] : false;
    }

    /**
     * 发送文本客服信息
     * @param $openId 关注者openID
     * @param $content 文本消息内容
     * @return bool
     */
    public function sendText($openId, $content)
    {
        return $this->sendCustomMessage([
            'touser' => $openId,
            'msgtype' => 'text',
            'text' => [
                'content' => $content
            ]
        ]);
    }

    /**
     * 发送图片客服消息
     * @param string $openId 关注者openID
     * @param string $mediaId 发送的图片的媒体ID
     * @return bool
     */
    public function sendImage($openId, $mediaId)
    {
        return $this->sendCustomMessage([
            'touser' => $openId,
            'msgtype' => 'voice',
            'voice' => [
                'media_id' => $mediaId
            ]
        ]);
    }

    /**
     * 发送声音客服消息
     * @param string $openId 关注者openID
     * @param string $mediaId 发送的语音的媒体ID
     * @return bool
     */
    public function sendVoice($openId, $mediaId)
    {
        return $this->sendCustomMessage([
            'touser' => $openId,
            'msgtype' => 'voice',
            'voice' => [
                'media_id' => $mediaId
            ]
        ]);
    }

    /**
     * 发送视频客服信息
     * @param string $openId 关注者openID
     * @param string $mediaId 发送的视频的媒体ID
     * @param string $thumbMediaId 缩略图的媒体ID
     * @param string $title 视频消息的标题
     * @param string $description 视频消息的描述
     * @return bool
     */
    public function sendVideo($openId, $mediaId, $thumbMediaId, $title = null, $description = null)
    {
        return $this->sendCustomMessage([
            'touser' => $openId,
            'msgtype' => 'video',
            'video' => [
                'media_id' => $mediaId,
                'thumb_media_id' => $thumbMediaId,
                'title' => $title,
                'description' => $description
            ]
        ]);
    }

    /**
     * 发送音乐客服消息
     * @param $openId 关注者openID
     * @param $thumbMediaId 缩略图的媒体ID
     * @param $musicUrl 音乐链接
     * @param $hqMusicUrl 高品质音乐链接，wifi环境优先使用该链接播放音乐
     * @param null $title 音乐标题
     * @param null $description 音乐描述
     * @return bool
     */
    public function sendMusic($openId, $thumbMediaId, $musicUrl, $hqMusicUrl, $title = null, $description = null)
    {
        return $this->sendCustomMessage([
            'touser' => $openId,
            'msgtype' => 'music',
            'music' => [
                'thumb_media_id' => $thumbMediaId,
                'musicurl' => $musicUrl,
                'hqMusicUrl' => $hqMusicUrl,
                'title' => $title,
                'description' => $description
            ]
        ]);
    }

    /**
     * 发送图文客服消息
     * @param $openId 关注者openID
     * @param array $articles 图文信息内容部分
     * ~~~
     * $articles = [
     *      'title' => 'Happy Day',
     *      'description' => 'Is Really A Happy Day',
     *      'url' => 'URL',
     *      'picurl' => 'PIC_URL'
     * ]
     * ~~~
     * @return bool
     */
    public function sendNews($openId, array $articles)
    {
        return $this->sendCustomMessage([
            'touser' => $openId,
            'msgtype' => 'news',
            'news' => [
                'articles' => $articles
            ]
        ]);
    }

    /**
     * 发送客服消息
     * @param array $data
     * @return bool
     */
    protected function sendCustomMessage(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_CUSTOM_MESSAGE_SEND_URL . 'access_token=' . $this->getAccessToken(),
            json_encode($data, JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 图文消息上传(高级群发接口)
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
        $result = $this->httpRaw(self::WECHAT_ARTICLES_UPLOAD_URL . 'access_token=' . $this->getAccessToken(),
            json_encode([
                'articles' => $articles
            ], JSON_UNESCAPED_UNICODE));
        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 群发消息
     * @param $target 发送对象 groupid 或 openid
     * ~~~
     * $target = [
     *     'filter' => [
     *          'group_id' => 'groupId',
     *      ]
     * ];
     * //OR
     *  $target = [
     *     'touser' => [
     *          'openId1',
     *          'openId2',
     *          'openId3',
     *      ]
     * ];
     * ~~~
     * @param $content 发送内容 (发送多媒体只需发送media_id)
     * @param string $type 发送类型text, mpnews, voice, image, mpvideo(发送给指定群组), video(发送给指定关注者)
     */
    public function sendArticles(array $target, $content, $type)
    {
        if ($type !== 'video') {
            $content = [
                $type === 'text' ? 'content' : 'media_id' => $content
            ];
        }
        $result = $this->httpRaw(self::WECHAT_ARTICLES_SEND_URL . 'access_token=' . $this->getAccessToken(),
            json_encode($target + [
                    $type => $content,
                    'msgtype' => $type
                ], JSON_UNESCAPED_UNICODE));
        return isset($result['msg_id']) ? $result : false;
    }

    /**
     * 取消群发消息
     * @param $messageId 消息ID
     */
    public function cancelSendArticles($messageId)
    {
        $result = $this->httpRaw(self::WECHAT_ARTICLES_SEND_CANCEL_URL . 'access_token=' .
            $this->getAccessToken(), [
                'msgid' => $messageId
            ]);
        isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 上传多媒体文件(群发接口)
     * 推送消息时 video信息需另外发送一次.
     * 但是上传还是必须通过 uploadMedia() 上传 获取到mediaId后再通过此函数 提交一次
     * 获得最终的media_id 才可以推送消息
     * @param $mediaId 媒体id
     * @param $title 媒体文件的标题
     * @param $description 媒体文件的描述
     * @return array|bool
     */
    public function uploadVideo($mediaId, $title, $description)
    {
        $result = $this->httpRaw(self::WECHAT_MEDIA_VIDEO_UPLOAD_URL . 'access_token=' . $this->getAccessToken(),
            json_encode([
                'media_id' => $mediaId,
                'title' => $title,
                'description' => $description
            ], JSON_UNESCAPED_UNICODE));
        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 上传媒体文件
     * @param $filePath 媒体文件路径
     * @param $mediaType 媒体文件类型，
     * 分别有图片（image）、语音（voice）、视频（video）和缩略图（thumb，主要用于视频与音乐格式的缩略图）
     * 图片（image）: 1M，支持JPG格式
     * 语音（voice）：2M，播放长度不超过60s，支持AMR\MP3格式
     * 视频（video）：10MB，支持MP4格式
     * 缩略图（thumb）：64KB，支持JPG格式
     * @return array|bool
     */
    public function uploadMedia($filePath, $mediaType)
    {
        $result = $this->httpPost(self::WECHAT_MEDIA_UPLOAD_URL .
            'access_token=' . $this->getAccessToken() . '&type=' . $mediaType, [
                'media' => '@' . $filePath
            ]);
        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 下载媒体文件
     * @param $mediaId 媒体文件id
     * @return array|bool
     */
    public function getMedia($mediaId)
    {
        $result = $this->httpGet(self::WECHAT_MEDIA_URL . 'access_token=' . $this->getAccessToken(), [
            'media_id' => $mediaId
        ]);
        return !isset($result['errcode']) ? $result : false;
    }

    /**
     * 创建分组
     * @param $name 分组名称
     * @return array|bool
     */
    public function createGroup($name)
    {
        $result = $this->httpRaw(self::WECHAT_CREATE_GROUP_URL . 'access_token=' . $this->getAccessToken(),
            json_encode([
                'group' => [
                    'name' => $name
                ]
            ], JSON_UNESCAPED_UNICODE));
        return isset($result['group']) ? $result['group'] : false;
    }

    /**
     * 获取分组列表
     * @return array|bool
     */
    public function getGroupList()
    {
        $result = $this->httpRaw(self::WECHAT_GROUP_GET_URL . 'access_token=' . $this->getAccessToken());
        return isset($result['groups']) ? $result['groups'] : false;
    }

    /**
     * 根据关注者openID获取分组ID
     * @param $openId 关注者openID
     * @return array|bool
     */
    public function getGroupId($openId)
    {
        $result = $this->httpRaw(self::WECHAT_GET_GROUP_ID_URL . 'access_token=' . $this->getAccessToken(),
            json_encode([
                'openid' => $openId
            ], JSON_UNESCAPED_UNICODE));
        return isset($result['groupid']) ? $result['groupid'] : false;
    }

    /**
     * 根据分组ID修改分组名
     * @param $id 分组的ID
     * @param $name 修改后的分组名
     * @return bool
     */
    public function updateGroupName($id, $name)
    {
        $result = $this->httpRaw(self::WECHAT_UPDATE_GROUP_NAME_URL . 'access_token=' . $this->getAccessToken(),
            json_encode([
                'group' => [
                    'id' => $id,
                    'name' => $name
                ]
            ], JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 修改关注者所在分组
     * @param $openId 关注者openID
     * @param $toGroupId 分组ID
     * @return bool
     */
    public function updateMemberGroup($openId, $toGroupId)
    {
        $result = $this->httpRaw(self::WECHAT_MEMBER_GROUP_UPDATE_URL . 'access_token=' . $this->getAccessToken(),
            json_encode([
                'openid' => $openId,
                'to_groupid' => $toGroupId
            ], JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 修改关注者备注
     * @param $openId 关注者openID
     * @param $remark 备注内容
     */
    public function updateMemberRemark($openId, $remark)
    {
        $result = $this->httpRaw(self::WECHAT_MEMBER_REMARK_UPDATE_URL . 'access_token=' . $this->getAccessToken(),
            json_encode([
                'openid' => $openId,
                'remark' => $remark
            ], JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取关注者基本信息
     * @param $openId 关注者openID
     * @param string $lang 返回国家地区语言版本，zh_CN 简体，zh_TW 繁体，en 英语
     * @return array|bool
     */
    public function getMemberInfo($openId, $lang = 'zh_CN')
    {
        $result = $this->httpGet(self::WECHAT_MEMBER_INFO_URL . 'access_token=' . $this->getAccessToken(), [
            'openid' => $openId,
            'lang' => $lang
        ]);
        return !isset($result['errcode']) ? $result : false;
    }

    /**
     * 获取关注者列表
     * @param string $nextOpenId 第一个拉取的OPENID，不填默认从头开始拉取
     * @return array|bool
     */
    public function getMemberList($nextOpenId = null)
    {
        $result = $this->httpGet(self::WECHAT_MEMBER_GET_URL . 'access_token=' . $this->getAccessToken(),
            $nextOpenId === null ? [] : ['next_openid' => $nextOpenId]);
        return !isset($result['errcode']) ? $result : false;
    }

    /**
     * 获取关注者的客服聊天记录
     * @param $openId 关注者的openID
     * @param $startTime 查询开始时间，UNIX时间戳
     * @param $endTime 查询结束时间，UNIX时间戳，每次查询不能跨日查询
     * @param $pageIndex 每页大小，每页最多拉取1000条
     * @param $pageSize 查询第几页，默认从1开始
     * @return array|bool
     */
    public function getCustomerServiceRecords($openId, $startTime, $endTime, $pageIndex = 1, $pageSize = 1000)
    {
        $result = $this->httpRaw(self::WECHAT_CUSTOMER_SERVICE_RECORD_GET_URL . 'access_token=' .
            $this->getAccessToken(), json_encode([
                'openid' => $openId,
                'starttime' => $startTime,
                'endtime' => $endTime,
                'pageindex' => $pageIndex,
                'pagesize' => $pageSize,
            ]));
        return isset($result['recordlist']) ? $result : false;
    }

    /**
     * 创建QR二维码
     * @param $sceneId 场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
     * @param bool $isLimitScene 是否永久二维码
     * @param int $expireSeconds 临时二维码存在时间 (永久二维码该参数无效)
     */
    public function createQrCode($sceneId, $isLimitScene = false, $expireSeconds = 1800)
    {
        $params = [
            'action_name' => $isLimitScene ? 'QR_LIMIT_SCENE' : 'QR_SCENE',
            'action_info' => [
                'scene' => [
                    'scene_id' => $sceneId
                ]
            ]
        ];
        if (!$isLimitScene) {
            $params += ['expireSeconds' => $expireSeconds];
        }
        $result = $this->httpRaw(self::WECHAT_CREATE_QRCODE_URL . 'access_token=' . $this->getAccessToken(),
            json_encode($params, JSON_UNESCAPED_UNICODE));
        return isset($result['ticket']) ? $result : false;
    }

    /**
     * 获取二维码图片
     * @param $ticket 获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
     */
    public function getQrCodeUrl($ticket)
    {
        return self::WECHAT_SHOW_QRCODE_URL . 'ticket=' . urlencode($ticket);
    }

    /**
     * 创建短链接
     * @param $longUrl 需要转换的长链接，支持http://、https://、weixin://wxpay 格式的url
     * @return bool
     */
    public function createShortUrl($longUrl)
    {
        $result = $this->httpRaw(self::WECHAT_SHORT_URL_URL, [
            'action' => 'long2short',
            'long_url' => $longUrl,
        ]);
        return isset($result['short_url']) ? $result['short_url'] : false;
    }

    /**
     * 网页授权获取用户基本信息, 通过此函数生成授权url
     * @param $redirectUrl 授权后重定向的回调链接地址，请使用urlencode对链接进行处理
     * @param $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
     * @param string $scope 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），
     * snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
     * @return string
     */
    public function getAuthorizeUrl($redirectUrl, $state = 'authorize', $scope = 'snsapi_base')
    {
        return self::WECHAT_OAUTH2_AUTHORIZE_URL . http_build_query(array(
            'appid' => $this->appId,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
        )) . '#wechat_redirect';
    }

    /**
     * 标记客户的投诉处理状态
     * @param $openId 关注者openID
     * @param $feedbackId 客户投诉对应的单号
     * @return bool
     */
    public function updateFeedback($openId, $feedbackId)
    {
        $result = $this->httpGet(self::WECHAT_PAY_FEEDBACK_URL . 'access_token=' . $this->getAccessToken(), [
            'openid' => $openId,
            'feedbackid' => $feedbackId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 添加商品
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
     */
    public function createProduct(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_CREATE_URL . 'access_token=' . $this->getAccessToken(),
            json_encode($data, JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['product_id'] : false;
    }

    /**
     * 删除商品
     * @param $productId 商品id
     * @return bool
     */
    public function deleteProduct($productId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_DELETE_URL . 'access_token=' . $this->getAccessToken(), [
            'product_id' => $productId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * @param $productId
     * @param array $data
     * @return bool
     */
    public function updateProduct($productId, array $data)
    {
        $data['product_id'] = $productId;
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_UPDATE_URL . 'access_token=' . $this->getAccessToken(),
            json_encode($data, JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取商品信息
     * @param $productId
     * @return array|bool
     */
    public function getProduct($productId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_GET_URL . 'access_token=' . $this->getAccessToken(), [
            'product_id' => $productId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['product_info'] : false;
    }

    /**
     * 更改商品状态(上下架)
     * @param $productId
     * @param $status 商品状态(0-全部, 1-上架, 2-下架)
     * @return bool
     */
    public function updateProductStatus($productId, $status)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_STATUS_PRODUCT_UPDATE_URL .
            'access_token=' . $this->getAccessToken(), [
                'product_id' => $productId,
                'status' => $status
            ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取指定状态的商品
     * @param $status 商品状态(0-全部, 1-上架, 2-下架)
     * @return array|bool
     */
    public function getProductByStatus($status)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_STATUS_PRODUCT_GET_URL . 'access_token=' . $this->getAccessToken(), [
            'status' => $status
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['product_info'] : false;
    }

    /**
     * 增加库存
     * @param $productId 商品ID
     * @param $quantity 增加的库存数量
     * @param array $skuInfo sku信息,格式"id1:vid1;id2:vid2",如商品为统一规格，则此处赋值为空字符串即可
     * @return bool
     */
    public function addProductStock($productId, $quantity, array $skuInfo = null)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_STOCK_ADD_URL . 'access_token=' . $this->getAccessToken(), [
            'product_id' => $productId,
            'quantity' => $quantity,
            'sku_info' => $skuInfo === nulll ? $skuInfo : implode(':', $skuInfo)
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 减少库存
     * @param $productId 商品ID
     * @param $quantity 增加的库存数量
     * @param array $skuInfo sku信息,格式"id1:vid1;id2:vid2",如商品为统一规格，则此处赋值为空字符串即可
     * @return bool
     */
    public function reduceProductStock($productId, $quantity, array $skuInfo = null)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_STOCK_REDUCE_URL . 'access_token=' . $this->getAccessToken(), [
            'product_id' => $productId,
            'quantity' => $quantity,
            'sku_info' => $skuInfo === nulll ? $skuInfo : implode(':', $skuInfo)
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 增加店铺分组
     * @param string $groupName 分组名称
     * @param array $productIdList 商品ID集合
     * @return bool
     */
    public function addShopGroup($groupName, array $productIdList)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_ADD_URL . 'access_token=' . $this->getAccessToken(),
            json_encode([
                'group_detail' => [
                    'group_name' => $groupName,
                    'product_list' => $productIdList
                ]
            ], JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['group_id'] : false;
    }

    /**
     * 删除店铺分组
     * @param int $groupId
     * @return bool
     */
    public function deleteShopGroup($groupId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_DELETE_URL . 'access_token=' . $this->getAccessToken(), [
            'group_id' => $groupId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改分组属性
     * @param int $groupId
     * @param $groupName
     */
    public function updateShopGroup($groupId, $groupName)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_UPDATE_URL . 'access_token=' . $this->getAccessToken(), [
            'group_id' => $groupId,
            'group_name' => $groupName
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改分组商品
     * @param $groupId
     * @param array $productList 分组的商品集合
     */
    public function updateShopGroupProduct($groupId, array $productList)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_PRODUCT_UPDATE_URL .
            'access_token=' . $this->getAccessToken(), [
                'group_id' => $groupId,
                'product' => $productList
            ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取所有分组
     * @return array|bool
     */
    public function getShopGroupList()
    {
        $result = $this->httpGet(self::WECHAT_SHOP_GROUP_LIST_URL . 'access_token=' . $this->getAccessToken());
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['groups_detail'] : false;
    }

    /**
     * 根据分组ID获取分组信息
     * @param int $groupId
     * @return array|bool
     */
    public function getShopGroup($groupId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_ID_GET_URL . 'access_token=' . $this->getAccessToken(), [
            'group_id' => $groupId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['groups_detail'] : false;
    }

    /**
     * 获取指定分类的子分类
     * @param $catId 分类ID
     * @return array|bool
     */
    public function getCategorySubCategory($cateId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_CATEGORY_SUB_GET_URL . 'access_token=' . $this->getAccessToken(), [
            'cate_id' => $cateId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['cate_list'] : false;
    }

    /**
     * 获取指定分类的单品
     * @param $cateId 分类ID
     * @return array|bool
     */
    public function getCategorySkuList($cateId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_CATEGORY_SKU_LIST_GET_URL .
            'access_token=' . $this->getAccessToken(), [
                'cate_id' => $cateId
            ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['sku_table'] : false;
    }

    /**
     * 获取指定分类的所有属性
     * @param $cateId 分类ID
     * @return array|bool
     */
    public function getCategoryProperty($cateId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_CATEGORY_PROPERTY_GET_URL .
            'access_token=' . $this->getAccessToken(), [
                'cate_id' => $cateId
            ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['properties'] : false;
    }

    /**
     * 增加邮费模板
     * @param array $deliveryemplate 邮费信息
     * ~~~
     * wip.
     * ~~~
     * @return int|bool
     */
    public function addDeliveryTemplate(array $deliveryTemplate)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_DELIVERY_TEMPLATE_ADD_URL .
            'access_token=' . $this->getAccessToken(), json_encode([
                'delivery_template' => $deliveryTemplate
            ], JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['template_id'] : false;
    }

    /**
     * 删除邮费模板
     * @param int $templateId 邮费模板ID
     * @return bool
     */
    public function deleteDeliverTemplate($templateId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_DELIVERY_TEMPLATE_DELETE_URL .
            'access_token=' . $this->getAccessToken(), [
                'template_id' => $templateId
            ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改邮费模板
     * @param int $templateId 邮费模板ID
     * @param array $deliveryTemplate
     * @return bool
     */
    public function updateDeliverTemplate($templateId, array $deliveryTemplate)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_DELIVERY_TEMPLATE_UPDATE_URL .
            'access_token=' . $this->getAccessToken(), json_encode([
                'template_id' => $templateId,
                'delivery_template' => $deliveryTemplate
            ], JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取指定ID的邮费模板
     * @param int $templateId 邮费模板ID
     * @return array|bool
     */
    public function getDeliverTemplate($templateId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_DELIVERY_TEMPLATE_ID_GET_URL .
            'access_token=' . $this->getAccessToken(), [
                'template_id' => $templateId
            ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['template_info'] : false;
    }

    /**
     * 获取所有邮费模板
     * @return array|bool
     */
    public function getDeliverTemplateList()
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_DELIVERY_TEMPLATE_LIST_GET_URL .
            'access_token=' . $this->getAccessToken());
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['template_info'] : false;
    }

    /**
     * 增加货架
     * @param array $data
     * @return bool
     */
    public function addShelf(array $data)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_SHELF_ADD_URL . 'access_token=' . $this->getAccessToken(),
            json_encode($data, JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['shelf_id'] : false;
    }

    /**
     * 删除货架
     * @param $shelfId
     * @return bool
     */
    public function deleteShelf($shelfId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_SHELF_DELETE_URL . 'access_token=' . $this->getAccessToken(), [
            'shelf_id' => $shelfId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改货架
     * @param $shelfId
     * @param $data
     * @return bool
     */
    public function updateShelf($shelfId, $data)
    {
        $data = [
            'shelf_id' => $shelfId
        ] + $data;
        $result = $this->httpRaw(self::WECHAT_SHOP_SHELF_UPDATE_URL . 'access_token=' . $this->getAccessToken(),
            json_encode($data, JSON_UNESCAPED_UNICODE));
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取所有货架
     * @return array|bool
     */
    public function getShelfList()
    {
        $result = $this->httpGet(self::WECHAT_SHOP_SHELF_LIST_URL . 'access_token=' . $this->getAccessToken());
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['shelves'] : false;
    }

    /**
     * 根据货架ID获取货架信息
     * @param $shelfId
     * @return array|bool
     */
    public function getShelf($shelfId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_SHELF_ID_GET_URL . 'access_token=' . $this->getAccessToken(), [
            'shelf_id' => $shelfId
        ]);
        if (isset($result['errmsg']) && $result['errmsg'] == 'success') {
            unset($result['errcode'], $result['errmsg']);
            return $result;
        }
        return false;
    }

    /**
     * 根据订单ID获取订单详情
     * @param int $orderId
     */
    public function getOrder($orderId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_ORDER_GET_URL . 'access_token=' . $this->getAccessToken(), [
            'order_id' => $orderId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['order'] : false;
    }

    /**
     * 根据订单状态/创建时间获取订单详情
     * @param array $condition
     * ~~~
     * $condition = [
     *     'status' => 2, // 订单状态(不带该字段-全部状态, 2-待发货, 3-已发货, 5-已完成, 8-维权中, )
     *     'begintime' => 1397130460, // 订单创建时间起始时间(不带该字段则不按照时间做筛选)
     *     'endtime' => 1397130470 // 订单创建时间终止时间(不带该字段则不按照时间做筛选)
     * ];
     * ~~~
     * @return bool
     */
    public function getOrderByFilter(array $condition)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_ORDER_FILTER_GET_URL . 'access_token=' . $this->getAccessToken(),
            $condition);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['order_list'] : false;
    }

    /**
     * 设置订单发货信息
     * @param $orderId 订单ID
     * @param $deliveryCompany 物流公司ID
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
     * @param $deliveryTrackNo 运单ID
     * @return bool
     */
    public function setOrderDelivery($orderId, $deliveryCompany, $deliveryTrackNo)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_ORDER_DELIVERY_SET_URL .
            'access_token=' . $this->getAccessToken(), [
                'order_id' => $orderId,
                'delivery_company' => $deliveryCompany,
                'delivery_track_no' => $deliveryTrackNo
            ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['order_list'] : false;
    }

    /**
     * 关闭订单
     * @param int $orderId
     * @return bool
     */
    public function closeOrder($orderId)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_ORDER_CLOSE_URL . 'access_token=' . $this->getAccessToken(), [
            'order_id' => $orderId,
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

    /**
     * 缓存微信数据
     * @param $name
     * @param $value
     * @param null $duration
     * @return bool
     */
    protected function setCache($name, $value, $duration = null)
    {
        $duration === null && $duration = $this->cacheTime;
        return Yii::$app->getCache()->set("{$this->cacheKey}_{$this->appId}_{$name}", $value, $duration);
    }

    /**
     * 获取微信缓存数据
     * @param $name
     * @param null $defaultValue
     * @return mixed
     */
    protected function getCache($name, $defaultValue = null)
    {
        return Yii::$app->getCache()->get("{$this->cacheKey}_{$this->appId}_{$name}", $defaultValue);
    }

    /**
     * Get方式调用微信接口
     * @param $url
     * @param null $params
     * @return array
     */
    public function httpGet($url, $params = null)
    {
        return $this->parseHttpResult($url, $params, 'get');
    }

    /**
     * Post方式调用微信接口
     * @param $url
     * @param null $params
     * @return array
     */
    public function httpPost($url, $params = null)
    {
        return $this->parseHttpResult($url, $params, 'post');
    }

    /**
     * Post方式发送raw包调用微信接口
     * @param $url
     * @param null $params
     * @return array
     */
    public function httpRaw($url, $params = null)
    {
        return $this->parseHttpResult($url, $params, 'raw');
    }

    /**
     * 解析api回调请求
     * 会根据返回结果处理响应的回调结果.如 40001 access_token失效(会强制更新access_token后)重发, 保证请求的的有效
     * @param $url
     * @param $params
     * @param $method
     * @param bool $force
     * @return bool|mixed
     */
    protected function parseHttpResult($url, $params, $method, $force = true)
    {
        if (stripos($url, 'http://') === false && stripos($url, 'https://') === false) {
            $url = self::WECHAT_BASE_URL . $url;
        }
        $return = $this->http($url, $params, $method);
        $return = json_decode($return, true) ? : $return;
        if (isset($return['errcode'])) {
            switch ($return['errcode']) {
                case 40001: //access_token 失效,强制更新access_token重新获取
                    if ($force) {
                        $url = preg_replace("/access_token=([^&]*)/ies",
                            '"access_token=" . \$this->getAccessToken(true)', $url);
                        $return = $this->parseHttpResult($url, $params, $method, false); // 就更新一次
                        break;
                    }
                default:
                    $this->lastErrorInfo = $return;
            }
        }
        return $return;
    }

    /**
     * Http协议调用微信接口方法
     * @param $url api地址
     * @param $params 参数
     * @param string $type 提交类型
     * @return bool|mixed
     * @throws \yii\base\InvalidParamException
     */
    protected function http($url, $params = null, $type = 'get')
    {
        $curl = curl_init();
        switch ($type) {
            case 'get':
                is_array($params) && $params = http_build_query($params);
                !empty($params) && $url .= (stripos($url, '?') === false ? '?' : '&') . $params;
                break;
            case 'post':
                curl_setopt($curl, CURLOPT_POST, true);
                if (!is_array($params)) {
                    throw new InvalidParamException("Post data must be an array.");
                }
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
            case 'raw':
                curl_setopt($curl, CURLOPT_POST, true);
                if (is_array($params)) {
                    throw new InvalidParamException("Post raw data must not be an array.");
                }
                curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
                break;
            default:
                throw new InvalidParamException("Invalid http type '{$type}.' called.");
        }
        if (stripos($url, "https://") !== false) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec($curl);
        $status = curl_getinfo($curl);
        curl_close($curl);
        if (isset($status['http_code']) && intval($status['http_code']) == 200) {
            return $content;
        }
        return false;
    }
}
