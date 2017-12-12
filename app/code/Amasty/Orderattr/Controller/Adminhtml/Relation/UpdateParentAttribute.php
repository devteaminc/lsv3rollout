<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */


namespace Amasty\Orderattr\Controller\Adminhtml\Relation;

class UpdateParentAttribute extends \Amasty\Orderattr\Controller\Adminhtml\Relation
{
    /**
     * @var \Amasty\Orderattr\Model\Relation\AttributeOptionsProvider
     */
    private $optionsProvider;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Amasty\Orderattr\Model\Relation\DependentAttributeProvider
     */
    private $attributeProvider;

    /**
     * UpdateParentAttribute constructor.
     *
     * @param \Magento\Backend\App\Action\Context                                  $context
     * @param \Magento\Framework\Registry                                          $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory                           $resultPageFactory
     * @param \Amasty\Orderattr\Api\RelationRepositoryInterface           $relationRepository
     * @param \Amasty\Orderattr\Model\RelationFactory                     $relationFactory
     * @param \Magento\Framework\Json\EncoderInterface                             $jsonEncoder
     * @param \Amasty\Orderattr\Model\Relation\AttributeOptionsProvider   $optionsProvider
     * @param \Amasty\Orderattr\Model\Relation\DependentAttributeProvider $attributeProvider
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Amasty\Orderattr\Api\RelationRepositoryInterface $relationRepository,
        \Amasty\Orderattr\Model\RelationFactory $relationFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Amasty\Orderattr\Model\Relation\AttributeOptionsProvider $optionsProvider,
        \Amasty\Orderattr\Model\Relation\DependentAttributeProvider $attributeProvider
    ) {
        parent::__construct($context, $coreRegistry, $resultPageFactory, $relationRepository, $relationFactory);
        $this->jsonEncoder = $jsonEncoder;
        $this->optionsProvider = $optionsProvider;
        $this->attributeProvider = $attributeProvider;
    }

    /**
     * For Ajax
     *
     * @return \Magento\Framework\App\Response\Http with JSON
     */
    public function execute()
    {
        $attributeId = $this->getRequest()->getParam('attribute_id');
        $response = [
            'error' => __('The attribute_id is not defined. Please try to reload the page. ')
        ];
        if ($attributeId) {
            try {
                $attributeOptions = $this->optionsProvider->setParentAttributeId($attributeId)->toOptionArray();
                $dependentAttributes = $this->attributeProvider->setParentAttributeId($attributeId)->toOptionArray();
                $response = [
                    'attribute_options' => $attributeOptions,
                    'dependent_attributes' => $dependentAttributes,
                    'error' => 0
                ];
            } catch (\Exception $exception) {
                $response = [
                    'error' => $exception->getMessage()
                ];
            }
        }

        return $this->getResponse()->representJson(
            $this->jsonEncoder->encode($response)
        );
    }
}
