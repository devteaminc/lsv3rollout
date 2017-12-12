<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Block\Adminhtml\Order\Attribute\Edit\Tab;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;

class Conditions extends \Magento\Backend\Block\Widget\Form\Generic
{

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $shippingConfig;

    /**
     * @var \Amasty\Orderattr\Helper\Config
     */
    protected $configHelper;

    /**
     * @var Attribute
     */
    protected $attribute;

    /**
     * @var \Amasty\Orderattr\Model\ResourceModel\ShippingMethod\CollectionFactory
     */
    protected $shippingCollectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Amasty\Orderattr\Helper\Config $configHelper,
        \Amasty\Orderattr\Model\ResourceModel\ShippingMethod\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->shippingConfig = $shippingConfig;
        $this->configHelper = $configHelper;
        $this->shippingCollectionFactory = $collectionFactory;
    }

    public function getActiveShippingMethods()
    {
        $methods = [];

        $activeCarriers = $this->shippingConfig->getActiveCarriers();

        foreach ($activeCarriers as $carrierCode => $carrierModel) {
            $options = [];
            if ($carrierMethods = $carrierModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $carrierCode . '_' . $methodCode;
                    $options[] = ['value' => $code, 'label' => $method];

                }
            }
            $carrierTitle = $this->configHelper->getCarrierConfigValue($carrierCode);
            $methods[] = ['value' => $options, 'label' => $carrierTitle];
        }
        return $methods;
    }
    
    protected function _prepareForm()
    {
        /**
         * @var \Amasty\Orderattr\Model\ResourceModel\Eav\Attribute $model
         */
        $model = $this->getAttributeObject();

        /**
         *@var \Amasty\Orderattr\Model\ResourceModel\ShippingMethod\Collection $currentShippingMethods
         */
        $currentShippingCollection = $this->shippingCollectionFactory->create();
        $currentShippingMethods = $currentShippingCollection
            ->getShippingMethodsByAttributeId($model->getId());
        $formData = [];
        
        foreach($currentShippingMethods as $method){
            $formData[] = $method->getShippingMethod();
        }

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post'
                ]
            ]
        );

        $fieldset = $form->addFieldset('conditions_fieldset',
            ['legend' => __('Manage Conditions')]
        );

        $methods = $this->getActiveShippingMethods();
        $fieldset->addField('shipping_methods', 'multiselect', [
            'name'   => 'shipping_methods[]',
            'label'  => __('Shipping Methods'),
            'title'  => __('Shipping Methods'),
            'values' => $methods,
        ]);
        
        $form->addValues([
            'shipping_methods' => $formData
        ]);
        
        $this->setForm($form);
        return parent::_prepareForm();
    }

    /**
     * Return attribute object
     *
     * @return Attribute
     */
    public function getAttributeObject()
    {
        if (null === $this->attribute) {
            return $this->_coreRegistry->registry('entity_attribute');
        }
        return $this->attribute;
    }
}
