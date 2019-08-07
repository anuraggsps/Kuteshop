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
class UrlToJsonEncode extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() { // 
        $param = $this->getRequest()->getParam('data');
        $basedecoded = str_replace("&amp;", '&', base64_decode($param));

        $urlDecoded = urldecode($basedecoded);

        parse_str($urlDecoded, $output);

        $this->getResponse()->setBody(json_encode($output))->sendResponse();
        die;
    }

}
