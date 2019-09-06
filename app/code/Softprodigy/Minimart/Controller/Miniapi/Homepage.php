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
class Homepage extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            //-------------Mobile theme color------------
            /* $subs = $this->checkPackageSubcription();
             */
            $subs = [];
            $youmayArray = [];
            $subs['subs_closed'] = false;
            $subs['active_package'] = 'Gold';
            $subscriptionExpire = ($subs and is_array($subs)) ? $subs['subs_closed'] : false;
            if ($subs['active_package']) {
                $this->activePackage = $subs['active_package'];
            }
            $color = [];
            if ($this->__helper->getStoreConfig('minimart/general/enable')) {
                $color['primary'] = $this->__helper->getStoreConfig('minimart/general/primary_color');
                $color['primary_light'] = $this->__helper->getStoreConfig('minimart/general/primary_light_color');
                $color['secondary'] = $this->__helper->getStoreConfig('minimart/general/secondary_color');
            }
            //------------End Mobile theme color---------
            $is_sectionOnly = $this->getRequest()->getParam('only_section', false);
            //---------------slider images ------------------
            $slider = array();
            if ($this->__helper->getStoreConfig('minimart/slider_settings/enable') and ! $is_sectionOnly) {
                $url = $this->__helper->getMediaUrl() . 'softprodigy/slider/';
                $slideIndex = array(
                    'one', 'two', 'three', 'four', 'five', 'six', 'seven'
                );
                foreach ($slideIndex as $_indxr => $slnum) {
                    if ($this->__helper->getStoreConfig('minimart/slider_settings/slide_' . $slnum . '_image')) {
                        $slider[$_indxr]['img'] = $url . $this->__helper->getStoreConfig('minimart/slider_settings/slide_' . $slnum . '_image');
                        $linkType = $this->__helper->getStoreConfig('minimart/slider_settings/slide_' . $slnum . '_link_type');
                        $linkVal = $this->__helper->getStoreConfig('minimart/slider_settings/slide_' . $slnum . '_link_value');
                        $hasLink = false;
                        if (!empty($linkType) && !empty($linkVal)) {
                            $hasLink = true;
                        }
                        $slider[$_indxr]['has_link'] = $hasLink;
                        $slider[$_indxr]['link_type'] = $linkType;
                        $slider[$_indxr]['link_val'] = $linkVal;
                        if ($linkType == 'product') {
                            $product = $this->productFactory->load($linkVal);
                            $slider[$_indxr]['link_val'] = $linkVal . "#" . $product->getTypeId();
                        } else if ($linkType == 'page') {
                            $page = Mage::getModel('cms/page')->load($linkVal, 'identifier');
                            $slider[$_indxr]['link_val'] = $this->_filterProvider->getPageFilter()->filter($page->getContent());
                        }
                    }
                }
            }
            

            //------------End Slider images-----------------
            //custom banner
            $custombanner = array();
            if ($this->activePackage == self::Gold_Package and ! $is_sectionOnly) {
                $url = $this->__helper->getMediaUrl(). 'softprodigy/slider/';
                $bannerIndex = array(
                    'one', 'two', 'three', 'four', 'five'
                );
                foreach ($bannerIndex as $_indxr => $slnum) {
                    if ($this->__helper->getStoreConfig('minimart/custom_banner_settings/banner_' . $slnum . '_image')) {
                        $custombanner[$_indxr]['img'] = $url . $this->__helper->getStoreConfig('minimart/custom_banner_settings/banner_' . $slnum . '_image');
                        $linkType = $this->__helper->getStoreConfig('minimart/custom_banner_settings/banner_' . $slnum . '_link_type');
                        $linkVal = $this->__helper->getStoreConfig('minimart/custom_banner_settings/banner_' . $slnum . '_link_value');
                        $hasLink = false;
                        if (!empty($linkType) && !empty($linkVal)) {
                            $hasLink = true;
                        }
                        $custombanner[$_indxr]['has_link'] = $hasLink;
                        $custombanner[$_indxr]['link_type'] = $linkType;
                        $custombanner[$_indxr]['link_val'] = $linkVal;
                        if ($linkType == 'product') {
                            $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($linkVal);
                            $custombanner[$_indxr]['link_val'] = $linkVal . "#" . $product->getTypeId();
                        } else if ($linkType == 'page') {
                            $page = $this->_objectManager->get('Magento\Cms\Model\Page')->load($linkVal, 'identifier');
                            $custombanner[$_indxr]['link_val'] = $this->_filterProvider->getPageFilter()->filter($page->getContent());
                        }
                    }
                }
            }

            $limit = 10;
            $h = 0;
            $prod = [];
            $page = 1;

            if (!$is_sectionOnly) {
                $Option = $this->_objectManager->get('Magento\Catalog\Model\ResourceModel\Product\Option\Collection')->addFieldToSelect('product_id');
                $Option->getSelect()->group('main_table.product_id');
                $entityIds = new \Zend_Db_Expr($Option->getSelect()->__toString());
                if ($this->activePackage == self::Basic_Package) {
                    $n_collection = $this->newProducts($limit, $page, $entityIds);
                } else {
                    $n_collection = $this->newProducts($limit, $page);
                }
                //----------------New Products-------------------------				

                $n_collection = $this->newProducts($limit, $page);
                //$n_collection = $this->newProducts($limit,$page,$entityIds);
                if (!empty($n_collection['collection'])) {
                    $prod[$h]['type'] = __('New Arrival');
                    $prod[$h]['type_id'] = 'new';
                    //$prod[$h]['product'] = $this->collectionDetail($n_collection['collection']);
                    $ncolle = $this->collectionDetail($n_collection['collection']);
                    $prod[$h]['product'] = $ncolle ? $ncolle : array();
                    ++$h;
                } else {
                    $prod[$h]['type'] = __('New Arrival');
                    $prod[$h]['type_id'] = 'new';
                    $prod[$h]['product'] = array();
                    ++$h;
                }

                //---------------End of New Products------------
                //-------------Best Seller Product------------

                if ($this->activePackage == self::Basic_Package) {
                    $b_collection = $this->getBestsellerProducts($limit, $page, $entityIds);
                } else {
                    $b_collection = $this->getBestsellerProducts($limit, $page);
                }
                //$b_collection = $this->getBestsellerProducts($limit,$page,$entityIds);
                if (!empty($b_collection['collection'])) {
                    $prod[$h]['type'] = __('Most Selling');
                    $prod[$h]['type_id'] = 'bestseller';
                    $prod[$h]['product'] = $this->collectionDetail($b_collection['collection']);
                    ++$h;
                } else {
                    $prod[$h]['type'] = __('Most Selling');
                    $prod[$h]['type_id'] = 'bestseller';
                    $prod[$h]['product'] = array();
                    ++$h;
                }

                //----------End Best seller products--------
                //-------------Featured Product------------
                if ($this->__helper->getStoreConfig('minimart/homepage_settings/feat_enable')) {
                    if ($this->activePackage == self::Basic_Package) {
                        $f_collection = $this->getFeatured($limit, $page, $entityIds);
                    } else {
                        $f_collection = $this->getFeatured($limit, $page);
                    }
                    //$f_collection = $this->getFeatured($limit,$page,$entityIds);
                    if (!empty($f_collection['collection'])) {
                        $prod[$h]['type'] = __('Featured');
                        $prod[$h]['type_id'] = 'featured';
                        $prod[$h]['product'] = $this->collectionDetail($f_collection['collection']);
                        ++$h;
                    } else {
                        $prod[$h]['type'] = __('Featured');
                        $prod[$h]['type_id'] = 'featured';
                        $prod[$h]['product'] = array();
                        ++$h;
                    }
                } else {
                    $prod[$h]['type'] = __('Featured');
                    $prod[$h]['type_id'] = 'featured';
                    $prod[$h]['product'] = array();
                    ++$h;
                }

                $cCatIndex = array(
                    'first', 'second', 'third'
                );

                foreach ($cCatIndex as $_indxr => $lnum) {
                    $_collection = array();

                    $name = '';
                    $name = $this->__helper->getStoreConfig('minimart/homepage_settings/cust_cat_' . $lnum . '_label');

                    if ($this->activePackage == self::Basic_Package) {
                        $_collection = $this->getCustomCategoryProducts($limit, $page, $lnum, $entityIds);
                    } else {
                        $_collection = $this->getCustomCategoryProducts($limit, $page, $lnum);
                    }
                    //$f_collection = $this->getFeatured($limit,$page,$entityIds);
                    if (!empty($_collection['collection'])) {
                        $prod[$h]['type'] = !empty($name)? $name: '';
                        $prod[$h]['type_id'] = $lnum;
                        $prod[$h]['product'] = $this->collectionDetail($_collection['collection']);
                        ++$h;
                    } else {
                        $prod[$h]['type'] = !empty($name)? $name: '';
                        $prod[$h]['type_id'] = $lnum;
                        $prod[$h]['product'] = array();
                        ++$h;
                    }
                }

                //----------Featured products--------
                //$data['color'] = $color;
                
                // youmay
                $yname = '';
                $yname = $this->__helper->getStoreConfig('minimart/homepage_settings/cust_cat_youmay_label');

                $_ycollection = $this->getCustomCategoryProducts($limit, $page, 'youmay');

                //$f_collection = $this->getFeatured($limit,$page,$entityIds);
                if (!empty($_ycollection['collection'])) {
                    $youmayArray['type'] = !empty($yname)? $yname: '';
                    $youmayArray['type_id'] = 'youmay';
                    $youmayArray['product'] = $this->collectionDetail($_ycollection['collection']);
                } else {
                    $youmayArray['type'] = !empty($yname)? $yname: '';
                    $youmayArray['type_id'] = 'youmay';
                    $youmayArray['product'] = array();
                }
            }
            
            $yourCustomerId = $this->getRequest()->getParam('cust_id');
            $paramVisitID = $this->getRequest()->getParam('visitor_id');
            if (!$paramVisitID or empty($paramVisitID) or $paramVisitID == 'null' or $paramVisitID == null) {
                $visitorID = $this->getVisitorId();
            } else {
                $visitorID = $paramVisitID;
            }

            if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {

                $recentViewd = array();
            } else {
                $recentViewd = array(); //$this->__getRecentViewItms($yourCustomerId, $visitorID);
            }
            //var_dump($recentViewd); die;
            $prod[$h]['type'] = __('Recently Viewed Products');
            $prod[$h]['type_id'] = 'recentview';
            $prod[$h]['product'] = $recentViewd;
            ++$h;
            
            $customCats = array();
            $blockCats = $this->__helper->getStoreConfig('minimart/homepage_settings/blockcategories');
            $isCatView = $this->__helper->getStoreConfig('minimart/homepage_settings/enable_category_view');
            if ($this->activePackage == self::Gold_Package and ! $is_sectionOnly) {
                if (!empty($blockCats) and $isCatView == 1) {
                    $rootcatID = $this->_storeManager->getStore()->getRootCategoryId();
                    //parent blocks
                    $ctImg = $this->__helper->getMediaUrl() . 'catalog/category/';

                    $firstcategories = $this->_objectManager->get('Magento\Catalog\Model\Category')->getCollection()
                            ->addAttributeToSelect('*')
                            ->addAttributeToFilter('level', 2)//2 is actually the first level
                            ->addAttributeToFilter('is_active', 1);

                    foreach ($firstcategories as $_fc_items) {
                        $thumb = '';
                        if ($_fc_items->getThumbnail()) {
                            $thumb = $ctImg . $_fc_items->getThumbnail();
                        } else if ($_fc_items->getImage()) {
                            $thumb = $ctImg . $_fc_items->getImage();
                        }
                        $customCats[] = array('id' => $_fc_items->getId(), 'label' => $_fc_items->getName(), 'img' => $thumb);
                    }

                    $bcatIds = explode(",", $blockCats);
                    $catCollect = $this->_objectManager->get('Magento\Catalog\Model\Category')->getCollection();

                    $catCollect->addAttributeToSelect('*');

                    $catCollect->addAttributeToFilter('entity_id', array('in' => $bcatIds));

                    foreach ($catCollect as $_c_items) {
                        $thumb = '';

                        if ($_c_items->getThumbnail()) {
                            $thumb = $ctImg . $_c_items->getThumbnail();
                        } else if ($_c_items->getImage()) {
                            $thumb = $ctImg . $_c_items->getImage();
                        }

                        $customCats[] = array('id' => $_c_items->getId(), 'label' => $_c_items->getName(), 'img' => $thumb);
                    }
                }
            } else {
                $isCatView = 0;
            }

            $data['homepage_sections'] = array();
            if ($is_sectionOnly) {
                $data['homepage_sections'] = $this->getHomeSections();
            }
            
            //$data['recently_viewed'] = $recentViewd;
            $data['slider'] = $slider;
            $data['product_slider'] = $prod;
            $data['custom_banner'] = $custombanner;
            $data['category_block'] = $customCats;
            $data['youmay_block'] = $youmayArray;
            $data['visitor_id'] = $visitorID;

            $crCode = $this->_storeManager->getStore()->getCurrentCurrencyCode();
            $crSymb = $this->_objectManager->get('Magento\Framework\Locale\CurrencyInterface')->getCurrency($crCode)->getSymbol();

            $filerSybm = str_replace(array(' ', '\n', '\t', '\r', '&nbsp;', '&emsp;'), '', trim($crSymb));

            $isCurCode = $this->__helper->getStoreConfig('minimart/homepage_settings/show_cur_code');

            if (empty($filerSybm) and ( $isCurCode == 1))
                $crSymb = $crCode;
            else if (empty($filerSybm) and ( $isCurCode == 0))
                $crSymb = '';

            $data['currency_symbol'] = $crSymb;

            $subs = ''; //$this->checkPackageSubcription();

            $data['subscription']['subs_closed'] = $subscriptionExpire;

            $adsData = array(); //$this->getSpsAds();

            $data['sps_ads'] = $adsData;
            $jsonArray['response'] = $data;
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        //echo "<pre>";
        //print_r($jsonArray);
        //var_Dump(get_class($this->getResponse())); die;
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
        //echo json_encode($jsonArray); die;
    }

}
