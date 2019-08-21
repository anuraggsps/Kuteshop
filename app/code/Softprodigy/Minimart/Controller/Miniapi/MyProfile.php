<?php

namespace Softprodigy\Minimart\Controller\Miniapi;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

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
class MyProfile extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {
    public function execute() {
        try {  
			$request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
			$user_id = $param['user_id'];
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($user_id);
			if (!$customer->getId()) {
				$jsonArray['data'] = [];
				$jsonArray['status'] = "fail";
				$jsonArray['status_code'] = 201;
				$jsonArray['message'] = __('Invalid customer');
				$this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
				die;
			}
				
				$customerObj = $objectManager->create('Magento\Customer\Model\Customer')->load($user_id);
				$customerAddress = array();	
				foreach($customerObj->getAddresses() as $address){
				   $customerAddress[] = $address->toArray();
				}
				$order_data['is_customer_has_address'] =1;
				if(empty($customerAddress)){
					$order_data['is_customer_has_address'] =0;
				}
				
				foreach($customerAddress as $customerAddres){   
					$order_data['telephone'] = $customerAddres['telephone'];
					$order_data['city'] = $customerAddres['city'];
					$order_data['city'] = $customerAddres['city'];
					$order_data['state'] = $customerAddres['region'];
					$order_data['pincode'] = $customerAddres['postcode'];
					$order_data['country'] = $customerAddres['country_id'];
					$order_data['country_name'] =  $this->_objectManager->create('\Magento\Directory\Model\Country')->load($customerAddres['country_id'])->getName();
				}
				$custModel = $this->_objectManager->get('Magento\Customer\Model\Customer');
				$cuCollection = $custModel->getCollection();
				$cuCollection->addAttributeToFilter('entity_id', $user_id);
				foreach($cuCollection->getData() as $key=>$value){
					$order_data['firstname'] = $value['firstname']?$value['firstname']:''; 
					$order_data['lastname'] = $value['lastname']?$value['lastname']:'';
					$order_data['dob'] = $value['dob']?$value['dob']:''; 
					$order_data['email'] = $value['email']; 
				}
				$jsonArray['data'] = $order_data;
				$jsonArray['status'] = "success";
				$jsonArray['status_code'] = 200;
				$jsonArray['message'] = "Get data successfully";
        } catch (\Exception $e) {
           $jsonArray['data'] = "";
		   $jsonArray['message'] = "Something went wrong";
		   $jsonArray['status'] = "failure";
		   $jsonArray['status_code'] = 201;
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }
    
	public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool{
        return true;
    }
    

}
