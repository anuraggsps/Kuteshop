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
class Getmore extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $limit = 20;
            $data = array();
            
            $params = $this->getRequest()->getParams();
            $page = (isset($params['page_no']) and ! empty($params['page_no'])) ? $params['page_no'] : 1;

            $params['type'] = isset($params['type']) ? $params['type'] : false;

            $cCatIndex = array(
                'first', 'second', 'third', 'fourth', 'fifth', 'youmay'
            );
            //-----custom option product id array------//--------Remove it in next version--------- 
            $Option = $this->_objectManager->get('Magento\Catalog\Model\ResourceModel\Product\Option\Collection')->addFieldToSelect('product_id');
            $Option->getSelect()->group('main_table.product_id');
            $entityIds = new \Zend_Db_Expr($Option->getSelect()->__toString());

            if (!$this->activePackage) {
                $subs = $this->checkPackageSubcription();
                if ($subs['active_package']) {
                    $this->activePackage = $subs['active_package'];
                }
            }

            if ($params['type'] == 'featured') {
                if ($this->activePackage == self::Basic_Package) {
                    $f_collection = $this->getFeatured($limit, $page, $entityIds);
                } else {
                    $f_collection = $this->getFeatured($limit, $page);
                }
                // $f_collection = $this->getFeatured($limit, $page); //$f_collection = $this->getFeatured($limit,$page,$entityIds);
            } else if ($params['type'] == 'new') {
                if ($this->activePackage == self::Basic_Package) {
                    $f_collection = $this->newProducts($limit, $page, $entityIds);
                } else {
                    $f_collection = $this->newProducts($limit, $page);
                }
                //$f_collection = $this->newProducts($limit, $page); //$f_collection = $this->newProducts($limit,$page,$entityIds);
            } else if ($params['type'] == 'bestseller') {
                if ($this->activePackage == self::Basic_Package) {
                    $f_collection = $this->getBestsellerProducts($limit, $page, $entityIds);
                } else {
                    $f_collection = $this->getBestsellerProducts($limit, $page);
                }
                //$f_collection = $this->getBestsellerProducts($limit, $page); //$f_collection = $this->getBestsellerProducts($limit,$page,$entityIds);	
            } else if (in_array($params['type'], $cCatIndex)) {
                $name = $lnum = '';
                $lnum = $params['type'];
                if ($this->activePackage == self::Basic_Package) {
                    $f_collection = $this->getCustomCategoryProducts($limit, $page, $lnum, $entityIds);
                } else {
                    $f_collection = $this->getCustomCategoryProducts($limit, $page, $lnum);
                }
            } else {
                $jsonArray['response'] = __("Please check parameters.");
                $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
                $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
                die;
            }

            if (!empty($f_collection['collection'])) {
                $data['product'] = $this->collectionDetail($f_collection['collection']);
            }

            $more = count($f_collection);

            /* if($more < $limit)  //------------ here commented because page no not effecting in collection------
              $data['more'] = 0;
              else
              $data['more'] = 1; */

            $prod_count = $f_collection['count'];
            if ($prod_count <= ($limit * $page))
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

            $jsonArray['response'] = $data;
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }

        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

}
