<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMessaging
 * @author 	CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMessaging\Controller\Vendor;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Ainbox
 * @package Ced\CsMessaging\Controller\Vendor
 */
class Ainbox extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * Cinbox constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper
     */
    public function __construct(Context $context,
                                Session $customerSession,
                                PageFactory $resultPageFactory,
                                UrlFactory $urlFactory,
                                \Magento\Framework\Module\Manager $moduleManager,
                                \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper)
    {
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
        $this->csmarketplaceHelper = $csmarketplaceHelper;
    }


    /**
     * @var
     */
    protected $resultPageFactory;

    /**
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        if(!$this->_getSession()->getVendorId()) {
            return;
        }

        if (!$this->csmarketplaceHelper->getStoreConfig('ced_csmarketplace/general/messaging_active'))
            return $this->_redirect('customer/account');


        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('Admin Inbox'));
        return $resultPage;

    }
}
