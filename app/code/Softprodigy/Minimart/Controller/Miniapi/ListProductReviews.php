<?php

namespace Softprodigy\Minimart\Controller\Miniapi;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
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
class ListProductReviews extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() { //

        $return = $this->_listProductReviews();
        $finalreturn = array();
        $finalreturn['response'] = $return;
        $finalreturn['returnCode'] = [
            "result" => 1,
            "resultText" => "success"
        ];
        $this->getResponse()->setBody(json_encode($finalreturn))->sendResponse();
        die;
    }
	public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool{
        return true;
    }
}

