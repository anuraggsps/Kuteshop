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

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Description of Homepage
 *
 * @author mannu
 */
class SetCurrency extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {
	
	public function execute(){
		$request = $this->getRequest()->getContent();
		$param = json_decode($request, true);
		
	    $customercustom  = $this->_customerRepository->getById($param['user_id']); 
		$customeattr =  $customercustom->setCustomAttribute('currency',$param['currency']);
		$c_currencies = $this->_customerRepository->save($customercustom);
		$quote= $this->_objectManager->create('Magento\Quote\Model\Quote')->loadByCustomer($param['user_id']); 
		if($quote->getId() !=''){
			$this->SetQuoteCurrencyCode($param['currency'],$quote->getId());
		}

		$jsonArray['status'] = 'success';
		$jsonArray['status_code'] = 200;
		$jsonArray['msg'] = 'You Set the currency succesfully';
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
