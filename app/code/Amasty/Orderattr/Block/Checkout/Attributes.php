<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Block\Checkout;

use Magento\Framework\View\Element\Template;

class Attributes extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Orderattr\Model\AttributeMetadataDataProvider
     */
    private $attributeMetadataDataProvider;

    /**
     * Attributes constructor.
     * @param Template\Context $context
     * @param \Amasty\Orderattr\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Amasty\Orderattr\Model\AttributeMetadataDataProvider $attributeMetadataDataProvider,
        array $data = []
    ) {
        $this->attributeMetadataDataProvider = $attributeMetadataDataProvider;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getAttributeCodes()
    {
        $collection = $this->attributeMetadataDataProvider->loadAttributesCollection();

        $codesArray = \Zend_Json::encode($collection->getColumnValues('attribute_code'));

        return $codesArray;
    }
}
