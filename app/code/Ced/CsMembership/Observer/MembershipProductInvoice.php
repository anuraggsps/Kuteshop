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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Observer; 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
Class MembershipProductInvoice implements ObserverInterface
{
	
	protected $_objectManager;
	protected $_quoteFactory;
	protected $_advanceFactory;
	protected $_object;
    protected $_coreRegistry = null;
    protected $frontController;
    protected $request;
	
	public function __construct(		
			\Magento\Framework\DataObject $object,
			\Magento\Framework\ObjectManagerInterface $objectManager,
			\Magento\Quote\Model\QuoteFactory $quoteFactory,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\App\FrontControllerInterface $frontController,
            \Magento\Framework\App\Request\Http $request
	)
    {
    	$this->_object = $object;
        $this->_objectManager = $objectManager;
        $this->_quoteFactory = $quoteFactory;
        $this->_coreRegistry = $registry;
        $this->frontController = $frontController;
        $this->request = $request;
    }
	
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
     	$order = $observer->getEvent()->getOrder();
        $this->_objectManager->create('Ced\CsMembership\Helper\Data')->setSubscription($order);
        $items = $order->getAllItems();
            foreach($items as $item):
              $productId = $item->getProductId();
              break;
            endforeach;
        try{
            $subscription = $this->_objectManager->create('Ced\CsMembership\Helper\Data')->getSubscriptionByProduct($productId);
            $subscriptionId = $subscription['id'];
            if ($subscriptionId) {
                // Create invoice for this order
                $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);

                // Make sure there is a qty on the invoice
                if (!$invoice->getTotalQty()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                                __('You can\'t create an invoice without products.')
                            );
                }

                // Register as invoice item
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                $invoice->register();

                // Save the invoice to the order
                $transaction = $this->_objectManager->create('Magento\Framework\DB\Transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

                $transaction->save();
            }
        }
        catch(\Exception $e)
        {
            print_r($e->getMessage());die;
        }   
                
    }	
    

}