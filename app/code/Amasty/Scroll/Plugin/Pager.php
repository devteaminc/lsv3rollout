<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */
namespace Amasty\Scroll\Plugin;
use \Magento\Theme\Block\Html\Pager as NativePager;

class Pager
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    public function __construct(
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
    }

    public function afterToHtml(
        NativePager $subject,
        $result
    ) {
        $last = $subject->getLastPageNum();
        $current = $subject->getCurrentPage();


        $html = '';
        if ($current >= 2) {
            $prev = $current - 1;
            $url = $this->_generateUrl($prev);
            $html .= '<link rel="prev" href="' . $url . '" />';
        }

        if ($current < $last) {
            $next = $current + 1;
            $url = $this->_generateUrl($next);
            $html .= '<link rel="next" href="' . $url . '" />';
        }

        if($html) {
            $result .= '<!--amasty_scroll_body' . $html . 'amasty_scroll_body-->';
        }

        $result .= '<div id="am-page-count" style="display: none">' . $last . '</div>';

        return  $result;
    }

    public function getPreviousPageUrl()
    {
        $currentUrl = $this->_getCurrentUrl();
        $prevPageNum = $this->getCurrentPage() - 1;

        $result = preg_replace('/(\W)p=\d+/', '$1p=' . $prevPageNum, $currentUrl);

        return $result;
    }

    public function getNextPageUrl()
    {
        $currentUrl = $this->_getCurrentUrl();
        $nextPageNum = $this->getCurrentPage() + 1;

        $result = preg_replace('/(\W)p=\d+/', '$1p=' . $nextPageNum, $currentUrl, -1, $count);

        if (!$count) {
            $delimiter = (strpos($currentUrl, '?') === false) ? '?' : '&';
            $result.= $delimiter . 'p=' . $nextPageNum;
        }

        return $result;
    }

    protected function _generateUrl($page){
        $currentUrl = $this->urlInterface->getCurrentUrl();
        $result = preg_replace('/(\W)p=\d+/', '$1p=' . $page, $currentUrl, -1, $count);

        if (!$count) {
            $delimiter = (strpos($currentUrl, '?') === false) ? '?' : '&';
            $result .= $delimiter . 'p=' . $page;
        }

        return $result;
    }
}
