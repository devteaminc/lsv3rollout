<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */

/**
 * Copyright © 2016 Amasty. All rights reserved.
 */
namespace Amasty\Scroll\Plugin\Ajax;

class InitAjaxSearchPage extends AjaxAbstract
{
    public function aroundRenderLayout(
        \Magento\Framework\App\View $subject,
        \Closure $proceed,
        $output = ''
    )  {
        $page = $subject->getPage();
        if(!$page instanceof \Magento\Framework\View\Result\Page){
            return $proceed($output);
        }

        if(!$this->isAjax() || $this->request->getRouteName() !== 'catalogsearch')
        {
            return $proceed($output);
        }

        $responseData = $this->getAjaxResponseData($page);

        $this->response->setBody(json_encode($responseData))->sendResponse();
    }
}
