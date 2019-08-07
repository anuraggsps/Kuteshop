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
class UserInfo extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $params = $this->getRequest()->getParams();
            //$sess = $this->soapconnection();
            //$result = $sess['client']->call($sess['session_id'], 'customer.info', $params['cust_id']);
            //$result = Mage::getSingleton('customer/customer_api')->info($params['cust_id']);
            $custModel = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($params['cust_id']);
            
            $result = $custModel->getData();
            
            $data['firstname'] = $result['firstname'];
            $data['lastname'] = $result['lastname'];
            $data['email'] = $result['email'];
            $data['gender'] = $result['gender'];
            $data['customer_id'] = $result['entity_id'];
            
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
