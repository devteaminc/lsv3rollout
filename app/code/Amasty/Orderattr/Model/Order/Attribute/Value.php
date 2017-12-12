<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\Order\Attribute;

use Amasty\Orderattr\Model\AttributeMetadataDataProvider;
use Amasty\Orderattr\Model\OrderAttributeDataFactory;
use Amasty\Orderattr\Model\ResourceModel\Order\Attribute\Collection;
use Amasty\Orderattr\Api\Data\OrderAttributeValueInterface;

class Value extends \Magento\Framework\Model\AbstractModel implements OrderAttributeValueInterface
{
    /**
     * @var AttributeMetadataDataProvider
     */
    protected $attributeMetadataDataProvider;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;
    /**
     * @var OrderAttributeDataFactory
     */
    private $attributeDataFactory;

    /**
     * @var \Amasty\Orderattr\Helper\Config
     */
    private $config;

    /**
     * @var \Magento\Framework\Data\Form\Filter\DateFactory
     */
    private $dateFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Amasty\Orderattr\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Amasty\Orderattr\Model\OrderAttributeDataFactory $attributeDataFactory,
        \Amasty\Orderattr\Helper\Config $config,
        \Magento\Framework\Data\Form\Filter\DateFactory $dateFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
        $this->localeDate                    = $localeDate;
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        $this->state                         = $context->getAppState();
        $this->attributeDataFactory = $attributeDataFactory;
        $this->config = $config;
        $this->dateFactory = $dateFactory;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->_init('Amasty\Orderattr\Model\ResourceModel\Order\Attribute\Value');
    }

    /**
     * @param $orderId
     *
     * @return $this
     */
    public function loadByOrderId($orderId)
    {
        return $this->load($orderId, 'order_entity_id');
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getOrderAttributeValues($storeId)
    {
        $attributes = $this->attributeMetadataDataProvider->loadAttributesForEditFormByStoreId($storeId);

        return $this->doGetAttributeValues($attributes);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getOrderAttributeValuesForPrintHtml($storeId)
    {
        $attributes = $this->attributeMetadataDataProvider->loadAttributesForPrintHtml($storeId);

        return $this->doGetAttributeValues($attributes);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getOrderAttributeValuesForPdf($storeId)
    {
        $attributes = $this->attributeMetadataDataProvider->loadAttributesForPdf($storeId);

        return $this->doGetAttributeValues($attributes);
    }

    /**
     * @param int $storeId
     *
     * @return array
     */
    public function getOrderAttributeValuesForApi($storeId)
    {
        $attributes = $this->attributeMetadataDataProvider->loadAttributesForApi($storeId);

        return $this->doGetAttributeValues($attributes);
    }

    /**
     * @param Collection $attributesCollection
     *
     * @return array
     */
    protected function doGetAttributeValues($attributesCollection)
    {
        $list = [];
        if ($attributesCollection->getSize()) {
            foreach ($attributesCollection as $attribute) {
                $areaCode = $this->state->getAreaCode();
                if (!$attribute['is_visible_on_front'] && $areaCode == 'frontend'
                    || !$attribute['is_visible_on_back'] && $areaCode == 'adminhtml'
                ) {
                    continue;
                }

                $value = $this->prepareAttributeValue($attribute);
                if ($attribute->getStoreLabel() && $value !== null) {
                    $list[$attribute->getStoreLabel()] = str_replace('$', '\$', $value);
                }
            }
        }

        return $list;
    }

    /**
     * Return Attribute Value Output for Admin scope
     *
     * @param $attribute
     *
     * @return int|null|string
     */
    public function getAdminAttributeValue($attribute)
    {
        $oldStore = $attribute->getStoreId();
        $attribute->setStoreId(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
        $result = $this->prepareAttributeValue($attribute);
        $attribute->setStoreId($oldStore);

        return $result;
    }

    /**
     * Parse current order Data attribute value
     *
     * @param \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute $attribute
     *
     * @return string|int|null
     */
    public function prepareAttributeValue($attribute)
    {
        $value = $this->getData($attribute->getAttributeCode());
        if ($value === null) {
            return null;
        }
        switch ($attribute->getFrontendInput()) {
            case 'select':
            case 'boolean':
            case 'radios':
                $value = $attribute->getSource()->getOptionText($value);
                break;
            case 'date':
                if ($value) {
                    $value = $this->dateFactory->create(['format' => $this->config->getCheckoutDateFormat()])
                        ->outputFilter($value);
                }
                break;
            case 'datetime':
                if ($value) {
                    $value = new \DateTime($value, new \DateTimeZone($this->localeDate->getConfigTimezone()));
                    $value = $this->localeDate->formatDateTime($value);
                }
                break;
            case 'checkboxes':
                $value  = explode(',', $value);
                $labels = [];
                foreach ($value as $item) {
                    $labels[] = $attribute->getSource()->getOptionText($item);
                }
                $value = implode(', ', $labels);
                break;
        }

        return $value;
    }

    /**
     * @param int $customerId
     *
     * @return \Amasty\Orderattr\Model\Order\Attribute\Value
     */
    public function getLastValueByCustomerId($customerId)
    {
        $attributeValue = $this->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->setOrder('created_at')
            ->getFirstItem();

        return $attributeValue;
    }

    /**
     * @return array
     */
    public function getAttributeCodes()
    {
        if (!$this->hasData('attribute_codes')) {
            $codes = [];
            foreach ($this->getAttributes() as $attribute) {
                $codes[] = $attribute->getAttributeCode();
            }
            $this->setData('attribute_codes', $codes);
        }

        return $this->_getData('attribute_codes');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->_getData(self::ID);
    }

    /**
     * @param int $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->setData(self::ID, $id);
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderEntityId()
    {
        return $this->_getData(self::ORDER_ENTITY_ID);
    }

    /**
     * @param int $orderId
     *
     * @return $this
     */
    public function setOrderEntityId($orderId)
    {
        $this->setData(self::ORDER_ENTITY_ID, $orderId);
        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_getData(self::CUSTOMER_ID);
    }

    /**
     * @param int $customerId
     *
     * @return $this
     */
    public function setCustomerId($customerId)
    {
        $this->setData(self::CUSTOMER_ID, $customerId);
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * @param string $date
     *
     * @return $this
     */
    public function setCreatedAt($date)
    {
        $this->setData(self::CREATED_AT, $date);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes($storeId = null)
    {
        $key = 'attributes';
        if ($storeId !== null) {
            $key .= $storeId;
        }
        if (!$this->hasData($key)) {
            $attributeCollection = $this->attributeMetadataDataProvider->loadAttributesCollection();
            if ($storeId !== null) {
                $attributeCollection->addStoreFilter($storeId);
            }
            $attributes = [];
            /** @var \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute $attribute */
            foreach ($attributeCollection as $attribute) {
                $code = $attribute->getAttributeCode();
                $attributes[$code] = $this->attributeDataFactory->create()
                    ->setAttributeCode($code)
                    ->setLabel($attribute->getFrontendLabel())
                    ->setValue($this->getData($attribute->getAttributeCode()))
                    ->setValueOutput($this->prepareAttributeValue($attribute));
            }
            $this->setData($key, $attributes);
        }
        return $this->getData($key);
    }

    /**
     * {@inheritdoc}
     */
    public function setAttributes($attributes)
    {
        $this->setData('attributes', $attributes);
        return $this;
    }
}
