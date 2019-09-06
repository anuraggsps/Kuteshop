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
class OrderInfo extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {

    public function execute() {
        try {
            $request = $this->getRequest()->getContent();
			$params = json_decode($request, true);
            $order = $this->_objectManager->get("Magento\Sales\Model\Order")->loadByIncrementId($params['order_id']);
            $result = $order->getData();
            if(empty($result)){
               throw new \Exception(__("Invalid order request.")); 
            }      
            
            $data = $this->getOrderInfo($order, $result, $params);
            
			$jsonArray['data'] = $data;
			$jsonArray['currency'] = $this->GetCurrency($params);
			$jsonArray['status'] =  'success';
			$jsonArray['status_code'] = 200; 
			$jsonArray['message'] = "Get data Succesfully";
        } catch (\Exception $e) {
			$jsonArray['message'] = $e->getMessage();
			$jsonArray['status'] =  "failure";
			$jsonArray['status_code'] =  201;	
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
