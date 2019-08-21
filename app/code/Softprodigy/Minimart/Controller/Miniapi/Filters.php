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
class Filters extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{
	
		public function execute() {
			try{
				$request = $this->getRequest()->getContent();
				$param = json_decode($request, true);
				
				$category = $param['category_id'];
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$filterableAttributes = $objectManager->getInstance()->get(\Magento\Catalog\Model\Layer\Category\FilterableAttributeList::class);
				$appState = $objectManager->getInstance()->get(\Magento\Framework\App\State::class);
				$layerResolver = $objectManager->getInstance()->get(\Magento\Catalog\Model\Layer\Resolver::class);
				$filterList = $objectManager->getInstance()->create(
					\Magento\Catalog\Model\Layer\FilterList::class,
						[
							'filterableAttributes' => $filterableAttributes
						]
					);      

					$layer = $layerResolver->get();
					$layer->setCurrentCategory($category);
					$filters = $filterList->getFilters($layer);
					$maxPrice = $layer->getProductCollection()->getMaxPrice();
					$minPrice = $layer->getProductCollection()->getMinPrice();  

				$i = 0;
				
				foreach($filters as $filter) {
					$filterValues = array();
				   $items = $filter->getItems(); //Gives all available filter options in that particular filter
					if(!empty($items)){
						$filterValues['title'] = (string)$filter->getName(); //Gives Display Name of the filter such as Category,Price etc.
						
						$j = 0;
						foreach($items as $item){
						   $filterValues['items'][$j]['display'] = strip_tags($item->getLabel());
						   $filterValues['items'][$j]['value']   = $item->getValue();
						   $filterValues['items'][$j]['count']   = $item->getCount(); //Gives no. of products in each filter options
						   $j++;
						}
						$i++;
						$jsonArray['data'][] =$filterValues;
					}
					
				}  
			
				
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
