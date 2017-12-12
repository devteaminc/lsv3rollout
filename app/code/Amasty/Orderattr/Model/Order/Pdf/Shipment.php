<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Orderattr
 */

namespace Amasty\Orderattr\Model\Order\Pdf;


class Shipment extends \Magento\Sales\Model\Order\Pdf\Shipment
{

    /**
     * @var \Amasty\Orderattr\Model\Order\Attribute\Value
     */
    protected $attributeValue;

    /**
     * @var \Amasty\Orderattr\Helper\Config
     */
    protected $config;

    /**
     * Shipment constructor.
     *
     * @param \Magento\Payment\Helper\Data                         $paymentData
     * @param \Magento\Framework\Stdlib\StringUtils                $string
     * @param \Magento\Framework\App\Config\ScopeConfigInterface   $scopeConfig
     * @param \Magento\Framework\Filesystem                        $filesystem
     * @param \Magento\Sales\Model\Order\Pdf\Config                $pdfConfig
     * @param \Magento\Sales\Model\Order\Pdf\Total\Factory         $pdfTotalFactory
     * @param \Magento\Sales\Model\Order\Pdf\ItemsFactory          $pdfItemsFactory
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Translate\Inline\StateInterface   $inlineTranslation
     * @param \Magento\Sales\Model\Order\Address\Renderer          $addressRenderer
     * @param \Magento\Store\Model\StoreManagerInterface           $storeManager
     * @param \Magento\Framework\Locale\ResolverInterface          $localeResolver
     * @param \Amasty\Orderattr\Model\Order\Attribute\Value        $attributeValue
     * @param \Amasty\Orderattr\Helper\Config                      $config
     * @param array                                                $data
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Order\Pdf\Config $pdfConfig,
        \Magento\Sales\Model\Order\Pdf\Total\Factory $pdfTotalFactory,
        \Magento\Sales\Model\Order\Pdf\ItemsFactory $pdfItemsFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        \Amasty\Orderattr\Model\Order\Attribute\Value $attributeValue,
        \Amasty\Orderattr\Helper\Config $config,
        array $data = []
    ) {
        $this->attributeValue = $attributeValue;
        parent::__construct(
            $paymentData,
            $string,
            $scopeConfig,
            $filesystem,
            $pdfConfig,
            $pdfTotalFactory,
            $pdfItemsFactory,
            $localeDate,
            $inlineTranslation,
            $addressRenderer,
            $storeManager,
            $localeResolver,
            $data
        );
        $this->config = $config;
    }

    /**
     * Return PDF document
     *
     * @param array|Collection $invoices
     * @return \Zend_Pdf
     */
    public function getPdf($invoices = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                $this->_localeResolver->emulate($invoice->getStoreId());
                $this->_storeManager->setCurrentStore($invoice->getStoreId());
            }
            $page = $this->newPage();
            $order = $invoice->getOrder();
            /* Add image */
            $this->insertLogo($page, $invoice->getStore());
            /* Add address */
            $this->insertAddress($page, $invoice->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $order,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page, __('Packing Slip # ') . $invoice->getIncrementId());

            $orderAttributesList = $this->getOrderAttributeList(
                $order->getId(), $order->getStoreId()
            );
            if ($this->config->getPdfShipment() && count($orderAttributesList) > 0) {
                $this->drawAttributesHeader($page);
                $this->drawAttributesList($page, $orderAttributesList);
            }

            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($invoice->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                 $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @return void
     */
    protected function drawAttributesHeader(\Zend_Pdf_Page $page)
    {
        $this->_setFontRegular($page, 10);
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
        $page->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(25, $this->y, 570, $this->y - 15);
        $this->y -= 10;
        $page->setFillColor(new \Zend_Pdf_Color_RGB(0, 0, 0));

        //columns headers
        $lines[0][] = ['text' => __('Order Attributes'), 'feed' => 35];

        $lineBlock = ['lines' => $lines, 'height' => 5];

        $this->drawLineBlocks($page, [$lineBlock], ['table_header' => true]);
        $page->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->y -= 20;
    }

    /**
     * @param \Zend_Pdf_Page $page
     * @param array $list
     */
    protected function drawAttributesList($page, $list)
    {

        foreach ($list as $label => $value) {
            if (is_array($value)) {
                $page->drawText($label . ': ', 35, $this->y, 'UTF-8');
                foreach ($value as $str) {
                    $page->drawText($str, 120, $this->y, 'UTF-8');
                    $this->y -= 10;
                }
            } else {
                $page->drawText($label . ': ', 35, $this->y, 'UTF-8');
                $page->drawText($value, 120, $this->y, 'UTF-8');
                $this->y -= 15;
            }
        }

        $this->y -= 10;
    }

    /**
     * @param string $orderId
     * @param string $storeId
     * @return array
     */
    protected function getOrderAttributeList($orderId, $storeId)
    {
        if ($this->getData('attributes_list' . $orderId . '_' . $storeId)) {
            return $this->getData('attributes_list' . $orderId . '_' . $storeId);
        }
        $this->attributeValue->loadByOrderId($orderId);
        $attributesList = $this->attributeValue->getOrderAttributeValuesForPdf($storeId);
        $this->setData('attributes_list' . $orderId . '_' . $storeId, $attributesList);
        return $attributesList;
    }

}
