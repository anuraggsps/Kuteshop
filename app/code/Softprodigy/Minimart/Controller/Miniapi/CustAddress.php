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
class CustAddress extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() {
        try {
			$request = $this->getRequest()->getContent();
			$params = json_decode($request, true);
            $bill = null;
            $ship = null;
            $data['ship_to_same'] = 0;
            $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($params['user_id']);
            //~ $billingAddress = $customer->getPrimaryBillingAddress();
            $billingAddress = $customer->getDefaultBillingAddress();
            if ($billingAddress)
                $bill = $billingAddress->getData();

            //~ $shippingAddress = $customer->getPrimaryShippingAddress();
            $shippingAddress = $customer->getDefaultShippingAddress();
            if ($shippingAddress)
                $ship = $shippingAddress->getData();
			
            if (isset($bill['entity_id']) && isset($ship['entity_id']) && $bill['entity_id'] == $ship['entity_id'])
                $data['ship_to_same'] = 1;

            
            //~ if(empty($ship['region']))
                //~ $ship['region'] = '';
            
            //~ if(empty($bill['region']))
                //~ $bill['region'] = '';
            if(!empty($bill['country_id']))
			{
                $bill['country_name'] = $this->_objectManager->create('\Magento\Directory\Model\Country')->load($bill['country_id'])->getName();
			}
                 $data['billing'] = $bill;
            //~ if(empty($bill)){
				//~ $data['billing'] = (object)array();
				
			//~ }
			if(!empty($ship['country_id']))
			{
				$ship['country_name'] = $this->_objectManager->create('\Magento\Directory\Model\Country')->load($ship['country_id'])->getName();
			}
			 $data['shipping'] = $ship;
			//~ if(empty($ship)){
							
				//~ $data['shipping'] = (object)array();
			//~ }
           
           
            
			$customerAddress = array();
			foreach ($customer->getAddresses() as $address)
			{
				$customerAddress[] = $address->toArray();
			}
			
			

			$x = 0;
			$data1 = [];
			$data['other_address'] =[];
			foreach ($customerAddress as $customerAddres) {
				if($bill['entity_id']!=$customerAddres['entity_id'] && $ship['entity_id']!=$customerAddres['entity_id']){
					$data1['entity_id'] = $customerAddres['entity_id']?$customerAddres['entity_id']:'';
					$data1['parent_id'] = $customerAddres['parent_id']?$customerAddres['parent_id']:'';
					$data1['is_active'] = $customerAddres['is_active']?$customerAddres['is_active']:'';
					$data1['city'] = $customerAddres['city']?$customerAddres['city']:'';
					$data1['company'] = $customerAddres['company']?$customerAddres['company']:'';
					$data1['country_id'] = $customerAddres['country_id']?$customerAddres['country_id']:'';
					if(!empty($data1['country_id']))
					{
						$data1['country_name'] = $this->_objectManager->create('\Magento\Directory\Model\Country')->load($data1['country_id'])->getName();
					}
					$data1['fax'] = $customerAddres['fax']?$customerAddres['fax']:'';
					$data1['firstname'] = $customerAddres['firstname']?$customerAddres['firstname']:'';
					$data1['lastname'] = $customerAddres['lastname']?$customerAddres['lastname']:'';
					$data1['middlename'] = $customerAddres['middlename']?$customerAddres['middlename']:'';
					$data1['postcode'] = $customerAddres['postcode']?$customerAddres['postcode']:'';
					$data1['prefix'] = $customerAddres['prefix']?$customerAddres['prefix']:'';
					$data1['region'] = $customerAddres['region']?$customerAddres['region']:'';
					$data1['region_id'] = $customerAddres['region_id']?$customerAddres['region_id']:'';
					$data1['street'] = $customerAddres['street']?$customerAddres['street']:'';
					$data1['suffix'] = $customerAddres['suffix']?$customerAddres['suffix']:'';
					$data1['telephone'] = $customerAddres['telephone']?$customerAddres['telephone']:'';
					$data1['customer_id'] = $customerAddres['customer_id']?$customerAddres['customer_id']:'';
					$data['other_address'][] = $data1;
				}
				$x++;
				
				
			}




				$jsonArray['data'] =  $data;
				$jsonArray['status'] =  'success';
				$jsonArray['status_code'] = 200; 
				$jsonArray['message'] =  "Get Data Succesfully";
        } catch (\Exception $e) {
				$jsonArray['data'] = null;
				$jsonArray['message'] = $e->getMessage();
				$jsonArray['status'] =  "failure";
				$jsonArray['status_code'] =  201;	
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
