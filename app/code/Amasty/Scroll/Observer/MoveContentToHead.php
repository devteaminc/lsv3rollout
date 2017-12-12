<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


namespace Amasty\Scroll\Observer;

use Magento\Framework\Event\ObserverInterface;

class MoveContentToHead implements ObserverInterface {
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $response = $observer->getResponse();
        if($response) {
            $content = $response->getContent();
            $this->_moveLinkBlock($content);
            $observer->getResponse()->setContent($content);
        }
    }

    protected function _moveLinkBlock(&$content){
        $textInHead = '<!--amasty_scroll_head--!>';
        $textInBody = '<!--amasty_scroll_body';
        $textInBodyEnd = 'amasty_scroll_body-->';
        if (strpos($content, $textInHead) !== FALSE && strpos($content, $textInBody) !== FALSE) {
            $posStart  = strpos($content, $textInBody);
            $posEnd    = strpos($content, $textInBodyEnd);
            $links = substr($content,  $posStart, $posEnd - $posStart + 21);

            $content = str_replace($links, '', $content);
            $links = str_replace('<!--amasty_scroll_body', '', $links);
            $links = str_replace('amasty_scroll_body-->', '', $links);
            $content = str_replace($textInHead, $links, $content);
        }
        else{
            $content = str_replace($textInHead, '', $content);
        }

        return $content;
    }
}
