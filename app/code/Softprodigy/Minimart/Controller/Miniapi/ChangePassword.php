<?php
namespace Softprodigy\Minimart\Controller\Miniapi;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*//**
* Description of Homepage
*
* @author mannu
*/
class ChangePassword extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{
    
        public function execute() {
            try{
                $request = $this->getRequest()->getContent();
                $param = json_decode($request, true);    
                
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$customerFactory = $objectManager->get('\Magento\Customer\Model\CustomerFactory')->create();
				$customer = $customerFactory->load($param['user_id']);
				$customer = $this->customerAccountManagement->authenticate($customer->getEmail(), $param['current_password']);
				$customer_id = $customer->getId();
				
				if($customer_id !=''){
					$customer = $this->_customerRepository->getById($customer_id);
					$this->_customerRepository->save($customer, $this->_encryptor->getHash($param['new_password'], true));	
					$jsonArray['status'] =  'success';
					$jsonArray['status_code'] = 200;
					$jsonArray['message'] =  "Change Password Succesfully";
				}else{
					$jsonArray['status'] =  'success';
					$jsonArray['status_code'] = 401;
					$jsonArray['message'] =  "Invalid Password";	
				}
				
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
