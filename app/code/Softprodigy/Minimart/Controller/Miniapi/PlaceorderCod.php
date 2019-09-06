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
 * @author anurag
 */
class PlaceorderCod extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {

	public function execute() {
		$request = $this->getRequest()->getContent();
		$param = json_decode($request, true);
		try{
			
			$customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($param['user_id']);
			$shippingAddress = $customer->getDefaultShippingAddress();
			$orderData= '';
			if (!empty($shippingAddress->getData())){
				$ship = $shippingAddress->getData();
				//set shiiping address to quote
				$orderData=[
					'shipping_address' =>[
					'firstname'    => $ship['firstname'], //address Details
					'lastname'     => $ship['lastname'],
					'street' => $ship['street'],
					'city' => $ship['city'],
					'country_id' => $ship['country_id'],
					'region' => $ship['region'],
					'region_id' => $ship['region_id'],
					'postcode' => $ship['postcode'],
					'telephone' => $ship['telephone'],
					'fax' => $ship['fax'],
					'save_in_address_book' => 1
					]
				];
				//ends here
				//load quote by customer id
				$quote= $this->_objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($param['user_id']); 
				if($quote->getId() !=''){					
					$explode =  explode("_",$quote->getShippingAddress()->getShippingMethod());
					$shipping_method = end($explode);
					$quote->getBillingAddress()->addData($orderData['shipping_address']);
					$quote->getShippingAddress()->addData($orderData['shipping_address']);
                    $quote->save();
					$orderid = $this->Placeorder($param,$shipping_method);
					if(!is_object($orderid)){
						
						$cartId = $this->cartManagementInterface->createEmptyCart(); //Create empty cart
						$quote = $this->cartRepositoryInterface->get($cartId); // load empty cart quote
						$customer= $this->_customerRepository->getById($param['user_id']);
						$store=$this->_storeManager->getStore();
						$quote->setStore($store);
					    $quote->setCurrency();
						$quote->assignCustomer($customer);
						$quote->save();
						
						
						$order = $this->_objectManager->create('\Magento\Sales\Model\OrderRepository')->get($orderid);
						$jsonArray['status'] =  'success';
						$jsonArray['order_id'] =  $order->getIncrementId();;
						$jsonArray['status_code'] = 200; 
						$jsonArray['message'] = "Placed Order Succesfully";
					}else{
						$jsonArray['status'] =  'fail';
						$jsonArray['status_code'] = 201; 
						$jsonArray['message'] = $orderid->message;
					}
				}else{
					$jsonArray['status'] =  'success';
					$jsonArray['status_code'] = 200; 
					$jsonArray['message'] = "You do not have items in the cart";
				}
			}else{
				$jsonArray['status'] =  'success';
				$jsonArray['status_code'] = 200; 
				$jsonArray['message'] = "Please set the shipping Address";
			}
		} catch (\Exception $e) {
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
	
	public function Placeorder($param,$shipping_method){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseurl = $storeManager->getStore()->getBaseUrl();
		
		$payment_method = $param['payment_method'];
		//get user token by id
		$customerToken = $this->_tokenModelFactory->create();
        $tokenKey = $customerToken->createCustomerToken($param['user_id'])->getToken();
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $baseurl."rest/V1/carts/mine/payment-information",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "{\n \"paymentMethod\":{\"method\":\"$payment_method\"},\n \"shippingMethod\":\n    {\n      \"method_code\":\"$shipping_method\",\n\n      \"carrier_code\":\"$shipping_method\",\n      \"additionalProperties\":{}\n\n    }\n\n}\n",
		  CURLOPT_HTTPHEADER => array(
			"Authorization: Bearer ".$tokenKey,
			"Content-Type: application/json"
		  ),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		return json_decode($response);
	}
}		
