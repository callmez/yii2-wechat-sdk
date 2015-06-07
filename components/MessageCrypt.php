<?php
namespace callmez\wechat\sdk\components;

require_once 'messageCrypt/wxBizMsgCrypt.php';

/**
 * 消息加密类
 * @package callmez\wechat\sdk
 */
class MessageCrypt extends \WXBizMsgCrypt
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