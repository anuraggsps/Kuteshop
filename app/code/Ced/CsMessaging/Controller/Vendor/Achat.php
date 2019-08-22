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
use Ced\CsMessaging\Helper\Data;
/**
 * Class Achat
 * @package Ced\CsMessaging\Controller\Vendor
 */
class Achat extends \Ced\CsMarketplace\Controller\Vendor
{

    public function __construct(Context $context,
                                Session $customerSession,
                                PageFactory $resultPageFactory,
                                UrlFactory $urlFactory,
                                \Magento\Framework\Module\Manager $moduleManager,
                                \Ced\CsMessaging\Model\VadminFactory $vadminFactory,
                                \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper)
    {
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
        $this->vadminFactory = $vadminFactory;
        $this->csmarketplaceHelper = $csmarketplaceHelper;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        if(!$this->_getSession()->getVendorId()) {
            return;
        }

        if (!$this->csmarketplaceHelper->getStoreConfig(Data::IS_MESSAGING_ENABLED))
            return $this->_redirect('customer/account');


        $id = $this->getRequest()->getParam('id');
        $threadFactory = $this->vadminFactory->create();
        $threadData = $threadFactory->load($id);

        $resultRedirect = $this->resultPageFactory->create();
        $resultRedirect->getConfig()->getTitle()->set($threadData->getSubject());
        return $resultRedirect;

    }
}
