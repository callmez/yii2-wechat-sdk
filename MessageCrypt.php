<?php
namespace callmez\wechat\sdk;

require_once 'lib/wxBizMsgCrypt.php';

/**
 * 消息加密类
 * @package callmez\wechat\sdk
 */
class MessageCrypt extends WXBizMsgCrypt
{
    /**
     * Returns the fully qualified name of this class.
     * @return string the fully qualified name of this class.
     */
    public static function className()
    {
        return get_called_class();
    }
}