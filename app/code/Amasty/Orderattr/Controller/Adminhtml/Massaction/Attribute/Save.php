<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Controller\Adminhtml\Massaction\Attribute;

use Magento\Backend\App\Action;

/**
 * Class Save
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Save extends \Amasty\Orderattr\Controller\Adminhtml\Massaction\Attribute
{
    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\Value
     */
    private $attributeValueModel;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    private $redirectFactory;

    public function __construct(
        Action\Context $context,
        \Amasty\Orderattr\Model\ResourceModel\Order\Attribute\ValueFactory $attributeValueModel,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
    ) {
        parent::__construct($context);
        $this->attributeValueModel = $attributeValueModel;
        $this->redirectFactory = $redirectFactory;
    }

    public function execute()
    {
        $data = $this->getRequest()->getParams();
        if ($data && isset($data['attributes']) && isset($data['order-ids'])) {
            $attributes = $data['attributes'];
            $ids = $data['order-ids'];
            if ($attributes && $ids) {
                $ids = explode(',', $ids);
                try {
                    $this->attributeValueModel->create()->updateAttributes($attributes, $ids);
                    $this->messageManager->addSuccessMessage(__('Order attributes was successfully saved.'));
                    return $this->redirectFactory->create()->setPath('sales/order/index', ['_current' => true]);
                } catch (\Exception $ex) {
                    $this->messageManager->addErrorMessage($ex->getMessage());
                }
            }
        }

        $this->messageManager->addErrorMessage(
            __('Something went wrong while saving the item data.')
        );
        return $this->redirectFactory->create()->setPath('*/*/attribute_edit', ['_current' => true]);
    }
}
