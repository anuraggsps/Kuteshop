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
class GetWishlistItems extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() { //
		
		try{
			
			ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
			$request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
			$customer_Id = $param['cust_id'];
			$return  = $this->_getWishlistItems($customer_Id,$param);
			$finalreturn = array();
			$status_code = 200;
			$responsetext = 'success';
			$message = "Get Data Succesfully";
			
			if($return['resultCode'] == 0){
				$status_code = 201;
				$responsetext = 'fail';
				$message = 'Invalid customer';
			}else if($return['resultCode'] == 1 && empty($return['return']['products'])){
				$message = 'You have no items in your Wishlist';
			}
			$jsonArray['data'] = $return['return'];
			$jsonArray['currency'] = $this->GetCurrency($param);
			$jsonArray['status'] =  $responsetext;
			$jsonArray['status_code'] =  $status_code; 
			$jsonArray['message'] =  $message;
        }catch (\Exception $e) {
			$jsonArray['data'] = null;
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
