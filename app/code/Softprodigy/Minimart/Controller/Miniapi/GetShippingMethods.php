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
class GetShippingMethods extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{
	
		public function execute() {
			try{
				
				$request = $this->getRequest()->getContent();
				$param = json_decode($request, true);
				$arraval = $this->GetShippingsMethods($param);
				$datat = [];
				foreach($arraval as $value){
					
					//~ if(is_object($value)){
						if($value->carrier_code != 'vendor_rates'){
							//~ $data['carrier_code'] = $value->carrier_code;
							$data['method_code'] = $value->method_code;
							

							//~ $data['carrier_title'] = $value->carrier_title;
							$data['method_title'] = $value->method_title;
							$data['amount'] = strval($value->amount);
							//~ $data['base_amount'] = $value->base_amount;
							//~ $data['available'] = $value->available;
							//~ $data['price_excl_tax'] = $value->price_excl_tax;
							//~ $data['price_incl_tax'] = $value->price_incl_tax;
							$datat[] = $data;
						}
					//~ }
				}
				
				
				
				$jsonArray['data'] = $datat;
				$jsonArray['show_title'] = $arraval[0]->carrier_title;
				$jsonArray['status'] =  'success';
				$jsonArray['status_code'] = 200; 
				$jsonArray['message'] =  "Get Data Succesfully";
					
			}catch (\Exception $e) {
				$jsonArray['data'] = null;
				$jsonArray['message'] = 'Address id does not exist.';//$e->getMessage();
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
		
		public function GetShippingsMethods($param){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			$baseurl = $storeManager->getStore()->getBaseUrl();
			
			$customerToken = $this->_tokenModelFactory->create();
			$tokenKey = $customerToken->createCustomerToken($param['user_id'])->getToken();
			$param = $param['address_id'];
			$curl = curl_init();

			curl_setopt_array($curl, array(
			  CURLOPT_URL => $baseurl."rest/V1/carts/mine/estimate-shipping-methods-by-address-id",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => "{\"addressId\":\"$param\"}",
			  CURLOPT_HTTPHEADER => array(
				"Authorization: Bearer ".$tokenKey,
				"Content-Type: application/json"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);

			curl_close($curl);


			$datat = [];
			$x= 0;
			return	$arr5a= json_decode($response); 
	
		}
		
		
    }    
