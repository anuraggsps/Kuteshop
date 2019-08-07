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
class CustAddress extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $params = $this->getRequest()->getParams();
            $bill = array();
            $ship = array();
            $data['ship_to_same'] = 0;
            $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($params['cust_id']);
            $billingAddress = $customer->getPrimaryBillingAddress();
            if ($billingAddress)
                $bill = $billingAddress->getData();

            $shippingAddress = $customer->getPrimaryShippingAddress();
            if ($shippingAddress)
                $ship = $shippingAddress->getData();

            if (isset($bill['entity_id']) && isset($ship['entity_id']) && $bill['entity_id'] == $ship['entity_id'])
                $data['ship_to_same'] = 1;

            
            if(empty($ship['region']))
                $ship['region'] = '';
            
            if(empty($bill['region']))
                $bill['region'] = '';
            
            $data['billing'] = $bill;
            $data['shipping'] = $ship;

            $jsonArray['response'] = $data;
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

}
