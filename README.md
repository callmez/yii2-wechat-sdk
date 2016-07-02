yii2-wechat-sdk
===============

感谢选择 yii2-wechat-sdk 扩展, 该扩展是基于[Yii2](https://github.com/yiisoft/yii2)框架基础开发,借助Yii2的强劲特性可以定制开发属于您自己的微信公众号

[![Latest Stable Version](https://poser.pugx.org/callmez/yii2-wechat-sdk/v/stable.svg)](https://packagist.org/packages/callmez/yii2-wechat-sdk) [![Total Downloads](https://poser.pugx.org/callmez/yii2-wechat-sdk/downloads.svg)](https://packagist.org/packages/callmez/yii2-wechat-sdk) [![Latest Unstable Version](https://poser.pugx.org/callmez/yii2-wechat-sdk/v/unstable.svg)](https://packagist.org/packages/callmez/yii2-wechat-sdk) [![License](https://poser.pugx.org/callmez/yii2-wechat-sdk/license.svg)](https://packagist.org/packages/callmez/yii2-wechat-sdk)

注意
---
  ** 新版本正在重构中, 直到1.0正式版发布前.你依然可以继续使用功能 **
  
  目前有3个主要文件可以使用
  - `Wechat.php` 旧版微信公众号操作类(在新版[1.0]发布后会删除)
  - `MpWechat.php` 新版微信公众号操作类(更标准,更完善), 如果您是新使用该库请按照文档说明替换旧版`Wechat.php`使用
  - `QyWechat.php` 新版微信企业号操作类(为了更加全面的微信功能操作, 将在[1.1版本中完善发布]), 强势集成企业号功能

环境条件
--------
- >= php5.4
- Yii2

安装
----

您可以使用composer来安装, 添加下列代码在您的``composer.json``文件中并执行``composer update``操作

```json
{
    "require": {
       "callmez/yii2-wechat-sdk": "dev-master"
    }
}
```

使用示例
--------
在使用前,请先参考微信公众平台的[开发文档](http://mp.weixin.qq.com/wiki/index.php?title=%E9%A6%96%E9%A1%B5)

Wechat定义方式
```php
//在config/web.php配置文件中定义component配置信息
'components' => [
  .....
  'wechat' => [
    'class' => 'callmez\wechat\sdk\Wechat',
    'appId' => '微信公众平台中的appid',
    'appSecret' => '微信公众平台中的secret',
    'token' => '微信服务器对接您的服务器验证token'
  ]
  ....
]
// 全局公众号sdk使用
$wechat = Yii::$app->wechat; 


//多公众号使用方式
$wechat = Yii::createObject([
    'class' => 'callmez\wechat\sdk\Wechat',
    'appId' => '微信公众平台中的appid',
    'appSecret' => '微信公众平台中的secret',
    'token' => '微信服务器对接您的服务器验证token'
]);
```

Wechat方法使用(部分示例)
```php
//获取access_token
var_dump($wechat->accessToken);

//获取二维码ticket
$qrcode = $wechat->createQrCode([
    'expire_seconds' => 604800,
    'action_name' => 'QR_SCENE',
    'action_info' => ['scene' => ['scene_id' => rand(1, 999999999)]]
]);
var_dump($qrcode);

//获取二维码
$imgRawData = $wechat->getQrCodeUrl($qrcode['ticket']);

//获取群组列表
var_dump($wechat->getGroups());


//创建分组
$group = $wechat->createGroup('测试分组');
echo $group ? '测试分组创建成功' : '测试分组创建失败';

//修改分组
echo $wechat->updateGroupName($group['id'], '修改测试分组') ? '修改测试分组成功' : '测试分组创建失败';


//根据关注者ID获取关注者所在分组ID
$openID = 'oiNHQjh-8k4DrQgY5H7xofx_ayfQ'; //此处应填写公众号关注者的唯一openId

//修改关注者所在分组
echo $wechat->updateMemberGroup($openID, 1) ? '修改关注者分组成功' : '修改关注者分组失败';

//获取关注者所在分组
echo $wechat->getGroupId($openID);

//修改关注者备注
echo $wechat->updateMemberRemark($openID, '测试更改备注') ? '关注者备注修改成功' : '关注者备注修改失败';

//获取关注者基本信息
var_dump($wechat->getMemberInfo($openID));

//获取关注者列表
var_dump($wechat->getMemberList());

//获取关注者的客服聊天记录, 
var_dump($wechat->getCustomerServiceRecords($openID, mktime(0, 0, 0, 1, 1, date('Y')), time())); //获取今年的聊天数据(可能获取不到数据)

//上传媒体文件
$filePath = '图片绝对路径'; //目前微信只开发jpg上传
var_dump($media = $wechat->uploadMedia(realpath($filePath), 'image'));

//下载媒体文件
echo $wechat->getMedia($media['media_id']) ? 'media下载成功' : 'media下载失败';

```

反馈或贡献代码
--------------
您可以在[这里](https://github.com/callmez/yii2-wechat-sdk/issues)给我提出在使用中碰到的问题或Bug.
我会在第一时间回复您并修复.

您也可以 发送邮件callme-z@qq.com给我并且说明您的问题.

如果你有更好代码实现,请fork项目并发起您的pull request.我会及时处理. 感谢!
