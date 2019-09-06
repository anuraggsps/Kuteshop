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
class PaymentMethods extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() {
		$request = $this->getRequest()->getContent();
		$params = json_decode($request, true);
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
		$quote= $quoteFactory->create()->load($params['quote_id']);
		$user_id= $quote->getCustomerId();
		$data=$this->getPaymentInformation($user_id);

        $jsonArray['data']['payment_methods'] = $data->payment_methods;
        $jsonArray['status'] = 'success';
        $jsonArray['status_code'] = '200';
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
		die();
	}
	
	protected function getPaymentInformation($user_id){
		
		$customerToken = $this->_tokenModelFactory->create();
        $tokenKey = $customerToken->createCustomerToken($user_id)->getToken();
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseurl = $storeManager->getStore()->getBaseUrl();
		$curl = curl_init();
		curl_setopt_array($curl, array(
		CURLOPT_URL => $baseurl."rest/V1/carts/mine/payment-information",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => "",
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => "GET",
		CURLOPT_HTTPHEADER => array(
		"Authorization: Bearer ".$tokenKey
		),
		));
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err) {
		echo "cURL Error #:" . $err;
		} else {
		 return json_decode($response);
		}	

	}
	
	public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
			return null;
	}

	public function validateForCsrf(RequestInterface $request): ?bool{
			return true;
	}
}
