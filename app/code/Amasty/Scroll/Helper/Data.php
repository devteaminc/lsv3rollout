<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_Scroll
 */
namespace Amasty\Scroll\Helper;

use Magento\Store\Model\ScopeInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $context->getScopeConfig();
    }

    public function getModuleConfig($path)
    {
        return $this->_scopeConfig->getValue('amasty_scroll/' . $path, ScopeInterface::SCOPE_STORE);
    }

    public function isEnabled()
    {
        if ($this->getModuleConfig('general/loading') == 'none') {
            return false;
        }

        return true;
    }
}
