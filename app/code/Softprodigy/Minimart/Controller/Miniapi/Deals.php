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
class Deals extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{
	
		public function execute() {
			try{
				

				$request = $this->getRequest()->getContent();
				$param = json_decode($request, true);
				
				$storeManager = \Magento\Framework\App\ObjectManager::getInstance()->create(
					 '\Magento\Store\Model\StoreManagerInterface'
				);
				$catalogRule = \Magento\Framework\App\ObjectManager::getInstance()->create(
					 '\Magento\CatalogRule\Model\RuleFactory'
				);

				$websiteId = $storeManager->getStore()->getWebsiteId();//current Website Id
				$resultProductIds = [];
				$catalogRuleCollection = $catalogRule->create()->getCollection();
				$catalogRuleCollection->addIsActiveFilter(1);//filter for active rules only
				foreach ($catalogRuleCollection as $catalogRule) {
					$productIdsAccToRule = $catalogRule->getMatchingProductIds();
					foreach ($productIdsAccToRule as $productId => $ruleProductArray) {
						if (!empty($ruleProductArray[$websiteId])) {
							$resultProductIds[$productId] = $catalogRule->getName();
						}
					}
				}
				$selectedarray = $this->GetSelectedArray($resultProductIds,$param);
				$jsonArray['data'] = $selectedarray ;
				$jsonArray['images']= $this->ImageURL(14,$type='page');
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
		
		public function array_flip_multiple(array $a) {
			$result = array();
			foreach($a as $k=>$v)
				$result[$v][]=$k;
			return $result;
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
			$arary =[];
			foreach(json_decode($response) as $key=>$value){
				$arary[$value->sku] = $value->item_id;
			}
			return  (array)$arary;
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
			
			if(!is_object($reslutzs)){
				$arary =[];
				foreach(json_decode($response) as $key=>$value){
					$arary[$value->sku] = $value->item_id;
				}
				return  (array)$arary;
				
			}
			
		}
		
		
		public function GetQty($productId){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
			return $StockState->getStockQty($productId);
		}
		
		public function GetSelectedArray($array,$param){
			$hash = $this->array_flip_multiple($array);
			// filter $hash based on your specs (2 or more)
			$hash = array_filter($hash, function($items) { return count($items) >= 1;});
			// get all remaining keys
			$keys = array_reduce($hash, 'array_merge', array());
			$y = 0;
			foreach($hash as $key=>$values){
				$collection = $this->_productCollectionFactory->create();
				$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
				$collection = $this->_addProductAttributesAndPrices(
					$collection
				)->addStoreFilter()->addAttributeToFilter('entity_id', array('in' => $values));
				//~ echo "<pre>";print_r($collection->getData());die;
				//~ echo $collection->getSelect()->__toString();
				
				
				$itemidsarray =[];
				if(isset($param['user_id']) && $param['user_id'] !=''){
					$itemidsarray = $this->GetItems($param['user_id']);
				}else if(isset($param['token']) && $param['token'] !=''){
					$itemidsarray = $this->GetitemsForGuestUsers($param['token']);
				}
				
				$x= 0;
				foreach($collection as $itemkey=>$item){
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$product = $objectManager->create('Magento\Catalog\Model\Product')->load($item->getEntityId());
					$othercategoryproducts[$y]['title'] =$key ;
					$othercategoryproducts[$y]['products'][$x]['id'] = $item->getEntityId();
					//get image url
					$helperImport = $objectManager->get('\Magento\Catalog\Helper\Image');
					$imageUrl = $helperImport->init($product, 'product_page_image_large')
									->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
									->resize(380)
									->getUrl();
					//
					$othercategoryproducts[$y]['products'][$x]['image'] = $imageUrl;
					$othercategoryproducts[$y]['products'][$x]['company'] = $product->getAttributeText('manufacturer')?$product->getAttributeText('manufacturer'):'';
					$othercategoryproducts[$y]['products'][$x]['name'] = $product->getName();
					$othercategoryproducts[$y]['products'][$x]['original_price'] = $this->GetConvertedPrice($param,$item->getMaxPrice())?$this->GetConvertedPrice($param,$item->getMaxPrice()):$item->getMaxPrice();;
					
					
					
					$othercategoryproducts[$y]['products'][$x]['discounted_price'] = strval($this->GetConvertedPrice($param,$item['final_price'])?strval($this->GetConvertedPrice($param,$item['final_price'])):$item['final_price']);;
					
					
					//strval($item->getFinalPrice());final_price
					$othercategoryproducts[$y]['products'][$x]['sku'] = $item->getSku();
					$othercategoryproducts[$y]['products'][$x]['qty'] = $this->GetQty($item->getEntityId());
					$productType = $product->getTypeID();
					$othercategoryproducts[$y]['products'][$x]['is_configurable'] = "0";
					if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
						$othercategoryproducts[$y]['products'][$x]['is_configurable'] = "1";
					}
					$othercategoryproducts[$y]['products'][$x]['is_wishlist'] = false;
					//check if product is in the cart
						$othercategoryproducts[$y]['products'][$x]['is_addtocart'] = false;
						$othercategoryproducts[$y]['products'][$x]['item_id'] = "";
						if(!empty($itemidsarray)){
							if(array_key_exists($item->getSku(), $itemidsarray)){
								$othercategoryproducts[$y]['products'][$x]['is_addtocart'] = true;
								$othercategoryproducts[$y]['products'][$x]['item_id'] = strval($itemidsarray[$item->getSku()]);
							} 
						}
					//ends here
					if(isset($param["user_id"]) && $param["user_id"] !=''){
						$othercategoryproducts[$y]['products'][$x]['wishlist_id'] = '';
						if(!empty($this->CheckIfProductInWishList($param["user_id"],$item->getEntityId()))){
							
							$wishlistarray = $this->CheckIfProductInWishList($param["user_id"],$item->getEntityId());
							$othercategoryproducts[$y]['products'][$x]['is_wishlist'] = true;
							$othercategoryproducts[$y]['products'][$x]['wishlist_id'] = $wishlistarray[$item->getEntityId()];
						}
					}
					
					
					$x++;
				}
				
				$y++;
			}
			return $othercategoryproducts;
		}
		
		
		public function CheckIfProductInWishList($userid,$productid){
			$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
			$id =  $userid;
			$wishlist            = $objectManager->get('\Magento\Wishlist\Model\Wishlist');
			$wishlist_collection = $wishlist->loadByCustomerId( $id , true)->getItemCollection();
			$_in_wishlist        = "false";
			$arary =[];
			foreach ($wishlist_collection->getData() as $key=>$wishlist_product){
				if($productid == $wishlist_product['product_id']){
					$arary[$wishlist_product['product_id']] = $wishlist_product['wishlist_item_id'];
				}
			}
			return  (array)$arary;
		}
		
		public function Get_Token($userid){
			$customerToken = $this->_tokenModelFactory->create();
			$tokenKey = $customerToken->createCustomerToken($userid)->getToken();
			return $tokenKey;
		}
		
		
    }    
