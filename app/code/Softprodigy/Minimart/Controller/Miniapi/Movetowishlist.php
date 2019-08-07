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
class Movetowishlist extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {

        $return = $this->_addCartItemToWishList();
        $finalreturn = array();
        $finalreturn['response'] = $return['return']['msg'];
        $finalreturn['wishlist_item_id'] = isset($return['return']['wishlist_item_id']) ? $return['return']['wishlist_item_id'] : '';
        $finalreturn['returnCode'] = [
            "result" => $return['resultCode'],
            "resultText" => $return['resultText']
        ];
        $this->getResponse()->setBody(json_encode($finalreturn))->sendResponse();
        die;
    }

}
