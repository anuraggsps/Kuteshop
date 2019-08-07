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
class AddProductToWishList extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() { //
		try{
		
			$request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
			$prod_id =$param['prod_id']; 
			$cust_id =$param['cust_id']; 
			$qty =$param['qty']; 
			
			$return = $this->_addProdToWishList($prod_id,$cust_id,$qty);
			$finalreturn = array();
			$finalreturn['response'] = $return['return']['msg'];
			$finalreturn['wishlist_item_id'] = isset($return['return']['wishlist_item_id']) ? $return['return']['wishlist_item_id'] : '';
            $data['wishlistid'] = $finalreturn['wishlist_item_id'];
            $status_code = 200;
            $responsetext = 'success';
            if($return['resultCode'] == 0){
				$status_code = 201;
				$responsetext = 'fail';
			}
            
            
			$jsonArray['data'] = $data;
			$jsonArray['status'] =  $responsetext;
			$jsonArray['status_code'] =  $status_code; 
			$jsonArray['message'] = $return['return']['msg'];
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
