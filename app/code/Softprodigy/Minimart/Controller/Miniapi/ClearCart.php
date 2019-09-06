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
class ClearCart extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {

    public function execute() {
        try {
			
			$request = $this->getRequest()->getContent();
		    $param = json_decode($request, true);
			$customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($param['user_id']);
			if ($customer->getEntityId() == $param['user_id']) {
				
				$custModel = $this->_objectManager->get("Magento\Customer\Model\Customer");
				$customerObj = $custModel->load($param['user_id']);
				$quote = $this->_quoteLoader->loadByCustomer($customerObj);
			 	$quoteId = $quote->getId(); 
				
				$jsonArray = array();
				$allItems = $quote->getAllVisibleItems();
				foreach ($allItems as $item) {
					$itemId = $item->getItemId();
					$quote->removeItem($itemId);
					$quote->collectTotals()->save();
				}
				if($quote){
					$jsonArray ['data'] = '';
					$string  = "Cart is empty";
					$jsonArray ['message'] = $string;
					$jsonArray ['status_code'] = 200;
					$jsonArray ['status'] = "Success";
					
				}else{
					$jsonArray ['data'] = '';
					$string  = "Cart is not empty";
					$jsonArray ['message'] = $string;
					$jsonArray ['status_code'] = 201;
					$jsonArray ['status'] = "Failure";
				}		
			}else{
				
				$jsonArray['data'] = "";
				$string  = "User is not authorised";
				$jsonArray['message'] = $string;
				$jsonArray['status'] = "Failure";
				$jsonArray['status_code'] = 401;
				
			}	
		} catch (\Exception $e) {
            $jsonArray['data'] = null;
			$jsonArray['status_code'] = 201;
			$jsonArray['status'] = "Failure";
			$jsonArray['message'] = $e->getMessage();
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
