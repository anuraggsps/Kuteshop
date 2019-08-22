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
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class VorderSaveAfter implements ObserverInterface
{
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    protected $request;
    
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
        RequestInterface $request, \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsOrder\Helper\Data $csorderHelper,
        \Ced\CsTransaction\Model\Items $vtorders
    ) {
        $this->_objectManager = $objectManager;
        $this->request = $request;
        $this->_vorders = $vorders;
        $this->_csorderHelper = $csorderHelper;
        $this->_vtorders = $vtorders;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    { 
        try{
            if($observer->getVorder()->getId() && $this->_csorderHelper->isActive()){
                $vorder = $this->_vorders->load($observer->getVorder()->getId());
                
                $itemsFee = json_decode($vorder->getItemsCommission(), true);
            
                $order = $this->_objectManager->get('\Magento\Sales\Model\Order')->load($vorder->getOrderId(), 'increment_id');

                foreach ($order->getAllItems() as $item) {
                    if ($item->getParentItem()) continue;
                    $existingItem = $this->_vtorders->getCollection()
                                    ->addFieldToFilter('order_item_id', array('eq'=>$item->getId()))
                                    ->getFirstItem();
                    if (!$existingItem->getId() && $item->getVendorId() == $vorder->getVendorId()) {
                        $vorderItem = $this->_vtorders;
                        $vorderItem->setParentId($observer->getVorder()->getId());
                        $vorderItem->setOrderItemId($item->getId());
                        $vorderItem->setOrderId($order->getId());
                        $vorderItem->setOrderIncrementId($order->getIncrementId());
                        $vorderItem->setVendorId($vorder->getVendorId());
                        $vorderItem->setCurrency($vorder->getCurrency());
                        $vorderItem->setBaseRowTotal($item->getBaseRowTotal());
                        $vorderItem->setRowTotal($item->getRowTotal());
                        $vorderItem->setSku($item->getSku());                       
                        $vorderItem->setShopCommissionTypeId($vorder->getShopCommissionTypeId());
                        $vorderItem->setShopCommissionRate($vorder->getShopCommissionRate());
                        $vorderItem->setShopCommissionBaseFee($vorder->getShopCommissionBaseFee());
                        $vorderItem->setShopCommissionFee($vorder->getShopCommissionFee());
                        $vorderItem->setProductQty($item->getQtyOrdered());
                        $vorderItem->setItemPaymentState(0);
                        $vorderItem->setItemFee(($item->getRowTotal()-$itemsFee[$item->getQuoteItemId()]['base_fee'])/$item->getQtyOrdered());
                        $vorderItem->setItemCommission($itemsFee[$item->getQuoteItemId()]['base_fee']/$item->getQtyOrdered());
                        $vorderItem->save();
                    }     
                }  
            }
        } catch(\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }
    }
}
