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
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsTransaction\Helper;
 
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    public function __construct(\Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\CsMarketplace\Helper\Data $helperData  
    ) {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
        $this->_csMarketplaceHelper = $helperData;
    }
    
    public function getAvailableShipping($vorder,$type){
        $shippingAmount = '';
      if($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {
            if($vorder->getShippingPaid()>$vorder->getShippingRefunded())
            {
              //$shippingAmount=$vorder->getShippingPaid()-$vorder->getShippingRefunded();
            
              $shippingAmount = $vorder->getShippingPaid()-$vorder->getShippingRefunded();
              
            }
            elseif($vorder->getShippingPaid()==$vorder->getShippingRefunded()&&$vorder->getShippingPaid()!=0)
            {
              $shippingAmount='Refunded';
            }
            else
            {
              $shippingAmount='N/A';
            }
          }
          else if($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT )
          {
            //if($vorder->getShippingAmount()>$vorder->getShippingPaid())
            if($vorder->getShippingPaid()==0)
            {
              //$shippingAmount=$vorder->getShippingAmount()-$vorder->getShippingPaid();

              
              
              //print_r($vorder->getCreditmemoBaseShippingAmountRefunded());

              $shippingAmount = $vorder->getShippingAmount()+$vorder->getShippingRefunded();
              //print_r($shippingAmount);
             
            }
            //elseif($vorder->getShippingAmount()==$vorder->getShippingPaid()&&$vorder->getShippingAmount()!=0)
            elseif($vorder->getShippingPaid()>0 && $vorder->getShippingAmount()!=0)
            {
              $shippingAmount='Paid';
            }
            else
            {
              $shippingAmount='N/A';
            }
          }
          return $shippingAmount;
    }



    public function getTotalEarn($orderId, $vendorId,$itemId=null){
      
        $collection = $this->_objectManager->get('\Ced\CsTransaction\Model\Items')
                  ->getCollection()
                  ->addFieldToFilter('vendor_id',array('eq'=>$vendorId))
                  ->addFieldToFilter('parent_id',array('eq'=>$orderId));
                  
        $main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
        $item_fee = $this->_csMarketplaceHelper->getTableKey('item_fee');
        $item_commission= $this->_csMarketplaceHelper->getTableKey('item_commission');
        
        $collection->addFieldToFilter('qty_ready_to_pay',array('gt'=>0));
       
        $collection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("sum({$main_table}.{$item_fee})")));
        $collection->getSelect()->columns(array('commission_fee' => new \Zend_Db_Expr("({$main_table}.{$item_commission})")));
        
        $earn =  $collection->getFirstItem()->getNetVendorEarn();

        $vorder = $this->_objectManager->get('\Ced\CsMarketplace\Model\Vorders')->load($orderId);
        $shippingAmount = $this->getAvailableShipping($vorder,'credit');
        $totalEarn = $earn + $shippingAmount;
        
        return $totalEarn;
      }


}
