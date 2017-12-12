<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
namespace Amasty\Label\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * File check
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_ioFile;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var array
     */
    protected $_labels;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var  \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var int
     */
    protected $_statusId = null;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    private $layoutFactory;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $_productTypeConfigurable;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_localeDate = $localeDate;
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->_ioFile = $ioFile;
        $this->layoutFactory = $layoutFactory;
        $this->_productTypeConfigurable = $catalogProductTypeConfigurable;
    }

    public function getModuleConfig($path)
    {
        return $this->_scopeConfig->getValue(
            'amasty_label/' . $path,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function renderProductLabel(\Magento\Catalog\Model\Product $product, $mode = 'category', $shouldMove = false)
    {
        $html = '';

        $applied = false;
        foreach ($this->_getCollection() as $label) {
            /** @var \Amasty\Label\Model\Labels  $label */
            if ($label->getIsSingle() && $applied) {
                continue;
            }

            $label->setShouldMove($shouldMove);
            $label->init($product, $mode);
            if ($label->isApplicable()) {
                $applied = true;
                $html .= $this->_generateHtml($label);
            } elseif ($label->getUseForParent()
                && ($product->getTypeId() == 'configurable' || $product->getTypeId() == 'grouped')
            ) {
                $usedProds = $this->getUsedProducts($product);
                foreach ($usedProds as $child) {
                    $label->init($child, $mode, $product);
                    if ($label->isApplicable()) {
                        $applied = true;
                        $html .= $this->_generateHtml($label);
                        break;
                    }
                }
            }
        }

        return $html;
    }

    /*
     * generate block with label configuration
     */
    protected function _generateHtml(\Amasty\Label\Model\Labels $label)
    {
        $layout = $this->layoutFactory->create();
        $block = $layout->createBlock(
            'Amasty\Label\Block\Label',
            'amasty.label',
            [ 'data' => ['label' => $label] ]
        );
        $html = $block->setLabel($label)->toHtml();

        return $html;
    }

    /*
     * return label collection
     */
    protected function _getCollection()
    {
        if ($this->_labels === null) {
            $id    = $this->_storeManager->getStore(true)->getId();
            $model = $this->_objectManager->create('Amasty\Label\Model\Labels');
            $this->_labels = $model->getCollection()
                ->addFieldToFilter('stores', ['like' => "%$id%"])
                ->addFieldToFilter('status', 1)
                ->setOrder('pos', 'asc');
        }

        return $this->_labels;
    }

    /*
     * return url with magento path
     * @return string
     */
    public function getImageUrl($name)
    {
        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'amasty/amlabel/'
        );

        if ($this->_ioFile->fileExists($path . $name) && $name != "") {
            $path = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
            return $path . 'amasty/amlabel/'. $name;
        }

        return '';
    }

    public function getImagePath($name)
    {
        $path = $this->_filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'amasty/amlabel/'
        );

        if ($this->_ioFile->fileExists($path . $name) && $name != "") {
            return $path . $name;
        }

        return '';
    }

    public function getUsedProducts(\Magento\Catalog\Model\Product $product)
    {
        if ($product->getTypeId() == 'configurable') {
            return $this->_productTypeConfigurable->getUsedProducts($product);
        } else { // product is grouped
            return $product->getTypeInstance(true)->getAssociatedProducts($product);
        }
    }
}
