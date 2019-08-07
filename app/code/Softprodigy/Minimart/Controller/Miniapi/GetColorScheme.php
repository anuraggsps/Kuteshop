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
class GetColorScheme extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() { // 
        $return = [];
        $finalreturn = [];

        $return['theme_color'] = $this->__helper->getStoreConfig("minimart/app_design_setup/theme_color");
        $return['price_color'] = $this->__helper->getStoreConfig("minimart/app_design_setup/price_color");
        $return['button_color'] = $this->__helper->getStoreConfig("minimart/app_design_setup/button_color");
        $return['sec_button_color'] = $this->__helper->getStoreConfig("minimart/app_design_setup/sec_button_color");

        $finalreturn['response'] = $return;
        $finalreturn['returnCode'] = [
            "result" => 1,
            "resultText" => "success"
        ];
        $this->getResponse()->setBody(json_encode($finalreturn));
        die;
    }

}
