<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
namespace Amasty\Label\Observer;
use Magento\Framework\Event\ObserverInterface;
class viewBlockAbstractToHtmlBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_request;

    /**
     * @var \Amasty\Label\Helper\Data
     */
    public $_helper;

    protected $_logger;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        \Amasty\Label\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $registry;
        $this->_productFactory = $productFactory;
        $this->scopeConfig = $scopeConfig;
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_helper = $helper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $block = $observer->getBlock();
        if ($block instanceof \Magento\Catalog\Block\Adminhtml\Product\Edit\Tabs) {
            $block->addTabAfter(
                'amasty-label',
                [
                    'label' => __('Product Labels'),
                    'title' => __('Product Labels'),
                    'content' => $block->getChildHtml('amasty-label'),

                ],
                'front'
            );
        }
    }
}