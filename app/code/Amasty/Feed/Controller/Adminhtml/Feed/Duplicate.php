<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Adminhtml\Feed;

class Duplicate extends \Amasty\Feed\Controller\Adminhtml\Feed\AbstractMassAction
{
    protected function massAction($collection)
    {
        foreach($collection as $model)
        {
            $newModel = $this->feedCopier->copy($model);
            $this->messageManager->addSuccessMessage(__('Feed %1 was duplicated', $model->getName()));
        }
    }
}
