<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


namespace Amasty\Scroll\Plugin\Ajax;

use Magento\MediaStorage\Model\File\Storage\Response;
use Magento\Framework\Url\Helper\Data as UrlHelper;

class AjaxAbstract
{
    /**
     * @var \Amasty\Scroll\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * AjaxAbstract constructor.
     * @param \Amasty\Scroll\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param UrlHelper $urlHelper
     * @param Response $response
     */
    public function __construct(
        \Amasty\Scroll\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\UrlInterface $url,
        UrlHelper $urlHelper,
        Response $response
    ) {
        $this->helper = $helper;
        $this->resultRawFactory = $resultRawFactory;
        $this->request = $request;
        $this->response = $response;
        $this->urlHelper = $urlHelper;
        $this->url = $url;
    }

    /**
     * @param
     *
     * @return bool
     */
    protected function isAjax()
    {
        $isAjax = $this->request->isAjax();
        $isScroll = $this->request->getParam('is_scroll');
        $result = $this->helper->isEnabled() && $isAjax && $isScroll;

        return $result;
    }

    /**
     * @param \Magento\Framework\View\Result\Page $page
     *
     * @return array
     */
    protected function getAjaxResponseData(\Magento\Framework\View\Result\Page $page)
    {
        $products = $page->getLayout()->getBlock('category.products');
        if (!$products) {
            $products = $page->getLayout()->getBlock('search_result_list');
        }

        $currentPage = $this->request->getParam('p');
        if (!$currentPage) {
            $currentPage = 1;
        }

        //fix bug with multiple adding to cart
        $html = $products->toHtml();
        $search = '[data-role=tocart-form]';
        $replace = ".amscroll-pages[amscroll-page='" . $currentPage . "'] " . $search;
        $html = str_replace($search, $replace, $html);

        $this->replaceUencFromHtml($html);

        $responseData = [
            'categoryProducts' => $html,
            'currentPage' => $currentPage
        ];

        return $responseData;
    }

    /**
     * replace uenc for correct redirect
     * @param $html
     */
    private function replaceUencFromHtml(&$html)
    {
        $currentUenc = $this->urlHelper->getEncodedUrl();
        $refererUrl = $this->url->getCurrentUrl();
        $refererUrl = $this->urlHelper->removeRequestParam($refererUrl, 'is_scroll');

        $newUenc = $this->urlHelper->getEncodedUrl($refererUrl);
        $html = str_replace($currentUenc, $newUenc, $html);
    }
}
