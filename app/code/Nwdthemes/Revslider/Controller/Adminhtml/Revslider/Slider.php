<?php

namespace Nwdthemes\Revslider\Controller\Adminhtml\Revslider;

use Nwdthemes\Revslider\Helper\Data;

class Slider extends \Nwdthemes\Revslider\Controller\Adminhtml\Revslider {

    protected $_resultPageFactory;

    /**
     * Constructor
     */

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;

        Data::setPage('revslider');
        Data::setView('slider');

        parent::__construct($context);
    }

    /**
     * Sliders Overview
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */

    public function execute() {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->setActiveMenu('Nwdthemes_Revslider::overview');
        $resultPage->getConfig()->getTitle()->prepend(__('Slider Settings'));
        $resultPage->addBreadcrumb(__('Nwdthemes'), __('Nwdthemes'));
        $resultPage->addBreadcrumb(__('Slider Revolution'), __('Slider Revolution'));
        $resultPage->addBreadcrumb(__('Slider Overview'), __('Slider Settings'));
        return $resultPage;
    }

}
