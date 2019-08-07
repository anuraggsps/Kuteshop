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
class GetStaticpages extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $static_ids = $this->__helper->getStoreConfig('minimart/static_pages/stat_page');
            $static = explode(',', $static_ids);
            $page_data = array();
            if (!empty($static)) {
                $collection = $this->_objectManager->get("Magento\Cms\Model\Page")->getCollection()
                        //->addStoreFilter($storeId)
                        ->addFieldToFilter('is_active', 1)
                        ->addFieldToFilter('page_id', array('in' => $static));
                $p = 0;
                foreach ($collection as $page) {
                    $page_data[$p]['id'] = $page->getPageId();
                    $page_data[$p]['title'] = $page->getTitle();
                    $page_data[$p]['content'] = $this->_filterProvider->getPageFilter()->filter($page->getContent()); 
                    ++$p;
                }
            }

            $jsonArray['response'] = $page_data;
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

}
