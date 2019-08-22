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
class UpdateCart extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() {
            $request = $this->getRequest()->getContent();
            $param = json_decode($request, true);
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			$baseurl = $storeManager->getStore()->getBaseUrl();
			$jsonArray = []; 
        try {
			if(isset($param['token']) && $param['token'] !=''){
				$cartData = [
					'cartItem' => [
					"quote_id" => $param['quote_id'],
					"sku" => $param['sku'],
					"qty" => $param['qty'],
					]
				];
				$result = $this->UpdateGuestUserCart(json_encode($cartData),$param['token'],$baseurl,$param['item_id']);
			}else{
				$cartData = [
					'cartItem' => [
					"quote_id" => $param['quote_id'],
					"sku" => $param['sku'],
					"qty" => $param['qty'],
					]
				];
				$result = $this->UpdateUserCart(json_encode($cartData),$param['user_id'],$baseurl,$param['item_id']);
			}
			
			$message =  "Get Data Succesfully";
			$status_code = '200';
			$status = 'success';
		if(!is_array($result)){
			$message = $result;
			$status_code = '201';
			$status = 'fail';
			$result = null;
		}	
			
			$jsonArray['data'] = $result;
			$jsonArray['status'] = 'success';
			$jsonArray['status_code'] =$status_code ;
			$jsonArray['message'] =  $message;
			
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t update the shopping cart.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            
            $jsonArray['data'] = null;
			$jsonArray['status'] = 'fail';
			$jsonArray['status_code'] ='201';
			$jsonArray['message'] = __('We can\'t update the shopping cart.');
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
    
    public function UpdateGuestUserCart($cartData,$token,$baseurl,$item_id){
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $baseurl."index.php/rest/V1/guest-carts/".$token."/items/".$item_id,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "PUT",
		  CURLOPT_POSTFIELDS =>$cartData,
		  CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			"Postman-Token: fa835ab4-771e-49c2-829b-9425bb77c1f7"
		  ),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		if ($err) {
		 return $err['message'];
		} else {
		  return (array)json_decode($response);
		}
	}
	public function UpdateUserCart($cartData,$user_id,$baseurl,$item_id){
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
		  CURLOPT_POSTFIELDS => $cartData,
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

		if (isset($results['message'])) {
		  return $results['msg'] = $results['message'];
		} else {
		  return $results;
		}
	}	 
	
	
	
}
