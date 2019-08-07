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
class GetDLinkUrl extends \Softprodigy\Minimart\Controller\AbstractAction {
    
    public function execute() { // 
        $return = $this->getDLinkUrlFormHash();
        $finalreturn = array();
        $finalreturn['response'] = $return['return'];
        $finalreturn['returnCode'] = [
            "result" => $return['resultCode'],
            "resultText" => $return['resultText']
        ];
        $this->getResponse()->setBody(json_encode($finalreturn))->sendResponse();
        die;
    }

}
