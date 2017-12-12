<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\Config;

class SourceFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $instanceName
     *
     * @return \Amasty\Orderattr\Model\Config\Source\CustomerGroup
     */
    public function createCustomerGroupSource($instanceName = '\\Amasty\\Orderattr\\Model\\Config\\Source\\CustomerGroup')
    {
        return $this->create($instanceName);
    }

    /**
     * @param string $instanceName
     *
     * @return \Amasty\Orderattr\Model\Config\Source\CheckoutStep
     */
    public function createCheckoutStepSource($instanceName = '\\Amasty\\Orderattr\\Model\\Config\\Source\\CheckoutStep')
    {
        return $this->create($instanceName);
    }

    protected function create($instanceName)
    {
        return $this->objectManager->create($instanceName);
    }

    /**
     * @param string $instanceName
     *
     * @return \Amasty\Orderattr\Model\Config\Source\CustomerGroup
     */
    public function getCustomerGroupSource($instanceName = '\\Amasty\\Orderattr\\Model\\Config\\Source\\CustomerGroup')
    {
        return $this->get($instanceName);
    }

    /**
     * @param string $instanceName
     *
     * @return \Amasty\Orderattr\Model\Config\Source\CheckoutStep
     */
    public function getCheckoutStepSource($instanceName = '\\Amasty\\Orderattr\\Model\\Config\\Source\\CheckoutStep')
    {
        return $this->get($instanceName);
    }

    protected function get($instanceName)
    {
        return $this->objectManager->get($instanceName);
    }

}