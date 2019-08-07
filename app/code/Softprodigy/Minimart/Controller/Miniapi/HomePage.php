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
            $subs = [];
            $subs['subs_closed'] = false;
            $subs['active_package'] = 'Gold';
            $pkgtype = $this->pkgCode[$subs['active_package']];
            $catIds = $this->__helper->getStoreConfig('minimart/minimart_registration/categories');
            
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		    $baseurl = $storeManager->getStore()->getBaseUrl();
		    //~ $baseurl2 = explode("index.php",$baseurl);  
		    //~ $baseurl = $baseurl2[0];
		  
			$staticdata['type'] = "0";
			$staticdata['multiple_banners'][0]['image'] ="http://dreamarkets.com/pub/media/mageplaza/bannerslider/banner/image/e/n/en_banner-01.png";
			$staticdata['multiple_banners'][0]['id'] = "1";
			$staticdata['multiple_banners'][1]['image'] = "http://dreamarkets.com/pub/media/mageplaza/bannerslider/banner/image/e/n/en_cat-module-01.gif";
			$staticdata['multiple_banners'][1]['id'] = "2";
			$data[] =$staticdata;
			
			

			
			// code for best selling products
				$data[] = $this->getbestsellingproducts($baseurl);
			// ends here
			
			$static1data['type'] = "3";
			$static1data['two_banners'][0]['image'] = "http://dreamarkets.com/pub/media/magiccart/magicproduct/catalog//f/3/f3.jpg";
			$static1data['two_banners'][0]['id'] = "1";
			$static1data['two_banners'][1]['image'] = "http://dreamarkets.com/pub/media/magiccart/magicproduct/catalog//f/6/f6.jpg";
			$static1data['two_banners'][1]['id'] = "2";
			$data[] =$static1data;
			
			// code for featured products
				$data[] = $this->getfeaturedproducts($baseurl);
			// ends here
			
			// code for random products
				$data[] = $this->getrandomproducts($baseurl);
			// ends here

			//code for other categories
				$categories =array(3,6,62,7,8,93);
				foreach ($categories as $category) {
					$data[]  = $this->getProductCollection($category);
				}	
				
				
			//codes ends here
		
            //~ $jsonArray['response'] = $data;
            //~ $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
            
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

	
	public function getProductCollection($catid){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	    $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseurl = $storeManager->getStore()->getBaseUrl();
		
		$categoryId = $catid;
		$category = $this->_categoryFactory->create()->load($categoryId);
		$collection = $this->_productCollectionFactory->create();
		$collection->setPageSize(10)->setCurPage(1);
		$collection->addAttributeToSelect('*');
		$collection->addCategoryFilter($category);
		$collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
		$collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
		foreach( $collection as $item){
			$othercategoryproducts['type'] = "1";
			$othercategoryproducts['title'] = $category->getName();
			$othercategoryproducts['category_id'] = $categoryId;
			$x= 0;
			foreach ($collection as $item) {
				$othercategoryproducts['products'][$x]['id'] = $item->getEntityId();
				$othercategoryproducts['products'][$x]['image'] = $baseurl."pub/media/catalog/product".$item->getImage();
				$othercategoryproducts['products'][$x]['company'] = "";
				$othercategoryproducts['products'][$x]['name'] = $item->getname();
				$othercategoryproducts['products'][$x]['original_price'] = $item->getPrice();
				$othercategoryproducts['products'][$x]['discounted_price'] = $item->getFinalPrice();
				$othercategoryproducts['products'][$x]['sku'] =  $item->getSku();
				$othercategoryproducts['products'][$x]['qty'] =  $this->GetQty($item->getEntityId());
				$othercategoryproducts['products'][$x]['is_configurable'] = "0";
				$othercategoryproducts['products'][$x]['configurable_attributes'] = [];
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
	
	public function getbestsellingproducts($baseurl){
		//code for getBestsellerProducts
		$collection = $this->_objectManager->get('\Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory')->create()->setModel('Magento\Catalog\Model\Product');
		$collection->setPageSize(10)->setCurPage(1);
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
			$bestsellingproducts['products'][$x]['qty'] =  $this->GetQty($item->getEntityId());
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
	
	
	public function getfeaturedproducts($baseurl){
		// code for fetured list products
		$collection = $this->_productCollectionFactory->create();
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection->addAttributeToFilter('featured', '1')
					->addStoreFilter()
					->addAttributeToSelect('*')
					->addMinimalPrice()
					->addFinalPrice()
					->addTaxPercents()
					->setPageSize(10)->setCurPage(1);;
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
			$featureproducts['products'][$xf]['qty'] =  $this->GetQty($items->getEntityId());
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
	
	public function getrandomproducts($baseurl){
		// code for random products
		$collection = $this->_productCollectionFactory->create();
		$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
		$collection = $this->_addProductAttributesAndPrices(
			$collection
		)->addStoreFilter();
		$collection->getSelect()->order('rand()');
		// getNumProduct
		$collection->setPageSize(10)->setCurPage(1);
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
			$randomproducts['products'][$xf]['qty'] = $this->GetQty($items->getEntityId());
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
