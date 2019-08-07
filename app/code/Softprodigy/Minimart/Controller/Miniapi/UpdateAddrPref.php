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
class UpdateAddrPref extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $return = $this->_updateAddrPref();
            $jsonArray['response'] = $return;
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

}
