<?php

namespace Softprodigy\Minimart\Controller\Miniapi;

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
class Productlist extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            if (!$this->activePackage) {
                /* $subs = $this->checkPackageSubcription();
                  if ($subs['active_package']) {
                  $this->activePackage = $subs['active_package'];
                  } */
            }

            $param = $this->getRequest()->getParams();
            $limit = 20;
            $cat_id = isset($param['cat_id']) ? $param['cat_id'] : false;
            if (empty($cat_id)) {
                $jsonArray['response'] = __("Please enter valid category id.");
                $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
                echo json_encode($jsonArray);
                die;
            }
            $page_no = 1;

            if (!isset($param['page_id']))
                $param['page_id'] = 1;

            $page_no = $param['page_id'];

            $catagory_model1 = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($cat_id);
            $setIds = $catagory_model1->getProductCollection()->getSetIds();
            $paccollection = $this->_objectManager->get('Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection');
            //Mage::getResourceModel('catalog/product_attribute_collection');
            $paccollection
                    ->setItemObjectClass('Magento\Catalog\Model\ResourceModel\Eav\Attribute')
                    ->setAttributeSetFilter($setIds)
                    ->addStoreLabel($this->_storeManager->getStore()->getId())
                    ->setOrder('position', 'ASC');
            $paccollection->addIsFilterableFilter();
            $fl = $paccollection->load();

            $catagory_model = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($cat_id);

            $layout = $this->layoutFactory->create();
            //$layout->loadLayout();
            $filtrableAttrs = array();
            $layerBlock = $this->_objectManager->get('Magento\LayeredNavigation\Block\Navigation\Category'); //

            $layerBlock->getLayer()->setCurrentCategory($catagory_model);
            $_filters = $layerBlock->getFilters();

            $filterarray = array();
            foreach ($_filters as $_filter):
                if ($_filter->getItemsCount()):
                    $options = array();
                    foreach ($_filter->getItems() as $_item):
                        if ($_item->getCount() > 0):

                            $options[] = array('code' => $_item->getFilter()->getRequestVar() == 'cat' ? 'cat_id' : $_item->getFilter()->getRequestVar(),
                                'value' => $_item->getValue(),
                                'label' => strip_tags($_item->getLabel()));
                        endif;
                    endforeach;
                    $filterarray[] = array(
                        'label' => __($_filter->getName()),
                        'options' => $options
                    );

                endif;
            endforeach;

            $filtrableAttrs = $fl->getColumnValues('attribute_code');


            if (isset($filtrableAttrs['cat']))
                unset($filtrableAttrs['cat']);

            $resModel = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');

            $collectionbkp = $this->_objectManager->get('Magento\Catalog\Model\Product')->getCollection();
            $collectionbkp->addAttributeToFilter('status', 1);
            $collectionbkp->addAttributeToSelect('entity_id');
            $collectionbkp->getSelect()->joinLeft(array('pr' => $resModel->getTableName('catalog_product_relation')), 'pr.child_id=e.entity_id', ['pr.parent_id']);

            $hadLFilter = false;

            foreach ($param as $_rkey => $rval) {
                filter_var($_rkey, FILTER_SANITIZE_STRING);
                filter_var($rval, FILTER_SANITIZE_STRING);
                if (in_array($_rkey, $filtrableAttrs)) {
                    $hadLFilter = true;
                    if ($_rkey !== 'price') {
                        $collectionbkp->addAttributeToFilter($_rkey, $rval);
                    } else if ($_rkey == 'price') {
                        $rqval = explode('-', $rval);
                        if (empty($rqval[0])) {
                            $collectionbkp->addAttributeToFilter($_rkey, array('lteq' => $rqval[1]));
                        } else if (empty($rqval[1])) {
                            $collectionbkp->addAttributeToFilter($_rkey, array('gteq' => $rqval[0]));
                        } else {
                            $collectionbkp->addAttributeToFilter($_rkey, array('gteq' => $rqval[0]));
                            $collectionbkp->addAttributeToFilter($_rkey, array('lteq' => $rqval[1]));
                        }
                    }
                }
            }

            $collectionbkp->getSelect()->group('e.entity_id');
        
            if ($hadLFilter) {
                $categoryProducts = $collectionbkp->getColumnValues('entity_id');
                $categoryProductParenst = $collectionbkp->getColumnValues('parent_id');

                $categoryProducts = array_unique(array_filter(array_merge($categoryProducts, $categoryProductParenst)));
            } else {
                $categoryProducts = array();
            }
            
            //var_dump($categoryProducts); die;
            
            $collection = $this->_productCollection; //->addAttributeToSort('position');
            $collection->addCategoryFilter($catagory_model); //category filter
            $collection->addAttributeToFilter('status', 1);
            $collection->addAttributeToSelect('image');
            $collection->addMinimalPrice();
            $collection->addAttributeToFilter('visibility', array('eq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH));

            $collection->addAttributeToSelect('*');

            if ($this->activePackage == self::Basic_Package) {

                $Option = $this->_productOptionCollection->addFieldToSelect('product_id');
                $Option->getSelect()->group('main_table.product_id');
                $entityIds = new \Zend_Db_Expr($Option->getSelect()->__toString());

                $collection->addAttributeToFilter('entity_id', array('nin' => $entityIds));
                $collection->addAttributeToFilter('type_id', array('in' => array('simple')));
            } else if ($this->activePackage == self::Basic_Exd_Package) {
                $collection->addAttributeToFilter('type_id', array('in' => array('simple')));
            } else if ($this->activePackage == self::Silver_Package) {
                $collection->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable', 'virtual')));
            }

            if ($hadLFilter) {
                $collection->addAttributeToFilter('entity_id', array('in' => $categoryProducts));
                //$this->_objectManager->get('Magento\CatalogInventory\Helper\Stock')->addInStockFilterToCollection($collection);
            }

            if (isset($param['sort']) and ! empty($param['sort'])) {
                $sortArray = explode("_", $param['sort']);

                $order = $sortArray[0];
                $direction = strtoupper($sortArray[1]);

                $collection->addAttributeToSort($order, $direction);
            } else {
                $collection->addAttributeToSort("position");
            }
            
           // var_dump($collection->getSelect()->__toString()); die;
            
            $colcBkp = clone $collection;
            $prod_count = count($colcBkp);
           //   var_dump($prod_count); die;
            $collection->setPageSize($limit)->setCurPage($page_no);
            
            $pageno = (isset($param['page_id']) and !empty($param['page_id']))? $param['page_id']: 1;
            
             
            if ($prod_count <= ($limit * $pageno))
                $data['more'] = 0;
            else
                $data['more'] = 1;


            $crCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
            $crSymb = $this->_objectManager->get('Magento\Framework\Locale\CurrencyInterface')->getCurrency($crCode)->getSymbol();

            $filerSybm = str_replace(array(' ', '\n', '\t', '\r', '&nbsp;', '&emsp;'), '', trim($crSymb));

            $isCurCode = $this->__helper->getStoreConfig('minimart/homepage_settings/show_cur_code');

            if (empty($filerSybm) and ( $isCurCode == 1))
                $crSymb = $crCode;
            else if (empty($filerSybm) and ( $isCurCode == 0))
                $crSymb = '';


            $data['currency_symbol'] = $crSymb;
            $data['product'] = array();
            $i = 0;
            $data['filters'] = $filterarray;
            
            foreach ($collection as $product) {
                
               // $isinStock = $product->getStockItem() ? $product->getStockItem()->getIsInStock() : 0;
                
                if ($hadLFilter and !$product->isAvailable()) {
                    continue;
                }
                
                $data['product'][$i]['product_id'] = $product->getId();
                $data['product'][$i]['type_id'] = $product->getTypeId();
                $data['product'][$i]['name'] = $product->getName();
                $data['product'][$i]['final_price'] = $this->currencyHelper->currency($product->getFinalPrice(), false, false);
                $data['product'][$i]['price'] = $this->currencyHelper->currency($product->getPrice(), false, false);
                $data['product'][$i]['minimal_price'] = $this->getMinimalPrice($product);
                
                $data['product'][$i]['inWishlist'] = '';
                try {
                    $customer_id = $this->getRequest()->getParam('cust_id');
                    $witemid = $this->checkInWishilist($product->getId(), $customer_id);
                    $data['product'][$i]['inWishlist'] = $witemid;
                } catch (Exception $ex) {
                     $data['product'][$i]['inWishlist'] = "";
                }
                if (!$data['product'][$i]['inWishlist']) {
                    $data['product'][$i]['inWishlist'] = "";
                }
                
                // $data['product'][$i]['price_html'] = strip_tags($this->getLayout()->getBlock('product_list')->getPriceHtml($product, true));
                $imgUrl = '';
                if ($product->getImage() && (($product->getImage()) != 'no_selection')) {
                    $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
                    //$imgUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
                }

                if (empty($imgUrl)) {
                    if ($product->getThumbnail() && (($product->getThumbnail()) != 'no_selection')) {
                        $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getThumbnail();
                        //$imgUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getThumbnail());
                    }
                }


                $data['product'][$i]['price_html'] = ''; //strip_tags($layout->getLayout()->getBlock('product_list')->getPriceHtml($prod, true));
                /* End For price_html */
                if ($product->getTypeId() == 'grouped') {
                    $minprice = '';
                    $oModel = $this->_objectManager->get("Magento\GroupedProduct\Model\Product\CatalogPrice");
                    $minprice = $oModel->getCatalogPrice($product);
                    $data['product'][$i]['final_price'] = number_format($minprice, 2);
                } else if ($product->getTypeId() == 'bundle') {
                    $tierprice = [];
                    $oModel = $this->_objectManager->get("Magento\Bundle\Model\Product\Price");
                    $tierprice = $oModel->getTotalPrices($product);
                    if (is_array($tierprice)) {
                        $data['product'][$i]['price_html'] = __('From %1 - Upto %2', $this->currencyHelper->currency($tierprice[0], true, false), $this->currencyHelper->currency($tierprice[1], true, false)); //number_format($tierprice,2);
                    } else {
                        $data['product'][$i]['final_price'] = number_format($this->currencyHelper->currency($tierprice, false, false), 2);

                        $product->setFinalPrice($tierprice);
                    }
                }

                $data['product'][$i]['final_disc'] = "0";
                if (floatval($product->getPrice()) > 0 && floatval($product->getFinalPrice()) < floatval($product->getPrice()))
                    $data['product'][$i]['final_disc'] = number_format(100 - (floatval($product->getFinalPrice()) / floatval($product->getPrice()) * 100), 2);


                $data['product'][$i]['image'] = $imgUrl; //$product->getImageUrl();
                $data['product'][$i]['in_stock'] = $product->isSalable();
                $data['product'][$i]['created'] = $product->getCreatedAt();

                $RatingOb = $this->_objectManager->get('Magento\Review\Model\Rating')->getEntitySummary($product->getId());
                $ratings = $RatingOb->getCount() > 0 ? ($RatingOb->getSum() / $RatingOb->getCount()) : false;
                if ($ratings == false) {
                    $ratings = 0;
                }
                $data['product'][$i]['rating'] = number_format($ratings, 2);
                $data['product'][$i]['is_new_prod'] = strval((int) $this->isProductNew($product));
                ++$i;
            }

            $jsonArray['response'] = $data;
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }

        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
        //echo json_encode($jsonArray); die;
    }

}
