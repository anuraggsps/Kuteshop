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
class EditProfile extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{
	
		public function execute() {
			try{
			    $request = $this->getRequest()->getContent();
				$param = json_decode($request, true);	
			
				$customerModel = $this->_objectManager->get('Magento\Customer\Model\Customer');
				$customer = $customerModel->load($param['user_id']);
				$customer->setFirstname($param['firstname']);
				$customer->setLastname($param['lastname']);
				if(isset($param['email'])&& $param['email'] !=''){
					$customer->setEmail($param['email']);
				}
				$customer->save();
				
				
				$cuCollection = $customerModel->getCollection();
				$cuCollection->addAttributeToFilter('entity_id', $param['user_id']);
				foreach($cuCollection->getData() as $key=>$value){
					$order_data['firstname'] = $value['firstname']?$value['firstname']:''; 
					$order_data['lastname'] = $value['lastname']?$value['lastname']:'';
				}
				
				$jsonArray['data'] =  $order_data;
				$jsonArray['status'] =  'success';
				$jsonArray['status_code'] = 200; 
				$jsonArray['message'] =  "Save Data Succesfully";
					
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
