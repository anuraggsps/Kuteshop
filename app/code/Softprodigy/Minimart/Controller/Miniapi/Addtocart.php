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

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Description of Homepage
 *
 * @author mannu
 */
class Addtocart extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {

    public function execute() {
        $request = $this->getRequest()->getContent();
		$param = json_decode($request, true);
	    $jsonArray = [];
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseurl = $storeManager->getStore()->getBaseUrl();
	
        //~ try {
			$productId = $param['prod_id'];
			$product = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);
			$productType = $product->getTypeID();
			
			// Here we are checking the product if it is configurable
			$cartData = array();
			if(isset($productType) && $productType =='configurable' && $productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
				$optionsarray =array();
				$x=0;
				foreach($param['attributes'] as $key=>$value){
					$optionsarray[$x]['option_id'] = $value['attribute_id'];
					$optionsarray[$x]['option_value'] = $value['value_index'];
					$x++;
				}
				
				if(isset($param['is_login']) && $param['is_login'] == 0){
					$cartData = [
						'cartItem' => [
						"quote_id" => $this->GetGuestCartToken(),
						"sku" => $param['sku'],
						"qty" => $param['qty'],
						"product_option"=> array("extension_attributes"=>array("configurable_item_options"=>$optionsarray))
						]
					];
				}else{
					$cartData = [
						'cartItem' => [
						"quote_id" => $this->createQuote($baseurl,$param['user_id']),
						"sku" => $param['sku'],
						"qty" => $param['qty'],
						"product_option"=> array("extension_attributes"=>array("configurable_item_options"=>$optionsarray))
						]
					];
				}

			// Here we are checking the product if it is bundlle product	
			}else if(isset($productType) && $productType == 'bundle' && $productType == \Magento\ConfigurableProduct\Model\Product\Type\Bundle::TYPE_CODE){
				
			// Here we are checking the product either virtual or simple    
			}else{
				
				if(isset($param['is_login']) && $param['is_login'] == 0){
					if(isset($param['token']) && $param['token'] != ''){
						$tokens = $param['token'];
						$cartData = [
							'cartItem' => [
							"quote_id" => $tokens,
							"sku" => $param['sku'],
							"qty" => $param['qty'],
							]
						];
					}else{
						$cartData = [
							'cartItem' => [
							"quote_id" => $this->GetGuestCartToken(),
							"sku" => $param['sku'],
							"qty" => $param['qty'],
							]
						];
					}
				}else{
					$cartData = [
						'cartItem' => [
						"quote_id" => $this->createQuote($baseurl,$param['user_id']),
						"sku" => $param['sku'],
						"qty" => $param['qty'],
						]
					];
				}
				

			} 
			
			//functionality for without login user add to cart
				if(isset($param['is_login']) && $param['is_login'] == 0){
					$resultobj = $this->AddTogUESTCart($param,$cartData);
					$name = $resultobj['name'];
				}else{
					$resultobj = (array)$this->addItemInProduct($baseurl,$cartData,$param);
					if(!isset($resultobj['price'])){
						$resultobj = $this->UpdateUserCart($cartData,$param['user_id'],$baseurl,$resultobj['item_id'],$param);
					}
					$name = $resultobj['name'];
				}
			//ends here

			$jsonArray['data'] = $resultobj;
			$jsonArray['status'] =  'success';
			$jsonArray['status_code'] =  200;
			$jsonArray['msg'] = 'You added '. $name . ' in your cart';
			$this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
			die;
            
        //~ } catch (\Magento\Framework\Exception\LocalizedException $e) {
            //~ $messages = '';
            //~ if ($this->__checkoutSession->getUseNotice(true)) {
                //~ $message = $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage());
            //~ } else {
                //~ $messages = $e->getMessage();
            //~ }
            //~ $mnArray = explode(PHP_EOL, $messages);
            //~ $ewmessages = array_unique($mnArray);
            //~ $jsonArray['response'] = implode(', ', $ewmessages);
        //~ } catch (\Exception $e) {
            //~ $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            //~ $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            //~ $jsonArray['msg'] = __('We can\'t add this item to your shopping cart right now.');
        //~ }
        $data = [];
        $jsonArray['data'] = $data;
        $jsonArray['status'] = 'fail';
        $jsonArray['status_code'] = '201';
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool{
        return true;
    }
   
	public function createQuote($baseurl,$userid){
		$customerToken = $this->_tokenModelFactory->create();
        $tokenKey = $customerToken->createCustomerToken($userid)->getToken();
		$url =$baseurl.'index.php/rest/V1/carts/mine';
		$chQuote = curl_init($url);
		curl_setopt($chQuote, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer ".$tokenKey));
		curl_setopt($chQuote, CURLOPT_RETURNTRANSFER, true);
		$quote = json_decode(curl_exec($chQuote));	
	
		return $quote->id;
	}
	public function addItemInProduct($baseurl,$cartData,$param){
		$customerToken = $this->_tokenModelFactory->create();
        $tokenKey = $customerToken->createCustomerToken($param['user_id'])->getToken();
		$url =$baseurl.'index.php/rest/V1/carts/mine/items';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cartData));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer ".$tokenKey));
		$result = json_decode(curl_exec($ch));
		
	   //get quote id from token and set currency code in the quote table according to their param or if user has set the code 
		if(isset($param["user_id"]) && $param["user_id"]!= ''){
			$customer1 =  $this->_customerRepository->getById($param["user_id"]);
			$curr = $customer1->getCustomAttribute('currency');
			if(!empty($curr)){ 
				if($curr->getValue() !=''){
					$this->SetQuoteCurrencyCode($curr->getValue(),$cartData['cartItem']['quote_id']);
				}else if(isset($param['currency']) && $param['currency'] !=''){
					$this->SetQuoteCurrencyCode($param['currency'],$cartData['cartItem']['quote_id']);
				}
			
			}else{
				if(isset($param['currency']) && $param['currency'] !=''){
					$this->SetQuoteCurrencyCode($param['currency'],$cartData['cartItem']['quote_id']);
				}
			}
			
		}	

		return $result;
	}
	// functionality for guest user
		//create guest-cart token
			public function GetGuestCartToken(){
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
				$baseurl = $storeManager->getStore()->getBaseUrl();
				$curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $baseurl."rest/V1/guest-carts",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_HTTPHEADER => array(
					"Cache-Control: no-cache",
					"Content-Type: application/json",
				  ),
				));
				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				return json_decode($response);
			}
			public function AddTogUESTCart($param,$cartData){
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
				$baseurl = $storeManager->getStore()->getBaseUrl();
				return $this->addItemInProductForGuestUsers($param,$baseurl,$cartData);
			}
			public function addItemInProductForGuestUsers($param,$baseurl,$cartData){
				 $token = $cartData['cartItem']['quote_id'];
				 $curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $baseurl."rest/V1/guest-carts/".$token."/items",
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "POST",
				  CURLOPT_POSTFIELDS => json_encode($cartData),
				  CURLOPT_HTTPHEADER => array(
					"Cache-Control: no-cache",
					"Content-Type: application/json",
					"Postman-Token: ff073211-c355-4d8d-8465-39edfa110b90"
				  ),
				));

				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				//get quote id from token and set currency code in the quote table according to their param or if user has set the code 
					if(isset($param['currency']) && $param['currency'] !=''){
						$this->SetQuoteCurrencyCode($param['currency'],$this->GetQuoteIDFromGuestToken($token));
					}
				
				return array_merge(array("token"=>$token),(array)json_decode($response));
			}
			public function UpdateUserCart($cartData,$user_id,$baseurl,$item_id,$param){
			
				$customerToken = $this->_tokenModelFactory->create();
				$tokenKey = $customerToken->createCustomerToken($user_id)->getToken();
				$curl = curl_init();
				curl_setopt_array($curl, array(
				  CURLOPT_URL => $baseurl."index.php/rest/V1/carts/mine/items/".$item_id,
				  CURLOPT_RETURNTRANSFER => true,
				  CURLOPT_ENCODING => "",
				  CURLOPT_MAXREDIRS => 10,
				  CURLOPT_TIMEOUT => 30,
				  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				  CURLOPT_CUSTOMREQUEST => "PUT",
				  CURLOPT_POSTFIELDS => json_encode($cartData),
				  CURLOPT_HTTPHEADER => array(
					"Authorization: Bearer ".$tokenKey,
					"Cache-Control: no-cache",
					"Content-Type: application/json",
					"Postman-Token: e84cba18-f11a-4845-99cf-c2de157a0700"
				  ),
				));
				$response = curl_exec($curl);
				$err = curl_error($curl);
				curl_close($curl);
				$results = (array)json_decode($response);
				
				
			  
		   //get quote id from token and set currency code in the quote table according to their param or if user has set the code 
			if(isset($param["user_id"]) && $param["user_id"]!= ''){
				$customer1 =  $this->_customerRepository->getById($param["user_id"]);
				$curr = $customer1->getCustomAttribute('currency');
				if(!empty($curr)){ 
					if($curr->getValue() !=''){
						$this->SetQuoteCurrencyCode($curr->getValue(),$cartData['cartItem']['quote_id']);
					}else if(isset($param['currency']) && $param['currency'] !=''){
						$this->SetQuoteCurrencyCode($param['currency'],$cartData['cartItem']['quote_id']);
					}
				
				}else{
					if(isset($param['currency']) && $param['currency'] !=''){
						
						$this->SetQuoteCurrencyCode($param['currency'],$cartData['cartItem']['quote_id']);
					}
				}
				
			}
				
				
				if (isset($results['message'])) {
				  return $results['msg'] = $results['message'];
				} else {
				  return $results;
				}
			}
			
		    public function GetQuoteIDFromGuestToken($token){
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
				$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
				$connection = $resource->getConnection();
				$tableName = $resource->getTableName('quote_id_mask'); //gives table name with prefix

				//Select Data from table
				$sql = "Select * FROM " . $tableName ." where masked_id = '$token'";
				$result = $connection->fetchAll($sql);
				return $result[0]['quote_id'] ;
			}	
			
			
	//ends here
}
