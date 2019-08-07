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
	
        try {
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
				
				$cartData = [
					'cartItem' => [
					"quote_id" => $this->createQuote($baseurl,$param['user_id']),
					"sku" => $param['sku'],
					"qty" => $param['qty'],
					"product_option"=> array("extension_attributes"=>array("configurable_item_options"=>$optionsarray))
					]
				];
			// Here we are checking the product if it is bundlle product	
			}else if(isset($productType) && $productType == 'bundle' && $productType == \Magento\ConfigurableProduct\Model\Product\Type\Bundle::TYPE_CODE){
				
			// Here we are checking the product either virtual or simple    
			}else{
				$cartData = [
					'cartItem' => [
					"quote_id" => $this->createQuote($baseurl,$param['user_id']),
					"sku" => $param['sku'],
					"qty" => $param['qty'],
					]
				];
			} 
			$resultobj = $this->addItemInProduct($baseurl,$cartData,$param['user_id']);
			$jsonArray['data'] = $resultobj;
			$jsonArray['status'] =  'success';
			$jsonArray['status_code'] =  200;
			$jsonArray['msg'] = 'You added '. $resultobj->name . ' in your cart';
			$this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
			die;
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $messages = '';
            if ($this->__checkoutSession->getUseNotice(true)) {
                $message = $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage());
            } else {
                $messages = $e->getMessage();
            }
            $mnArray = explode(PHP_EOL, $messages);
            $ewmessages = array_unique($mnArray);
            $jsonArray['response'] = implode(', ', $ewmessages);
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t add this item to your shopping cart right now.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            $jsonArray['msg'] = __('We can\'t add this item to your shopping cart right now.');
        }
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
	public function addItemInProduct($baseurl,$cartData,$userid){
		$customerToken = $this->_tokenModelFactory->create();
        $tokenKey = $customerToken->createCustomerToken($userid)->getToken();
		$url =$baseurl.'index.php/rest/V1/carts/mine/items';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cartData));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer ".$tokenKey));
		$result = json_decode(curl_exec($ch));
		return $result;
	}

}
