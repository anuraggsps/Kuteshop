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
class UpdateUserInfo extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $result = false;
            $params = $this->getRequest()->getParams();
            //$sess = $this->soapconnection();
            $customer = '';
            //------------validate current password-----------
            try {
                $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($params['cust_id']);
                $currPass = isset($params['current_pass'])? $params['current_pass']: '';
                $newPass = isset($params['password'])? $params['password']: '';
                if (!empty($currPass) && !empty($newPass)) {
                    
                    $oldPass = $customer->getPasswordHash();
                    if (strpos($oldPass, ':')) {
                        list($_salt, $salt) = explode(':', $oldPass);
                    } else {
                        $salt = false;
                    }
                    if ($customer->hashPassword($currPass, $salt) != $oldPass) {
                        $jsonArray['response'] = __('Invalid current password');
                        $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
                        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
                        die;
                    }
                    $customer->changePassword($newPass, false);
                    $customer->save();
                    if(!(isset($params['firstname']) || isset($params['lastname']) || isset($params['email']))){
                        $jsonArray['response'] = true;
                        $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
                        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
                        die;
                    }
                }
            } catch (\Exception $e) {
                $jsonArray['response'] = $e->getMessage();
                $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
                $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
                die;
            }

            //------------End validate current password-------
            $customerdata = [];
            if(isset($params['firstname']))
                $customerdata['firstname'] = $params['firstname'];
            
            if(isset($params['lastname']))
                $customerdata['lastname'] = $params['lastname'];
            
            if(isset($params['email'])):
                $customerdata['email'] = $params['email'];
            else:
                $customerdata['email'] = $customer->getEmail();
            endif;
                
            //if(isset($params['password']))
               // $customerdata['password'] = $params['password'];
            
            if(!empty($customerdata)){
                //$result = $sess['client']->call($sess['session_id'], 'customer.update', $customerdata);
                $custM = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($params['cust_id']);
                if($custM->getId()){
                    $customerdata['website_id'] = $this->_storeManager->getStore()->getWebsiteId(); 
                    $custM->setData($customerdata);
                    $custM->setId($params['cust_id']);
                    $custM->save();
                    $result = true; 
                }else{
                    throw new \Exception(__("Customer does not exists"));
                }
            }else{
                $result = __("Sorry! can not update customer");
            }
            $jsonArray['response'] = $result;
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

}
