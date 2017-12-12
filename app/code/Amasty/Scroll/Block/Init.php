<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */

/**
 * Copyright В© 2016 Amasty. All rights reserved.
 */
namespace Amasty\Scroll\Block;

class Init extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amasty\Scroll\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $urlEncoder;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = [],
        \Amasty\Scroll\Helper\Data $helper,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Url\EncoderInterface $urlEncoder
    )
    {
        parent::__construct($context, $data);

        $this->_helper = $helper;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_jsonEncoder = $jsonEncoder;
        $this->urlEncoder = $urlEncoder;
        $this->request = $request;
    }

    public function getHelper()
    {
        return $this->_helper;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_helper->isEnabled();
    }

    /**
     * @return string
     */
    public function getProductsBlockSelector()
    {
        $originSelectors = $this->_helper->getModuleConfig('advanced/product_container_group');

        //compatibility with Amasty_PromoBanners
        if ($originSelectors === null) {
            $selectors = ['.products.wrapper'];
        } else {
            $selectors = explode(',', $originSelectors);
        }
        foreach ($selectors as &$selector) {
            $selector = rtrim($selector);
            $selector .= ':not(.amasty-banners)';
        }

        return implode(',', $selectors);
    }

    /**
     * @return string
     */
    public function getConfig()
    {
        $currentPage = $this->request->getParam('p');
        if (!$currentPage) {
            $currentPage = 1;
        }

        $params = [
            'actionMode'                => $this->_helper->getModuleConfig('general/loading'),
            'product_container'         => $this->getProductsBlockSelector(),
            'loadingImage'              => $this->getViewFileUrl($this->_helper->getModuleConfig('general/loading_icon')),
            'pageNumbers'               => $this->_helper->getModuleConfig('general/page_numbers'),
            'pageNumberContent'         => __('PAGE #'),
            'loadNextStyle'             => $this->_helper->getModuleConfig('button/styles'),
            'loadingafterTextButton'    => $this->_helper->getModuleConfig('button/label_after'),
            'loadingbeforeTextButton'   => $this->_helper->getModuleConfig('button/label_before'),
            'progressbar'               => $this->_helper->getModuleConfig('info'),
            'progressbarText'           => __('PAGE %1 of %2'),
            'current_page'              => $currentPage,
        ];

        return $this->_jsonEncoder->encode($params);
    }
}
