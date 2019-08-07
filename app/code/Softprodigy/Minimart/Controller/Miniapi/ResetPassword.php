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
class ResetPassword extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() { //
        $finalreturn = $this->resetMyPassword();
        $this->getResponse()->setBody(json_encode($finalreturn))->sendResponse();
        die;
    }

}
