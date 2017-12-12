<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Label
 */


namespace Amasty\Label\Controller\Adminhtml;

use Magento\Framework\Stdlib\DateTime\Filter\Date;

/**
 * Items controller
 */
abstract class Labels extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Backend\Model\View\Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Date filter instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     */
    protected $_dateFilter;

    /**
     * File system
     *
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * File check
     *
     * @var \Magento\Framework\Filesystem\Io\File
     */
    protected $_ioFile;

    /**
     * @var \Amasty\Label\Helper\Shape
     */
    protected $shapeHelper;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_appCache;

    /**
     * @var \Amasty\Label\Model\LabelsFactory
     */
    protected $labelsFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    protected $serializer;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        Date $dateFilter,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Io\File $ioFile,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\App\Cache\TypeListInterface $appCache,
        \Amasty\Label\Helper\Shape $shapeHelper,
        \Amasty\Label\Model\LabelsFactory $labelsFactory,
        \Psr\Log\LoggerInterface $logger,
        \Amasty\Base\Model\Serializer $serializer
    ) {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->_dateFilter = $dateFilter;
        $this->_filesystem = $filesystem;
        $this->_ioFile = $ioFile;
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->shapeHelper = $shapeHelper;
        $this->_appCache = $appCache;
        $this->labelsFactory = $labelsFactory;
        $this->logger = $logger;
        $this->serializer = $serializer;
    }

    /**
     * Initiate action
     *
     * @return this
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Amasty_Label::labels')->_addBreadcrumb(__('Product Labels'), __('Product Labels'));
        return $this;
    }

    /**
     * Determine if authorized to perform group actions.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Amasty_Label::label');
    }
}
