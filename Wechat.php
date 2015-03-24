<?php
namespace callmez\wechat\sdk;

use Yii;
use yii\base\Event;
use callmez\wechat\sdk\WechatBasic;
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
class Wechat extends WechatBasic
{
    /**
     * 创建菜单
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
        $result = $this->httpRaw(self::WECHAT_MENU_CREATE_URL . 'access_token=' . $this->getAccessToken(), [
            'button' => $buttons
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 获取菜单列表
     * @return bool
     */
    public function getMenuList()
    {
        $result = $this->httpRaw(self::WECHAT_MENU_GET_URL . 'access_token=' . $this->getAccessToken());
        return isset($result['menu']['button']) ? $result['menu']['button'] : false;
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
        $result = $this->httpRaw(self::WECHAT_TEMPLATE_MESSAGE_SEND_URL . 'access_token=' . $this->getAccessToken(), $data);
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
        $result = $this->httpRaw(self::WECHAT_CUSTOM_MESSAGE_SEND_URL . 'access_token=' . $this->getAccessToken(), $data);
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
        $result = $this->httpRaw(self::WECHAT_ARTICLES_UPLOAD_URL . 'access_token=' . $this->getAccessToken(), [
            'articles' => $articles
        ]);
        return isset($result['media_id']) ? $result : false;
    }

    /**
     * 群发消息
     * @param array $target 发送对象 groupid 或 openid
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
     * @param $type
     * @return array|bool
     * @throws HttpException
     */
    public function sendArticles(array $target, $content, $type)
    {
        if ($type !== 'video') {
            $content = [
                $type === 'text' ? 'content' : 'media_id' => $content
            ];
        }
        $result = $this->httpRaw(self::WECHAT_ARTICLES_SEND_URL . 'access_token=' . $this->getAccessToken(), $target + [
            $type => $content,
            'msgtype' => $type
        ]);
        return isset($result['msg_id']) ? $result : false;
    }

    /**
     * 取消群发消息
     * @param $messageId 消息ID
     */
    public function cancelSendArticles($messageId)
    {
        $result = $this->httpRaw(self::WECHAT_ARTICLES_SEND_CANCEL_URL . 'access_token=' . $this->getAccessToken(), [
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
        $result = $this->httpRaw(self::WECHAT_MEDIA_VIDEO_UPLOAD_URL . 'access_token=' . $this->getAccessToken(), [
            'media_id' => $mediaId,
            'title' => $title,
            'description' => $description
        ]);
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
        $result = $this->httpRaw(self::WECHAT_CREATE_GROUP_URL . 'access_token=' . $this->getAccessToken(), [
            'group' => [
                'name' => $name
            ]
        ]);
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
        $result = $this->httpRaw(self::WECHAT_GET_GROUP_ID_URL . 'access_token=' . $this->getAccessToken(), [
            'openid' => $openId
        ]);
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
        $result = $this->httpRaw(self::WECHAT_UPDATE_GROUP_NAME_URL . 'access_token=' . $this->getAccessToken(), [
            'group' => [
                'id' => $id,
                'name' => $name
            ]
        ]);
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
        $result = $this->httpRaw(self::WECHAT_MEMBER_GROUP_UPDATE_URL . 'access_token=' . $this->getAccessToken(), [
            'openid' => $openId,
            'to_groupid' => $toGroupId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 修改关注者备注
     * @param $openId 关注者openID
     * @param $remark 备注内容
     * @return bool
     * @throws HttpException
     */
    public function updateMemberRemark($openId, $remark)
    {
        $result = $this->httpRaw(self::WECHAT_MEMBER_REMARK_UPDATE_URL . 'access_token=' . $this->getAccessToken(), [
            'openid' => $openId,
            'remark' => $remark
        ]);
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
        $nextOpenId === null && $nextOpenId = ['next_openid' => $nextOpenId];
        $result = $this->httpGet(self::WECHAT_MEMBER_GET_URL . 'access_token=' . $this->getAccessToken(), $nextOpenId);
        return !isset($result['errcode']) ? $result : false;
    }

    /**
     * 获取关注者的客服聊天记录
     * @param string $openId 关注者的openID
     * @param int $startTime 查询开始时间，UNIX时间戳
     * @param int $endTime 查询结束时间，UNIX时间戳，每次查询不能跨日查询
     * @param int $pageIndex 每页大小，每页最多拉取1000条
     * @param int $pageSize 查询第几页，默认从1开始
     * @return array|bool
     * @throws HttpException
     */
    public function getCustomerServiceRecords($openId, $startTime, $endTime, $pageIndex = 1, $pageSize = 1000)
    {
        $result = $this->httpRaw(self::WECHAT_CUSTOMER_SERVICE_RECORD_GET_URL . 'access_token=' . $this->getAccessToken(), [
            'openid' => $openId,
            'starttime' => $startTime,
            'endtime' => $endTime,
            'pageindex' => $pageIndex,
            'pagesize' => $pageSize,
        ]);
        return isset($result['recordlist']) ? $result : false;
    }

    /**
     * 创建QR二维码
     * 调用的参数示例
     * $params = [
     *     // 二维码类型，QR_SCENE为临时,QR_LIMIT_SCENE为永久,QR_LIMIT_STR_SCENE为永久的字符串参数值,默认为QR_SCENE
     *     'action_name' => 'QR_SCENE',
     *     'action_info' => [
     *         'scene' => [
     *             // 场景值ID，临时二维码时为32位非0整型，永久二维码时最大值为100000（目前参数只支持1--100000）
     *             'scene_id' => $sceneId,
     *             // 场景值ID（字符串形式的ID），字符串类型，长度限制为1到64，仅永久二维码支持此字段
     *             'scene_str' => $sceneStr
     *         ]
     *     ]
     * ];
     * @param array $params
     * @return array|bool
     * @throws HttpException
     */
    public function createQrCode(array $params)
    {
        $params = array_merge([
            'expire_seconds' => 1800,
            'action_name' => 'QR_SCENE'
        ], $params);

        $result = $this->httpRaw(self::WECHAT_CREATE_QRCODE_URL . 'access_token=' . $this->getAccessToken(), $params);
        return isset($result['ticket']) ? $result : false;
    }

    /**
     * 获取二维码图片
     * @param $ticket 获取的二维码ticket，凭借此ticket可以在有效时间内换取二维码。
     * @return string
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
     * 网页授权获取用户信息:第一步
     * 通过此函数生成授权url
     * @param $redirectUrl 授权后重定向的回调链接地址，请使用urlencode对链接进行处理
     * @param string $state 重定向后会带上state参数，开发者可以填写a-zA-Z0-9的参数值
     * @param string $scope 应用授权作用域，snsapi_base （不弹出授权页面，直接跳转，只能获取用户openid），
     * snsapi_userinfo （弹出授权页面，可通过openid拿到昵称、性别、所在地。并且，即使在未关注的情况下，只要用户授权，也能获取其信息）
     * @return string
     */
    public function getOauth2AuthorizeUrl($redirectUrl, $state = 'authorize', $scope = 'snsapi_base')
    {
        return self::WECHAT_OAUTH2_AUTHORIZE_URL . http_build_query([
            'appid' => $this->appId,
            'redirect_uri' => $redirectUrl,
            'response_type' => 'code',
            'scope' => $scope,
            'state' => $state,
        ]) . '#wechat_redirect';
    }

    /**
     * 网页授权获取用户信息:第二步
     * 通过跳转到getOauth2AuthorizeUrl返回的授权code获取用户资料 (该函数和getAccessToken函数作用不同.请参考文档)
     * @param $code
     * @param string $grantType
     * @return array
     */
    public function getOauth2AccessToken($code, $grantType = 'authorization_code')
    {
        $result = $this->httpGet(self::WECHAT_OAUTH2_ACCESS_TOKEN_URL . http_build_query([
            'appid' => $this->appId,
            'secret' => $this->appSecret,
            'code' => $code,
            'grant_type' => $grantType
        ]));
        return isset($result['errmsg']) ? false : $result;
    }

    /**
     * 网页授权获取用户信息:第三步(非必须)
     * 由于access_token拥有较短的有效期，当access_token超时后，可以使用refresh_token进行刷新，refresh_token拥有较长的有效期（7天、30天、60天、90天），当refresh_token失效的后，需要用户重新授权。
     * @param $refreshToken
     * @param string $grantType
     * @return array|bool
     */
    public function refreshOauth2AccessToken($refreshToken, $grantType = 'refresh_token')
    {
        $result = $this->httpGet(self::WECHAT_OAUTH2_ACCESS_TOKEN_REFRESH_URL . http_build_query([
            'appid' => $this->appId,
            'grant_type' => $grantType,
            'refresh_token' => $refreshToken
        ]));
        return isset($result['errmsg']) ? false : $result;
    }

    /**
     * 检验授权凭证（access_token）是否有效
     * @param $accessToken
     * @param $openId
     * @return bool
     */
    public function checkOauth2AccessToken($accessToken, $openId)
    {
        $result = $this->httpGet(self::WECHAT_SNS_AUTH_URL . http_build_query([
            'access_token' => $accessToken,
            'openid' => $openId
        ]));
        return isset($result['errmsg']) && $result['errmsg'] == 'ok';
    }

    /**
     * 如果网页授权作用域为snsapi_userinfo，则此时开发者可以通过网页授权后的access_token和openid拉取用户信息了。
     * @param $openId
     * @param string $oauth2AccessToken
     * @param string $lang
     * @return array|bool
     */
    public function getSnsMemberInfo($openId, $oauth2AccessToken, $lang = 'zh_CN')
    {
        $result = $this->httpGet(self::WEHCAT_SNS_USER_INFO_URL . http_build_query([
            'access_token' => $oauth2AccessToken,
            'openid' => $openId,
            'lang' => $lang
        ]));
        return isset($result['errmsg']) ? false : $result;
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
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_CREATE_URL . 'access_token=' . $this->getAccessToken(), $data);
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
        $result = $this->httpRaw(self::WECHAT_SHOP_PRODUCT_UPDATE_URL . 'access_token=' . $this->getAccessToken(), $data);
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
        $result = $this->httpRaw(self::WECHAT_SHOP_STATUS_PRODUCT_UPDATE_URL . 'access_token=' . $this->getAccessToken(), [
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
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_ADD_URL . 'access_token=' . $this->getAccessToken(), [
            'group_detail' => [
                'group_name' => $groupName,
                'product_list' => $productIdList
            ]
        ]);
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
     * @param $groupId
     * @param $groupName
     * @return bool
     * @throws HttpException
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
     * @return bool
     * @throws HttpException
     */
    public function updateShopGroupProduct($groupId, array $productList)
    {
        $result = $this->httpRaw(self::WECHAT_SHOP_GROUP_PRODUCT_UPDATE_URL . 'access_token=' . $this->getAccessToken(), [
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
        $result = $this->httpRaw(self::WECHAT_SHOP_CATEGORY_SKU_LIST_GET_URL . 'access_token=' . $this->getAccessToken(), [
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
        $result = $this->httpRaw(self::WECHAT_SHOP_CATEGORY_PROPERTY_GET_URL . 'access_token=' . $this->getAccessToken(), [
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
        $result = $this->httpRaw(self::WECHAT_SHOP_DELIVERY_TEMPLATE_ADD_URL . 'access_token=' . $this->getAccessToken(), [
            'delivery_template' => $deliveryTemplate
        ]);
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
            'access_token=' . $this->getAccessToken(), [
                'template_id' => $templateId,
                'delivery_template' => $deliveryTemplate
            ]);
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
        $result = $this->httpRaw(self::WECHAT_SHOP_SHELF_ADD_URL . 'access_token=' . $this->getAccessToken(), $data);
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
        $result = $this->httpRaw(self::WECHAT_SHOP_SHELF_UPDATE_URL . 'access_token=' . $this->getAccessToken(), $data);
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
     * @param $orderId
     * @return bool
     * @throws HttpException
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
}
