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
class OrderList extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {

    public function execute() {
        //~ try {
            //~ $param = $this->getRequest()->getParams();
			$request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
            $limit = 10;
            $pageno = (isset($param['page_no']) && $param['page_no'] > 0)? $param['page_no'] : 1;
             
            $order_data = $this->getOrderList($param,$pageno,$limit);
            $message = "Get data successfully";
            
            if(empty($order_data['order'])){
				//~ echo "<pre>";print_r();die;
				$orders_data =array("order"=>array());
				$message = "You don't have any orders";
				$order_data = $orders_data;
			}
            
			$jsonArray['data'] = $order_data;
			$jsonArray['currency'] = $this->GetCurrency($param);
			$jsonArray['status'] = "success";
			$jsonArray['status_code'] = 200;
			$jsonArray['message'] = $message;
            
        //~ } catch (\Exception $e) {
           //~ $jsonArray['data'] = "";
		   //~ $jsonArray['message'] = "Something went wrong";
		   //~ $jsonArray['status'] = "failure";
		   //~ $jsonArray['status_code'] = 201;
        //~ }
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
