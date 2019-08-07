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
class GetSearch extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $params = $this->getRequest()->getParams();

            $params['keyword'] = trim($params['keyword']);


            if (!$this->activePackage) {
                $subs = $this->checkPackageSubcription();
                if ($subs['active_package']) {
                    $this->activePackage = $subs['active_package'];
                }
            }
            //----------End custom option product id array-----------------------

            $collection = $this->productFactory->getCollection();
            $collection->addAttributeToSelect('*');
            $collection->addMinimalPrice()
                    ->addFinalPrice();
            $collection->addAttributeToFilter('name', array('like' => '%' . $params['keyword'] . '%'));
            $collection->addAttributeToFilter('status', array('eq' => 1));
            //$collection->addAttributeToFilter('type_id', array('eq' => 'simple'));
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

            $collection->addAttributeToFilter('visibility', array('eq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH));

            $collection->getSelect()->limit(50);
            //$collection->getSelect()->where('e.entity_id not in(?)', $entityIds);//--will comment later in next version----------
            //var_dump($collection->getSelect()->__toString()); die;
            $first_count = $collection->count();

            $collection->addStoreFilter();


            $arrname = array();
            //var_dump($first_count); die;
            if ($first_count != 0) {
                foreach ($collection as $_product) {
                    $RatingOb = $this->_objectManager->get('Magento\Review\Model\Rating')->getEntitySummary($_product->getId());
                    $ratings = $RatingOb->getCount() > 0 ? ($RatingOb->getSum() / $RatingOb->getCount()) : false;
                    if ($ratings == false) {
                        $ratings = 0;
                    }

                    $imgUrl = '';
                    if ($_product->getImage() && (($_product->getImage()) != 'no_selection')) {
                        //$imgUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($_product->getImage());
                        $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getImage();
                    }

                    if ($_product->getThumbnail() && (($_product->getThumbnail()) != 'no_selection')) {
                        $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getThumbnail();
                    }

                    $final_disc = '0';
                    if (floatval($_product->getPrice()) > 0 && floatval($_product->getFinalPrice()) < floatval($_product->getPrice()))
                        $final_disc = number_format(100 - (floatval($_product->getFinalPrice()) / floatval($_product->getPrice()) * 100), 2);

                    $final_price = number_format($this->currencyHelper->currency($_product->getFinalPrice(), false, false), 2);
                    $price_html = '';
                    if ($_product->getTypeId() == 'grouped') {
                        $oModel = $this->_objectManager->get("Magento\GroupedProduct\Model\Product\CatalogPrice");
                        $minprice = $oModel->getCatalogPrice($_product);
                        $final_price = number_format($minprice, 2);
                    } else if ($_product->getTypeId() == 'bundle') {
                        $oModel = $this->_objectManager->get("Magento\Bundle\Model\Product\Price");
                        $tierprice = $oModel->getTotalPrices($_product);
                        if (is_array($tierprice))
                            $price_html = __('From %1 - Upto %2', $this->currencyHelper->currency($tierprice[0], true, false), $this->currencyHelper->currency($tierprice[1], true, false)); //number_format($tierprice,2);
                        else
                            $final_price = number_format($this->currencyHelper->currency($tierprice, false, false), 2);
                    }
                    
                    $inWishlist = "";
                    try {
                        $customer_id = $this->getRequest()->getParam('cust_id');
                        $witemid = $this->checkInWishilist($_product->getId(), $customer_id);
                        $inWishlist = $witemid;
                    } catch (Exception $ex) {
                        $inWishlist = "";
                    }
                    
                    $arrname[] = [
                        'product_id' => $_product->getId(),
                        'type_id' => $_product->getTypeId(),
                        'name' => $_product->getName(),
                        'final_price' => $final_price,
                        'price' => number_format($this->currencyHelper->currency($_product->getPrice(), false, false), 2),
                        'final_disc' => $final_disc,
                        'price_html' => $price_html,
                        'image' => $imgUrl, //$_product->getImageUrl(),
                        'sku' => $_product->getSku(),
                        'in_stock' => $_product->isSalable(),
                        'rating' => number_format($ratings, 2),
                        'inWishlist'=>$inWishlist
                    ];
                }
                $jsonArray ['response'] = $arrname;
                $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
            } else {
                $expl = explode(' ', $params['keyword']);
                $count = count($expl);
                $final = array();
                if ($count > 1) { 
                    $pro_id_arr = array();
                    foreach ($expl as $searchword) {
                        $collection = $this->productFactory->getCollection();
                        $collection->addAttributeToSelect('*');
                        $collection->addMinimalPrice()
                                ->addFinalPrice();
                        $collection->addAttributeToFilter('name', array('like' => '%' . $searchword . '%'));
                        $collection->addAttributeToFilter('status', array('eq' => 1));
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
                        //$collection->addAttributeToFilter('type_id', array('eq' => 'simple'));
                        $collection->addAttributeToFilter('visibility', array('eq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH));

                        //$collection->getSelect()->limit(10);
                        $collection->setPageSize(10);
                        //$collection->getSelect()->where('e.entity_id not in(?)', $entityIds);//--will comment later in next version----------


                        $second_count = $collection->count();

                        //$arrname = array();	
                        if ($second_count > 0) {
                            foreach ($collection as $_product) {
                                if (!in_array($_product->getId(), $pro_id_arr)) {
                                    $RatingOb = $this->_objectManager->get('Magento\Review\Model\Rating')->getEntitySummary($_product->getId());
                                    $ratings = $RatingOb->getCount() > 0 ? ($RatingOb->getSum() / $RatingOb->getCount()) : false;
                                    if ($ratings == false) {
                                        $ratings = 0;
                                    }

                                    $imgUrl = '';

                                    if ($_product->getImage() && (($_product->getImage()) != 'no_selection')) {
                                        // $imgUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($_product->getImage());
                                        $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getImage();
                                    }

                                    if ($_product->getThumbnail() && (($_product->getThumbnail()) != 'no_selection')) {
                                        //$imgUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($_product->getThumbnail());
                                        $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getThumbnail();
                                    }

                                    $final_disc = '0';
                                    if (floatval($_product->getPrice()) > 0 && floatval($_product->getFinalPrice()) < floatval($_product->getPrice()))
                                        $final_disc = number_format(100 - (floatval($_product->getFinalPrice()) / floatval($_product->getPrice()) * 100), 2);


                                    $final_price = number_format($this->currencyHelper->currency($_product->getFinalPrice(), false, false), 2);
                                    $price_html = '';
                                    if ($_product->getTypeId() == 'grouped') {
                                        $oModel = $this->_objectManager->get("Magento\GroupedProduct\Model\Product\CatalogPrice");
                                        $minprice = $oModel->getCatalogPrice($_product);
                                        $final_price = number_format($minprice, 2);
                                    } else if ($_product->getTypeId() == 'bundle') {
                                        $oModel = $this->_objectManager->get("Magento\Bundle\Model\Product\Price");
                                        $tierprice = $oModel->getTotalPrices($_product);
                                        if (is_array($tierprice))
                                            $price_html = __('From %1 - Upto %2', $this->currencyHelper->currency($tierprice[0], true, false), $this->currencyHelper->currency($tierprice[1], true, false)); //number_format($tierprice,2);
                                        else
                                            $final_price = number_format($this->currencyHelper->currency($tierprice, false, false), 2);
                                    }

                                    $final[] = array(
                                        'product_id' => $_product->getId(),
                                        'type_id' => $_product->getTypeId(),
                                        'name' => $_product->getName(),
                                        'final_price' => $final_price,
                                        'price' => number_format($this->currencyHelper->currency($_product->getPrice(), false, false), 2),
                                        'final_disc' => $final_disc,
                                        'price_html' => $price_html,
                                        'image' => $imgUrl, //Mage::getModel('catalog/product_media_config')->getMediaUrl($_product->getImage()),//$_product->getImageUrl(),
                                        'sku' => $_product->getSku(),
                                        'in_stock' => $_product->isSalable(),
                                        'rating' => number_format($ratings, 2),
                                    );

                                    $pro_id_arr[] = $_product->getId();
                                }
                            }
                        }
                    } 
                }  
                $jsonArray ['response'] = $final;
                $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
            }
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

}
