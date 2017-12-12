<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\ResourceModel\Eav;

use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;
use Magento\Customer\Model\Session as CustomerSession;

class Attribute extends \Magento\Eav\Model\Entity\Attribute implements
    \Amasty\Orderattr\Api\Data\OrderAttributeInterface, ScopedAttributeInterface
{
    const MODULE_NAME = 'Amasty_Orderattr';

    const ENTITY = 'amasty_orderattr_order_eav_attribute';

    /**
     * @var \Amasty\Orderattr\Model\Order\Attribute\Value
     */
    protected $attributeValue;
    /**
     * @var CustomerSession
     */
    private $customerSession;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Model\Entity\TypeFactory $eavTypeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Eav\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionDataFactory,
        \Magento\Framework\Reflection\DataObjectProcessor $dataObjectProcessor,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Catalog\Model\Product\ReservedAttributeList $reservedAttributeList,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        DateTimeFormatterInterface $dateTimeFormatter,
        CustomerSession $customerSession,
        \Amasty\Orderattr\Model\Order\Attribute\Value $attributeValue,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $extensionFactory,
            $customAttributeFactory, $eavConfig, $eavTypeFactory, $storeManager,
            $resourceHelper, $universalFactory, $optionDataFactory,
            $dataObjectProcessor, $dataObjectHelper, $localeDate,
            $reservedAttributeList, $localeResolver, $dateTimeFormatter,
            $resource, $resourceCollection, $data);
        $this->attributeValue = $attributeValue;
        $this->customerSession = $customerSession;
    }


    protected function _construct()
    {
        $this->_init('Amasty\Orderattr\Model\ResourceModel\Attribute');
    }

    /**
     * Event prefix
     *
     * @var string
     */
    protected $_eventPrefix = 'amasty_orderattr_order_eav_attribute';

    /**
     * Get default attribute source model
     *
     * @return string
     */
    public function _getDefaultSourceModel()
    {
        return 'Magento\Eav\Model\Entity\Attribute\Source\Table';
    }

    public function getIsFrontRequired()
    {
        return ($this->getIsRequired() || $this->getRequiredOnFrontOnly())
            ? 1 : 0;
    }

    public function getDefaultOrLastValue()
    {
        $value = ($this->getSaveSelected() && $this->getLastValue())
            ? $this->getLastValue()
            : $this->getDefaultValue();

        return $value;
    }

    /**
     * @return \Amasty\Orderattr\Model\Order\Attribute\Value
     */
    protected function getLastValueModel()
    {

        if ($this->getData('last_order_value')) {
            return $this->getData('last_order_value');
        }

        $customerId = $this->customerSession->getCustomerId();
        $attributeValue = $this->attributeValue->getLastValueByCustomerId(
            $customerId
        );

        $this->setData('last_order_value', $attributeValue);

        return $attributeValue;
    }

    /**
     * @return \Amasty\Orderattr\Model\Order\Attribute\Value
     */
    protected function getLastValue()
    {

        $attributeValue = $this->getLastValueModel();

        $lastValue = $attributeValue->getId()
            ? $attributeValue->getData($this->getAttributeCode())
            : null;

        return $lastValue;
    }

    /**
     * @param string $attributeCode
     *
     * @return \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute
     */
    public function loadOrderAttributeByCode($attributeCode)
    {
        return $this->_eavConfig->getAttribute(
            \Magento\Sales\Model\Order::ENTITY,
            $attributeCode
        );
    }

    /**
     * @return array
     */
    public function getOrderAttributesCodes()
    {
        return $this->_eavConfig->getEntityAttributeCodes(
            \Magento\Sales\Model\Order::ENTITY
        );

    }

    public function usesSource()
    {
        $input = $this->getFrontendInput();
        return parent::usesSource() || ($input === 'radios' || $input === 'checkboxes');
    }

    public function isOrderAttribute($attributeCode)
    {
        $orderAttributeCodes = $this->getOrderAttributesCodes();

        return in_array($attributeCode, $orderAttributeCodes);
    }

    public function isScopeGlobal()
    {
        return true;
    }

    public function isScopeWebsite()
    {
        return false;
    }

}
