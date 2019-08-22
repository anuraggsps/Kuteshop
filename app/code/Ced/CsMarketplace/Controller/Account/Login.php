<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */ 

namespace Ced\CsMarketplace\Controller\Account;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Ced\CsMarketplace\Helper\Data;
use Magento\Framework\Module\Manager;

class Login extends \Ced\CsMarketplace\Controller\Vendor
{

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @return $this|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page|void
     */
    public $helper;

    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        UrlFactory $urlFactory,
        Manager $moduleManager,
        Data $datahelper
    ) {
        $this->helper = $datahelper;
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);

    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $scopeConfig = $this->_objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
        $enable=$scopeConfig->getValue('ced_csmarketplace/general/activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($enable) {
            if ($this->session->isLoggedIn() && $this->helper->authenticate($this->session->getCustomerId())) {
                return $resultRedirect->setPath('csmarketplace/vendor/');

            }
            if ($this->session->isLoggedIn() && !$this->helper->authenticate($this->session->getCustomerId())) {
                return $resultRedirect->setPath('csmarketplace/account/approval');
            }
        }
        else{
            $this->messageManager->addErrorMessage(__('The Url\'s you are trying to access is not available at this moment.'));
            return $resultRedirect->setPath('/');
        }    
        return $this->resultPageFactory->create();
    }

}
