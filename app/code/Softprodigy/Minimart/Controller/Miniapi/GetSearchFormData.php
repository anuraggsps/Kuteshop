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
class GetSearchFormData extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        $collection = $this->productFactory
                ->getCollection()
                ->addAttributeToSelect('name');
        $collection->addFieldToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $names = [];
        if ($collection->count() > 0) {
            $names = $collection->getColumnValues('name');
        }
        $categories = $this->getCategoryListArray();
        $subcategories = [];
        $subcategories = $this->render_flat_nav($categories);
        $finalreturn = $final = [];
        $final['products'] = $names;
        $final['categories'] = $subcategories;
        $finalreturn['response'] = $final;
        $finalreturn['returnCode'] = [
            "result" => 1,
            "resultText" => "success"
        ];
        $this->getResponse()->setBody(json_encode($finalreturn))->sendResponse();
        die;
    }

}
