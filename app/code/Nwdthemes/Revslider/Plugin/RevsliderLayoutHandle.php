<?php

namespace Nwdthemes\Revslider\Plugin;

use \Magento\Store\Model\ScopeInterface;
use \Nwdthemes\Revslider\Helper\Data;

class RevsliderLayoutHandle {

    protected $_optionFactory;
    protected $_pageRepository;
    protected $_request;
    protected $_status;

    public function __construct(
        \Magento\Cms\Api\PageRepositoryInterface $pageRepository,
        \Magento\Framework\App\Helper\Context $context,
        \Nwdthemes\Revslider\Model\OptionFactory $optionFactory
    ) {
        $this->_optionFactory = $optionFactory;
        $this->_pageRepository = $pageRepository;
        $this->_request = $context->getRequest();
        $this->_status = $context->getScopeConfig()->getValue('nwdthemes_revslider/revslider_configuration/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function afterAddPageLayoutHandles(\Magento\Framework\View\Result\Page $resultPage) {
        if ($this->_status) {

            $option = $this->_optionFactory
                ->create()
                ->getCollection()
                ->addFieldToFilter('handle', 'revslider-global-settings')
                ->setPageSize(1)
                ->getFirstItem()
                ->getData('option');
            $settings = unserialize($option);

            $includeSlider = ! isset($settings['includes_globally']) || $settings['includes_globally'] == 'on';
            if ( ! $includeSlider && isset($settings['pages_for_includes'])) {

                $pageHandles = [$resultPage->getDefaultLayoutHandle()];

                if ($pageId = $this->_request->getParam('page_id', $this->_request->getParam('id', false))) {
                    try {
                        if ($page = $this->_pageRepository->getById($pageId)) {
                            $pageHandles[] = $page->getIdentifier();
                        }
                    } catch (\Exception $e) {}
                }

                $arrHandles = explode(',', $settings['pages_for_includes']);
                foreach ($arrHandles as $handle) {
                    if (in_array(trim($handle), $pageHandles)) {
                        $includeSlider = true;
                        continue;
                    }
                }
            }

            if ($includeSlider) {
                $resultPage->addHandle('nwdthemes_revslider_default');
            }
        }
        return $resultPage;
    }

}