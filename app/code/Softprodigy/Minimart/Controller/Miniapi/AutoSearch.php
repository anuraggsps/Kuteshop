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
class AutoSearch extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{
	
		public function execute() {
			try{
				
				$request = $this->getRequest()->getContent();
				$param = json_decode($request, true);
				$jsonArray['data'] =[];
				$data = $this->GetdataOfSearch($param);
				if(empty($param['view'])){
					$x=0;
					foreach($data as $value){
						if($x>4){
							break;
						}
						$jsonArray['data'][] =  $value;
						$x++;
					}
				
				}elseif(!empty($param['view'])){
					
					$page=$param['view'][1]['page'];
					if($param['view'][0]['limit']>count($data)){
						$limit=count($data);
					}elseif($param['view'][0]['limit']<=count($data)){
						$limit=$param['view'][0]['limit'];
					}	
					if(count($data)==0){
						$total_pages=0;
					}else{
						$total_pages=ceil(count($data)/$limit);
					}
					$offset=($page-1)*($limit);
					$currpage=$offset+$limit;
					if($page <= $total_pages){
						for($i=$offset;$i<=$currpage-1;$i++){
							$jsonArray['data'][] =  $data[$i];
						}
					}	
				}
				$jsonArray['total_items'] =  count($data);
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
		
		public function GetdataOfSearch($param){
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
			$baseurl = $storeManager->getStore()->getBaseUrl();
			
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			  CURLOPT_URL => $baseurl."minimart/miniapi/GetAutoSearchData?q=".$param['q'],
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "GET",
			  CURLOPT_POSTFIELDS => "",
			  CURLOPT_HTTPHEADER => array(
				"Content-Type: application/json"
			  ),
			));

			$response = curl_exec($curl);
			$err = curl_error($curl);
			curl_close($curl);
			return json_decode($response);
		}
		
    }    
