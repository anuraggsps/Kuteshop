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
class OrderSummary extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{
	
	public function execute() {
		try{
			
			$request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
			
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$quoteFactory = $objectManager->create('\Magento\Quote\Model\QuoteFactory');
			$quote= $quoteFactory->create()->load($param['quote_id']);
					$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			$baseurl = $storeManager->getStore()->getBaseUrl();
			$items = $quote->getAllItems();
			$x = 0 ;
			$image_url = '';
			foreach($items as $item) {
				$data['products'][$x]['product_id'] = $item->getProductId(); 
				$data['products'][$x]['name'] = $item->getName(); 
				$data['products'][$x]['sku'] = $item->getSku(); 
				$data['products'][$x]['qty'] = $item->getQty(); 
				
				
				$data['products'][$x]['price'] = doubleval($this->GetConvertedPrice($param,$item->getPrice())?$this->GetConvertedPrice($param,$item->getPrice()):$item->getPrice()); 
				$_product = $this->productRepositoryInf->getById($item->getProductId());
				$data['products'][$x]['image'] = $baseurl."pub/media/catalog/product".$_product->getData("image"); 
				$x++;
			}
			
			$totals = $quote->getTotals();//Total object
				foreach ($totals as $key => $obj) {
					$data[$key] = array('label' => $obj->getTitle(), 'value' => number_format($this->currencyHelper->currency($obj->getValue(), false, false), 2));
				}	
			if (isset($totals['tax']) && $totals['tax']->getValue()) {
				$tax = $totals['tax']->getValue(); //Tax value if present
			} else {
				$tax = 0.00;
			}
				// coupon_code
				$data['coupon_code'] = '';
				$discount = "0";
				if($quote->getCouponCode() !=''){
					$data['coupon_code'] = $quote->getCouponCode();
					$data['is_discount'] = 1;
					$discount = $quote->getSubtotal()-$quote->getSubtotalWithDiscount() ; //Discount value if applied
				}
			

	
				//  $ship_method = $ship_method = $this->currencyHelper->currency($totals['shipping']->getValue(), false, false); //Tax value if present
				$ship_method = number_format($this->currencyHelper->currency($quote->getShippingAddress()->getBaseShippingAmount()?$quote->getShippingAddress()->getBaseShippingAmount():'0.00', false, false), 2);
			
			$data['ship_charge'] = (object)array();
			if(isset($data['shipping'])){
				$data['ship_charge'] = $data['shipping'];
				$data['ship_charge']['value'] = $ship_method;
				
				if (isset($data['shipping']))
				unset($data['shipping']);
			}
			

			unset($data['grand_total']);
            
			$data['grandtotal'] = (string)round($quote->getGrandTotal(), 2);
			$data['subtotal'] = (string)round($quote->getSubtotal(), 2);
			$data['discount'] = (string)round($discount, 2);
			$data['deposit_amount'] = (string)round($quote->getFee(), 2);
			$data['tax'] = (string)round($tax, 2);
			$data['ship_cost'] = (string)round($ship_method, 2);
			$data['coupon_applied'] = $quote->getCouponCode()?'':"";
			
			$jsonArray['data'] = $data;
			$jsonArray['currency'] = $this->GetCurrency($param);
			$jsonArray['status'] =  'success';
			$jsonArray['status_code'] = 200; 
			$jsonArray['message'] =  "Get Data Succesfully";
		}catch (\Exception $e) {
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
