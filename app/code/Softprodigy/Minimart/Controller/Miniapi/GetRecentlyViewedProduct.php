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
class GetRecentlyViewedProduct extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() { //
        $yourCustomerId = $this->getRequest()->getParam('cust_id',null);
        $yourVisitorId = $this->getRequest()->getParam('visitor_id',null);
        $subs = $this->checkPackageSubcription();
        if ($subs['active_package']) {
            $this->activePackage = $subs['active_package'];
        }
        if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {
            $return = array();
        } else {
            $return = $this->__getRecentViewItms($yourCustomerId,$yourVisitorId);
        }

        $finalreturn = [];
        $finalreturn['response'] = $return;
        $finalreturn['returnCode'] = [
             "result" => 1,
            "resultText" => "success"
        ];
        $this->getResponse()->setBody(json_encode($finalreturn))->sendResponse();
        die;
    }
    
 
}
