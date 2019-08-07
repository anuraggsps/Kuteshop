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
class GetMethods extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {

            $data = [];

            //used for paypal exp
            $data['username'] = "";
            $data['password'] = "";
            $data['signature'] = "";
            $data['currency_code'] = "";
            $data['payment_action'] = "";
            $data['sandbox'] = "";

            $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->loadActive($this->getRequest()->getParam('quote_id', null));
            if ($quote->getId()) {

                $zip = $quote->getShippingAddress()->getPostcode();
                $this->cart->setQuote($quote);

                if ($this->__helper->getStoreConfig('payment/paypal_express/active')) {
                    $show_paypal = 1;

                    $store = $quote->getStoreId();
                    $methods = $this->_objectManager->get("Magento\Payment\Helper\Data")->getStoreMethods($store, $quote);

                    foreach ($methods as $method) {
                        if ($method->getCode() == 'paypal_express') {
                            if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
                                $show_paypal = 0;
                            }

                            if (!$method->canUseForCurrency($this->_storeManager->getStore($quote->getStoreId())->getBaseCurrencyCode())) {
                                $show_paypal = 0;
                            }

                            /**
                             * Checking for min/max order total for assigned payment method
                             */
                            $total = $quote->getBaseGrandTotal();
                            $minTotal = $method->getConfigData('min_order_total');
                            $maxTotal = $method->getConfigData('max_order_total');

                            if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
                                $show_paypal = 0;
                            }
                            if ($total <= 0) {
                                $show_paypal = 0;
                            }

                            if ($show_paypal) {
                                /* $methodsResult[0] = array(
                                  'code' => $method->getCode(),
                                  'title' => $method->getTitle(),
                                  'cc_types' => '',
                                  ); */
                                $data['username'] = $this->__helper->getStoreConfig('paypal/wpp/api_username');
                                $data['password'] = $this->__helper->getStoreConfig('paypal/wpp/api_password');
                                $data['signature'] = $this->__helper->getStoreConfig('paypal/wpp/api_signature');
                                $data['currency_code'] = $this->_storeManager->getStore()->getCurrentCurrencyCode();
                                $data['payment_action'] = $this->__helper->getStoreConfig("payment/paypal_express/payment_action");
                                $data['sandbox'] = $this->__helper->getStoreConfig("paypal/wpp/sandbox_flag");

                                //$methodsResult[0]['data'] = $data;
                            }
                        }
                    }
                }

                //$sess = $this->soapconnection();
                //$ship_method = $sess['client']->call($sess['session_id'], 'cart_shipping.list', $params['quote_id']);
                //$model = Mage::getModel("checkout/cart_shipping_api");
                $ship_method = [];
                try {
                    $ship_method = $this->getQuoteShippingMethodsList($quote);

                    if (!is_array($ship_method)) {
                        $ship_method = [];
                    }
                } catch (\Exception $ex) {

                    $this->logger->debug($ex->getMessage());
                }


                $newShipArray = [];

                foreach ($ship_method as $_smethod) {
                    $_smethod['amount'] = number_format($_smethod['amount'], 2);
                    $_smethod['base_amount'] = number_format($_smethod['base_amount'], 2);

                    $_smethod['price_excl_tax'] = number_format($_smethod['price_excl_tax'], 2);
                    $_smethod['price_incl_tax'] = number_format($_smethod['price_incl_tax'], 2);

                    $newShipArray[] = $_smethod;
                }

                $result['ship_method'] = $newShipArray;

                //$result['payment'] = $sess['client']->call($sess['session_id'], 'cart_payment.list', $params['quote_id']);
                //var_dump($result['payment']); die;

                $payment_methods = [];

                $activemethods = $this->getActivPaymentMethod($quote);

                $payment_methods = $activemethods['methods'];
                $isPayu = $activemethods['is_payu'];

                $payuInfo = [];
                if ($isPayu === true) {
                    $payuInfo['key'] = $this->__helper->getStoreConfig('payment/payucheckout_shared/key');
                    $payuInfo['salt'] = $this->__helper->getStoreConfig('payment/payucheckout_shared/salt');
                    $demoMode = $this->__helper->getStoreConfig('payment/payucheckout_shared/demo_mode');
                    $payuInfo['sandbox'] = empty($demoMode) ? false : true;
                    $payuInfo['addrand'] = empty($demoMode) ? true : false;
                    $payuInfo['success_url'] = $this->_storeManager->getStore()->getBaseUrl() . 'payucheckout/shared/success/';
                    $payuInfo['failure_url'] = $this->_storeManager->getStore()->getBaseUrl() . 'payucheckout/shared/failure/';
                } else {
                    $payuInfo['key'] = "";
                    $payuInfo['salt'] = "";
                    $payuInfo['sandbox'] = "";
                    $payuInfo['addrand'] = "";
                    $payuInfo['success_url'] = "";
                    $payuInfo['failure_url'] = "";
                }
                /* foreach($result['payment'] as $payment)
                  {
                  $payment['data'] = '';
                  $payment_methods[] = $payment;
                  } */

                $result['payment'] = $payment_methods;

                $jsonArray['response'] = $result;
                $jsonArray['paypal_info'] = $data;
                $jsonArray['payu_info'] = $payuInfo;
                $jsonArray['zip'] = $zip;
                $jsonArray['returnCode'] = ['result' => 1, 'resultText' => 'success'];
            } else {
                throw new \Exception(__("Quote does not exist."));
            }
        } catch (\Exception $ex) {
            $jsonArray['response'] = $ex->getMessage();
            $jsonArray['returnCode'] = ['result' => 0, 'resultText' => 'fail'];
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

}
