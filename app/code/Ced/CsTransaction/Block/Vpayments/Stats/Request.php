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
 * @package     Ced_CsTransaction
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsTransaction\Block\Vpayments\Stats;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;
class Request extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
	/**
	 * Get set collection of Products report
	 *
	 */
	public $_objectManager;
	public function __construct( Context $context,
		Session $customerSession,
		\Magento\Framework\ObjectManagerInterface $objectManager,		
		UrlFactory $urlFactory){
			$this->_session = $customerSession;
			$this->urlModel = $urlFactory;
			$this->_objectManager = $objectManager;
		parent::__construct($context,$customerSession,$objectManager,$urlFactory);
	
	}
	
	
	public function getPendingAmountOfVendor(){
		if($this->getVendor() && $this->getVendor()->getId()){
			$paymentHelper = $this->_objectManager->get('Ced\CsTransaction\Helper\Payment');
			$collection=$paymentHelper->_getTransactionsStats($this->getVendor());
			return $collection->getFirstItem()->getNetAmount();
			}
		return null;
	}
	
	public function getRequestedAmount(){
	    
	    $collection = $this->_objectManager->create('Ced\CsTransaction\Model\Items')->getCollection()->addFieldToFilter('vendor_id',$this->getVendorId())->addFieldToFilter('is_requested', array('1','2'));
	    $collection->getSelect()->reset('columns')->columns(['requested_amount'=>new \Zend_Db_Expr('SUM(item_fee)+SUM(amount_refunded)')]);
	    return  $collection->getFirstItem()->getRequestedAmount();
	   
	    
	}
	public function getPendingAmount(){
	    
	     $collection = $this->_objectManager->create('Ced\CsTransaction\Model\Items')->getCollection()->addFieldToFilter('vendor_id',$this->getVendorId());
	     $collection->getSelect()->reset('columns')->columns(['pending_amount'=>new \Zend_Db_Expr('SUM(item_fee)')]);
	     return  $collection->getFirstItem()->getPendingAmount();
	}
	public function getCancelledAmount(){
	    
	    $collection = $this->_objectManager->create('Ced\CsTransaction\Model\Items')->getCollection()->addFieldToFilter('vendor_id',$this->getVendorId());
	    
	     $collection->getSelect()->reset('columns')->columns(['cancelled_amount'=>new \Zend_Db_Expr('SUM(amount_refunded)')]);
	        return  $collection->getFirstItem()->getCancelledAmount();
	}
}
