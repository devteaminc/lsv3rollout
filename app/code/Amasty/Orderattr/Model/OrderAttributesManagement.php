<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model;

use Magento\Customer\Model\Session as CustomerSession;

/**
 * Attribute Metadata data provider class
 */
class OrderAttributesManagement
{
    /**
     * @var \Amasty\Orderattr\Model\Order\Attribute\ValueFactory
     */
    protected $orderAttributesValueFactory;

    /**
     * @var AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    /**
     * @var ResourceModel\Order\Attribute\CollectionFactory
     */
    protected $orderAttributeCollectionFactory;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var Order\Attribute\ValueRepository
     */
    private $valueRepository;

    /**
     * OrderAttributesManagement constructor.
     * @param Order\Attribute\ValueFactory $orderAttributeValueFactory
     * @param AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param ResourceModel\Order\Attribute\CollectionFactory $orderAttributeCollectionFactory
     * @param CustomerSession $customerSession
     * @param Validator $validator
     */
    public function __construct(
        \Amasty\Orderattr\Model\Order\Attribute\ValueFactory $orderAttributeValueFactory,
        \Amasty\Orderattr\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\CollectionFactory $orderAttributeCollectionFactory,
        \Amasty\Orderattr\Model\Order\Attribute\ValueRepository $valueRepository,
        CustomerSession $customerSession,
        Validator $validator
    ) {
        $this->orderAttributesValueFactory = $orderAttributeValueFactory;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->orderAttributeCollectionFactory = $orderAttributeCollectionFactory;
        $this->validator = $validator;
        $this->customerSession = $customerSession;
        $this->valueRepository = $valueRepository;
    }

    /**
     * Save attribute values of order
     *
     * @param int | \Magento\Sales\Api\Data\OrderInterface $order
     * @param array $orderAttributesData
     */
    public function saveOrderAttributes($order, $orderAttributesData = null)
    {
        if ($order instanceof \Magento\Sales\Api\Data\OrderInterface) {
            $orderId = $order->getEntityId();
        } else {
            $orderId = $order;
            $order = null;
        }

        /**
         * @var \Amasty\Orderattr\Model\Order\Attribute\Value $orderAttributesModel
         */
        if (!$orderAttributesData && $order) {
            $orderAttributesModel = $this->loadOrderAttributeValuesByQuoteId($order->getQuoteId());
            $orderAttributesData = $orderAttributesModel->getData();
            //remove empty
            $orderAttributesData = array_diff($orderAttributesData, [null]);
        } else {
            $orderAttributesModel = $this->loadOrderAttributeValuesAndSetOrderId($orderId);
        }

        $attributes = $this->validateAttributes($order, $orderAttributesData);

        $valuesToInsert = array_merge($this->getDefaultValues($order), $orderAttributesData);
        if ($valuesToInsert) {
            foreach ($valuesToInsert as $key => $value) {
                if (strpos($key, '_output') !== false) {
                    continue;
                }
                $value = is_array($value) ? implode(',', $value) : $value;
                $orderAttributesModel->setData($key, $value);
                /* not default values prepare output */
                if (array_key_exists($key, $attributes)) {
                    $value =  $orderAttributesModel->prepareAttributeValue($attributes[$key]);
                    $orderAttributesModel->setData(
                        $key . '_output',
                        $value
                    );
                }
            }

            $orderAttributesModel->setOrderEntityId($orderId);
            if ($customerId = $this->customerSession->getCustomerId()) {
                $orderAttributesModel->setCustomerId($customerId);
            } elseif ($order && $order->getCustomerId()) {
                $orderAttributesModel->setCustomerId($order->getCustomerId());
            }
            $this->valueRepository->save($orderAttributesModel);
        }
    }

    /**
     * @param $order
     * @param $orderAttributesData
     * @return array
     */
    protected function validateAttributes(&$order, &$orderAttributesData)
    {
        $attributes = [];
        $attributesCollection = $this->orderAttributeCollectionFactory->create()
            ->addFieldToFilter(
                'attribute_code',
                ['in' => array_keys($orderAttributesData)]
            );

        /* attribute validations */
        if ($order && is_array($orderAttributesData)) {
            $orderAttributesData = $this->validator->validateAttributeRelations($orderAttributesData);
            $orderAttributesData = $this->validator->validateShippingMethods(
                $order,
                $orderAttributesData,
                $attributesCollection
            );
        }

        foreach ($attributesCollection as $attribute) {
            $attributes[$attribute->getAttributeCode()] = $attribute;
        }

        return $attributes;
    }

    /**
     * @param \Magento\Sales\Model\Order\Interceptor|null $order
     * @return array
     */
    protected function getDefaultValues($order)
    {
        $defaultValues = [];
        $orderAttributesWithDefaultValues = $this->attributeMetadataDataProvider
            ->loadAttributesWithDefaultValueCollection();

        if ($order !== null && $order->getIsVirtual() == true) {
            $orderAttributesWithDefaultValues->addFieldToFilter(
                'checkout_step',
                ['eq' => \Amasty\Orderattr\Model\Config\Source\CheckoutStep::PAYMENT_STEP]
            );
        }

        foreach ($orderAttributesWithDefaultValues as $orderAttribute) {
            /**
             * @var \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute $orderAttribute
             */
            $defaultValues[$orderAttribute->getAttributeCode()] = $orderAttribute->getDefaultValue();
        }

        return $defaultValues;
    }

    /**
     * @param \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute $orderAttribute
     *
     * @return string|int|bool|float
     */
    protected function getDefaultValueFromOrderAttribute($orderAttribute)
    {
        return $orderAttribute->getDefaultValue();
    }

    /**
     * @param int $orderId
     *
     * @return \Amasty\Orderattr\Model\Order\Attribute\Value
     */
    protected function loadOrderAttributeValuesAndSetOrderId($orderId)
    {
        /**
         * @var \Amasty\Orderattr\Model\Order\Attribute\Value $orderAttributes
         */
        $orderAttributes = $this->orderAttributesValueFactory->create();
        $orderAttributes->load($orderId, 'order_entity_id');
        if (!$orderAttributes->getOrderEntityId()) {
            $orderAttributes->setOrderEntityId($orderId);
        }

        return $orderAttributes;
    }

    /**
     * @param int $quoteId
     *
     * @return \Amasty\Orderattr\Model\Order\Attribute\Value
     */
    protected function loadOrderAttributeValuesByQuoteId($quoteId)
    {
        /**
         * @var \Amasty\Orderattr\Model\Order\Attribute\Value $orderAttributes
         */
        $orderAttributes = $this->orderAttributesValueFactory->create();
        $orderAttributes->load($quoteId, 'quote_id');
        if (!$orderAttributes->getQuoteId()) {
            $orderAttributes->setQuoteId($quoteId);
        }

        return $orderAttributes;
    }

    /**
     * @param int $quoteId
     * @param \Magento\Framework\Api\AttributeValue[] $orderAttributes
     *
     * @return \Amasty\Orderattr\Model\Order\Attribute\Value
     */
    public function saveAttributesFromQuote($quoteId, $orderAttributes)
    {
        if (!is_array($orderAttributes)) {
            $orderAttributes = [];
        }
        $existsOrderAttributes = $this->loadOrderAttributeValuesByQuoteId($quoteId);
        foreach ($orderAttributes as $orderAttributeCode => $orderAttribute) {
            $value = is_string($orderAttribute)? $orderAttribute: $orderAttribute->getValue();
            $existsOrderAttributes->setData($orderAttributeCode, $value);
        }

        $this->valueRepository->save($existsOrderAttributes);

        $orderAttributes = $existsOrderAttributes->getData();
        return $orderAttributes;
    }
}
