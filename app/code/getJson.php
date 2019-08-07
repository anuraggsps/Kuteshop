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
class getJson extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {

			$array = array('versionCode'=>'32','cancellable'=>'false','url'=>'https://play.google.com/store/apps/details?id=com.XrentY&hl=en_IN');

        $this->getResponse()->setBody(json_encode($array))->sendResponse();
        die;
    }

}
