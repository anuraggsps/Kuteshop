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

    public function execute() {
        try {
            $request = $this->getRequest()->getContent();
            $param = json_decode($request, true);
			
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		    $baseurl = $storeManager->getStore()->getBaseUrl();
			
			if(isset($param['title']) && $param['title'] == 'best selling'){
				$data = $this->getbestsellingproducts($baseurl,$param['limit'],$param['page']);
			}else if(isset($param['title']) && $param['title'] == 'fetured products'){
				$data = $this->getfeaturedproducts($baseurl,$param['limit'],$param['page']);
			}else if(isset($param['title']) && $param['title'] == 'random products'){
				$data = $this->getrandomproducts($baseurl,$param['limit'],$param['page']);
			}else{
				//here is code for category
				$data = $this->getothercategoryproducts($param['limit'],$param['categoryId'],$param['page']);
			}
            
            if(empty($data)){
				$data = [];
			}
            $jsonArray['data'] = $data;
            $jsonArray['status'] =  'success';
            $jsonArray['status_code'] =  200;
            
        } catch (\Exception $e) {
			$jsonArray['data'] = null;
			$jsonArray['message'] = "not getting data";//$e->getMessage();
			$jsonArray['status'] =  "failure";
            $jsonArray['status_code'] =  201;
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }
    
    public function getothercategoryproducts($limit,$catid,$page){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');// Instance of Category Model
		$categoryId = $catid; 
		$category = $categoryFactory->create()->load($categoryId);
		 
		return $this->getProductCollection($categoryId,$limit,$page);
	}
    
    public function getProductCollection($catid,$limit,$page){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseurl = $storeManager->getStore()->getBaseUrl();
		$categoryId = $catid;
		$category = $this->_categoryFactory->create()->load($categoryId);
		$collection = $this->_productCollectionFactory->create();
		$collection->setPageSize($limit)->setCurPage($page);
		$collection->addAttributeToSelect('*');
		$collection->addCategoryFilter($category);
		$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		foreach( $collection as $item){
			$x= 0;
			foreach ($collection as $item) {
				$othercategoryproducts['products'][$x]['id'] = $item->getEntityId();
				$othercategoryproducts['products'][$x]['image'] = $baseurl."pub/media/catalog/product".$item->getImage();
				$othercategoryproducts['products'][$x]['company'] = "";
				$othercategoryproducts['products'][$x]['name'] = $item->getname();
				$othercategoryproducts['products'][$x]['original_price'] = $item->getPrice();
				$othercategoryproducts['products'][$x]['discounted_price'] = $item->getFinalPrice();
				$othercategoryproducts['products'][$x]['sku'] = $item->getSku();
				$othercategoryproducts['products'][$x]['qty'] = $this->GetQty($item->getEntityId());
				//check if product is configurable
					$product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getEntityId());
					$productType = $product->getTypeID();
					if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
						$product = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load( $item->getEntityId());
						$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
						$othercategoryproducts['products'][$x]['is_configurable'] = "1";
						$newarray = array();
						foreach($productAttributeOptions as $option){
							$dta['attribute_label'] =$option['label'];
							$dta['attribute_id'] =$option['attribute_id'];
							$dta['attribute__option'] =$option['values'];
							$newarray[] = $dta;
						}
						$othercategoryproducts['products'][$x]['configurable_attributes'] = $newarray;
					}
				//Ends here	
				$x++;
			}				
			return $othercategoryproducts;
		}
	}
    
    
    public function getbestsellingproducts($baseurl,$limit,$page){
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
		)->addStoreFilter()->addAttributeToFilter('entity_id', array('in' => $producIds));
		$bestsellingproducts['type'] = "1";
		$bestsellingproducts['title'] = "best selling";
		$x= 0;
		foreach ($collection as $item) {
			$bestsellingproducts['products'][$x]['id'] = $item->getEntityId();
			$bestsellingproducts['products'][$x]['image'] = $baseurl."pub/media/catalog/product".$item->getImage();
			$bestsellingproducts['products'][$x]['company'] = "";
			$bestsellingproducts['products'][$x]['name'] = $item->getname();
			$bestsellingproducts['products'][$x]['original_price'] = $item->getMaxPrice();
			$bestsellingproducts['products'][$x]['discounted_price'] = $item->getFinalPrice();
			$bestsellingproducts['products'][$x]['sku'] = $item->getSku();
			$bestsellingproducts['products'][$x]['qty'] = $this->GetQty($item->getEntityId());;
			$bestsellingproducts['products'][$x]['configurable_attributes'] = [];
			//check if product is configurable
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Catalog\Model\Product')->load($item->getEntityId());
				$productType = $product->getTypeID();
				if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
					$product = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($item->getEntityId());
					$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
					$bestsellingproducts['products'][$x]['is_configurable'] = "1";
					$newarray = array();
					foreach($productAttributeOptions as $option){
						$dta['attribute_label'] =$option['label'];
						$dta['attribute_id'] =$option['attribute_id'];
						$dta['attribute__option'] =$option['values'];
						$newarray[] = $dta;
					}
					$bestsellingproducts['products'][$x]['configurable_attributes'] = $newarray;
				}
			//Ends here	
			$x++;
		}
		  return $bestsellingproducts;
        //ends here	
	}
	
	
	public function getfeaturedproducts($baseurl,$limit,$page){
		// code for fetured list products
		$collection = $this->_productCollectionFactory->create();
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection->addAttributeToFilter('featured', '1')
					->addStoreFilter()
					->addAttributeToSelect('*')
					->addMinimalPrice()
					->addFinalPrice()
					->addTaxPercents()
					->setPageSize($limit)->setCurPage($page);;
		$xf= 0;			
		$featureproducts['type'] = "2";
		$featureproducts['title'] = "fetured products";
		foreach ($collection as $items){
			$featureproducts['products'][$xf]['id'] = $items->getEntityId();
			$featureproducts['products'][$xf]['image'] = $baseurl."pub/media/catalog/product".$items->getImage();
			$featureproducts['products'][$xf]['company'] = "";
			$featureproducts['products'][$xf]['name'] = $items->getname();
			$featureproducts['products'][$xf]['original_price'] = $items->getMaxPrice();
			$featureproducts['products'][$xf]['discounted_price'] = $items->getFinalPrice();
			$featureproducts['products'][$xf]['sku'] =  $items->getSku();
			$featureproducts['products'][$xf]['qty'] =  $this->GetQty($items->getEntityId());;
			$featureproducts['products'][$xf]['configurable_attributes'] = [];
			//check if product is configurable
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Catalog\Model\Product')->load($items->getEntityId());
				$productType = $product->getTypeID();
				if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
					$product = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($items->getEntityId());
					$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
					$featureproducts['products'][$x]['is_configurable'] = "1";
					$newarray = array();
					foreach($productAttributeOptions as $option){
						$dta['attribute_label'] =$option['label'];
						$dta['attribute_id'] =$option['attribute_id'];
						$dta['attribute__option'] =$option['values'];
						$newarray[] = $dta;
					}
					$featureproducts['products'][$x]['configurable_attributes'] = $newarray;
				}
			//Ends here	
			$xf++;
		}
			return $featureproducts;
	    //ends
	}
	
	public function getrandomproducts($baseurl,$limit,$page){
		// code for random products
		$collection = $this->_productCollectionFactory->create();
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection = $this->_addProductAttributesAndPrices(
			$collection
		)->addStoreFilter();
		$collection->getSelect()->order('rand()');
		// getNumProduct
		$collection->setPageSize($limit)->setCurPage($page);
		$xf= 0;			
		$randomproducts['type'] = "1";
		$randomproducts['title'] = "random products";
		foreach ($collection as $items){
			$randomproducts['products'][$xf]['id'] = $items->getEntityId();
			$randomproducts['products'][$xf]['image'] = $baseurl."pub/media/catalog/product".$items->getImage();
			$randomproducts['products'][$xf]['company'] = "";
			$randomproducts['products'][$xf]['name'] = $items->getname();
			$randomproducts['products'][$xf]['original_price'] = $items->getMaxPrice();
			$randomproducts['products'][$xf]['discounted_price'] = $items->getFinalPrice();
			$randomproducts['products'][$xf]['sku'] = $items->getSku();
			$randomproducts['products'][$xf]['qty'] = $this->GetQty($items->getEntityId());;
			$randomproducts['products'][$xf]['configurable_attributes'] = [];
			//check if product is configurable
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$product = $objectManager->get('Magento\Catalog\Model\Product')->load($items->getEntityId());
				$productType = $product->getTypeID();
				if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
					$product = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($items->getEntityId());
					$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
					$randomproducts['products'][$xf]['is_configurable'] = "1";
					$newarray = array();
					foreach($productAttributeOptions as $option){
						$dta['attribute_label'] =$option['label'];
						$dta['attribute_id'] =$option['attribute_id'];
						$dta['attribute__option'] =$option['values'];
						$newarray[] = $dta;
					}
					$randomproducts['products'][$xf]['configurable_attributes'] = $newarray;
				}
			//Ends here	
			$xf++;
		}
		return $randomproducts;
		// ends here
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
