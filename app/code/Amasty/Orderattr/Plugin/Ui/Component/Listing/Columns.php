<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Plugin\Ui\Component\Listing;

/**
 * Class Columns
 */
class Columns
{
    /**
     * Default columns max order
     */
    const DEFAULT_COLUMNS_MAX_ORDER = 100;

    /** @var \Amasty\Orderattr\Ui\Component\Listing\Attribute\RepositoryInterface */
    protected $attributeRepository;

    /**
     * @var array
     */
    protected $filterMap
        = [
            'default'     => 'text',
            'select'      => 'select',
            'boolean'     => 'select',
            'multiselect' => 'select',
            'radios'      => 'select',
            'checkboxes'  => 'select',
            'date'        => 'dateRange',
            'datetime'    => 'dateRange',
        ];

    /**
     * @var \Amasty\Orderattr\Ui\Component\ColumnFactory
     */
    protected $columnFactory;

    /**
     * @var \Amasty\Orderattr\Helper\Config
     */
    protected $config;

    /**
     * Columns Plugin constructor.
     *
     * @param \Amasty\Orderattr\Ui\Component\ColumnFactory                         $columnFactory
     * @param \Amasty\Orderattr\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository
     * @param \Amasty\Orderattr\Helper\Config                                      $config
     */
    public function __construct(
        \Amasty\Orderattr\Ui\Component\ColumnFactory $columnFactory,
        \Amasty\Orderattr\Ui\Component\Listing\Attribute\RepositoryInterface $attributeRepository,
        \Amasty\Orderattr\Helper\Config $config
    ) {
        $this->columnFactory = $columnFactory;
        $this->attributeRepository = $attributeRepository;
        $this->config = $config;
    }

    /**
     * @param \Magento\Ui\Component\Listing\Columns $subject
     * @param \Closure                              $proceed
     */
    public function aroundPrepare(\Magento\Ui\Component\Listing\Columns $subject, \Closure $proceed)
    {
        if ($this->allowedInlineEdit($subject)) {
            $this->addInlineEdit($subject);
        }
        if ($this->allowToAddAttributes($subject)) {
            $this->prepareOrderAttributes($subject);
        }

        $proceed();
    }

    /**
     * @param \Magento\Ui\Component\Listing\Columns $columnsComponent
     *
     * @return bool
     */
    private function allowedInlineEdit($columnsComponent)
    {
        return $columnsComponent->getName() == 'sales_order_columns';
    }

    /**
     * @param \Magento\Ui\Component\Listing\Columns $columnsComponent
     */
    private function addInlineEdit($columnsComponent)
    {
        $config = $columnsComponent->getData('config');
        /* some times xsi:type="boolean" recognizing as string, should be as boolean */
        /** @see app/code/Amasty/Orderattr/view/adminhtml/ui_component/sales_order_grid.xml */
        $config['childDefaults']['fieldAction'] = [
            'provider' => 'sales_order_grid.sales_order_grid.sales_order_columns_editor',
            'target' => 'startEdit',
            'params' => [
                0 => '${ $.$data.rowIndex }',
                1 => true
            ]
        ];

        $columnsComponent->setData('config', $config);
    }

    /**
     * @param \Magento\Ui\Component\Listing\Columns $columnsComponent
     */
    protected function prepareOrderAttributes($columnsComponent)
    {
        $columnSortOrder = self::DEFAULT_COLUMNS_MAX_ORDER;
        $components = $columnsComponent->getChildComponents();
        foreach ($this->attributeRepository->getList() as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            if (!isset($components[$attributeCode])) {
                $config = [
                    'sortOrder' => ++$columnSortOrder,
                    'add_field' => false,
                    'visible' => true,
                    'filter' => $this->getFilterType($attribute->getFrontendInput()),
                    'editor' => $this->getFilterType($attribute->getFrontendInput()),
                ];
                $column = $this->columnFactory->create($attribute, $columnsComponent->getContext(), $config);
                $column->prepare();
                $columnsComponent->addComponent($attributeCode, $column);
            }
        }
    }

    /**
     * Retrieve filter type by $frontendInput
     *
     * @param string $frontendInput
     * @return string
     */
    protected function getFilterType($frontendInput)
    {
        return isset($this->filterMap[$frontendInput]) ? $this->filterMap[$frontendInput] : $this->filterMap['default'];
    }

    /**
     * @param \Magento\Ui\Component\Listing\Columns $subject
     * @param \Closure                              $proceed
     * @param array                                 $dataSource
     *
     */
    public function aroundPrepareDataSource(
        \Magento\Ui\Component\Listing\Columns $subject,
        \Closure $proceed,
        array $dataSource
    ) {
        if ($this->allowToAddAttributes($subject)) {
            $dataSource = $this->prepareDataForOrderAttributes($dataSource);
        }

        return $proceed($dataSource);
    }

    /**
     * @param array $dataSource
     *
     * @return array
     */
    protected function prepareDataForOrderAttributes(array $dataSource)
    {
        $orderAttributesList = $this->attributeRepository->getList();
        foreach ($orderAttributesList as $attribute) {
            /**
             * @var \Magento\Eav\Model\Entity\Attribute $attribute
             */
            if ($attribute->getFrontendInput() == 'checkboxes') {
                $dataSource = $this->prepareDataForCheckboxes(
                    $dataSource,
                    $attribute->getAttributeCode()
                );
            }
        }

        return $dataSource;
    }

    /**
     * @param array $dataSource
     * @param       $attributeCode
     *
     * @return array
     */
    protected function prepareDataForCheckboxes(array $dataSource, $attributeCode)
    {
        $items = &$dataSource['data']['items'];
        foreach ($items as &$item) {
            if (array_key_exists($attributeCode, $item) && is_string($item[$attributeCode])) {
                $item[$attributeCode] = explode(',', $item[$attributeCode]);
            }
        }

        return $dataSource;
    }

    /**
     * Is can add order Attribute Columns to Component
     *
     * @param \Magento\Ui\Component\Listing\Columns $columnsComponent
     *
     * @return bool
     */
    public function allowToAddAttributes($columnsComponent)
    {
        $componentName = $columnsComponent->getName();
        $isOrder       = $componentName == 'sales_order_columns';
        $isInvoice     = $componentName == 'sales_order_invoice_columns' && $this->config->getShowInvoiceGrid();
        $isShipment    = $componentName == 'sales_order_shipment_columns' && $this->config->getShowShipmentGrid();

        return $isOrder || $isInvoice || $isShipment;
    }
}
