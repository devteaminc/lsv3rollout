<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Plugin\Order;


class AfterOrderLoad
{

    /**
     * @var \Magento\Catalog\Api\Data\ProductExtensionFactory
     */
    protected $productExtensionFactory;

    /**
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Catalog\Api\Data\ProductExtensionFactory $productExtensionFactory
     */
    public function __construct(
        \Magento\Catalog\Api\Data\ProductExtensionFactory $productExtensionFactory
    ) {
        $this->productExtensionFactory = $productExtensionFactory;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function afterLoad(\Magento\Sales\Model\Order $order)
    {
        $productExtension = $order->getExtensionAttributes();
        if ($productExtension === null) {
            $productExtension = $this->productExtensionFactory->create();
        }
        $order->setExtensionAttributes($productExtension);
        return $order;
    }
}
