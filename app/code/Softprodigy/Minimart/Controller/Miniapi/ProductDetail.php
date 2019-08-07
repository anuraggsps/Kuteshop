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
class ProductDetail extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() {
        try {
            $result = [];
            $request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
            $prod_id = $param['prod_id'];
            $pro_data = [];

            $product = $this->productFactory->load($prod_id);
            $this->_view->loadLayout();

            if (($product->getId()) && ($product->getStatus() == 1)) {
                $zarr = [];
                $getData = $product->getData();

                $pro_data['id'] = $prod_id;
                $pro_data['sku'] = $getData['sku'];
                $pro_data['name'] = $getData['name'];
                $pro_data['price'] = isset($getData['price']) ? number_format($this->currencyHelper->currency($getData['price'], false, false), 2) : '0.00';

                $store = $this->_storeManager->getStore();
                // $ppath = Mage::getResourceModel('core/url_rewrite')->getRequestPathByIdPath('product/' . $product->getId(), $store);

                $purl = $product->getProductUrl(); //$store->getBaseUrl($store::URL_TYPE_WEB) . $ppath;

                $pro_data['product_url'] = $purl;

                /* min product to cart */
                $productStockRepositry = $this->_objectManager->create("Magento\CatalogInventory\Model\Stock\StockItemRepository");
                $productStock = $productStockRepositry->get($product->getId());

                $minimumQty = $productStock->getMinSaleQty();
                $pro_data['min_qty_sale'] = $minimumQty;

                $this->registry->register('product', $product);
                $this->registry->register('current_product', $product);

                $proViewBlock = $this->_objectManager->get("Magento\Catalog\Pricing\Render");
                $proViewBlock->setPriceRender("product.price.render.default");
                $proViewBlock->setPriceTypeCode("tier_price");
                $proViewBlock->setZone("item_view");

                // $proViewBlock->setTierPriceTemplate('catalog/product/view/tierprices.phtml');
                //echo $proViewBlock->toHtml();
                $pro_data['tier_price_html'] = $proViewBlock->toHtml(); //html_entity_decode();

                $pro_data['price_html'] = '';

                $contentFiler = $this->_filterProvider->getPageFilter();
                // ->filter($page->getContent());

                $pro_data['final_price'] = number_format($this->currencyHelper->currency($product->getFinalPrice(), false, false), 2);
                $pro_data['description'] = base64_encode(html_entity_decode($contentFiler->filter($product->getDescription())));
                $pro_data['short_description'] = ''; //html_entity_decode($contentFiler->filter($getData['description']));
                $finalPr = $product->getFinalPrice();
                if ($product->getTypeId() == 'grouped') {
                    $oModel = $this->_objectManager->get("Magento\GroupedProduct\Model\Product\CatalogPrice");
                    $minprice = $oModel->getCatalogPrice($product);
                    $pro_data['final_price'] = number_format($minprice, 2);
                    $finalPr = $minprice;
                } else if ($product->getTypeId() == 'bundle') {
                    $oModel = $this->_objectManager->get("Magento\Bundle\Model\Product\Price");
                    $tierprice = $oModel->getTotalPrices($product);
                    if (is_array($tierprice)) {
                        $finalPr = $tierprice[0];
                        $pro_data['price_html'] = __('From %1 - Upto %2', $this->currencyHelper->currency($tierprice[0], true, false), $this->currencyHelper->currency($tierprice[1], true, false)); //number_format($tierprice,2);
                    } else {
                        $finalPr = $tierprice;
                        $pro_data['final_price'] = number_format($this->currencyHelper->currency($tierprice, false, false), 2);
                    }
                }

                $pro_data['final_disc'] = "0";
                if (floatval($product->getPrice()) > 0 && floatval($finalPr) < floatval($product->getPrice()))
                    $pro_data['final_disc'] = number_format(100 - (floatval($finalPr) / floatval($product->getPrice()) * 100), 2);


                $isdeschtml = preg_match_all("/(<video|<img|<embed|<audio|<object)/", $pro_data['description']);

                if ($isdeschtml > 0) {
                    $pro_data['is_desc_html'] = true;
                    $pro_data['description'] = "<div style='font-size: 48px;'>" . $pro_data['description'] . "</div>";
                } else {
                    $pro_data['is_desc_html'] = false;
                }


                $issortdeschtml = preg_match_all("/(<video|<img|<embed|<audio|<object)/", $pro_data['short_description']);

                if ($issortdeschtml > 0) {
                    $pro_data['is_sort_desc_html'] = true;
                    $pro_data['short_description'] = "<div style='font-size: 48px;'>" . $pro_data['short_description'] . "</div>";
                } else {
                    $pro_data['is_sort_desc_html'] = false;
                }


                if (!empty($getData['group_price']))
                    $pro_data['group_price'] = $getData['group_price'];
                else
                    $pro_data['group_price'] = [];

                if (!empty($getData['group_price']))
                    $pro_data['tier_price'] = $getData['tier_price'];
                else
                    $pro_data['tier_price'] = [];

                //var_Dump($getData['type_id']); die;
                $pro_data['price_type'] = null;

                if ($getData['type_id'] == 'bundle') {
                    if ($product->getPriceType() == '1')
                        $pro_data['price_type'] = "fixed";
                    else
                        $pro_data['price_type'] = "dynamic";
                }

                $pro_data['type_id'] = $getData['type_id'];
                if (isset($getData['is_recurring']))
                    $pro_data['is_recurring'] = $getData['is_recurring'];
                else
                    $pro_data['is_recurring'] = 0;

                $pro_data['start_date_is_editable'] = 0;

                if ($product->isSalable() == 1)
                    $pro_data["in_stock"] = 1;
                else
                    $pro_data["in_stock"] = 0;

                $image = $product->getMediaGalleryImages();
                $zarr = array();
                $i = 0;

                if (empty($zarr)) {
                    $imgurl = '';
                    if ($product->getImage() && (($product->getImage()) != 'no_selection')) {
                        $imgurl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
                    }

                    $zarr[$i]['url'] = $imgurl;
                    $zarr[$i]['imgname'] = '';
                    $i++;
                }

                foreach ($image as $img) {
                    if ($imgurl == $img->getUrl())
                        continue;

                    $zarr[$i]['url'] = $img->getUrl();
                    $zarr[$i]['imgname'] = $img->getFile();
                    $i++;
                }
                //echo "<pre>";
                //print_r($zarr); 



                $pro_data['images'] = $zarr;

                $reviewsCount = $this->_objectManager->get("Magento\Review\Model\ReviewFactory")->create()->getTotalReviews($prod_id, true, $this->_storeManager->getStore()->getId());

                $RatingOb = $this->_objectManager->get('Magento\Review\Model\Rating')->getEntitySummary($product->getId());
                $ratings = $RatingOb->getCount() > 0 ? ($RatingOb->getSum() / $RatingOb->getCount()) : false;
                if ($ratings == false) {
                    $ratings = 0;
                }

                //echo "<pre>";
                //echo $ratings;

                $pro_data['review_count'] = $reviewsCount;
                $pro_data['rating_percent'] = (float) number_format($ratings, 2);

                //----------Custom Option -----------------
                $pro_data['options'] = array();
                if ($product->getHasOptions()) {
                    $incr = 0;
                    foreach ($product->getOptions() as $in => $o) {
                        $op = $o->getData();
                        $op['price'] = number_format($op['price'], 2);
                        $op['default_price'] = number_format($op['default_price'], 2);

                        $op['default_title'] = html_entity_decode($op['default_title']);
                        $op['store_title'] = html_entity_decode($op['store_title']);
                        $op['title'] = html_entity_decode($op['title']);

                        $result[$incr] = $op;
                        $values = $o->getValues();
                        $result[$incr]['additional_fields'] = [];
                        if(is_array($values)){ 
                            foreach ($values as $v) {
                                $addtData = $v->getData();
                                $addtData['price_percent'] = '0.00';
                                $finaladdprice = 0;
                                if ($addtData['price_type'] == 'percent') {
                                    $perc = number_format($addtData['price'], 2);
                                    $finalprodPrice = $this->currencyHelper->currency($product->getFinalPrice(), false, false);
                                    $addtData['price_percent'] = $perc;
                                    $finaladdprice = ((float) $finalprodPrice / 100) * (float) $perc;
                                } else {
                                    $finaladdprice = number_format($this->currencyHelper->currency($addtData['price'], false, false), 2);
                                }
                                $addtData['price'] = (string) $finaladdprice;
                                $addtData['default_price'] = number_format($addtData['default_price'], 2);
                                $result[$incr]['additional_fields'][] = $addtData;
                            }
                        }  
                        if (empty($values)) {
                            $result[$incr]['price_percent'] = '0.00';
                            if ($result[$incr]['price_type'] == 'percent') {
                                $perc = number_format($result[$incr]['price'], 2);
                                $finalprodPrice = $this->currencyHelper->currency($product->getFinalPrice(), false, false);
                                $result[$incr]['price_percent'] = $perc;
                                $finaladdprice = ((float) $finalprodPrice / 100) * (float) $perc;

                                $result[$incr]['price'] = (string) $finaladdprice;
                            }
                        }
                        $incr++;
                    }
                }


                $pro_data['options'] = $result;
                //------------If recurring profile set to 'yes' ------------
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

                $pro_data['specification'] = $addtional_info;

                if (!$this->activePackage) {
                    $subs = $this->checkPackageSubcription();
                    if ($subs['active_package']) {
                        $this->activePackage = $subs['active_package'];
                    }
                }
                //----------related Product-----------
                $allRelatedProductIds = $product->getRelatedProductIds();

                $related_prod = array();
                if (!empty($allRelatedProductIds)) {
                    $related_prods = $this->productFactory
                            ->getCollection()
                            ->addAttributeToSelect('name')
                            ->addAttributeToSelect('sku')
                            ->addAttributeToSelect('image')
                            ->addAttributeToSelect('thumbnail')
                            ->addAttributeToSelect('price')
                            ->addAttributeToSelect('special_price');
                    if ($this->activePackage == self::Basic_Package) {
                        $Option = $this->_productOptionCollection->addFieldToSelect('product_id');
                        $Option->getSelect()->group('main_table.product_id');
                        $entityIds = new \Zend_Db_Expr($Option->getSelect()->__toString());
                        $allRelatedProductIds = array_diff($allRelatedProductIds, $entityIds);
                        $related_prods->addAttributeToFilter('type_id', array('in' => array('simple')));
                    } else if ($this->activePackage == self::Basic_Exd_Package) {
                        $related_prods->addAttributeToFilter('type_id', array('in' => array('simple')));
                    } else if ($this->activePackage == self::Silver_Package) {
                        $related_prods->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable', 'virtual')));
                    }
                    $related_prods->addAttributeToFilter('entity_id', array('in' => $allRelatedProductIds));
                    $related_prods->addAttributeToFilter('status', array('eq' => 1));
                    $r = 0;
                    foreach ($related_prods as $related) {
                        $related_prod[$r]['name'] = $related->getName();
                        $related_prod[$r]['id'] = $related->getId();
                        $related_prod[$r]['name'] = $related->getName();
                        //~ $related_prod[$r]['minimal_price'] = $this->getMinimalPrice($related);

                        $imageUrl = '';
                        if ($related->getImage() && (($related->getImage()) != 'no_selection')) {
                            // $imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($related->getImage());
                            $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $related->getImage();
                        }

                        if (empty($imageUrl)) {
                            if ($related->getThumbnail() && (($related->getThumbnail()) != 'no_selection')) {
                                $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $related->getThumbnail();
                                //$imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($related->getThumbnail());
                            }
                        }

                        $related_prod[$r]['img'] = $imageUrl; //Mage::getModel('catalog/product_media_config')->getMediaUrl($related->getImage());
                        $related_prod[$r]['price'] = number_format($this->currencyHelper->currency($related->getPrice(), false, false), 2);
                        $related_prod[$r]['final_price'] = number_format($this->currencyHelper->currency($related->getFinalPrice(), false, false), 2);
                        $related_prod[$r]['final_disc'] = "0";
                        if (floatval($related->getPrice()) > 0 && floatval($related->getFinalPrice()) < floatval($related->getPrice()))
                            $related_prod[$r]['final_disc'] = number_format(100 - (floatval($related->getFinalPrice()) / floatval($related->getPrice()) * 100), 2);

                        ++$r;
                    }
                }
                $pro_data['related'] = $related_prod;

                //----------get Associated product of Grouped product---------------
                $a_prod = array();
                if ($pro_data['type_id'] == 'grouped') {
                    $associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
                    $j = 0;
                    foreach ($associatedProducts as $asso_prod) {
                        //var_dump($asso_prod->getData()); die;
                        $a_prod[$j]['id'] = $asso_prod->getId();
                        $a_prod[$j]['name'] = $asso_prod->getName();
                        $a_prod[$j]['img'] = $asso_prod->getImageUrl();
                        $a_prod[$j]['price'] = number_format($this->currencyHelper->currency($asso_prod->getPrice(), false, false), 2);
                        $a_prod[$j]['qty'] = number_format($asso_prod->getQty());
                        $a_prod[$j]['final_price'] = number_format($this->currencyHelper->currency($asso_prod->getFinalPrice(), false, false), 2);
                        $a_prod[$j]['is_in_stock'] = $asso_prod->getData('is_in_stock');
                        ++$j;
                    }
                }
                $pro_data['associated'] = $a_prod;


                //--------Get option array for Configurable product--------
                $configroup = array();
                $product_attrs = array();
                $newArray = array();
                if ($pro_data['type_id'] == 'configurable') {
                    $product_attrs = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);

                    $confBlock = $this->_objectManager->get("Magento\ConfigurableProduct\Block\Product\View\Type\Configurable");
                    $confBlock->setProduct($product);
                    $configroup = $confBlock->getJsonConfig();

                    $newArray = json_decode($configroup, true);
                    if (isset($newArray['attributes'])) {
                        //$attrs = $newArray['attributes'];
                        //unset($newArray['attributes']);
                        $newArray['attributes'] = array_values($newArray['attributes']);
                    }
                    $attrs = $newArray['attributes'];
                }
                if (empty($newArray)) {
                    $newArray = new \stdClass();
                }
                $pro_data['config_attributes'] = $newArray;


                //---------get bundled Items ------------
                $imgPrepend = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product';
                $bundle = array();
                if ($pro_data['type_id'] == 'bundle') {

                    $selectionCollection = $product->getTypeInstance(true)->getSelectionsCollection($product->getTypeInstance(true)->getOptionsIds($product), $product);

                    $bundled_items = array();

                    foreach ($selectionCollection as $option) {

                        //$stock = $option->getStockItem()->getData();
                        $optData = $option->getData();
                        //$optData['stock_item'] = $stock;
                        if(isset($optData['image']))
                            $optData['image'] = $imgPrepend . $optData['image'];
                        else
                            $optData['image'] = '';
                        
                        if(isset($optData['small_image']))
                            $optData['small_image'] = $imgPrepend . $optData['small_image'];
                        else
                            $optData['small_image'] = '';
                        
                        if(isset($optData['thumbnail']))
                            $optData['thumbnail'] = $imgPrepend . $optData['thumbnail'];
                        else
                            $optData['thumbnail'] = '';
                        
                         
                        $bundled_items[$optData['option_id']][] = $optData;
                    }
                    //var_dump(json_encode($bundled_items));
                    //die;
                    $selectionCollection = $product->getTypeInstance(true)->getOptions($product);
                    $inc = 0;
                    $bundle = array();
                    foreach ($selectionCollection as $in => $o) {
                        $op = $o->getData();
                        if (isset($bundled_items[$op['option_id']])) {
                            $_secls = $bundled_items[$op['option_id']];
                            $copyOf_secl = array();
                            foreach ($_secls as $sinx => $__secl) {

                                if ($__secl['selection_price_type'] == '1') {
                                    $Optperc = (float) $__secl['selection_price_value'];
                                    $finalOptprodPrice = $this->currencyHelper->currency($product->getFinalPrice(), false, false);
                                    $finalOptaddprice = ((float) $finalOptprodPrice / 100) * (float) $Optperc;
                                    $__secl['price'] = $finalOptaddprice;
                                } else {
                                    if ("fixed" == $pro_data['price_type']) {
                                        $__secl['price'] = $this->currencyHelper->currency($__secl['selection_price_value'], false, false);
                                    }
                                }

                                $__secl['selection_price_value'] = number_format($this->currencyHelper->currency($__secl['selection_price_value'], false, false), 2);
                                $__secl['selection_qty'] = number_format($__secl['selection_qty']);

                                //if price type is percentage


                                $__secl['price'] = number_format($__secl['price'], 2);
                                $unrequired = array('created_at', 'updated_at', 'msrp_enabled', 'msrp_display_actual_price_type', 'news_from_date', 'news_to_date');
                                foreach ($unrequired as $_unsv) {
                                    unset($__secl[$_unsv]);
                                }

                                $copyOf_secl[$sinx] = $__secl;
                            }
                            $op['selections'] = $copyOf_secl;
                        }

                        $bundle[$inc] = $op;
                        $inc++;
                    }
                    // var_dump(json_encode($result2));
                    //die;
                }
                $pro_data['bundle_options'] = $bundle;

                //---------get downloadable info ------------
                $downloadable = array();
                if ($pro_data['type_id'] == 'downloadable') {
                    $sampleBlock = $this->_objectManager->get('Magento\Downloadable\Block\Catalog\Product\Samples');
                    $sampleBlock->setProduct($product);
                    $downloadable['samples'] = array();
                    if ($sampleBlock->hasSamples()) {
                        $downloadable['samples']['title'] = $sampleBlock->getSamplesTitle();
                        $downloadable['samples']['prepend_url'] = $this->actionBuilder->getUrl('downloadable/download/sample');
                        $downloadable['samples']['rows'] = $sampleBlock->getSamples()->getData();
                    } else {
                        $downloadable['samples'] = new \stdClass;
                    }

                    $downloadable['links'] = array();
                    $linksBlock = $this->_objectManager->get('Magento\Downloadable\Block\Catalog\Product\Links');
                    $linksBlock->setProduct($product);
                    if ($linksBlock->hasLinks()) {
                        $downloadable['links']['title'] = $linksBlock->getLinksTitle();
                        $downloadable['links']['link_selection_required'] = $linksBlock->getLinkSelectionRequired();
                        $downloadable['links']['links_purchased_separately'] = $linksBlock->getLinksPurchasedSeparately();
                        $downloadable['links']['prepend_link'] = $this->actionBuilder->getUrl('downloadable/download/linkSample');
                        $downloadable['links']['is_visible'] = ($downloadable['links']['link_selection_required'] == 0 && $downloadable['links']['links_purchased_separately'] == 0) ? false : true;
                        $_links = $linksBlock->getLinks();
                        foreach ($_links as $_link) {
                            $ldata = $_link->getData();

                            $var = array(
                                'id' => $ldata['link_id'],
                                'title' => $linksBlock->escapeHtml($ldata['title']),
                                'is_shareable' => $ldata['is_shareable'],
                                'sample_file' => $ldata['sample_file'],
                                'sample_url' => $linksBlock->getLinkSamlpeUrl($_link),
                                'link_price' => number_format($this->currencyHelper->currency($ldata['price'], false, false), 2)/* $linksBlock->getFormattedLinkPrice($_link) */,
                                'is_required' => $linksBlock->getLinkSelectionRequired()
                            );
                            $downloadable['links']['rows'][] = $var;
                        }
                    } else {
                        $downloadable['samples'] = new stdClass;
                    }
                }
                $pro_data['downloadable_options'] = $downloadable;

                try {
                    $visitorid = $this->getRequest()->getParam('visitor_id', false);
                    if (!empty($visitorid)) {
                        $this->_loadVisitorById($visitorid);
                    }

                    $this->_eventManager->dispatch('catalog_controller_product_view', [
                        'product' => $product
                    ]);
                } catch (\Exception $ex) {
                    $this->logger->degub($ex);
                }


                $pro_data['wishlist_item_id'] = '';
                try {
                    $customer_id = $this->getRequest()->getParam('cust_id');
                    $witemid = $this->checkInWishilist($prod_id, $customer_id);
                    $pro_data['wishlist_item_id'] = $witemid;
                } catch (\Exception $ex) {
                    $this->logger->error($ex->__toString());
                }

                $pro_data['custom_banner'] = [];
                if ($this->__helper->getStoreConfig('minimart/custom_banner_settings/banner_product_image')) {
                    $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'softprodigy/slider/';
                    $pro_data['custom_banner']['img'] = $url . $this->__helper->getStoreConfig('minimart/custom_banner_settings/banner_product_image');
                    $linkType = $this->__helper->getStoreConfig('minimart/custom_banner_settings/banner_product_type');
                    $linkVal = $this->__helper->getStoreConfig('minimart/custom_banner_settings/banner_product_value');
                    $hasLink = false;
                    if (!empty($linkType) && !empty($linkVal)) {
                        $hasLink = true;
                    }
                    $pro_data['custom_banner']['has_link'] = $hasLink;
                    $pro_data['custom_banner']['link_type'] = $linkType;
                    $pro_data['custom_banner']['link_val'] = $linkVal;
                    if ($linkType == 'product') {
                        $product = $this->productFactory->load($linkVal);
                        $pro_data['custom_banner']['link_val'] = $linkVal . "#" . $product->getTypeId();
                    } else if ($linkType == 'page') {
                        $page = $this->_objectManager->get('Magento\Cms\Model\Page')->load($linkVal, 'identifier');
                        $pro_data['custom_banner']['link_val'] = $this->_filterProvider->getPageFilter()->filter($page->getContent());
                    }
                }
                if (empty($pro_data['custom_banner']))
                    $pro_data['custom_banner'] = new \StdClass;

                $pro_data['youtube_video'] = $product->getYoutubeVideo()? $product->getYoutubeVideo(): '';
                $pro_data['is_featured'] = (int) $product->getIsFeatured();
                $pro_data['is_new_prod'] = (int) $this->isProductNew($product);
                //$data['jchat_id'] = $customerObj->getJchatId();
                
                $jsonArray['response'] = $pro_data;
                $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
            } else {
                $jsonArray['response'] = "Please check product id.";
                $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
            }
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
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
