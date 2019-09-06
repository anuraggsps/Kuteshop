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
class ViewAll extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {

	protected $total_items_main;
    public function execute() {
        //~ try {
            $request = $this->getRequest()->getContent();
            $param = json_decode($request, true);
		
			
		  
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		    $baseurl = $storeManager->getStore()->getBaseUrl();
			
			$itemidsarray =[];
			if(isset($param['user_id']) && $param['user_id'] !=''){
				$itemidsarray = $this->GetItems($param['user_id']);
			}else if(isset($param['token']) && $param['token'] !=''){
				$itemidsarray = $this->GetitemsForGuestUsers($param['token']);
			}
			
			
			if(isset($param['title']) && strtolower($param['title']) == 'best selling'){
				$data = $this->getbestsellingproducts($baseurl,$param['limit'],$param['page'],$param,$itemidsarray);
			}else if(isset($param['title']) && strtolower($param['title']) == 'featured products'){
				$data = $this->getfeaturedproducts($baseurl,$param['limit'],$param['page'],$param,$itemidsarray);
			}else if(isset($param['title']) && strtolower($param['title']) == 'random products'){
				$data = $this->getrandomproducts($baseurl,$param['limit'],$param['page'],$param,$itemidsarray);
			}else{
				//here is code for category
				$data = $this->getothercategoryproducts($param['limit'],$param['categoryId'],$param['page'],$param,$itemidsarray);
			}
            
            if(empty($data)){
				$data = ["product"=>array()];
			}
			//~ echo"<pre>"; print_r($data['products']); die;
			//~ $total_items_main=count($data['products']);
            $jsonArray['data'] = $data;
            $jsonArray['total_item'] = $this->total_items_main;
            $jsonArray['currency'] = $this->GetCurrency($param);
            $jsonArray['status'] =  'success';
            $jsonArray['status_code'] =  200;
            
        //~ } catch (\Exception $e) {
			//~ $jsonArray['data'] = null;
			//~ $jsonArray['message'] = "not getting data";//$e->getMessage();
			//~ $jsonArray['status'] =  "failure";
            //~ $jsonArray['status_code'] =  201;
        //~ }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }
    
    public function getothercategoryproducts($limit,$catid,$page,$param,$itemidsarray){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');// Instance of Category Model
		
		if ($test = array_column( $param['filter'],"Category")){
			$categoryId = $test[0];
		}else{
			$categoryId = $catid; 
		}
		
		return $this->getProductCollection($categoryId,$limit,$page,$param,$itemidsarray);
	}
    
    public function getProductCollection($catid,$limit,$page,$param,$itemidsarray){
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseurl = $storeManager->getStore()->getBaseUrl();
		$categoryId = $catid;
		//~ echo $param['sort']['sortorder'];die;
		// count total collection
				$category = $this->_categoryFactory->create()->load($categoryId);
				$collection1 = $this->_productCollectionFactory->create();
				$collection1->addAttributeToSelect('*');
				$collection1->addCategoryFilter($category);
				if($param['sort']['sortkey'] == 'price'){
					$collection1->addFinalPrice()->getSelect()->order('max_price', $param['sort']['sortorder']);
				}else{
					$collection1->addAttributeToSort($param['sort']['sortkey'] ,$param['sort']['sortorder']);
				}
				
				$collection1->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
				$collection1->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		
			if(!empty($param['filter'])){	
				foreach($param['filter'] as $key=>$value){
					foreach($value as $keys=>$values){
						if($keys =='price'){
							$val = explode("-",$values);
								$collection1->addAttributeToFilter('price', array('gteq' => $val[0]));
								if($val[1] != ''){
									$collection1->addAttributeToFilter('price', array('lteq' => $val[1]));
								}
						}else{
							if ($test = array_column( $param['filter'],"category")){
								
							}else{
							$collection1->addAttributeToFilter($keys, $values);	
							}
							
								
						}

					}
					
				}
				
			}	
			//~ echo $collection1->getSelect()->__toString();die;
		//
		$this->total_items_main=count($collection1);
		$totalcount = count($collection1);
		$shouldrun = $totalcount/ $limit;
		if(!is_int($shouldrun)){
			$shoulrunnuber = explode(".",$shouldrun);
			$shouldrun = $shoulrunnuber[0]+1;
		}

		if($page <= $shouldrun){
			$category = $this->_categoryFactory->create()->load($categoryId);
			$collection = $this->_productCollectionFactory->create();
			$collection->setPageSize($limit)->setCurPage($page);
			$collection->addAttributeToSelect('*');
			$collection->addCategoryFilter($category);
				if($param['sort']['sortkey'] == 'price'){
					//~ $collection->addFinalPrice()->getSelect()->order('max_price', $param['sort']['sortorder']);
					$collection->addAttributeToSort($param['sort']['sortkey'] ,$param['sort']['sortorder']);
				}else{
					$collection->addAttributeToSort($param['sort']['sortkey'] ,$param['sort']['sortorder']);
				}
				
			//~ echo $collection1->getSelect()->__toString();die;
			$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
			$collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
			if(!empty($param['filter'])){
				foreach($param['filter'] as $key=>$value){
						foreach($value as $keys=>$values){
							if($keys =='price'){
								$val = explode("-",$values);
									$collection->addAttributeToFilter('price', array('gteq' => $val[0]));
									if($val[1] != ''){
										$collection->addAttributeToFilter('price', array('lteq' => $val[1]));
									}
							}else{
								
							if ($test = array_column( $param['filter'],"category")){
							}else{
							     $collection->addAttributeToFilter($keys, $values);	
							}
									
							}

						}
						
				}

			}	
			foreach( $collection as $item){
				$x= 0;
				foreach ($collection as $item) {
					$othercategoryproducts['products'][$x]['id'] = $item->getEntityId();
					$othercategoryproducts['products'][$x]['image'] = $baseurl."pub/media/catalog/product".$item->getImage();
					$othercategoryproducts['products'][$x]['company'] = "";
					$othercategoryproducts['products'][$x]['name'] = $item->getname();
					$othercategoryproducts['products'][$x]['original_price'] = $this->GetConvertedPrice($param,$item->getPrice())?$this->GetConvertedPrice($param,$item->getPrice()):$item->getPrice();
					$othercategoryproducts['products'][$x]['discounted_price'] = strval($this->GetConvertedPrice($param,$item->getFinalPrice())?strval($this->GetConvertedPrice($param,$item->getFinalPrice())):$item->getFinalPrice());
					$othercategoryproducts['products'][$x]['sku'] = $item->getSku();
					$othercategoryproducts['products'][$x]['qty'] = $this->GetQty($item->getEntityId());
					
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
					$othercategoryproducts['products'][$x]['is_wishlist'] = false;
					if(isset($param["user_id"]) && $param["user_id"] !=''){
						$othercategoryproducts['products'][$x]['wishlist_id'] = '';
						if(!empty($this->CheckIfProductInWishList($param["user_id"],$item->getEntityId()))){
							
							$wishlistarray = $this->CheckIfProductInWishList($param["user_id"],$item->getEntityId());
							$othercategoryproducts['products'][$x]['is_wishlist'] = true;
							$othercategoryproducts['products'][$x]['wishlist_id'] = $wishlistarray[$item->getEntityId()];
						}
					}
					//check if product is configurable
						$othercategoryproducts['products'][$x]['is_configurable'] = "0";
						$product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getEntityId());
						$othercategoryproducts['products'][$x]['company'] = $product->getAttributeText('manufacturer')?$product->getAttributeText('manufacturer'):'';;
						$productType = $product->getTypeID();
						if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
							$othercategoryproducts['products'][$x]['is_configurable'] = "1";
						}
					//Ends here	
					$x++;
				}				
				return $othercategoryproducts;
			}
		}	
	}
    
    
    public function getbestsellingproducts($baseurl,$limit,$page,$param,$itemidsarray){
		//code for getBestsellerProducts
		

		$collection = $this->_objectManager->get('\Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory')->create()->setModel('Magento\Catalog\Model\Product');
		
		$producIds = array();
		
		foreach ($collection as $product) {
			$producIds[] = $product->getProductId();
		}
		
		$collection = $this->_productCollectionFactory->create();
		$collection->setPageSize($limit)->setCurPage($page);
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection = $this->_addProductAttributesAndPrices(
			$collection
		)->addStoreFilter()->addAttributeToFilter('entity_id', array('in' => $producIds))->setOrder('ASC');
		$this->total_items_main=count($collection);
		//~ if(!empty($param['filter'])){
			//~ foreach($param['filter'] as $key=>$value){
				//~ foreach($value as $keys=>$values){
					//~ if($keys =='price'){
						//~ $val = explode("-",$values);
							//~ $collection->addAttributeToFilter('price', array('gteq' => $val[0]));
							//~ if($val[1] != ''){
								//~ $collection->addAttributeToFilter('price', array('lteq' => $val[1]));
							//~ }
					//~ }else{
						//~ $collection->addAttributeToFilter($keys, $values);
					//~ }

				//~ }
					
			//~ }
		//~ }	
		$bestsellingproducts['type'] = "1";
		$bestsellingproducts['title'] = "best selling";
		$bestsellingproducts =[] ;;
		$x= 0;
		//~ if(count($collection)>0){
			foreach ($collection as $item) {
				$bestsellingproducts['products'][$x]['id'] = $item->getEntityId();
				$bestsellingproducts['products'][$x]['image'] = $baseurl."pub/media/catalog/product".$item->getImage();
				$bestsellingproducts['products'][$x]['company'] = "";
				$bestsellingproducts['products'][$x]['name'] = $item->getname();
				$bestsellingproducts['products'][$x]['original_price'] = $this->GetConvertedPrice($param,$item->getMaxPrice())?$this->GetConvertedPrice($param,$item->getMaxPrice()):$item->getMaxPrice();
				$bestsellingproducts['products'][$x]['discounted_price'] = strval($this->GetConvertedPrice($param,$item->getFinalPrice())?strval($this->GetConvertedPrice($param,$item->getFinalPrice())):$item->getFinalPrice());
				$bestsellingproducts['products'][$x]['sku'] = $item->getSku();
				$bestsellingproducts['products'][$x]['qty'] = $this->GetQty($item->getEntityId());;
				$bestsellingproducts['products'][$x]['is_configurable'] = "0";
				
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
				
				$bestsellingproducts['products'][$x]['is_wishlist'] = false;
				if(isset($param["user_id"]) && $param["user_id"] !=''){
					$bestsellingproducts['products'][$x]['wishlist_id'] = '';
					if(!empty($this->CheckIfProductInWishList($param["user_id"],$item->getEntityId()))){
						$wishlistarray = $this->CheckIfProductInWishList($param["user_id"],$item->getEntityId());
						$bestsellingproducts['products'][$x]['is_wishlist'] = true;
						$bestsellingproducts['products'][$x]['wishlist_id'] = $wishlistarray[$item->getEntityId()];
					}
				}
				
				
				//check if product is configurable
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getEntityId());
					$bestsellingproducts['products'][$x]['company'] = $product->getAttributeText('manufacturer')?$product->getAttributeText('manufacturer'):'';;
					$productType = $product->getTypeID();
					if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
						$bestsellingproducts['products'][$x]['is_configurable'] = "1";
					}
				//Ends here	
				$x++;
			}
		  return $bestsellingproducts;
		//~ }else{
			//~ return $bestsellingproducts;
		//~ }  
        //ends here	
	}
	
	
	public function getfeaturedproducts($baseurl,$limit,$page,$param,$itemidsarray){
		// code for fetured list products
		$collection1 = $this->_productCollectionFactory->create();
		$collection1->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection1->addAttributeToFilter('featured', '1')->setOrder('ASC')
					->addStoreFilter()
					->addAttributeToSelect('*')
					->addMinimalPrice()
					->addFinalPrice()
					->addTaxPercents();
		    //~ if(!empty($param['filter'])){			
				//~ foreach($param['filter'] as $key=>$value){
					//~ foreach($value as $keys=>$values){
						//~ if($keys =='price'){
							//~ $val = explode("-",$values);
								//~ $collection1->addAttributeToFilter('price', array('gteq' => $val[0]));
								//~ if($val[1] != ''){
									//~ $collection1->addAttributeToFilter('price', array('lteq' => $val[1]));
								//~ }
						//~ }else{
							//~ $collection1->addAttributeToFilter($keys, $values);
						//~ }

					//~ }
						
				//~ }
			//~ }
		$this->total_items_main=count($collection1);
		$totalcount = count($collection1);
		$shouldrun = $totalcount/ $limit;
		if(!is_int($shouldrun)){
			$shoulrunnuber = explode(".",$shouldrun);
			$shouldrun = $shoulrunnuber[0]+1;
		}

		if($page <= $shouldrun){
			//
			$collection = $this->_productCollectionFactory->create();
			$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
			$collection->addAttributeToFilter('featured', '1')->setOrder('ASC')
						->addStoreFilter()
						->addAttributeToSelect('*')
						->addMinimalPrice()
						->addFinalPrice()
						->addTaxPercents()
						->setPageSize($limit)->setCurPage($page);;
			//~ if(!empty($param['filter'])){			
				//~ foreach($param['filter'] as $key=>$value){
					//~ foreach($value as $keys=>$values){
						//~ if($keys =='price'){
							//~ $val = explode("-",$values);
								//~ $collection->addAttributeToFilter('price', array('gteq' => $val[0]));
								//~ if($val[1] != ''){
									//~ $collection->addAttributeToFilter('price', array('lteq' => $val[1]));
								//~ }
						//~ }else{
							//~ $collection->addAttributeToFilter($keys, $values);
						//~ }

					//~ }
						
				//~ }
					
			//~ }			
						
			$xf= 0;			
			$featureproducts['type'] = "2";
			$featureproducts['title'] = "featured products";
			foreach ($collection as $items){
				$featureproducts['products'][$xf]['id'] = $items->getEntityId();
				$featureproducts['products'][$xf]['image'] = $baseurl."pub/media/catalog/product".$items->getImage();
				$featureproducts['products'][$xf]['company'] = "";
				$featureproducts['products'][$xf]['name'] = $items->getname();
				$featureproducts['products'][$xf]['original_price'] = $this->GetConvertedPrice($param,$items->getMaxPrice())?$this->GetConvertedPrice($param,$items->getMaxPrice()):$items->getMaxPrice();
				$featureproducts['products'][$xf]['discounted_price'] = strval($this->GetConvertedPrice($param,$items->getFinalPrice())?strval($this->GetConvertedPrice($param,$items->getFinalPrice())):$items->getFinalPrice());
				$featureproducts['products'][$xf]['sku'] =  $items->getSku();
				$featureproducts['products'][$xf]['qty'] =  $this->GetQty($items->getEntityId());;
				$featureproducts['products'][$xf]['is_configurable'] = "0";
				
				
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
				
					$featureproducts['products'][$xf]['is_wishlist'] = false;
					if(isset($param["user_id"]) && $param["user_id"] !=''){
						$featureproducts['products'][$xf]['wishlist_id'] = '';
						if(!empty($this->CheckIfProductInWishList($param["user_id"],$items->getEntityId()))){
							$wishlistarray = $this->CheckIfProductInWishList($param["user_id"],$items->getEntityId());
							$featureproducts['products'][$xf]['is_wishlist'] = true;
							$featureproducts['products'][$xf]['wishlist_id'] = $wishlistarray[$items->getEntityId()];
						}
					}
				
				
				//check if product is configurable
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$product = $objectManager->get('Magento\Catalog\Model\Product')->load($items->getEntityId());
					$featureproducts['products'][$xf]['company'] = $product->getAttributeText('manufacturer')?$product->getAttributeText('manufacturer'):'';;
					$productType = $product->getTypeID();
					if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
						$featureproducts['products'][$xf]['is_configurable'] = "1";
					}
				//Ends here	
				$xf++;
			}
				return $featureproducts;
			//ends
		}	
	}
	
	public function getrandomproducts($baseurl,$limit,$page,$param,$itemidsarray){
		// code for random products
		$collection1 = $this->_productCollectionFactory->create();
		$collection1->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection1 = $this->_addProductAttributesAndPrices(
			$collection1
		)->addStoreFilter()->setOrder('ASC');
		$collection1->getSelect()->order('rand()');
		//~ if(!empty($param['filter'])){
			//~ foreach($param['filter'] as $key=>$value){
				//~ foreach($value as $keys=>$values){
					//~ if($keys =='price'){
						//~ $val = explode("-",$values);
							//~ $collection1->addAttributeToFilter('price', array('gteq' => $val[0]));
							//~ if($val[1] != ''){
								//~ $collection1->addAttributeToFilter('price', array('lteq' => $val[1]));
							//~ }
					//~ }else{
						//~ $collection1->addAttributeToFilter($keys, $values);
					//~ }

				//~ }
						
			//~ }
		//~ }	
		$this->total_items_main=count($collection1);
		$totalcount = count($collection1);
		$shouldrun = $totalcount/ $limit;
		if(!is_int($shouldrun)){
			$shoulrunnuber = explode(".",$shouldrun);
			$shouldrun = $shoulrunnuber[0]+1;
		}

		if($page <= $shouldrun){
			$collection = $this->_productCollectionFactory->create();
			$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
			$collection = $this->_addProductAttributesAndPrices(
				$collection
			)->addStoreFilter()->setOrder('ASC');
			$collection->getSelect()->order('rand()');
			// getNumProduct
			$collection->setPageSize($limit)->setCurPage($page);
			//~ if(!empty($param['filter'])){
				//~ foreach($param['filter'] as $key=>$value){
					//~ foreach($value as $keys=>$values){
						//~ if($keys =='price'){
							//~ $val = explode("-",$values);
								//~ $collection->addAttributeToFilter('price', array('gteq' => $val[0]));
								//~ if($val[1] != ''){
									//~ $collection->addAttributeToFilter('price', array('lteq' => $val[1]));
								//~ }
						//~ }else{
							//~ $collection->addAttributeToFilter($keys, $values);
						//~ }

					//~ }
						
				//~ }
			//~ }
			$xf= 0;			
			$randomproducts['type'] = "1";
			$randomproducts['title'] = "random products";
			foreach ($collection as $items){
				$randomproducts['products'][$xf]['id'] = $items->getEntityId();
				$randomproducts['products'][$xf]['image'] = $baseurl."pub/media/catalog/product".$items->getImage();
				$randomproducts['products'][$xf]['company'] = "";
				$randomproducts['products'][$xf]['name'] = $items->getname();
				$randomproducts['products'][$xf]['original_price'] = $this->GetConvertedPrice($param,$items->getMaxPrice())?$this->GetConvertedPrice($param,$items->getMaxPrice()):$items->getMaxPrice();
				$randomproducts['products'][$xf]['discounted_price'] = strval($this->GetConvertedPrice($param,$items->getFinalPrice())?strval($this->GetConvertedPrice($param,$items->getFinalPrice())):$items->getFinalPrice());
				$randomproducts['products'][$xf]['sku'] = $items->getSku();
				$randomproducts['products'][$xf]['qty'] = $this->GetQty($items->getEntityId());;
				$randomproducts['products'][$xf]['is_configurable'] = "0";
				
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
				//check here for is product added in wishlist to particular user
					$randomproducts['products'][$xf]['is_wishlist'] = false;
					if(isset($param["user_id"]) && $param["user_id"] !=''){
						$randomproducts['products'][$xf]['wishlist_id'] = '';
						if(!empty($this->CheckIfProductInWishList($param["user_id"],$items->getEntityId()))){
							$wishlistarray = $this->CheckIfProductInWishList($param["user_id"],$items->getEntityId());
							$randomproducts['products'][$xf]['is_wishlist'] = true;
							$randomproducts['products'][$xf]['wishlist_id'] = $wishlistarray[$items->getEntityId()];
						}
					}
				//ends here
				//check if product is configurable
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$product = $objectManager->get('Magento\Catalog\Model\Product')->load($items->getEntityId());
					$randomproducts['products'][$xf]['company'] = $product->getAttributeText('manufacturer')?$product->getAttributeText('manufacturer'):'';;
					$productType = $product->getTypeID();
					if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
						$randomproducts['products'][$xf]['is_configurable'] = "1";
					}
				//Ends here	
				$xf++;
			}
			return $randomproducts;
			// ends here
		}	
	}
    
    public function GetQty($productId){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$StockState = $objectManager->get('\Magento\CatalogInventory\Api\StockStateInterface');
		return $StockState->getStockQty($productId);
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
		if(!is_object($response)){
			$arary =[];
			foreach(json_decode($response) as $key=>$value){
				$arary[$value->sku] = $value->item_id;
			}
			return  (array)$arary;
		}	
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
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool{
        return true;
    }

}
