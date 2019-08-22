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

class PrepareItemsForRefund implements ObserverInterface {
	
	/**
	 *
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
	protected $_objectManager;
	protected $request;

	public function __construct(
		\Magento\Framework\ObjectManagerInterface $objectManager, 
		\Ced\CsMarketplace\Model\Vorders $vorders, 
		\Ced\CsOrder\Helper\Data $csorderHelper, 
		\Ced\CsTransaction\Model\Items $vtorders, 
		\Magento\Framework\App\Request\Http $request
	) 
	{
		$this->_objectManager = $objectManager;
		$this->_vorders = $vorders;
		$this->_csorderHelper = $csorderHelper;
		$this->_vtorders = $vtorders;
		$this->_request = $request;
	}

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
	public function execute(\Magento\Framework\Event\Observer $observer) {
    	$state =  $this->_objectManager->get('Magento\Framework\App\State');
    //print_r($state->getAreaCode());die;
		if ($this->_csorderHelper->isActive()) {
			$creditmemo = $observer->getCreditmemo();
			$quoteitemid = [];
			$creditMemoItems = [];
			$qtyrefunded = 0;
			$credit_memo_item = $this->_request->getPost('creditmemo');
			if (!$credit_memo_item) {
				$credit_memo_item = $this->_request->getParam('creditmemo');
			}
			
			try {
				foreach($creditmemo->getAllItems () as $item) {
					$quoteItem = $this->_objectManager->create ('Magento\Sales\Model\Order\Item')->load ( $item->getOrderItemId () );
					if($state->getAreaCode() == 'adminhtml'){
						$paymentItemcollection = $this->_vtorders->getCollection()
											->addFieldToFilter('order_id', $this->_request->getParam('order_id'))
											->addFieldToFilter('order_item_id', $item->getOrderItemId () )
											->addFieldToFilter('is_requested', array('neq'=>'2'));
					}else{
						$paymentItemcollection = $this->_vtorders->getCollection()
											->addFieldToFilter('parent_id', $this->_request->getParam('order_id'))
											->addFieldToFilter('order_item_id', $item->getOrderItemId () )
											->addFieldToFilter('is_requested', array('neq'=>'2'));
					}
				   
					if (!empty($paymentItemcollection->getData())) {
						foreach ($paymentItemcollection as $items) {
							$vorder = $this->_vorders->getCollection()
													->addFieldToFilter('order_id', $creditmemo->getOrder()->getIncrementId())
													->addFieldToFilter('vendor_id', $items->getVendorId())
													->getFirstItem();
							$can_refund = false;
							if ($items->getItemPaymentState()!=\Ced\CsTransaction\Model\Items::STATE_PAID || $items->getItemPaymentState()==\Ced\CsTransaction\Model\Items::STATE_READY_TO_PAY) {
								$itemsFee = json_decode($vorder->getItemsCommission(), true);
								$saveItems = $this->_objectManager->create('Ced\CsTransaction\Model\Items')->load($items->getId());
								$quoteitemid[] = $items->getOrderItemId();
								//$qtyrefunded += $item->getQty();

								$saveItems->setQtyReadyToPay($items->getQtyReadyToPay() - $item->getQty());
				        		$saveItems->setTotalCreditmemoAmount($items->getTotalCreditmemoAmount()+$item->getBaseRowTotal());
				        		$totalAmount = $this->getTotalAmount($item);
				        		if(isset($itemsFee[$quoteItem->getQuoteItemId()])){
				        			$itemCommission = $itemsFee[$quoteItem->getQuoteItemId()] ['base_fee'] / $quoteItem->getQtyOrdered();
				        			$saveItems->setItemFee($saveItems->getItemFee() - ($totalAmount - ($itemCommission*$item->getQty())));
									$saveItems->setItemCommission($saveItems->getItemCommission() - ($itemCommission*$item->getQty()));
									$saveItems->setAmountRefunded($saveItems->getAmountRefunded() + ($totalAmount - ($itemCommission*$item->getQty())));
				        		}
								
				        		
								$saveItems->setBaseRowTotal($saveItems->getBaseRowTotal() - $item->getBaseRowTotal());
								$saveItems->setRowTotal($saveItems->getRowTotal() - $item->getRowTotal());
								$saveItems->setQtyReadyToRefund($saveItems->getQtyReadyToRefund() + $item->getQty());
								$saveItems->setQtyRefunded($saveItems->getQtyRefunded () + $item->getQty());
								

								if ($items->getQtyReadyToPay () == $item->getQty())
									$saveItems->setIsRequested('2');
								$saveItems->save();
								$creditMemoItems[$item->getOrderItemId ()] = $item->getQty();
							} else {
								$can_refund = true;
								$itemsFee = json_decode($vorder->getItemsCommission (), true);
								$saveItems = $this->_objectManager->create('Ced\CsTransaction\Model\Items')->load($items->getId());
								$quoteitemid[] = $items->getOrderItemId();
								$qtyrefunded += $item->getQty();

								$saveItems->setQtyReadyToPay($items->getQtyReadyToPay() - $item->getQty());
								$saveItems->setTotalCreditmemoAmount($items->getTotalCreditmemoAmount()+$item->getBaseRowTotal());
								$totalAmount = $this->getTotalAmount($item);
								if(isset($itemsFee[$quoteItem->getQuoteItemId()])){
									$itemCommission = $itemsFee[$quoteItem->getQuoteItemId()]['base_fee'] / $quoteItem->getQtyOrdered();
									$saveItems->setItemFee(floatval($saveItems->getItemFee()) - (floatval($totalAmount) - (floatval($itemCommission*$item->getQty()))));
									$saveItems->setAmountRefunded(floatval($saveItems->getAmountRefunded())+ floatval($totalAmount) - (floatval($itemCommission*$item->getQty())));
									$saveItems->setAmountReadyToRefund(floatval($saveItems->getAmountReadyToRefund())+ floatval($totalAmount) - (floatval($itemCommission*$item->getQty())));
								}
								
								

								$saveItems->setBaseRowTotal($saveItems->getBaseRowTotal() - $item->getBaseRowTotal());
								$saveItems->setRowTotal($saveItems->getRowTotal() - $item->getRowTotal());
								$saveItems->setQtyReadyToRefund($saveItems->getQtyReadyToRefund() + $item->getQty());
									
								$saveItems->setQtyRefunded($saveItems->getQtyRefunded () + $item->getQty());		
								

								$saveItems->setItemPaymentState(\Ced\CsTransaction\Model\Items::STATE_READY_TO_REFUND);
								if ($items->getQtyReadyToPay () == $item->getQty())
									$saveItems->setIsRequested('2');
								$saveItems->save();
								$creditMemoItems[$item->getOrderItemId ()] = $item->getQty();
							}
							$qtyordered = $this->_objectManager->create ( 'Magento\Sales\Model\Order\Item' )->load($items->getOrderItemId())->getQtyOrdered();
							$qtyrefunded = $saveItems->getQtyRefunded();
							if ($qtyordered == $qtyrefunded) {
								$vorder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_CANCELED);
								$vorder->save ();
							}else{
								$vorder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_REFUND );
								$vorder->save ();
							}
							//print_r($items->getData());die;
							
						}
					}
				}
			} catch ( \Exception $e ) {
				echo $e->getMessage();die;
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
			}

		/*	try {
				$qtyordered = 0;
				foreach ($quoteitemid as $id) {
					$vorderItems = $this->_objectManager->create ( 'Magento\Sales\Model\Order\Item' )->load($id);
					$qtyordered+= $vorderItems->getQtyOrdered(); 
				}
				if (!$can_refund) {
					if ($qtyordered == $qtyrefunded) {
						$vorder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_CANCELED);
						$vorder->save ();
					}
				} else {
					$vorder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_REFUND );
					$vorder->save ();
				}
			} catch ( \Exception $e ) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
			}*/
		}
	}

	public function getTotalAmount($item)
	{
		$amount = $item->getBaseRowTotal()+$item->getBaseTaxAmount()-$item->getBaseDiscountAmount();
		return $amount;
	}
}
