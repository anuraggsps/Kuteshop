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
class Search extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{
	
		public function execute() {
			try{
				$request = $this->getRequest()->getContent();
				$param = json_decode($request, true);
				$category = $param['category_id'];;
				
				$categoryId = '6';
				$productCollection = $this->_productCollectionFactory->create();
				$productCollection->addAttributeToSelect('*');
				$productCollection->addCategoriesFilter(array('in' => $categoryId));
				$productCollection->addAttributeToFilter('color',array('in' => array(4,5,6)));
				//~ $productCollection->addAttributeToFilter('price', array('gteq' => 10));
				//~ $productCollection->addAttributeToFilter('price', array('lteq' => 50));
				
				
				foreach ($productCollection as $product) {
					print_r($product->getData());
				}

				
				
				die;
				$jsonArray['status'] =  'success';
				$jsonArray['status_code'] = 200; 
				$jsonArray['message'] =  "Get Data Succesfully";
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
