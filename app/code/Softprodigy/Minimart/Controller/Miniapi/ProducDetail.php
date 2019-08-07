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
class ProductDetail extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() {
        try {
            $result = [];
            $request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
            $prod_id = $param['prod_id'];
            $pro_data = [];

            $product = $this->productFactory->load($prod_id);
            $this->_view->loadLayout();

            if (($product->getId()) && ($product->getStatus() == 1)) {

            } else {
                $jsonArray['response'] = "Please check product id.";
                $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
            }
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }

        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }
	
	public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool{
        return true;
    }
    
}
