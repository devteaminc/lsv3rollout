<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2017 Amasty (https://www.amasty.com)
 * @package Amasty_InvisibleCaptcha
 */


namespace Amasty\InvisibleCaptcha\Plugin;

use Amasty\InvisibleCaptcha\Helper\Data;

class Predispatch
{
    /**
     * Google URl for checking captcha response
     */
    const GOOGLE_VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $curl;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    private $redirect;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    private $responseFactory;

    /**
     * Predispatch constructor.
     * @param \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory
     * @param Data $helper
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     */
    public function __construct(
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        Data $helper,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\ResponseFactory $responseFactory
    ) {
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->helper = $helper;
        $this->curl = $curl;
        $this->urlBuilder = $urlBuilder;
        $this->messageManager = $messageManager;
        $this->redirect = $redirect;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @param \Magento\Framework\App\FrontControllerInterface $subject
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function aroundDispatch(
        \Magento\Framework\App\FrontControllerInterface $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if ($this->helper->isModuleOn()) {
            foreach ($this->helper->getCaptchaUrls() as $captchaUrl) {
                if (strpos($this->urlBuilder->getCurrentUrl(), $captchaUrl) !== false) {
                    if ($request->isPost()) {
                        $token = $request->getPost('amasty_invisible_token');
                        $validation = $this->verifyCaptcha($token);
                        if (!$validation) {
                            $this->messageManager->addErrorMessage(__('Something is wrong'));
                            $response = $this->responseFactory->create();
                            $response->setRedirect($this->redirect->getRefererUrl());
                            $response->setNoCacheHeaders();
                            return $response;
                        }
                    }
                    break;
                }
            }
        }
        $result = $proceed($request);

        return $result;
    }

    /**
     * @param string $token
     * @return bool
     */
    protected function verifyCaptcha($token)
    {
        if ($token) {
            $curlParams = [
                'secret' => $this->helper->getConfigValueByPath(Data::CONFIG_PATH_GENERAL_SECRET_KEY),
                'response' => $token
            ];
            $this->curl->post(self::GOOGLE_VERIFY_URL, $curlParams);
            try {
                if (($this->curl->getStatus() == 200)
                    && array_key_exists('success', $answer = \Zend_Json::decode($this->curl->getBody()))
                ) {
                    return $answer['success'];
                }
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }
}
