<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Shiprestriction
 */

/**
 * Copyright © 2015 Amasty. All rights reserved.
 */

namespace Amasty\Shiprestriction\Plugin;


class ProductAttributes
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    public function aroundGetProductAttributes(\Magento\Quote\Model\Quote\Config $subject, \Closure $closure)
    {
        $attributesTransfer = $closure();

        $attributes = $this->objectManager->create('Amasty\Shiprestriction\Model\ResourceModel\Rule')->getAttributes();

        //$result = array();
        foreach ($attributes as $code) {
            $attributesTransfer[] = $code;
            //$result[$code] = true;
        }
        //$attributesTransfer->addData($result);

        return $attributesTransfer;

    }
}
