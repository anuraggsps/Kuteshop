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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsTransaction\Block\Vpayments\ListBlock;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template\Context;

class Request extends \Ced\CsMarketplace\Block\Vpayments\ListBlock\Request
{
    /**
     * Request constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param \Ced\CsMarketplace\Helper\Acl $acl
     * @param \Ced\CsMarketplace\Helper\Data $csMarketplaceHelper
     * @param \Ced\CsTransaction\Model\Items $vtorder
     */
	public function __construct(Context $context,
		Session $customerSession,
		\Magento\Framework\ObjectManagerInterface $objectManager,
		UrlFactory $urlFactory,
		\Magento\Framework\Locale\Currency $localeCurrency,
		\Ced\CsMarketplace\Helper\Acl $acl,
		\Ced\CsMarketplace\Helper\Data $csMarketplaceHelper,
		\Ced\CsTransaction\Model\Items $vtorder){
		$this->_vtorders=$vtorder;
		parent::__construct($context, $customerSession, $objectManager, $urlFactory, $localeCurrency, $acl, $csMarketplaceHelper);
		$pendingPayments = array();
		if ($vendorId = $this->getVendorId()) {

			$collection = $this->_vtorders
								->getCollection()
								->addFieldToFilter('vendor_id',array('eq'=>$vendorId));
			$main_table=$this->_csMarketplaceHelper->getTableKey('main_table');
			$item_fee=$this->_csMarketplaceHelper->getTableKey('item_fee');
			$qty_ready_to_pay=$this->_csMarketplaceHelper->getTableKey('qty_ready_to_pay');
			$item_commission=$this->_csMarketplaceHelper->getTableKey('item_commission');
			
				$collection->addFieldToFilter('qty_ready_to_pay',array('gt'=>0));
				$collection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$item_fee} * {$main_table}.{$qty_ready_to_pay})")));
				$collection->getSelect()->columns(array('commission_fee' => new \Zend_Db_Expr("({$main_table}.{$item_commission} * {$main_table}.{$qty_ready_to_pay})")));
			
			$collection->getSelect()->columns(array('commission_fee' => new \Zend_Db_Expr("({$main_table}.{$item_commission} * {$main_table}.{$qty_ready_to_pay})")));
			
			$pendingPayments = $this->filterPayment($collection);
		}
		$this->setPendingVpayments($pendingPayments);
	}
	
	
	public function filterPayment($payment){	
		$params =$this->_session->getData('payment_request_filter');
		if(is_array($params) && count($params)>0){
			foreach($params as $field=>$value){
				if($field=="__SID")
					continue;
				if(is_array($value)){
					if(isset($value['from']) && urldecode($value['from'])!=""){
						$from = urldecode($value['from']);					
						if($field=='created_at'){
							$from=date("Y-m-d 00:00:00",strtotime($from));
						} 
						
						$payment->addFieldToFilter($field, array('gteq'=>$from));
					}
					if(isset($value['to'])  && urldecode($value['to'])!=""){
						$to = urldecode($value['to']);					
						if($field=='created_at'){
							$to=date("Y-m-d 59:59:59",strtotime($to));
						}
						
						$payment->addFieldToFilter($field, array('lteq'=>$to));
					}
				}else if(urldecode($value)!=""){
					if($field == 'payment_method') {
						$payment->addFieldToFilter($field, array("in"=>$this->_acl->getDefaultPaymentTypeValue(urldecode($value))));
					} else {
						$payment->addFieldToFilter($field, array("like"=>'%'.urldecode($value).'%'));
					}
				}
			
			}
		}
		return $payment;
	}
	
	/**
	 * prepare list layout
	 *
	 */
	protected function _prepareLayout() {
		parent::_prepareLayout();
		$pager = $this->getLayout()->createBlock('Ced\CsMarketplace\Block\Html\Pager', 'customs.pager');
		$pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'all'=>'all'));
		$pager->setCollection($this->getPendingVpayments());
		$this->setChild('pager', $pager);
		$this->getPendingVpayments()->load();
		return $this;
	}
	/**
	 * return the pager
	 *
	 */
	public function getPagerHtml() {
		return $this->getChildHtml('pager');
	}
	
	/**
	 * return Back Url
	 *
	 */
	public function getBackUrl()
	{
		return $this->getUrl('*/*/index',array('_secure'=>true,'_nosid'=>true));
	}
	
}
