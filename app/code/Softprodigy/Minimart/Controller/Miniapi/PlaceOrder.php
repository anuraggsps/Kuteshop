<?php

namespace Softprodigy\Minimart\Controller\Miniapi;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Homepage
 *
 * @author mannu
 */
class PlaceOrder extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $this->registry->register('api_req', true, true);

            $params = $this->getRequest()->getParams();

            $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->loadActive($this->getRequest()->getParam('quote_id', null));

            if (!$quote->getId()) {
                throw new \Exception(__("Quote does not exist."));
            }


            $q_custmorId = $quote->getCustomerId();
            if (empty($q_custmorId) && !empty($params['cust_id'])) {
                $customerObj = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($params['cust_id']);
                if ($customerObj->getId()) {
                    $customerRepositry = $this->_objectManager->create("Magento\Customer\Api\CustomerRepositoryInterface");

                    /*$onbejctHelper = $this->_objectManager->create('Magento\Framework\Api\DataObjectHelper');
                    $onbejctHelper->populateWithArray(
                            $newCustomerDataObject, $customerObj->getData(), '\Magento\Customer\Api\Data\CustomerInterface'
                    ); */
                    $newCustomerDataObject = $customerRepositry->getById($customerObj->getEntityId());
                 //   var_dump($newCustomerDataObject->__toArray()); die;
                    $quote->assignCustomer($newCustomerDataObject);
                    $quote->save();
                }
            }
           // var_dump(get_class_methods($quote));
           // var_dump($quote->getCustomer()->__toArray()); die;
            
            $quote->setPaymentMethod($params['pay_method']);
            if ($params['pay_method'] == 'paypal_express')
                $quote->getPayment()->importData(array('method' => 'minimart_pay'));
            else
                $quote->getPayment()->importData(array('method' => $params['pay_method']));

            $quote->save();

            //===========================================
            //----here we have overrided default soap API method ---------------
            //$quote = $this->_getQuote($quoteId, $store);

            $checkoutHelper = $this->_objectManager->get('Magento\Checkout\Helper\Data');

            if ($quote->getIsMultiShipping()) {
                throw new \Exception(__("The checkout type is not valid. Select single checkout type."));
            }

            if ($quote->getCheckoutMethod() == \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST && !$checkoutHelper->isAllowedGuestCheckout($quote, $quote->getStoreId())) {
                throw new \Exception(__("Checkout is not available for guest"));
            }

            /** @var $customerResource Mage_Checkout_Model_Api_Resource_Customer */
            $isNewCustomer = $this->prepareCustomerForQuote($quote);

            try {
                $quote->collectTotals();
                /** @var $service Mage_Sales_Model_Service_Quote */
                $order = $this->_objectManager->create("Magento\Quote\Api\CartManagementInterface")->submit($quote);
                if ($isNewCustomer) {
                    try {
                        $this->_involveNewCustomer();
                    } catch (\Exception $e) {
                        $this->logger->critical($e->getMessage());
                    }
                }

                $this->__checkoutSession
                        ->setLastQuoteId($quote->getId())
                        ->setLastSuccessQuoteId($quote->getId())
                        ->clearHelperData();

                if ($order) {
                    $this->_eventManager->dispatch(
                            'checkout_type_onepage_save_order_after', ['order' => $order, 'quote' => $quote]
                    );

                    /**
                     * a flag to set that there will be redirect to third party after confirmation
                     */
                    $redirectUrl = $quote->getPayment()->getOrderPlaceRedirectUrl();
                    /**
                     * we only want to send to customer about new order when there is no redirect to third party
                     */
                    if (!$redirectUrl && $order->getCanSendNewEmailFlag()) {
                        try {
                            $this->_objectManager->create("Magento\Sales\Model\Order\Email\Sender\OrderSender")->send($order);
                        } catch (\Exception $e) {
                            $this->logger->critical($e->getMessage());
                        }
                    }
                }
                
                try{
                    $canSaveAdr = $this->getRequest()->getParam('save_into_addr', false);
                    if($quote->getCustomer()->getId() and $canSaveAdr===1)
                        $this->saveCustomerAddrs($quote,$quote->getCustomer());
                } catch (\Exception $ex) {
                    $this->logger->debug($ex->getMessage());
                }
                
                $this->_eventManager->dispatch(
                        'checkout_submit_all_after', [
                    'order' => $order,
                    'quote' => $quote
                        ]
                );
            } catch (Mage_Core_Exception $e) {
                $jsonArray['response'] = $e->getMessage();
                $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
                echo json_encode($jsonArray);
                die;
            }

            $order_id = $order->getIncrementId();

            //==========End of overrided default soap API method =================================


            if ($quote->getCustomerIsGuest()) {
                $orderM = $this->_objectManager->create("Magento\Sales\Model\Order")->loadByIncrementId($order_id);
                if ($orid = $orderM->getId()) {
                    $orderM->setCustomerEmail($params['email']);
                    $orderM->setId($orid);
                    $orderM->save();
                }
            }

            if ($params['pay_method'] == 'paypal_express' && !empty($order_id)) {
                $order = $this->_objectManager->create("Magento\Sales\Model\Order")->loadByIncrementId($order_id);
                $payment = $order->getPayment();
                $payment->setMethod('paypal_express');
                $payment->setLastTransId($params['paypal_transactionId']);
                $payment >setAdditionalInformation('paypal_express_checkout_shipping_method', '');
                $payment->setAdditionalInformation('paypal_payer_id', $params['paypal_payer_id']);
                $payment->setAdditionalInformation('paypal_payer_email', $params['paypal_payer_email']);
                $payment->setAdditionalInformation('paypal_payer_status', $params['paypal_payer_status']);
                $payment->setAdditionalInformation('paypal_address_status', $params['paypal_address_status']);
                $payment->setAdditionalInformation('paypal_correlation_id', $params['paypal_correlation_id']);
                $payment->setAdditionalInformation('paypal_express_checkout_payer_id', $params['paypal_express_checkout_payer_id']);
                $payment->setAdditionalInformation('paypal_express_checkout_token', $params['paypal_express_checkout_token']);
                $payment->setAdditionalInformation('paypal_payment_status', $params['paypal_payment_status']);
                $payment->setAdditionalInformation('paypal_pending_reason', $params['paypal_pending_reason']);
                $payment->setAdditionalInformation('paypal_protection_eligibility', $params['paypal_protection_eligibility']);
                $payment->save();

                //======gettting paypal payment action
                $paymentAction = $this->__helper->getStoreConfig("payment/paypal_express/payment_action");

                $txnType = "";
                if ($paymentAction == \Magento\Paypal\Model\Config::PAYMENT_ACTION_SALE) {
                    $txnType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE;
                } else if ($paymentAction == \Magento\Paypal\Model\Config::PAYMENT_ACTION_ORDER) {
                    $txnType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_ORDER;
                } else if ($paymentAction == \Magento\Paypal\Model\Config::PAYMENT_ACTION_AUTH) {
                    $txnType = \Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH;
                }

                //============================================
                $order = $this->_objectManager->create("Magento\Sales\Model\Order")->loadByIncrementId($order_id);
                $state = \Magento\Sales\Model\Order::STATE_PROCESSING;
                $status = true;
                $amount = number_format($quote->getGrandTotal(), 2);
                $payment = $order->getPayment();
                $preparedMessage = $order->getPreparedMessage();

                if ($payment->getIsTransactionPending()) {
                    $message = _('Authorizing amount of %s is pending approval on gateway.', $this->_formatPrice($order, $amount));
                    $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
                    if ($payment->getIsFraudDetected()) {
                        $status = \Magento\Sales\Model\Order::STATUS_FRAUD;
                    }
                } else {
                    if ($payment->getIsFraudDetected()) {
                        $state = \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW;
                        $message = __('Order is suspended as its authorizing amount %s is suspected to be fraudulent.', $this->_formatPrice($order, $amount));
                        $status = \Magento\Sales\Model\Order::STATUS_FRAUD;
                    } else {
                        $message = __('Authorized amount of %s.', $this->_formatPrice($order, $amount));
                    }
                }

                $transaction = $this->_objectManager->create('Magento\Sales\Model\Order\Payment\Transaction');
                $transaction->setOrderPaymentObject($payment)
                        ->setTxnId($params['paypal_transactionId'])
                        ->setTxnType(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_AUTH)
                        ->isFailsafe(false)
                        ->save();

                if ($order->isNominal()) {
                    $message = $this->_prependMessage(__('Nominal order registered.'));
                } else {
                    $message = $this->_prependMessage($message);
                    $message = $this->_appendTransactionToMessage($transaction, $message);
                }

                $order->setState($state, $status, $message);
                $order->save();
                //============================================
                //$order->sendNewOrderEmail();
                $finalAmttoInvoice = floatval($params['paypal_txn_amt']);
                $payment = $order->getPayment();
                $payment->registerCaptureNotification($finalAmttoInvoice);
                $payment->getOrder()->save();
            }

            if ($quote->getCustomerIsGuest()) {
                $order = $this->_objectManager->create("Magento\Sales\Model\Order")->loadByIncrementId($order_id);
            }

            $order->setCanSendNewEmailFlag(true);
            
            /*Send new order email*/
            $order->setEmailSent(Null);
            $orderSender =  $this->_objectManager->create('Magento\Sales\Model\Order\Email\Sender\OrderSender');
            $orderSender->send($order);
            
            $quote->setIsActive(0);
            $quote->save();


            foreach ($order->getAllVisibleItems() as $_item) {
                $this->saveLinkPurchaged($_item);
            }

            $this->saveLinkStatus($order);
             
            //----------Save device token ------------
            try{
                $token = isset($params['device_id'])? $params['device_id']: '';
                $token_type = isset($params['device_type'])? $params['device_type']: '';
                if (!empty($token) && !empty($token_type)) {
                    $this->setUserToken($params['email'], $token_type, $token, $order->getCustomerId());
                }
            } catch(\Exception $e){
                $this->logger->debug($e->__toString());
            }
            //----------End Save device token ----------- 

            $jsonArray['response'] = $order_id;
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
           // var_dump($e->__toString()); die;
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

}
