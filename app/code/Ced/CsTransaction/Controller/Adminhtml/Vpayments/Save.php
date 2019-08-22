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

namespace Ced\CsTransaction\Controller\Adminhtml\Vpayments;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Ced\CsMarketplace\Controller\Adminhtml\Vpayments\Save
{
    /**
     * @param Context     $context
     *
     */
    public function __construct(
        Context $context
    ) {

        parent::__construct($context);

    }

    /**
     * Customer edit action
     *
     * @return bool|\Magento\Framework\App\ResponseInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute(){
        if ($data = $this->getRequest()->getPost()) {
            //print_r($data);die;
            try{
                $params = $this->getRequest()->getParams();
                $type = isset($params['type']) && in_array($params['type'], array_keys(\Ced\CsMarketplace\Model\Vpayment::getStates()))?$params['type']:\Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT;
          		$itemid = json_decode($data['order_item_id']);

                $model = $this->_objectManager->create('Ced\CsMarketplace\Model\Vpayment');
              //transaction id check
                $transaction_id_unique=$model->getCollection()->addFieldToFilter('transaction_id', $data['transaction_id'] )->getData();

                if(sizeof($transaction_id_unique)>0){
                    $this->messageManager->addError("Transaction id already exist. Please enter another transaction id");
                    $resultRedirects = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    $resultRedirects->setUrl($this->_redirect->getRefererUrl());
                    return $resultRedirects;
                }
              //transaction id end

                $amount_desc = isset($data['amount_desc'])?$data['amount_desc']:json_encode(array());
                $shipping_info = isset($data['shipping_info'])?$data['shipping_info']:json_encode(array());
                $total_shipping_amount = isset($data['total_shipping_amount'])?$data['total_shipping_amount']:0;

                $total_amount = json_decode($amount_desc,true);
              
                $this->_objectManager->get('\Ced\CsMarketplace\Helper\Data')->logProcessedData($total_amount, \Ced\CsMarketplace\Helper\Data::VPAYMENT_TOTAL_AMOUNT);  
                $baseCurrencyCode = $this->_objectManager->get('Magento\Directory\Helper\Data')->getBaseCurrencyCode();
                $allowedCurrencies = $this->_objectManager->get('Magento\Directory\Model\Currency')->getConfigAllowCurrencies();
                $rates = $this->_objectManager->get('Magento\Directory\Model\Currency')
                        ->getCurrencyRates($baseCurrencyCode, array_values($allowedCurrencies));
                $data['base_to_global_rate'] = isset($data['currency'])&& isset($rates[$data['currency']]) ? $rates[$data['currency']] : 1;

                if ($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {
                    $oldAmountDesc = [];
                    $base_amount = 0;
                    if (count($total_amount) > 0) {
                        foreach ($total_amount as $orderid=>$items) {
                            foreach ($items as $vorderItemId=>$value) {
                                $vorder = $this->_objectManager->get('Magento\Sales\Model\Order')->load($orderid);
                                $incrementId = $vorder->getIncrementId();
                                if(isset($oldAmountDesc[$incrementId])){
                                    $oldAmountDesc[$incrementId]+=$value;
                                } else {
                                    $oldAmountDesc[$incrementId]=$value;
                                }
                                $base_amount += (float)$value;
                            }
                        }
                    }

                    $oldAmountDesc=json_encode($oldAmountDesc);
                    $data['item_wise_amount_desc']=$data['amount_desc'];
                    $data['amount_desc']=$oldAmountDesc;
                    $base_amount+=$total_shipping_amount;

                  //check if RMA Fee is included
                    if(isset($data['processed_orders'])){
                        $rmaFee = 0;
                        foreach($data['processed_orders'] as $val)
                            $rmaFee +=$val;
                        $base_amount += $rmaFee;
                    }

                }  else {
                    if (isset($data['base_fee'])) {
                        if ($data['base_fee'] > $data['base_amount']) {
                            $this->messageManager->addError(__('Adjustment Amount cannot be greater that net paid amount'));
                            return $this->_redirect('csmarketplace/vorders/index');
                        }
                    }
              		$oldAmountDesc = [];
                    $paymentData = [];
                    $paymentData['vendor_id'] = $data['vendor_id'];
                    $paymentData['transaction_id'] = $data['transaction_id'];
                    $order_ids = [];
                    $adjustment_amount = 0;
                    if(isset($data['base_fee'])){
                    	if($data['base_fee'] > 0)
                    		$adjustment_amount = $data['base_fee'];
                    }
                    foreach($itemid as $_id){                    	
                    	$item_model = $this->_objectManager->create('Ced\CsTransaction\Model\Items')->load($_id);
                    	$order_ids[] = $item_model->getOrderId();
                    	$item_model->setQtyPaid($item_model->getQtyReadyToPay());
                    	$item_model->setQtyReadyToPay(0);
                    	$item_model->setAmountPaid($data['base_amount']+$adjustment_amount);
                    	$item_model->setItemPaymentState(\Ced\CsTransaction\Model\Items::STATE_PAID);
                    	$item_model->save();
                    	if(isset($oldAmountDesc[$item_model->getOrderIncrementId()])){
                    		$oldAmountDesc[$item_model->getOrderIncrementId()]+=$item_model->getTotalInvoicedAmount();
                    	} else {
                    		$oldAmountDesc[$item_model->getOrderIncrementId()] =$item_model->getTotalInvoicedAmount();
                    	}
                    }
                    $paymentData['total_shipping_amount'] = isset($data['total_shipping_amount'])?$data['total_shipping_amount']:0;

                    $data['base_amount'] = $data['base_amount']  -$adjustment_amount;
                    $paymentData['amount_desc'] = json_encode($oldAmountDesc);
                    $paymentData['base_amount'] =$data['base_amount'];
                    $paymentData['amount'] =$data['base_amount'];
                    $paymentData['currency'] = $data['currency'];
                    $paymentData['net_amount'] =$data['base_amount'];
                    $paymentData['base_net_amount'] =$data['base_amount'];
                    $paymentData['balance'] =$data['base_amount'];
                    $paymentData['base_balance'] =$data['base_amount'];
                    if(isset($data['notes']))
                    	$paymentData['notes'] =$data['notes'];
                    $paymentData['transaction_type'] =$type;
                    $paymentData['payment_code'] =$data['payment_code'];
                    $paymentData['payment_detail'] = isset($data['payment_detail'])?$data['payment_detail']:'n/a';
                   	$paymentData['status'] = $model->getOpenStatus();
                   	$paymentData['base_to_global_rate'] = $data['base_to_global_rate'];
                   	$paymentData['item_wise_amount_desc'] = $data['amount_desc'];
                   	$paymentData['tax'] = 0.00;
                   	$paymentData['payment_method'] = '0';
                    $paymentData['base_fee'] = $adjustment_amount;
                   
                   	$model->addData($paymentData);
                   	$model->save();
                   	foreach($data['processed_orders'] as $order_inc_id){
                   		$order_id = $this->_objectManager->create('Magento\Sales\Model\Order')
                                    ->loadByIncrementId($order_inc_id)->getId();
                   		$orderItemData = $this->_objectManager->create('Magento\Sales\Model\Order\Item')->getCollection()
                                     		->addFieldToFilter('vendor_id', $data['vendor_id'])
                                     		->addFieldToFilter('order_id', $order_id)
                                        ->addFieldToFilter('parent_item_id', array('null' => true), 'left');
                   		$orderItemData->getSelect()->reset('columns')->columns(['total_ordered_qty'=>'SUM(qty_ordered)']);
                   		$totalOrderedQty = $orderItemData->getFirstItem()->getTotalOrderedQty();               		
                   		$total_qty_paid = $this->_objectManager->create('Ced\CsTransaction\Model\Items')->getCollection()
                                         		->addFieldToFilter('vendor_id', $data['vendor_id'])
                                         		->addFieldToFilter('order_id', $order_id);  		
                   		$total_qty_paid->getSelect()->reset('columns')
                                ->columns(['total_orderqty_paid'=>new \Zend_Db_Expr('SUM((qty_paid)+(qty_refunded))')]);
                   		$totalPaidQty = $total_qty_paid->getFirstItem()->getTotalOrderqtyPaid();
                   		$vorder = $this->_objectManager->create('Ced\CsMarketplace\Model\Vorders')->getCollection()
           										->addFieldToFilter('vendor_id', $data['vendor_id'])
           										->addFieldToFilter('order_id', $order_inc_id)
           										->getFirstItem();
                   		if ($totalOrderedQty == $totalPaidQty) {
                   			$vorder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_PAID);
                   			$vorder->save();
                   		} else {
                   			$vorder->setPaymentState(\Ced\CsOrder\Model\Vorders::STATE_PARTIALLY_PAID);
                   			$vorder->save();
                   		}
                   	}
                }

                $this->_objectManager->get('\Ced\CsMarketplace\Helper\Data')
                    ->logProcessedData($model->getData(), \Ced\CsMarketplace\Helper\Data::VPAYMENT_CREATE);

                $this->messageManager->addSuccessMessage(__('Payment is  successfully saved'));
                $this->_session->setFormData(false);
                return $this->_redirect('*/*/');
            }catch (\Exception $e) {
                $this->_objectManager->get('\Ced\CsMarketplace\Helper\Data')->logException($e);
                $this->messageManager->addError($e->getMessage());
                $this->_session->setFormData($data);
                return $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
            }
        }
        return false;
    }
  }
