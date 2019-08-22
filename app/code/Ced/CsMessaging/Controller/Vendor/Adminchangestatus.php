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
 * Class Adminchangestatus
 * @package Ced\CsMessaging\Controller\Vendor
 */
class Adminchangestatus extends \Ced\CsMarketplace\Controller\Vendor
{

    public function __construct(Context $context,
                                Session $customerSession,
                                PageFactory $resultPageFactory,
                                UrlFactory $urlFactory,
                                \Magento\Framework\Module\Manager $moduleManager,
                                \Ced\CsMessaging\Model\ResourceModel\VadminMessage\CollectionFactory $vadminMessageCollFactory
    )
    {
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
        $this->vadminMessageCollFactory = $vadminMessageCollFactory;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterfac e|void
     */
    public function execute()
    {
        if(!$this->_getSession()->getVendorId())
            return;

        $threadId = $this->getRequest()->getParam('thread_id');

        $vadminCollection = $this->vadminMessageCollFactory->create();
        $vadminCollection->addFieldToFilter('thread_id',$threadId);

        if (!empty($vadminCollection))
        {
            foreach ($vadminCollection as $message)
            {
                if($message->getReceiverId()==$this->_getSession()->getVendorId())
                {
                    $message->setStatus(\Ced\CsMessaging\Helper\Data::STATUS_READ);
                }
                $message->save();
            }
        }
        $this->csmarketplaceHelper->readNotification($threadId);

        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(['succes'=>true]);
        return $resultJson;

    }
}
