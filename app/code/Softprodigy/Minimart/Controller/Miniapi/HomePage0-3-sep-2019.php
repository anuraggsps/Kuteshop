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
class HomePage extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {

    public function execute() {
        try {
            //-------------Mobile theme color------------
            /* $subs = $this->checkPackageSubcription();
             */
            $request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
            $subs = [];
            $subs['subs_closed'] = false;
            $subs['active_package'] = 'Gold';
            $pkgtype = $this->pkgCode[$subs['active_package']];
            $catIds = $this->__helper->getStoreConfig('minimart/minimart_registration/categories');
            
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		    $baseurl = $storeManager->getStore()->getBaseUrl();
			$itemidsarray =[];
			if(isset($param['user_id']) && $param['user_id'] !=''){
				$itemidsarray = $this->GetItems($param['user_id']);
			}else if(isset($param['token']) && $param['token'] !=''){
				$itemidsarray = $this->GetitemsForGuestUsers($param['token']);
			}
			  //~ echo "<pre>";print_r($itemidsarray);die;
		
			$staticdata['type'] = "0";
			$staticdata['multiple_banners'][0]['image'] ="https://dreamarkets.com/pub/media/magiccart/magicslider/b/g/bg12.png";
			$staticdata['multiple_banners'][0]['id'] = "1";
			$staticdata['multiple_banners'][1]['image'] = "https://dreamarkets.com/pub/media/magiccart/magicslider/b/g/bg23.png";
			$staticdata['multiple_banners'][1]['id'] = "2";
			$data[] =$staticdata;
			
			// code for best selling products
				$data[] = $this->getbestsellingproducts($baseurl,$param,$itemidsarray);
			// ends here
			
			$static1data['type'] = "3";
			$static1data['two_banners'][0]['image'] = "http://182.75.88.145/kuteshop/pub/media/wysiwyg/alothemes/static/demo7/home7-1.jpg";
			$static1data['two_banners'][0]['id'] = "1";
			$static1data['two_banners'][1]['image'] = "http://182.75.88.145/kuteshop/pub/media/wysiwyg/alothemes/static/demo7/home7-2.jpg";
			$static1data['two_banners'][1]['id'] = "2";
			$data[] =$static1data;
			
			// code for featured products
				$data[] = $this->getfeaturedproducts($baseurl,$param,$itemidsarray);
			// ends here
			
				$singleimagedata['type'] = "4";
				$singleimagedata['onebanners'][0]['image'] = "http://182.75.88.145/kuteshop/pub/media/wysiwyg/alothemes/static/demo1/home1-18.jpg";
				$singleimagedata['onebanners'][0]['id'] = "1";
				$data[] =$singleimagedata;
			
				
			// code for random products
				$data[] = $this->getrandomproducts($baseurl,$param,$itemidsarray);
			// ends here

			//code for other categories
				$categories =array(3,6,62,7,8,93);
				foreach ($categories as $category) {
					$data[]  = $this->getProductCollection($category,$param,$itemidsarray);
				}	
				
				
			//codes ends here
		

            
            $jsonArray['data'] = $data;
            $jsonArray['status'] =  'success';
            $jsonArray['status_code'] =  200;
            
            
            
        } catch (\Exception $e) {
            //~ $jsonArray['response'] = $e->getMessage();
            //~ $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
            
			$jsonArray['data'] = null;
			$jsonArray['message'] = $e->getMessage();//$e->getMessage();
			$jsonArray['status'] =  "failure";
            $jsonArray['status_code'] =  201;
            
        }

        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

	
	public function getProductCollection($catid,$param,$itemidsarray){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseurl = $storeManager->getStore()->getBaseUrl();
		
		$categoryId = $catid;
		$category = $this->_categoryFactory->create()->load($categoryId);
		$collection = $this->_productCollectionFactory->create();
		$collection->setPageSize(4)->setCurPage(1);
		$collection->addAttributeToSelect('*');
		$collection->addCategoryFilter($category);
		$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		foreach( $collection as $item){
			$othercategoryproducts['type'] = "1";
			$othercategoryproducts['title'] = $category->getName();
			$othercategoryproducts['category_id'] = $categoryId;
			
			if ($categoryId==3) {
				$res = $this->ImageURL(53,"block");
                $othercategoryproducts['banner_image']=$res[0];
			}elseif ($categoryId==6) {
				$res = $this->ImageURL(54,"block");
				 $othercategoryproducts['banner_image']=$res[0];
			}elseif ($categoryId==62) {
				$res = $this->ImageURL(55,"block");
				 $othercategoryproducts['banner_image']=$res[0];
			}elseif ($categoryId==7) {
				$res = $this->ImageURL(56,"block");
				 $othercategoryproducts['banner_image']=$res[0];
			}elseif ($categoryId==8) {
				$res = $this->ImageURL(57,"block");
				 $othercategoryproducts['banner_image']=$res[0];
			}elseif ($categoryId==93) {
				$res = $this->ImageURL(58,"block");
				 $othercategoryproducts['banner_image']=$res[0];
			}
			
			
			
			$x= 0;
			foreach ($collection as $item) {
				$othercategoryproducts['products'][$x]['id'] = $item->getEntityId();
				$othercategoryproducts['products'][$x]['image'] = $baseurl."pub/media/catalog/product".$item->getImage();
				$othercategoryproducts['products'][$x]['name'] = $item->getname();
				$othercategoryproducts['products'][$x]['original_price'] = $item->getPrice();
				$othercategoryproducts['products'][$x]['discounted_price'] = strval($item->getFinalPrice());
				$othercategoryproducts['products'][$x]['sku'] =  $item->getSku();
				$othercategoryproducts['products'][$x]['qty'] =  $this->GetQty($item->getEntityId());
				$othercategoryproducts['products'][$x]['is_configurable'] = "0";
				$othercategoryproducts['products'][$x]['is_wishlist'] = false;
				
				if(isset($param["user_id"]) && $param["user_id"] !=''){
					$othercategoryproducts['products'][$x]['wishlist_id'] = '';
					if(!empty($this->CheckIfProductInWishList($param["user_id"],$item->getEntityId()))){
						
						$wishlistarray = $this->CheckIfProductInWishList($param["user_id"],$item->getEntityId());
						$othercategoryproducts['products'][$x]['is_wishlist'] = true;
						$othercategoryproducts['products'][$x]['wishlist_id'] = $wishlistarray[$item->getEntityId()];
					}
				}
				
				//check if product is in the cart
					$othercategoryproducts['products'][$x]['is_addtocart'] = false;
					$othercategoryproducts['products'][$x]['item_id'] = "";
					if(!empty($itemidsarray)){
						if(array_key_exists($item->getSku(), $itemidsarray)){
							$othercategoryproducts['products'][$x]['is_addtocart'] = true;
							$othercategoryproducts['products'][$x]['item_id'] = strval($itemidsarray[$item->getSku()]);
						} 
					}
				//ends here
			
				
				
				//~ $othercategoryproducts['products'][$x]['configurable_attributes'] = [];
				//check if product is configurable
					$product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getEntityId());
					$othercategoryproducts['products'][$x]['company'] = $product->getAttributeText('manufacturer');
					$productType = $product->getTypeID();
					if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
						//~ $product = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load( $item->getEntityId());
						//~ $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
						$othercategoryproducts['products'][$x]['is_configurable'] = "1";
						//~ $newarray = array();
						//~ foreach($productAttributeOptions as $option){
							//~ $dta['attribute_label'] =$option['label'];
							//~ $dta['attribute_id'] =$option['attribute_id'];
							//~ $dta['attribute__option'] =$option['values'];
							//~ $newarray[] = $dta;
						//~ }
						//~ $othercategoryproducts['products'][$x]['configurable_attributes'] = $newarray;
					}
				//Ends here	
				$x++;
			}				
			return $othercategoryproducts;
		}
	}
	
	public function getbestsellingproducts($baseurl,$param,$itemidsarray){
		//code for getBestsellerProducts
		$collection = $this->_objectManager->get('\Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory')->create()->setModel('Magento\Catalog\Model\Product');
		$collection->setPageSize(4)->setCurPage(1);
		$producIds = array();
		foreach ($collection as $product) {
			$producIds[] = $product->getProductId();
		}
		$collection = $this->_productCollectionFactory->create();
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection = $this->_addProductAttributesAndPrices(
			$collection
		)->addStoreFilter()->addAttributeToFilter('entity_id', array('in' => $producIds));
		$bestsellingproducts['type'] = "1";
		$bestsellingproducts['title'] = "Best Selling";
		$x= 0;
		foreach ($collection as $item) {
			$bestsellingproducts['products'][$x]['id'] = $item->getEntityId();
			$bestsellingproducts['products'][$x]['image'] = $baseurl."pub/media/catalog/product".$item->getImage();
			$bestsellingproducts['products'][$x]['company'] = "";
			$bestsellingproducts['products'][$x]['name'] = $item->getname();
			$bestsellingproducts['products'][$x]['original_price'] = $item->getMaxPrice();
			$bestsellingproducts['products'][$x]['discounted_price'] = strval($item->getFinalPrice());
			$bestsellingproducts['products'][$x]['sku'] = $item->getSku();
			$bestsellingproducts['products'][$x]['qty'] =  $this->GetQty($item->getEntityId());
			$bestsellingproducts['products'][$x]['is_configurable'] = "0";
			$bestsellingproducts['products'][$x]['is_wishlist'] = false;
			
			if(isset($param["user_id"]) && $param["user_id"] !=''){
				$bestsellingproducts['products'][$x]['wishlist_id'] = '';
				if(!empty($this->CheckIfProductInWishList($param["user_id"],$item->getEntityId()))){
					$wishlistarray = $this->CheckIfProductInWishList($param["user_id"],$item->getEntityId());
					$bestsellingproducts['products'][$x]['is_wishlist'] = true;
					$bestsellingproducts['products'][$x]['wishlist_id'] = $wishlistarray[$item->getEntityId()];
				}
			}
			
				//check if product is in the cart
					$bestsellingproducts['products'][$x]['is_addtocart'] = false;
					$bestsellingproducts['products'][$x]['item_id'] = "";
					if(!empty($itemidsarray)){
						if(array_key_exists($item->getSku(), $itemidsarray)){
							$bestsellingproducts['products'][$x]['is_addtocart'] = true;
							$bestsellingproducts['products'][$x]['item_id'] = strval($itemidsarray[$item->getSku()]);
						} 
					}
				//ends here
			
			
			//~ $bestsellingproducts['products'][$x]['configurable_attributes'] = [];
			//check if product is configurable
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getEntityId());
				$bestsellingproducts['products'][$x]['company'] = $product->getAttributeText('manufacturer');;
				$productType = $product->getTypeID();
				if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
					$bestsellingproducts['products'][$x]['is_configurable'] = "1";
					//~ $product = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($item->getEntityId());
					//~ $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
					//~ $bestsellingproducts['products'][$x]['is_configurable'] = "1";
					//~ $newarray = array();
					//~ foreach($productAttributeOptions as $option){
						//~ $dta['attribute_label'] =$option['label'];
						//~ $dta['attribute_id'] =$option['attribute_id'];
						//~ $dta['attribute__option'] =$option['values'];
						//~ $newarray[] = $dta;
					//~ }
					//~ $bestsellingproducts['products'][$x]['configurable_attributes'] = $newarray;
				}
			//Ends here	
			$x++;
		}
		  return $bestsellingproducts;
        //ends here	
	}
	
	
	public function getfeaturedproducts($baseurl,$param,$itemidsarray){
		// code for fetured list products
		$collection = $this->_productCollectionFactory->create();
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection->addAttributeToFilter('featured', '1')
					->addStoreFilter()
					->addAttributeToSelect('*')
					->addMinimalPrice()
					->addFinalPrice()
					->addTaxPercents()
					->setPageSize(4)->setCurPage(1);;
		$xf= 0;			
		$featureproducts['type'] = "2";
		$featureproducts['title'] = "Featured Products";
		foreach ($collection as $items){
			$featureproducts['products'][$xf]['id'] = $items->getEntityId();
			$featureproducts['products'][$xf]['image'] = $baseurl."pub/media/catalog/product".$items->getImage();
			$featureproducts['products'][$xf]['company'] = "";
			$featureproducts['products'][$xf]['name'] = $items->getname();
			$featureproducts['products'][$xf]['original_price'] = $items->getMaxPrice();
			$featureproducts['products'][$xf]['discounted_price'] = strval($items->getFinalPrice());
			$featureproducts['products'][$xf]['sku'] =  $items->getSku();
			$featureproducts['products'][$xf]['qty'] =  $this->GetQty($items->getEntityId());
			$featureproducts['products'][$xf]['is_configurable'] = "0";
			$featureproducts['products'][$xf]['is_wishlist'] = false;
			
			if(isset($param["user_id"]) && $param["user_id"] !=''){
				$featureproducts['products'][$xf]['wishlist_id'] = '';
				if(!empty($this->CheckIfProductInWishList($param["user_id"],$items->getEntityId()))){
					$wishlistarray = $this->CheckIfProductInWishList($param["user_id"],$items->getEntityId());
					$featureproducts['products'][$xf]['is_wishlist'] = true;
					$featureproducts['products'][$xf]['wishlist_id'] = $wishlistarray[$items->getEntityId()];
				}
			}
			
				//check if product is in the cart
					$featureproducts['products'][$xf]['is_addtocart'] = false;
					$featureproducts['products'][$xf]['item_id'] = "";
					if(!empty($itemidsarray)){
						if(array_key_exists($items->getSku(), $itemidsarray)){
							$featureproducts['products'][$xf]['is_addtocart'] = true;
							$featureproducts['products'][$xf]['item_id'] = strval($itemidsarray[$items->getSku()]);
						} 
					}
				//ends here
			
			
			
			//~ $featureproducts['products'][$xf]['configurable_attributes'] = [];
			//check if product is configurable
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Catalog\Model\Product')->load($items->getEntityId());
				$featureproducts['products'][$xf]['company'] = $product->getAttributeText('manufacturer');;
				$productType = $product->getTypeID();
				if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
					//~ $product = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($items->getEntityId());
					//~ $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
					$featureproducts['products'][$x]['is_configurable'] = "1";
					//~ $newarray = array();
					//~ foreach($productAttributeOptions as $option){
						//~ $dta['attribute_label'] =$option['label'];
						//~ $dta['attribute_id'] =$option['attribute_id'];
						//~ $dta['attribute__option'] =$option['values'];
						//~ $newarray[] = $dta;
					//~ }
					//~ $featureproducts['products'][$x]['configurable_attributes'] = $newarray;
				}
			//Ends here	
			$xf++;
		}
			return $featureproducts;
	    //ends
	}
	
	public function getrandomproducts($baseurl,$param,$itemidsarray){
		// code for random products
		$collection = $this->_productCollectionFactory->create();
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection = $this->_addProductAttributesAndPrices(
			$collection
		)->addStoreFilter();
		$collection->getSelect()->order('rand()');
		// getNumProduct
		$collection->setPageSize(4)->setCurPage(1);
		$xf= 0;			
		$randomproducts['type'] = "1";
		$randomproducts['title'] = "Random Products";
		foreach ($collection as $items){
			$randomproducts['products'][$xf]['id'] = $items->getEntityId();
			$randomproducts['products'][$xf]['image'] = $baseurl."pub/media/catalog/product".$items->getImage();
			$randomproducts['products'][$xf]['company'] = "";
			$randomproducts['products'][$xf]['name'] = $items->getname();
			$randomproducts['products'][$xf]['original_price'] = $items->getMaxPrice();
			$randomproducts['products'][$xf]['discounted_price'] = strval($items->getFinalPrice());
			$randomproducts['products'][$xf]['sku'] = $items->getSku();
			$randomproducts['products'][$xf]['qty'] = $this->GetQty($items->getEntityId());
			$randomproducts['products'][$xf]['is_configurable'] = "0";
			$randomproducts['products'][$xf]['is_wishlist'] = false;
			
				if(isset($param["user_id"]) && $param["user_id"] !=''){
					$randomproducts['products'][$xf]['wishlist_id'] = '';
					if(!empty($this->CheckIfProductInWishList($param["user_id"],$items->getEntityId()))){
						$wishlistarray = $this->CheckIfProductInWishList($param["user_id"],$items->getEntityId());
						$randomproducts['products'][$xf]['is_wishlist'] = true;
						$randomproducts['products'][$xf]['wishlist_id'] = $wishlistarray[$items->getEntityId()];
					}
				}
			
				//check if product is in the cart
					$randomproducts['products'][$xf]['is_addtocart'] = false;
					$randomproducts['products'][$xf]['item_id'] = "";
					if(!empty($itemidsarray)){
						if(array_key_exists($items->getSku(), $itemidsarray)){
							$randomproducts['products'][$xf]['is_addtocart'] = true;
							$randomproducts['products'][$xf]['item_id'] = strval($itemidsarray[$items->getSku()]);
						} 
					}
				//ends here
			
			//~ $randomproducts['products'][$xf]['configurable_attributes'] = [];
			//check if product is configurable
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Catalog\Model\Product')->load($items->getEntityId());
				$randomproducts['products'][$xf]['company'] = $product->getAttributeText('manufacturer');;
				$productType = $product->getTypeID();
				if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
					//~ $product = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($items->getEntityId());
					//~ $productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
					$randomproducts['products'][$xf]['is_configurable'] = "1";
					//~ $newarray = array();
					//~ foreach($productAttributeOptions as $option){
						//~ $dta['attribute_label'] =$option['label'];
						//~ $dta['attribute_id'] =$option['attribute_id'];
						//~ $dta['attribute__option'] =$option['values'];
						//~ $newarray[] = $dta;
					//~ }
					//~ $randomproducts['products'][$xf]['configurable_attributes'] = $newarray;
				}
			//Ends here	
			$xf++;
		}
		return $randomproducts;
		// ends here
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
	
	
	public function Get_Token($userid){
		$customerToken = $this->_tokenModelFactory->create();
        $tokenKey = $customerToken->createCustomerToken($userid)->getToken();
		return $tokenKey;
	}
	
	public function GetQty($productId){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
		return $StockState->getStockQty($productId);
	}
	
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool{
        return true;
    }
}
