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
class ProducDetail extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() {
        try {
            $result = [];
            $request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
            $prod_id = $param['prod_id'];
            $user_id = $param['user_id'];
            $pro_data = [];
			
			
            $product = $this->productFactory->load($prod_id);
            $this->_view->loadLayout();
            if (($product->getId()) && ($product->getStatus() == 1)) {
				$getData = $product->getData();
				$data['productId'] = $product->getId();
				$data['title'] = $getData['name'];
				$data['sku'] =  $getData['sku'];
				$product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($product->getId());
				$data['company'] = $product->getAttributeText('manufacturer')?$product->getAttributeText('manufacturer'):'';;
				$product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($product->getId());
				$productType = $product->getTypeID();
				
				
				
				
				$data['price'] = number_format(strval($this->GetConvertedPrice($param,$product->getPrice())?$this->GetConvertedPrice($param,$product->getPrice()):$product->getPrice()),2);
				$data['is_configurable'] = "0";
				$data['config_attributes'] =[];
					if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE){
						$data['is_configurable'] = "1";
						$data['price'] = strval($this->GetConvertedPrice($param,$product->getFinalPrice())?$this->GetConvertedPrice($param,$product->getFinalPrice()):$product->getFinalPrice());
						//--------Get option array for Configurable product--------
						$product = $this->_objectManager->create('Magento\Catalog\Model\ProductFactory')->create()->load($product->getId());
						$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
						$newarray = array();
						$x= 0;
						$hex_code = [];
						foreach($productAttributeOptions as $option){
							$dta['attribute_label'] =$option['label'];
							$dta['attribute_id'] =$option['attribute_id'];
							$newdata =[];
								foreach($option['values'] as $hexkeys=>$hexdata){
									$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection'); 
									$connection = $resource->getConnection(); 
									$tableName = $resource->getTableName('eav_attribute_option_swatch');
									$sql = "Select * FROM " . $tableName." where option_id = ".$hexdata['value_index']; 
									$result_query = $connection->fetchAll($sql); 
									$hex_code['value_index'] = $hexdata['value_index'];
									$hex_code['label'] = $hexdata['label'];
									$hex_code['product_super_attribute_id'] = $hexdata['product_super_attribute_id'];
									$hex_code['default_label'] = $hexdata['default_label'];
									$hex_code['store_label'] = $hexdata['store_label'];
									$hex_code['use_default_value'] = $hexdata['use_default_value'];
									$hex_code['hex_code'] = $result_query[0]['value']? $result_query[0]['value']:'';
									$newdata[] = $hex_code;
								}
								$dta['attribute__option'] = $newdata;
							$newarray[] = $dta;
						}
						$data['config_attributes'] = $newarray;
					}
					//~ echo "<pre>";print_r($product->getSpecialPrice());die;
				$data['discountedPrice'] = strval($this->GetConvertedPrice($param,$product->getFinalPrice())?$this->GetConvertedPrice($param,$product->getFinalPrice()):$product->getFinalPrice());
				//~ if($user_id == ''){
					//~ $data['isFavorite'] = "0";
				//~ }else{
					//~ $res = $this->_getWishlistItems($user_id);
					//~ if(!empty($res['return']['products'])){
						//~ foreach($res['return']['products'] as $key=>$value){
							//~ if($value['product_id'] == $product->getId()){
								//~ $data['isFavorite'] ="1";
							//~ }
						//~ }
					//~ }
				//~ }
				$data['final_price'] = strval($this->GetConvertedPrice($param,$product->getFinalPrice())?$this->GetConvertedPrice($param,$product->getFinalPrice()):$product->getFinalPrice());
				
				$currentproduct = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product->getId());
				$str = str_replace(PHP_EOL, '', strip_tags($currentproduct->getDescription()));
				$des = str_replace("\r", '', $str); 
                $data['description'] = $des;
				
				$infoOptions = [];
			    //----------Get Addtional info-------------
                $addtional_info = [];
                $attributes = $product->getAttributes();
                foreach ($attributes as $attribute) {
                    //if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                    if ($attribute->getIsVisibleOnFront()) {
                        $value = $attribute->getFrontend()->getValue($product);

                        if (!$product->hasData($attribute->getAttributeCode())) {
                            $value = __('N/A');
                        } elseif ((string) $value == '') {
                            $value = __('No');
                        } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                            $value = $this->_objectManager->get('Magento\Checkout\Helper\Data')->convertPrice($value, true);
                        }

                        if (is_string($value) && strlen($value)) {
                            $addtional_info[] = array(
                                'label' => $attribute->getStoreLabel(),
                                'value' => $value,
                                'code' => $attribute->getAttributeCode()
                            );
                        }
                    }
                }

                $addtional_info = array_merge($addtional_info, $infoOptions);
                $data['specification'] = $addtional_info;
                
                 //check if product is in the cart
					$cart = $this->_objectManager->get('\Magento\Checkout\Model\Session')->getQuote();
					$result = $cart->getAllVisibleItems();
					$itemsIds = array();
					foreach ($result as $cartItem) {
						array_push($itemsIds, $product->getId());
					}
					if(in_array($product->getId(), $itemsIds)){
						$data['is_addtocart'] = true;
					}else{
						$data['is_addtocart'] = false;
					}
				//ends here
				
				$data['is_wishlist'] = false;
				$data['wishlist_item_id'] = '';
				if(isset($param['user_id']) && $param['user_id'] !=''){
					 //echo "<pre>";print_r($this->checkInWishilist($prod_id, $param['user_id']));die;
					 
					if($wishlist_itemid = $this->CheckIfProductInWishList($param['user_id'],$param['prod_id'])){
						
						$data['is_wishlist'] = true;
						$data['wishlist_item_id'] =$wishlist_itemid;
					}
				}
				
                
                // array of offer
                $offerarray =array(array("label"=>"trophy","text"=>"Money Back Guaranteed","image"=>""),array("label"=>"undo","text"=>"Enjoy hassle free returns with this offer.","image"=>""),array("label"=>"chevron","text"=>"old by Dream","image"=>""),array("label"=>"ship","text"=>" TRUSTED SHIPPING Free shipping when you spend $100 and above","image"=>""),array("label"=>"retweet","text"=>"EASY RETURNS Free returns on eligible items so you can shop with ease","image"=>""),array("label"=>"shield","text"=>"SECURE SHOPPING Your data is always protected","image"=>""));
            
                foreach($offerarray as $keys=>$details){
					$offerdata['label']= $details['label'];
					$offerdata['text']= $details['text'];
					$offerdata['image']= $details['image'];
					$data['offer'][] = $offerdata;
				}
				
                
                //end
                
                // add media gallery of products
                $image = $product->getMediaGalleryImages();
             
                //~ $zarr = array();
                //~ $i = 0;

                //~ if (empty($zarr)) {
                    //~ $imgurl = '';
                    //~ if ($product->getImage() && (($product->getImage()) != 'no_selection')) {
                        //~ $imgurl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
                    //~ }

                    //~ $zarr[$i]['url'] = $imgurl;
                    //~ $zarr[$i]['imgname'] = '';
                    //~ $i++;
                //~ }
				//~ $imgurl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
				    $zarr = array();
					$i = 0;
					$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
					$product = $objectManager->create('Magento\Catalog\Model\Product')->load($prod_id);        
					$images = $product->getMediaGalleryImages();
					foreach($images as $child){ 
						$zarr[$i]['url'] = $child->getUrl();
						$zarr[$i]['imgname'] = $child->getFile();
						$i++;
					}
					 $data['images'] = $zarr;			
				
                //~ foreach ($image as $img) {
                    //~ if ($imgurl == $img->getUrl())
                        //~ continue;

                    //~ $zarr[$i]['url'] = $img->getUrl();
                    //~ $zarr[$i]['imgname'] = $img->getFile();
                    //~ $i++;
                //~ }
                //~ $data['images'] = $zarr;
                //ends here
                
                //code to check if product is in the stock
                if ($product->isSalable() == 1)
                    $data["in_stock"] = 1;
                else
                    $data["in_stock"] = 0;
                    
                    
                //get review 
					$data['review_list'] = $this->_listProductReviews();
                //end here
                
                $reviewsCount = $this->_objectManager->get("Magento\Review\Model\ReviewFactory")->create()->getTotalReviews($product->getId(), true, $this->_storeManager->getStore()->getId());

                $RatingOb = $this->_objectManager->get('Magento\Review\Model\Rating')->getEntitySummary($product->getId());
                $ratings = $RatingOb->getCount() > 0 ? ($RatingOb->getSum() / $RatingOb->getCount()) : false;
                if ($ratings == false) {
                    $ratings = 0;
                }
                $data['review_count'] = $reviewsCount;
                $data['rating_percent'] = (float)number_format($ratings, 2);
                $purl = $product->getProductUrl(); //$store->getBaseUrl($store::URL_TYPE_WEB) . $ppath;
				$data['product_url'] = $purl;
               
                $jsonArray['data'] = $data;
                $jsonArray['currency'] = $this->GetCurrency($param);
				$jsonArray['message'] = "Get data Succesfully";
				$jsonArray['status'] =  "success";
				$jsonArray['status_code'] =  200;
            } else {
				$jsonArray['data'] = null;
				$jsonArray['message'] = "Bad Request";
				$jsonArray['status'] =  "failure";
				$jsonArray['status_code'] =  400;
            }
        } catch (\Exception $e) {
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
    
    public function CheckIfProductInWishList($userid,$productid){
		$objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
		$id =  $userid;
		$wishlist            = $objectManager->get('\Magento\Wishlist\Model\Wishlist');
		$wishlist_collection = $wishlist->loadByCustomerId( $id , true)->getItemCollection();
		$_in_wishlist        = "false";
		foreach ($wishlist_collection->getData() as $key=>$wishlist_product){
			if($productid == $wishlist_product['product_id']){
			  $return= $wishlist_product['wishlist_item_id'];
			  return $return;
			}
		}
	}

    
}
