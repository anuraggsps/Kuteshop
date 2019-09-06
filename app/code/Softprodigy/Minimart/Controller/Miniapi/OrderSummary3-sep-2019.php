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
				$data['products'][$x]['price'] = doubleval($item->getPrice()); 
				$_product = $this->productRepositoryInf->getById($item->getProductId());
				$data['products'][$x]['image'] = $baseurl."pub/media/catalog/product".$_product->getData("image"); 
				$x++;
			}
			
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
