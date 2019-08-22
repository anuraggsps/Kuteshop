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
 * @category  Ced
 * @package   Ced_CsVendorReview
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorReview\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

   /**
    * 
    * @param \Magento\Framework\App\Helper\Context $context
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    */
    public function __construct(
    	\Magento\Framework\App\Helper\Context $context,
    	\Magento\Framework\ObjectManagerInterface $objectManager
    )
    {
    	$this->_objectManager = $objectManager;
        parent::__construct($context);
    }
    
    public function isCustomerAllowed(){
    	return $this->scopeConfig->getValue('ced_csmarketplace/vendorreview/purchase_enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
    public function checkVendorProduct(){
    	$collection = $this->_objectManager->create('Magento\Sales\Model\Order')->getCollection()->addFieldToFilter('customer_id',$this->getCustomerId());
    	$collection->getSelect()->join(
    			'sales_order_item',
    			'`sales_order_item`.order_id=`main_table`.entity_id',
    			[
    			'product_ids' => new \Zend_Db_Expr('group_concat(`sales_order_item`.product_id SEPARATOR ",")')
    			]
    	)->group('main_table.customer_id');
    	 
    	$productIds = array_values(array_unique(explode(',',$collection->getFirstItem()->getProductIds())));
    	 
    	$vendorProductIds = array_column($this->getVendorProducts($this->getVendorId()), 'product_id');
    	 
    	$result = array_intersect($productIds,$vendorProductIds);
    	 
    	if(count($result)>0)
    		return true;
    	 
    	return false;
    
    }
    
    protected function getCustomerId(){
    	return $this->_objectManager->create('Magento\Customer\Model\Session')->getCustomerId();
    }
    
    public function getVendorProducts($vendor_id){
    	$products = $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')->getCollection()
    	->addFieldToFilter('vendor_id', $vendor_id)
    	->addFieldToFilter('check_status', 1)
    	->addFieldToSelect('product_id');
    	return $products->getData();
    }
    
    protected function getVendorId(){
    	return $this->_objectManager->get('Magento\Framework\Registry')->registry('current_vendor')->getId();
    }
}
