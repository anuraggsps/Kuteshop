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
class GetMyDownlodables extends \Softprodigy\Minimart\Controller\AbstractAction {
    
    public function execute() { // 
        $finalreturn = $this->_getMydownlodables();
        $this->getResponse()->setBody(json_encode($finalreturn))->sendResponse();
        die;
    }

}
