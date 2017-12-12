<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Feed
 */


namespace Amasty\Feed\Controller\Feed;

use Magento\Framework\App\Filesystem\DirectoryList;

class Download extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;
    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /** @var \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory */
    protected $collectionFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory $collectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Amasty\Feed\Model\ResourceModel\Feed\CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->filesystem = $filesystem;
        $this->collectionFactory = $collectionFactory;
    }

    public function execute()
    {
        $filename = $this->getRequest()->getParam('filename');

        $collection = $this->collectionFactory->create()
            ->addFieldToFilter('filename', $filename)
            ->addFieldToFilter('is_template', ['neq' => 1]);

        foreach ($collection as $model) {

            if ($model->getEntityId()) {
                $output = $model->getOutput();

                $this->fileFactory->create(
                    $output['filename'],
                    $output['content'],
                    DirectoryList::VAR_DIR
                );
            }

            break;
        }

        $this->_redirect($this->_redirect->getRefererUrl());
    }
}
