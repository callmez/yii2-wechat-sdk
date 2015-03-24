<?php
namespace callmez\wechat\sdk;

use Yii;
use yii\base\Event;
use yii\base\Component;
use yii\web\HttpException;
use yii\base\InvalidParamException;
use yii\base\InvalidConfigException;

/**
 * 微信公众号API类
 * 相关文档请参考 http://mp.weixin.qq.com/wiki 微信公众平台开发者文档
 *
 * @package callmez\wechat\components
 * @version 1.0.0alpha
 */
class WechatBasic extends Component
{
    const EVENT_AFTER_ACCESS_TOKEN_UPDATE = 'afterAccessTokenUpdate';
    const EVENT_AFTER_JS_API_TICKET_UPDATE = 'afterJsApiTicketUpdate';
    /**
     * 微信接口基本地址
     */
    const WECHAT_BASE_URL = 'https://api.weixin.qq.com';
    /**
     * access token获取
     */
    const WECHAT_ACCESS_TOKEN_URL = '/cgi-bin/token?';
    /**
     * js api ticket 获取
     */
    const WECHAT_JS_API_TICKET_URL = '/cgi-bin/ticket/getticket?';
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
     * 网页授权获取用户信息
     */
    const WECHAT_OAUTH2_AUTHORIZE_URL = 'https://open.weixin.qq.com/connect/oauth2/authorize?';
    /**
     * 获取网页授权后获取用户access_token地址
     */
    const WECHAT_OAUTH2_ACCESS_TOKEN_URL = '/sns/oauth2/access_token?';
    /**
     * 网页授权后获取的access_token失效刷新地址
     */
    const WECHAT_OAUTH2_ACCESS_TOKEN_REFRESH_URL = '/sns/oauth2/refresh_token?';
    /**
     * 检验授权凭证（access_token）是否有效地址
     */
    const WECHAT_SNS_AUTH_URL = '/sns/auth?';
    /**
     * 拉取用户信息
     */
    const WEHCAT_SNS_USER_INFO_URL = '/sns/userinfo?';
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
     * @var string 公众号接口验证token，可由您来设定。并填写在微信公众平台->开发者中心
     */
    public $token;
    /**
     * 数据缓存前缀
     * @var string
     */
    public $cachePrefix = 'wechat_cache';
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
        $request = Yii::$app->request;
        $signature === null && $signature = $request->getQueryParam('signature', '');
        $timestamp === null && $timestamp = $request->getQueryParam('timestamp', '');
        $nonce === null && $nonce = $request->getQueryParam('nonce', '');
        $tmpArr = [$this->token, $timestamp, $nonce];
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
        $xml === null && $xml = Yii::$app->request->getRawBody();
        return empty($xml) ? [] : (array)simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    }

    protected $_accessToken;

    /**
     * 设置AccessToken
     * @param string @param array $data  ['token' => 'token 字符串', 'expire' => 'token 超时时间']
     */
    public function setAccessToken(array $data)
    {
        if (!isset($data['access_token'])) {
            throw new InvalidParamException('The wechat access_token must be set.');
        } elseif(!isset($data['expire'])) {
            throw new InvalidParamException('Wechat access_token expire time must be set.');
        }
        $this->_accessToken = [
            'token' => $data['access_token'],
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
        if ($this->_accessToken === null || $this->_accessToken['expire'] < YII_BEGIN_TIME || $force) {
            $result = !$force && $this->_accessToken === null ? $this->getCache('access_token', false) : false;
            if ($result === false) {
                if (!($result = $this->requestAccessToken())) {
                    throw new HttpException(500, 'Fail to get access_token from wechat server.');
                }
                $this->trigger(self::EVENT_AFTER_ACCESS_TOKEN_UPDATE, new Event(['data' => $result]));
                $this->setCache('access_token', $result, $result['expires_in']);
            }
            $this->setAccessToken($result);
        }
        return $this->_accessToken['token'];
    }

    /**
     * 请求服务器access_token
     * @param string $grantType
     * @return array
     */
    protected function requestAccessToken($grantType = 'client_credential')
    {
        $result = $this->httpGet(static::WECHAT_ACCESS_TOKEN_URL, [
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'grant_type' => 'client_credential'
        ]);
        if (isset($result['access_token'])) {
            $result['expire'] = $result['expires_in'] + (int)YII_BEGIN_TIME;
            return $result;
        }
        return false;
    }

    protected $_jsApiTicket;

    /**
     * 设置jsapi_ticket
     * @param string @param array $data  ['ticket' => 'ticket 字符串', 'expire' => 'ticket 超时时间']
     */
    public function setJsApiTicket(array $data)
    {
        if (!isset($data['ticket'])) {
            throw new InvalidParamException('The wechat js api ticket must be set.');
        } elseif(!isset($data['expire'])) {
            throw new InvalidParamException('Wechat jsapi_ticket expire time must be set.');
        }
        $this->_jsApiTicket = [
            'ticket' => $data['ticket'],
            'expire' => $data['expire']
        ];
    }

    /**
     * 获取jsapi_ticket
     * 会自动判断超时时间然后重新获取新的ticket
     * (会智能缓存jsApiTicket)
     * @param bool $force
     * @return mixed
     * @throws HttpException
     */
    public function getJsApiTicket($force = false)
    {
        if ($this->_jsApiTicket === null || $this->_jsApiTicket['expire'] < YII_BEGIN_TIME || $force) {
            $result = !$force && $this->_jsApiTicket === null ? $this->getCache('js_api_ticket', false) : false;
            if ($result === false) {
                if (!($result = $this->requestJsApiTicket())) {
                    throw new HttpException(500, 'Fail to get jsapi_ticket from wechat server.');
                }
                $this->trigger(self::EVENT_AFTER_JS_API_TICKET_UPDATE, new Event(['data' => $result]));
                $this->setCache('js_api_ticket', $result, $result['expires_in']);
            }
            $this->setJsApiTicket($result);
        }
        return $this->_jsApiTicket['ticket'];
    }

    /**
     * 请求服务器jsapi_ticket
     * @param string $type
     * @return array
     */
    protected function requestJsApiTicket($type = 'jsapi')
    {
        $result = $this->httpGet(static::WECHAT_JS_API_TICKET_URL, [
            'access_token' => $this->getAccessToken(),
            'type' => $type
        ]);
        if (isset($result['ticket'])) {
            $result['expire'] = $result['expires_in'] + (int)YII_BEGIN_TIME;
            return $result;
        }
        return false;
    }

    /**
     * 生成js 必要的config
     * 只需在视图文件输出JS代码:
     *  wx.config(<?= json_encode($wehcat->jsApiConfig()) ?>); // 默认全权限
     *  wx.config(<?= json_encode($wehcat->jsApiConfig([ // 只允许使用分享到朋友圈功能
     *      'jsApiList' => [
     *          'onMenuShareTimeline'
     *      ]
     *  ])) ?>);
     * @param array $config
     * @param bool $debug
     * @return array
     * @throws HttpException
     */
    public function jsApiConfig(array $config = [], $debug = YII_DEBUG)
    {
        $data = [
            'jsapi_ticket' => $this->getJsApiTicket(),
            'noncestr' => Yii::$app->getSecurity()->generateRandomString(16),
            'timestamp' => (int)YII_BEGIN_TIME,
            'url' => explode('#', Yii::$app->getRequest()->getAbsoluteUrl())[0]
        ];
        return array_merge([
            'debug' => $debug,
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
        return Yii::$app->getCache()->set($this->getCacheKey($name), $value, $duration);
    }

    /**
     * 获取微信缓存数据
     * @param $name
     * @param null $defaultValue
     * @return mixed
     */
    protected function getCache($name, $defaultValue = null)
    {
        return Yii::$app->getCache()->get($this->getCacheKey($name), $defaultValue);
    }

    /**
     * 获取缓存的键值
     * @param $name
     * @return string
     */
    protected function getCacheKey($name)
    {
        return implode('_', [$this->cachePrefix, $this->appId, $name]);
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
        is_array($params) && $params = json_encode($params, JSON_UNESCAPED_UNICODE);
        return $this->parseHttpResult($url, $params, 'raw');
    }

    /**
     * 解析api回调请求
     * 会根据返回结果处理响应的回调结果.如 40001 access_token失效(会强制更新access_token后)重发, 保证请求的的有效
     * @param $url
     * @param $params
     * @param $method
     * @param bool $force 是否强制更新access_token 并再次请求
     * @return bool|mixed
     */
    protected function parseHttpResult($url, $params, $method, $force = true)
    {
        if (stripos($url, 'http://') === false && stripos($url, 'https://') === false) {
            $url = self::WECHAT_BASE_URL . $url;
        }
        $return = $this->http($url, $params, $method);
        $return = json_decode($return, true) ? : $return;
        if (isset($return['errcode']) && $return['errcode']) {
            $this->lastErrorInfo = $return;
            $log = [
                'class' => __METHOD__,
                'arguments' => func_get_args(),
                'result' => $return,
            ];
            switch ($return['errcode']) {
                case 40001: //access_token 失效,强制更新access_token, 并更新地址重新执行请求
                    if ($force) {
                        Yii::warning($log, 'wechat.sdk');
                        $url = preg_replace_callback("/access_token=([^&]*)/i", function(){
                            return 'access_token=' . $this->getAccessToken(true);
                        }, $url);
                        $return = $this->parseHttpResult($url, $params, $method, false); // 仅重新获取一次,否则容易死循环
                    }
                    break;
            }
            Yii::error($log, 'wechat.sdk');
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
            // curl_setopt($curl, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1); // 微信官方屏蔽了ssl2和ssl3, 启用更高级的ssl
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
