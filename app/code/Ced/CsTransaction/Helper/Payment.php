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
* @category    	Ced
* @package     	Ced_CsTransaction
* @author 		CedCommerce Core Team <connect@cedcommerce.com>
* @copyright   	Copyright CedCommerce (http://cedcommerce.com/)
* @license      http://cedcommerce.com/license-agreement.txt
*/
namespace Ced\CsTransaction\Helper;

class Payment extends \Magento\Framework\App\Helper\AbstractHelper
{
	/**
	 *
	 *
	 * @var object
	 */
	protected $_objectManager;

	public function __construct(\Magento\Framework\App\Helper\Context $context,
			\Magento\Framework\ObjectManagerInterface $objectManager
	) {
		$this->_objectManager=$objectManager;
		parent::__construct($context);
	}

	public function _getTransactionsStats($vendor)
	{
		$this->_vendor=$vendor;
		if ($this->_vendor != null && $this->_vendor && $this->_vendor->getId()) {
			$model = $this->getAssociatedOrders($this->_vendor->getId());
			$model->getSelect()->joinLeft('ced_cstransaction_vorder_items','main_table.order_id=ced_cstransaction_vorder_items.order_increment_id AND main_table.vendor_id=ced_cstransaction_vorder_items.vendor_id', ['ced_cstransaction_vorder_items.qty_ready_to_pay','ced_cstransaction_vorder_items.item_fee']);
			$model->getSelect()
				->reset(\Zend_Db_Select::COLUMNS)
				->columns('payment_state')
				->columns('COUNT(*) as count')
				->columns('SUM(ced_cstransaction_vorder_items.item_fee) as net_amount')
				->group("payment_state");
			return $model && count($model) ? $model: [] ;
		}
		return false;
	}
	
	public function getAssociatedOrders($vendorId)
	{
		$orderGridTable = $this->_objectManager->get('Magento\Framework\App\ResourceConnection')
							->getTableName('sales_order_grid');
		$collection = $this->_objectManager->get('Ced\CsMarketplace\Model\Vorders')->getCollection()
						->addFieldToFilter('main_table.vendor_id', $vendorId);
		$collection->getSelect()->join($orderGridTable, 'main_table.order_id LIKE  CONCAT('.$orderGridTable.".increment_id".')', array('billing_name','increment_id','status','store_id','store_name','customer_id','base_grand_total','base_total_paid','grand_total','total_paid','base_currency_code','order_currency_code','shipping_name','billing_address','shipping_address','shipping_information','customer_email','customer_group','subtotal','shipping_and_handling','customer_name','payment_method','total_refunded'));
		return $collection;
	}
}
