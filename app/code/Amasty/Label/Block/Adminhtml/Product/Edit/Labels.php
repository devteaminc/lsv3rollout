<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */
namespace Amasty\Label\Block\Adminhtml\Product\Edit;
use Magento\Framework\App\Filesystem\DirectoryList;

class Labels  extends \Magento\Catalog\Block\Adminhtml\Form
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_formFactory = $formFactory;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $product = $this->_coreRegistry->registry('product');
        $form->setDataObject($product);

        $fieldset = $form->addFieldset(
            'group-fields-amasty-labels',
            ['class' => 'user-defined', 'legend' => __('Product Labels'), 'collapsable' => 1]
        );

        $element = $fieldset->addField(
            'amlabels',
            'hidden',
            [
                'name' => 'amlabels',
                'label' => __('Product Labels'),
                'value' => 1,
            ]
        );

       $collection = $this->_objectManager
                        ->create('Amasty\Label\Model\Labels')
                        ->getCollection()
                        ->addFieldToFilter('include_type', array('neq'=>1));

        if (0 < $collection->getSize()) {
            foreach ($collection as $label) {
                $name = 'amlabel_' . $label->getId();
                $element = $fieldset->addField($name, 'checkbox', array(
                    'label'              => $label->getName(),
                    'name'               => $name,
                    'value'              => 1,
                    'after_element_html' => $this->getImageHtml($label->getProdImg()),
                ));
                if ($product->hasData($name)) {
                    $element->setIsChecked($product->getData($name));
                }
                elseif ($product->getData('sku')) {
                    $skus = explode(',', $label->getIncludeSku());
                    $element->setIsChecked(in_array($product->getData('sku'), $skus));
                }
            }
        }

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * @return image html from image name
     */
    protected function getImageHtml($img)
    {
        $html = '';
        if ($img) {
            $html .= '<p style="margin-top: 5px">';
            $html .= '<img style="max-width:300px" src="' . 'amlabel/' . $img . '" />';
            $html .= '</p>';
        }
        return $html;
    }

}