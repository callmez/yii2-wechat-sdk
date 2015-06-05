<?php
namespace callmez\wechat\sdk\mp;

use callmez\wechat\sdk\components\WechatComponent;

/**
 * 微信小店组件
 * @package callmez\wechat\sdk\components\mp
 */
class Merchant extends WechatComponent
{

    /**
     * 增加商品
     */
    const WECHAT_PRODUCT_ADD_PREFIX  = '/merchant/create';
    /**
     * 增加商品
     * @param array $data 商品详细信息
     * @return array|bool
     * @throws \yii\web\HttpException
     */
    public function addProduct(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PRODUCT_ADD_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['product_id'] : false;
    }

    /**
     * 商品删除
     */
    const WECHAT_PRODUCT_DELETE_PREFIX = '/merchant/del';
    /**
     * 删除商品
     * @param $productId
     * @return bool
     */
    public function deleteProduct($productId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PRODUCT_DELETE_PREFIX, [
            'product_id' => $productId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 商品修改
     */
    const WECHAT_PRODUCT_UPDATE_PREFIX = '/merchant/update';
    /**
     * 商品修改
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateProduct(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PRODUCT_UPDATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 查询商品信息
     */
    const WECHAT_PRODUCT_GET_PREFIX = '/merchant/get';
    /**
     * 查询商品信息
     * @param $productId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getProduct($productId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PRODUCT_GET_PREFIX, [
            'product_id' => $productId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['product_info'] : false;
    }

    /**
     * 获取指定状态的商品
     */
    const WECHAT_PRODUCT_STATUS_GET_PREFIX = '/merchant/getbystatus';
    /**
     * 获取指定状态的商品
     * @param $status 商品状态(0-全部, 1-上架, 2-下架)
     * @return array|bool
     */
    public function getProductByStatus($status)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PRODUCT_STATUS_GET_PREFIX, [
            'status' => $status
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['product_info'] : false;
    }

    /**
     * 更改商品状态(上下架)
     */
    const WECHAT_PRODUCT_STATUS_UPDATE_PREFIX = '/merchant/modproductstatus';
    /**
     * 更改商品状态(上下架)
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateProductStatus(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PRODUCT_STATUS_UPDATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取指定分类的所有子分类
     */
    const WECHAT_CATEGORY_SUB_CATEGROIES_GET_PREFIX = '/merchant/category/getsub';
    /**
     * 获取指定分类的所有子分类
     * @param $cateId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getCategorySubCategories($cateId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_CATEGORY_SUB_CATEGROIES_GET_PREFIX, [
            'cate_id' => $cateId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['cate_list'] : false;
    }

    /**
     * 获取指定子分类的所有SKU
     */
    const WECHAT_CATEGORY_SKU_LIST_PREFIX = '/merchant/category/getsku';
    /**
     * 获取指定分类的单品
     * @param $cateId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getCategorySkuList($cateId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_CATEGORY_SKU_LIST_PREFIX, [
            'cate_id' => $cateId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['sku_table'] : false;
    }

    /**
     * 获取指定分类的所有属性
     */
    const WECHAT_CATEGORY_PROPERTY_GET_PREFIX = '/merchant/category/getproperty';
    /**
     * 获取指定分类的所有属性
     * @param $cateId 分类ID
     * @return array|bool
     */
    public function getCategoryProperty($cateId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_CATEGORY_PROPERTY_GET_PREFIX, [
            'cate_id' => $cateId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['properties'] : false;
    }

    /**
     * 商品增加库存
     */
    const WECHAT_PRODUCT_STOCK_ADD_PREFIX = '/merchant/stock/add';
    /**
     * 增加库存
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addProductStock(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PRODUCT_STOCK_ADD_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 减少库存
     */
    const WECHAT_PRODUCT_STOCK_REDUCE_URL = '/merchant/stock/reduce';
    /**
     * 减少库存
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function reduceProductStock(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_PRODUCT_STOCK_REDUCE_URL, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 增加邮费模板
     */
    const WECHAT_DELIVERY_TEMPLATE_ADD_PREFIX = '/merchant/express/add';
    /**
     * @param array $deliveryTemplate
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addDeliveryTemplate(array $deliveryTemplate)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_DELIVERY_TEMPLATE_ADD_PREFIX, [
            'delivery_template' => $deliveryTemplate
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['template_id'] : false;
    }

    /**
     * 删除邮费模板
     */
    const WECHAT_DELIVERY_TEMPLATE_DELETE_PREFIX = '/merchant/express/del';
    /**
     * 删除邮费模板
     * @param int $templateId 邮费模板ID
     * @return bool
     */
    public function deleteDeliverTemplate($templateId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_DELIVERY_TEMPLATE_DELETE_PREFIX, [
            'template_id' => $templateId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改邮费模板
     */
    const WECHAT_DELIVERY_TEMPLATE_UPDATE_PREFIX = '/merchant/express/update';
    /**
     * 修改邮费模板
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateDeliverTemplate(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_DELIVERY_TEMPLATE_UPDATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取指定ID的邮费模板
     */
    const WECHAT_DELIVERY_TEMPLATE_ID_GET_PREFIX = '/merchant/express/getbyid';
    /**
     * 获取指定ID的邮费模板
     * @param $templateId
     * @return bool
     */
    public function getDeliverTemplate($templateId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_DELIVERY_TEMPLATE_ID_GET_PREFIX, [
            'template_id' => $templateId
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['template_info'] : false;
    }

    /**
     * 获取所有邮费模板
     */
    const WECHAT_DELIVERY_TEMPLATE_LIST_GET_PREFIX = '/merchant/express/getall';
    /**
     * 获取所有邮费模板
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getDeliverTemplateList()
    {
        $result = $this->wechat->httpGet(self::WECHAT_DELIVERY_TEMPLATE_LIST_GET_PREFIX, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['templates_info'] : false;
    }

    /**
     * 增加分组
     */
    const WECHAT_GROUP_ADD_PREFIX = '/merchant/group/add';
    /**
     * 增加分组
     * @param array $groupDetail
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addGroup(array $groupDetail)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_GROUP_ADD_PREFIX, [
            'group_detail' => $groupDetail
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['group_id'] : false;
    }

    /**
     * 删除分组
     */
    const WECHAT_GROUP_DELETE_PREFIX = '/merchant/group/del';
    /**
     * 删除店铺分组
     * @param $groupId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function deleteGroup($groupId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_GROUP_DELETE_PREFIX, [
            'group_id' => $groupId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改分组属性
     */
    const WECHAT_GROUP_UPDATE_PREFIX = '/merchant/group/propertymod';
    /**
     * 修改分组属性
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateGroup(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_GROUP_UPDATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改分组商品
     */
    const WECHAT_GROUP_PRODUCT_UPDATE_PREFIX = '/merchant/group/productmod';
    /**
     * 修改分组商品
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateGroupProduct(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_GROUP_PRODUCT_UPDATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取所有分组
     */
    const WECHAT_GROUP_LIST_PREFIX = '/merchant/group/getall';
    /**
     * 获取所有分组
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getGroupList()
    {
        $result = $this->wechat->httpGet(self::WECHAT_GROUP_LIST_PREFIX, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['groups_detail'] : false;
    }

    /**
     * 根据分组ID获取分组信息
     */
    const WECHAT_GROUP_ID_GET_PREFIX = '/merchant/group/getbyid';
    /**
     * 根据分组ID获取分组信息
     * @param $groupId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getGroup($groupId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_GROUP_ID_GET_PREFIX, [
            'group_id' => $groupId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['groups_detail'] : false;
    }

    /**
     * 增加货架
     */
    const WECHAT_SHELF_ADD_PREFIX = '/merchant/shelf/add';
    /**
     * 增加货架
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function addShelf(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_SHELF_ADD_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['shelf_id'] : false;
    }

    /**
     * 删除货架
     */
    const WECHAT_SHELF_DELETE_PREFIX = '/merchant/shelf/del';
    /**
     * 删除货架
     * @param $shelfId
     * @return bool
     */
    public function deleteShelf($shelfId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_SHELF_DELETE_PREFIX, [
            'shelf_id' => $shelfId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 修改货架
     */
    const WECHAT_SHELF_UPDATE_PREFIX = '/merchant/shelf/mod';
    /**
     * 修改货架
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function updateShelf(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_SHELF_UPDATE_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 获取所有货架
     */
    const WECHAT_SHELF_LIST_PREFIX = '/merchant/shelf/getall';
    /**
     * 获取所有货架
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function getShelfList()
    {
        $result = $this->wechat->httpGet(self::WECHAT_SHELF_LIST_PREFIX, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['shelves'] : false;
    }

    /**
     * 根据货架ID获取货架信息
     */
    const WECHAT_SHELF_ID_GET_PREFIX = '/merchant/shelf/getbyid';
    /**
     * 根据货架ID获取货架信息
     * @param $shelfId
     * @return array|bool
     */
    public function getShelf($shelfId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_SHELF_ID_GET_PREFIX, [
            'shelf_id' => $shelfId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['shelf_info'] : false;
    }

    /**
     * 根据订单ID获取订单详情
     */
    const WECHAT_ORDER_GET_PREFIX = '/merchant/order/getbyid';
    /**
     * 根据订单ID获取订单详情
     * @param $orderId
     * @return bool
     * @throws HttpException
     */
    public function getOrder($orderId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_ORDER_GET_PREFIX, [
            'order_id' => $orderId
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['order'] : false;
    }

    /**
     * 根据订单状态/创建时间获取订单详情
     */
    const WECHAT_ORDER_FILTER_GET_PREFIX = '/merchant/order/getbyfilter';
    /**
     * 根据订单状态/创建时间获取订单详情
     * @param array $data
     * @return bool
     */
    public function getOrderByFilter(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_ORDER_FILTER_GET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['order_list'] : false;
    }

    /**
     * 设置订单发货信息
     */
    const WECHAT_ORDER_DELIVERY_SET_PREFIX = '/merchant/order/setdelivery';
    /**
     * 设置订单发货信息
     * 注: 物流公司ID
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
     * @param array $data
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function setOrderDelivery(array $data)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_ORDER_DELIVERY_SET_PREFIX, $data, [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['order_list'] : false;
    }

    /**
     * 关闭订单
     */
    const WECHAT_ORDER_CLOSE_PREFIX = '/merchant/order/close';
    /**
     * 关闭订单
     * @param $orderId
     * @return bool
     * @throws \yii\web\HttpException
     */
    public function closeOrder($orderId)
    {
        $result = $this->wechat->httpRaw(self::WECHAT_ORDER_CLOSE_PREFIX, [
            'order_id' => $orderId,
        ], [
            'access_token' => $this->wechat->getAccessToken()
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success';
    }

    /**
     * 上传图片(小店接口)
     */
    const WECHAT_IMAGE_UPLOAD_PREFIX = '/merchant/common/upload_img';
    /**
     * 上传图片(小店接口)
     * @param $filePath 文件完整路径
     * @param null $fileName 文件名 如不填写.则使用文件路径里的名称
     * @return bool
     */
    public function uploadImage($filePath, $fileName = null)
    {
        $fileName === null && $fileName = pathinfo($filePath, PATHINFO_BASENAME);
        $result = $this->wechat->httpRaw(self::WECHAT_IMAGE_UPLOAD_PREFIX, [
            'media' => $this->wechat->uploadFile($filePath)
        ], [
            'access_token' => $this->wechat->getAccessToken(),
            'filename' => $fileName
        ]);
        return isset($result['errmsg']) && $result['errmsg'] == 'success' ? $result['image_url'] : false;
    }

}