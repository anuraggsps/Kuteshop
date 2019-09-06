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
class GetAutoSearchData extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{
	
		public function execute() {
			    $this->getRequest()->getContent('q',true);
				$query = $this->_queryFactory->get();
				$autocompleteItems = 	$this->_objectManager->get("Magento\CatalogSearch\Block\SearchResult\ListProduct")->getLoadedProductCollection();
				$results = [];
				foreach ($autocompleteItems as $product) {
					
					$results[] = [
						'id'      => $product->getId(),
						'name'    => $product->getName(),
						'price'   =>$this->_getProductPrice($product),
						'image'   => $this->_getImageUrl($product),
						'url'     => $product->getProductUrl()
					];
				}
				$jsonArray=  $results;
				$this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
				die;
			

		}	
		public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
			return null;
		}

		public function validateForCsrf(RequestInterface $request): ?bool{
			return true;
		}
		
	    protected function _getProductPrice($product){
			return $this->_priceCurrency->format($product->getFinalPrice($product),false,\Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,$product->getStore());
		}


		/**
		 * Product image url getter
		 *
		 * @param Product $product
		 * @return string
		 */
		protected function _getImageUrl($product)
		{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			$baseurl = $storeManager->getStore()->getBaseUrl();
			
			
			$_product = $this->productRepositoryInf->getById($product->getId());
			return $baseurl."pub/media/catalog/product".$_product->getData("image"); 
		}
	
		
    }    
