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
class SetQuoteAddress extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {

            $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->loadActive($this->getRequest()->getParam('quote_id', null));
            if ($quote->getId()) {
                $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($this->getRequest()->getParam('cust_id', null));

                $quoteShippingAddress = $this->_objectManager->create('Magento\Quote\Model\Quote\Address');
                $quoteBillingAddress = $this->_objectManager->create('Magento\Quote\Model\Quote\Address');
                $params = $this->getRequest()->getParams();

                if (isset($params['cust_id']) and ! empty($params['cust_id']))
                    $mode = 'customer';
                else
                    $mode = 'guest';

                $billingonly = false;

                if ((!isset($params['use_for_shipping']) or empty($params['use_for_shipping'])) and ( !isset($params['only_ship']) or empty($params['only_ship']))) {
                    $billingonly = true;
                }

                if (isset($params['region']) and is_numeric($params['region'])) {
                    $params['region_id'] = $params['region'];
                }

                $arrAddressesBilling = [];
                if (!isset($params['only_ship'])) {
                    $arrAddressesBilling = array(
                        "mode" => "billing",
                        "firstname" => $params['firstname'],
                        "lastname" => $params['lastname'],
                        "company" => "",
                        "street" => array($params['street']),
                        "city" => $params['city'],
                        "region" => $params['region'],
                        "postcode" => $params['zip'],
                        "country_id" => $params['country_id'],
                        "telephone" => $params['phone'],
                        "fax" => $params['fax'],
                        "use_for_shipping" => isset($params['use_for_shipping']) ? $params['use_for_shipping'] : '0',
                        'save_in_address_book' => 0
                    );

                    $entityid = '';
                    if (isset($params['cust_id']) and ! empty($params['cust_id'])) {
                        $entityid = $this->getRequest()->getParam('addr_id', false);
                        if (!empty($entityid)) {
                            $addrCollection = Mage::getModel('customer/address')->getCollection();
                            $addrCollection->addAttributeToSelect('*');
                            $addrCollection->addAttributeToFilter('entity_id', array('in' => array($entityid)));
                            $addrCollection->addAttributeToFilter('parent_id', $params['cust_id']);
                            $address = $addrCollection->getFirstItem();
                            if ($address->getId() and $address->getId() == $entityid) {
                                $arrAddressesBilling['parent_id'] = $params['cust_id'];
                                $address->setData($arrAddressesBilling);
                                $address->setId($entityid);
                                $address->save();
                            }
                        }
                    }
                    if (!empty($entityid)) {
                        $arrAddressesBilling["entity_id"] = $entityid;
                    }
                }

                $arrAddressesShipping = [];
                $params['use_for_shipping'] = isset($params['use_for_shipping']) ? $params['use_for_shipping'] : 0;
                if ($params['use_for_shipping'] == 0 and $billingonly == false) {
                    try {
                        $arrAddressesShipping = array(
                            "mode" => "shipping",
                            "firstname" => $params['s_firstname'],
                            "lastname" => $params['s_lastname'],
                            "company" => "",
                            "street" => array($params['s_street']),
                            "city" => $params['s_city'],
                            "region" => $params['s_region'],
                            "postcode" => $params['s_zip'],
                            "country_id" => $params['s_country_id'],
                            "telephone" => $params['s_phone'],
                            "fax" => $params['s_fax'],
                            "is_default_shipping" => 0,
                            'save_in_address_book' => 0
                        );
                    } catch (\Exception $e) {

                        throw new \Exception(__("Please fill valid shipping address."));
                    }
                }

                $params['use_for_shipping'] = isset($params['use_for_shipping']) ? (bool) $params['use_for_shipping'] : false;
                $params['only_ship'] = isset($params['only_ship']) ? (bool) $params['only_ship'] : false;


                //  var_dump($arrAddressesBilling);

                if (empty($arrAddressesShipping) and isset($params['use_for_shipping']) and $params['use_for_shipping'] == 1) {
                    $arrAddressesShipping = $arrAddressesBilling;
                    unset($arrAddressesShipping['mode']);
                    unset($arrAddressesShipping['use_for_shipping']);
                }
                $cBillingAddr = array();
                if (!isset($params['only_ship']) or empty($params['only_ship'])) {
                    $cBillingAddr = $this->exportCustomerAddress($arrAddressesBilling);
                }
                $cShippingAddr = array();
                if ($billingonly == false) {

                    $entityid = '';
                    if (isset($params['cust_id']) and ! empty($params['cust_id'])) {
                        $entityid = $this->getRequest()->getParam('addr_id', false);
                        if (!empty($entityid)) {
                            $addrCollection = Mage::getModel('customer/address')->getCollection();
                            $addrCollection->addAttributeToSelect('*');
                            $addrCollection->addAttributeToFilter('entity_id', array('in' => array($entityid)));
                            $addrCollection->addAttributeToFilter('parent_id', $params['cust_id']);
                            $address = $addrCollection->getFirstItem();
                            if ($address->getId() and $address->getId() == $entityid) {
                                $arrAddressesShipping['parent_id'] = $params['cust_id'];
                                $address->setData($arrAddressesBilling);
                                $address->setId($entityid);
                                $address->save();
                            }
                        }
                    }
                    
                    if (!empty($entityid)) {
                        $arrAddressesShipping["entity_id"] = $entityid;
                    }


                    $cShippingAddr = $this->exportCustomerAddress($arrAddressesShipping);

                    $quoteShippingAddress->importCustomerAddressData($cShippingAddr);
                    $sipEmail = isset($params['s_email']) ? $params['s_email'] : $params['email'];
                    $quoteShippingAddress->setEmail($sipEmail);
                }

                if (!isset($params['only_ship']) or empty($params['only_ship'])) {
                    $quoteBillingAddress->importCustomerAddressData($cBillingAddr);
                    $quoteBillingAddress->setEmail($params['email']);
                }

                if ($billingonly == false) {
                    if (($validateRes = $quoteShippingAddress->validate()) !== true) {
                        throw new \Exception(__("Customer's address data is not valid."));
                    }
                }

                if (!isset($params['only_ship']) or empty($params['only_ship'])) {
                    if (($validateRes = $quoteBillingAddress->validate()) !== true) {
                        throw new \Exception(__("Customer's address data is not valid."));
                    }
                }


                $quote->setIsMultiShipping(false);
                if ($customer->getId()):
                    $newCustomerDataObject = $this->_objectManager->create("Magento\Customer\Api\Data\CustomerInterface");

                    $onbejctHelper = $this->_objectManager->create('Magento\Framework\Api\DataObjectHelper');
                    $onbejctHelper->populateWithArray(
                            $newCustomerDataObject, $customer->getData(), '\Magento\Customer\Api\Data\CustomerInterface'
                    );
                    if (!isset($params['only_ship']) or empty($params['only_ship'])) {
                        $quote->assignCustomerWithAddressChange($newCustomerDataObject, $quoteBillingAddress, $quoteShippingAddress);
                    } else {
                        $quote->assignCustomerWithAddressChange($newCustomerDataObject, null, $quoteShippingAddress);
                    }
                endif;

                $quote->setCheckoutMethod($mode);
                if (!isset($params['only_ship']) or empty($params['only_ship'])) {
                    $quote->setBillingAddress($quoteBillingAddress);
                }
                if ($billingonly == false) {
                    $quote->setShippingAddress($quoteShippingAddress);
                }
            } else {
                throw new \Exception(__("Quote does not exist."));
            }

            try {

                $quote->collectTotals()->save();
                try {
                    //$quoteShippingAddress->save();
                    //$quoteBillingAddress->save();  
                } catch (\Exception $ex) {
                    //var_Dump($ex->getMessage()); die;
                    $this->logger->debug($ex->getMessage());
                }
            } catch (\Exception $ex) {
                var_dump($ex->__toString());
                die;
                throw new \Exception(__("Customer address is not set."));
            }


            $jsonArray['response'] = true;
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
            //var_dump($e->__toString());
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

}
