<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Plugin\Order\Api;

/**
 * For API. Extension Attributes Save Get
 */
class OrderRepository
{
    /**
     * @var \Magento\Sales\Api\Data\OrderInterface|null
     */
    protected $currentOrder;

    /**
     * @var \Magento\Sales\Api\Data\OrderExtensionFactory
     */
    private $orderExtensionFactory;

    /**
     * @var \Amasty\Orderattr\Model\AttributeMetadataDataProvider
     */
    private $attributeProvider;

    /**
     * @var \Amasty\Orderattr\Model\Order\Attribute\ValueFactory
     */
    private $valueFactory;

    /**
     * @var \Amasty\Orderattr\Model\OrderAttributesManagement
     */
    private $orderAttributesManagement;

    /**
     * @var \Amasty\Orderattr\Model\OrderAttributeDataFactory
     */
    private $dataFactory;

    /**
     * OrderRepository constructor.
     *
     * @param \Magento\Sales\Api\Data\OrderExtensionFactory         $orderExtensionFactory
     * @param \Amasty\Orderattr\Model\Order\Attribute\ValueFactory  $valueFactory
     * @param \Amasty\Orderattr\Model\AttributeMetadataDataProvider $attributeProvider
     * @param \Amasty\Orderattr\Model\OrderAttributesManagement     $attributesManagement
     * @param \Amasty\Orderattr\Model\OrderAttributeDataFactory     $dataFactory
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderExtensionFactory $orderExtensionFactory,
        \Amasty\Orderattr\Model\Order\Attribute\ValueFactory $valueFactory,
        \Amasty\Orderattr\Model\AttributeMetadataDataProvider $attributeProvider,
        \Amasty\Orderattr\Model\OrderAttributesManagement $attributesManagement,
        \Amasty\Orderattr\Model\OrderAttributeDataFactory $dataFactory
    ) {
        $this->orderExtensionFactory     = $orderExtensionFactory;
        $this->attributeProvider         = $attributeProvider;
        $this->valueFactory              = $valueFactory;
        $this->orderAttributesManagement = $attributesManagement;
        $this->dataFactory               = $dataFactory;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface      $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterGet(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->addOrderAttributes($order);

        return $order;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param                                             $searchResult
     *
     * @return mixed
     */
    public function afterGetList(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        $searchResult
    ) {
        foreach ($searchResult->getItems() as $order) {
            $this->addOrderAttributes($order);
        }

        return $searchResult;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface      $order
     */
    public function beforeSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        $this->currentOrder = $order;
    }

    /**
     * @param \Magento\Sales\Api\OrderRepositoryInterface $subject
     * @param \Magento\Sales\Api\Data\OrderInterface      $order
     *
     * @return \Magento\Sales\Api\Data\OrderInterface
     */
    public function afterSave(
        \Magento\Sales\Api\OrderRepositoryInterface $subject,
        \Magento\Sales\Api\Data\OrderInterface $order
    ) {
        if ($this->currentOrder !== null) {
            $extensionAttributes = $this->currentOrder->getExtensionAttributes();
            if ($extensionAttributes && $extensionAttributes->getAmastyOrderAttributes()) {
                $attributes      = $extensionAttributes->getAmastyOrderAttributes();
                $attributesValue = [];
                if (is_array($attributes)) {
                    foreach ($attributes as $attribute) {
                        if (isset($attribute['attribute_code']) && isset($attribute['value'])) {
                            $attributesValue[$attribute['attribute_code']] = $attribute['value'];
                        }
                    }
                    if (count($attributesValue)) {
                        $this->orderAttributesManagement
                            ->saveOrderAttributes($order, $attributesValue);
                    }
                }
            }
            $this->currentOrder = null;
        }

        return $order;
    }

    /**
     * Add amasty order attributes data to Extension Attributes
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     */
    private function addOrderAttributes(\Magento\Sales\Api\Data\OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (empty($extensionAttributes)) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        } elseif ($extensionAttributes->getAmastyOrderAttributes() !== null) {
            return;
        }
        $customAttributes = [];
        /** @var \Amasty\Orderattr\Model\Order\Attribute\Value $attributeModel */
        $attributeModel = $this->valueFactory->create();
        $attributeModel->loadByOrderId($order->getId());
        $attributes = $this->attributeProvider->loadAttributesForApi($order->getStoreId());

        /** @var \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute $attribute */
        foreach ($attributes as $attribute) {
            if ($attributeModel->prepareAttributeValue($attribute)) {
                /** @var \Amasty\Orderattr\Model\OrderAttributeData $data */
                $data                                             = $this->dataFactory->create();
                $customAttributes[$attribute->getAttributeCode()] = $data->addData(
                    [
                        'attribute_code'     => $attribute->getAttributeCode(),
                        'label'              => $attribute->getFrontendLabel(),
                        'value'              => $attributeModel->getData($attribute->getAttributeCode()),
                        'value_output'       => $attributeModel->prepareAttributeValue($attribute),
                        'value_output_admin' => $attributeModel->getAdminAttributeValue($attribute)
                    ]
                );
            }
        }
        $extensionAttributes->setAmastyOrderAttributes($customAttributes);
        $order->setExtensionAttributes($extensionAttributes);
    }
}
