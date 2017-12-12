<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */


namespace Amasty\Scroll\Plugin\Ajax;

use Magento\Catalog\Controller\Category\View ;

class InitAjaxResponse extends AjaxAbstract
{
    /**
     * @param $controller
     * @param null $page
     * @return \Magento\Framework\Controller\Result\Raw|null
     */
    public function afterExecute($controller, $page = null)
    {
        if (!$this->isAjax() || !$page instanceof \Magento\Framework\View\Result\Page) {
            return $page;
        }

        $responseData = $this->getAjaxResponseData($page);
        $response = $this->prepareResponse($responseData);
        return $response;
    }

    /**
     * @param array $data
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    protected function prepareResponse(array $data)
    {
        $response = $this->resultRawFactory->create();
        $response->setHeader('Content-Type', 'application/json');
        $response->setContents(json_encode($data));

        return $response;
    }
}
