<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Controller\Adminhtml\Relation;

class Save extends \Amasty\Orderattr\Controller\Adminhtml\Relation
{
    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * Save constructor.
     *
     * @param \Magento\Backend\App\Action\Context                        $context
     * @param \Magento\Framework\Registry                                $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory                 $resultPageFactory
     * @param \Amasty\Orderattr\Api\RelationRepositoryInterface $relationRepository
     * @param \Amasty\Orderattr\Model\RelationFactory           $relationFactory
     * @param \Magento\Framework\App\Request\DataPersistorInterface      $dataPersistor
     * @param \Psr\Log\LoggerInterface                                   $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\Orderattr\Api\RelationRepositoryInterface $relationRepository,
        \Amasty\Orderattr\Model\RelationFactory $relationFactory,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory, $relationRepository, $relationFactory);
        $this->dataPersistor = $dataPersistor;
        $this->logger = $logger;
    }

    /**
     * Save Action
     */
    public function execute()
    {
        if ($data = $this->getRequest()->getPostValue()) {

            /** @var \Amasty\Orderattr\Model\Relation $model */
            $model = $this->relationFactory->create();
            $relationId = $this->getRequest()->getParam('relation_id');

            try {
                if ($relationId) {
                    $model = $this->relationRepository->get($relationId);
                }

                $model->loadPost($data);

                $this->relationRepository->save($model);

                $this->messageManager->addSuccessMessage(__('The Relation has been saved.'));
                $this->_getSession()->setPageData(false);
                $this->dataPersistor->clear('amasty_order_attributes_relation');

                if ($this->getRequest()->getParam('back')) {
                    $this->redirectToEdit($model->getId());
                    return;
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->redirectToEdit($relationId, $data);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('The Relation has not been saved. Please review the error log for the details.')
                );
                $this->logger->critical($e);
                $this->redirectToEdit($relationId, $data);
                return;
            }
        }
        $this->_redirect('amorderattr/*/');
    }

    /**
     * Redirect to Edit or New and save $data to session
     *
     * @param null|int   $relationId
     * @param null|array $data
     */
    private function redirectToEdit($relationId = null, $data = null)
    {
        if ($data) {
            $this->_getSession()->setPageData($data);
            $this->dataPersistor->set('amasty_order_attributes_relation', $data);
        }
        if ($relationId) {
            $this->_redirect('amorderattr/*/edit', ['relation_id' => $relationId]);
            return;
        }
        $this->_redirect('amorderattr/*/new');
    }
}
