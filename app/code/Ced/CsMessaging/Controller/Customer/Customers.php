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
namespace Ced\CsMessaging\Controller\Customer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Customers
 * @package Ced\CsMessaging\Controller\Customer
 */
class Customers extends \Ced\CsMarketplace\Controller\Vendor
{

    public function __construct(Context $context,
                                Session $customerSession,
                                PageFactory $resultPageFactory,
                                UrlFactory $urlFactory,
                                \Magento\Framework\Module\Manager $moduleManager,
                                \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
                                \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $vcustomerMessageCollFactory
    )
    {
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
        $this->_customerCollectionFactory = $customerCollectionFactory;
        $this->customerSession = $customerSession;
        $this->vcustomerMessageCollFactory = $vcustomerMessageCollFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|void
     */
    public function execute()
    {
        if(!$this->_getSession()->getVendorId())
            return;

        $vcustomerCollection = $this->vcustomerMessageCollFactory->create();
        if (!empty($vcustomerCollection))
        {
            foreach ($vcustomerCollection as $message)
            {
                if($message->getVendorId()==$this->_getSession()->getVendorId() && $message->getSender()!='vendor')
                {
                    $message->setReceiverStatus(\Ced\CsMessaging\Helper\Data::STATUS_READ);
                }
                $message->save();
            }
        }

        $customer = $this->_customerCollectionFactory->create();
        $customer->addFieldToFilter('entity_id',['neq'=>$this->customerSession->getCustomerId()]);
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData($customer->toArray());
        return $resultJson;

    }
}
