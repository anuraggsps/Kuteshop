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
class ViewCart extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {
	
	public function execute(){
		$request = $this->getRequest()->getContent();
		$param = json_decode($request, true);
		//functionality for guest user
		if(isset($param['token']) && $param['token'] !=''){
			$guestitemresult ='';
			$guestitemresults = $this->GetitemsForGuestUsers($param['token']);

			if(!is_object($guestitemresults)&&!empty($guestitemresults) ){
					$data =[];
					$x = 0;
					
					foreach($guestitemresults as $key=>$value){
						$data1[$x]['item_id'] = $value->item_id;
						$data1[$x]['product_sku'] = $value->sku;
						$data1[$x]['qty'] = $value->qty;
						$data1[$x]['name'] = $value->name;
						$data1[$x]['price'] = $value->price;
						$data1[$x]['product_type'] = $value->product_type;
						$data1[$x]['image'] = $this->getItemImage($value->sku);
						$data1[$x]['quote_id'] = $value->quote_id;
						$data1[$x]['is_configurable'] = 0;
						$data1[$x]['optiondata'] = [];
						if($value->product_type == 'configurable'){
							$data1[$x]['is_configurable'] = 1;
							
							$res = $value->product_option->extension_attributes->configurable_item_options;
							$data1[$x]['optiondata'] = $this->GetProductOption($res,$value->item_id);
						}
						$x++;
						$data["products"] = $data1;
					}
					$storeid = $this->_storeManager->getStore()->getId();
					$quoteModel = $this->_objectManager->create('Magento\Quote\Model\Quote')->setStoreId($storeid);
					$quote      = $quoteModel->loadActive($param['token']);
					
					$totals = $quote->getTotals();//Total object
					if (isset($totals['discount']) && $totals['discount']->getValue()) {
						$discount = $totals['discount']->getValue(); //Discount value if applied
						$data['is_discount'] = 1;
					} else {
						$data['is_discount'] = 0;
						$discount = "0";
						
					}
					if (isset($totals['tax']) && $totals['tax']->getValue()) {
						$tax = $totals['tax']->getValue(); //Tax value if present
					} else {
						$tax = 0.00;
					}

					if (isset($totals['shipping']) && $totals['shipping']->getValue()) {
						$ship_method = $totals['shipping']->getValue(); //shipping if present
					} else {
						$ship_method = 0.00;
					}
					
					$data['grandtotal'] = (string)round($quote->getGrandTotal(), 2);
					$data['subtotal'] = (string)round($quote->getSubtotal(), 2);
					$data['discount'] = (string)round($discount, 2);
					$data['deposit_amount'] = (string)round($quote->getFee(), 2);
					$data['tax'] = (string)round($tax, 2);
					$data['ship_cost'] = (string)round($ship_method, 2);
					$data['coupon_applied'] = $quote->getCouponCode()?'':"";
					
					$jsonArray['data'] = $data;
					$jsonArray['status'] = 'success';
					$jsonArray['status_code'] = 200;
					$this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
					die;
					
			
			}else{
				$jsonArray['data'] = null;
				$jsonArray['status'] = 'success';
				$jsonArray['status_code'] = 200;
				$jsonArray['msg'] = 'You Do not have any product in your cart';
				$this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
				die;
			}
		}else{
			$result = $this->GetItems($param['user_id']);
			if(!empty($result)){
				if(is_object($result)){
					$jsonArray['data'] = [];
					$jsonArray['status'] = 'success';
					$jsonArray['status_code'] = 200;
					$jsonArray['msg'] = $result->message;
					$this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
					die;
				}else{
					$quoteid = $result[0]->quote_id;
					$data =[];
					$x = 0;
					foreach($result as $key=>$value){
						$data1[$x]['item_id'] = $value->item_id;
						$data1[$x]['product_sku'] = $value->sku;
						$data1[$x]['qty'] = $value->qty;
						$data1[$x]['name'] = $value->name;
						$data1[$x]['price'] = $value->price;
						$data1[$x]['product_type'] = $value->product_type;
						$data1[$x]['image'] = $this->getItemImage($value->sku);
						$data1[$x]['quote_id'] = $value->quote_id;
						$data1[$x]['is_configurable'] = 0;
						$data1[$x]['optiondata'] = [];
						if($value->product_type == 'configurable'){
							$data1[$x]['is_configurable'] = 1;
							$res = $value->product_option->extension_attributes->configurable_item_options;
							$data1[$x]['optiondata'] = $this->GetProductOption($res,$value->item_id);
						}
						$x++;
						$data["products"] = $data1;
					}
					$storeid = $this->_storeManager->getStore()->getId();
					$quoteModel = $this->_objectManager->create('Magento\Quote\Model\Quote')->setStoreId($storeid);
					$quote      = $quoteModel->loadActive($quoteid);
					
					$totals = $quote->getTotals();//Total object
					if (isset($totals['discount']) && $totals['discount']->getValue()) {
						$discount = $totals['discount']->getValue(); //Discount value if applied
						$data['is_discount'] = 1;
					} else {
						$data['is_discount'] = 0;
						$discount = "0";
						
					}
					if (isset($totals['tax']) && $totals['tax']->getValue()) {
						$tax = $totals['tax']->getValue(); //Tax value if present
					} else {
						$tax = 0.00;
					}

					if (isset($totals['shipping']) && $totals['shipping']->getValue()) {
						$ship_method = $totals['shipping']->getValue(); //shipping if present
					} else {
						$ship_method = 0.00;
					}
					
					$data['grandtotal'] = (string)round($quote->getGrandTotal(), 2);
					$data['subtotal'] = (string)round($quote->getSubtotal(), 2);
					$data['discount'] = (string)round($discount, 2);
					$data['deposit_amount'] = (string)round($quote->getFee(), 2);
					$data['tax'] = (string)round($tax, 2);
					$data['ship_cost'] = (string)round($ship_method, 2);
					$data['coupon_applied'] = $quote->getCouponCode()?'':"";
					
					
					
					
					$jsonArray['data'] = $data;
					$jsonArray['status'] = 'success';
					$jsonArray['status_code'] = 200;
					$this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
					die;
				}	
			}else{
				$jsonArray['data'] = null;
				$jsonArray['status'] = 'success';
				$jsonArray['status_code'] = 200;
				$jsonArray['msg'] = 'You Do not have any product in your cart';
				$this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
				die;
			}
		
		}	
	}	
	
	public function GetProductOption($res,$itemid){
		$x=0;
		foreach($res as $options=>$optiondata){
			$product = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($this->GetProductId($itemid));
			$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
			$newarray = array();
			foreach($productAttributeOptions as $option){
				if($option['attribute_id'] == $optiondata->option_id){	
					$optionarray['option_attributeid_label'] = $option['frontend_label'];
					foreach($option['options'] as $dtakey=>$datavalues){
						if($optiondata->option_value == $datavalues['value']){
							$optionarray['option_value_label'] = $datavalues['label'];
						}
					}
					
				}
			}
			$data[] = $optionarray;
		}
		return   $data;
	}
	
	public function GetProductId($itemid){
		$_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$productData = $_objectManager->get('Magento\Quote\Model\Quote\Item')->load($itemid);
		return  $productData->getProductId();
	}
	
	public function getItemImage($productId){
		try {
			$_product = $this->productRepositoryInf->get($productId);
		} catch (NoSuchEntityException $e) {
			return 'product not found';
		}
		$image_url = $this->imageHelper->init($_product, 'product_base_image')->getUrl();
		return $image_url;
	}
	
	public function GetItems($userid){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseurl = $storeManager->getStore()->getBaseUrl();
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $baseurl."rest/V1/carts/mine/items",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Authorization: Bearer ".$this->Get_Token($userid),
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			"Postman-Token: 44bed0ca-8e40-4fd1-bf89-e70a3b3585e3"
		  ),
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		return  json_decode($response);
	}
	
	public function GetitemsForGuestUsers($token){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseurl = $storeManager->getStore()->getBaseUrl();
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $baseurl."rest/V1/guest-carts/".$token."/items",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			"Postman-Token: fd4619b3-813c-4e70-92a1-2263752a76a5"
		  ),
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		$reslutzs= json_decode($response);
		
		if(is_object($reslutzs)){
			return  $reslutzs;
		}else{
			$reslutzss= (array)json_decode($response);
			if(isset($reslutzss['items'])){
				return  $reslutzss['items'];
			}else{
				return  $reslutzss;
			}
			
		}
		
		
	}
	
	public function Get_Token($userid){
		$customerToken = $this->_tokenModelFactory->create();
        $tokenKey = $customerToken->createCustomerToken($userid)->getToken();
		return $tokenKey;
	}
	
	public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool{
        return true;
    }
	
}
