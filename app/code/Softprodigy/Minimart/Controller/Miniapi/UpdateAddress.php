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
class UpdateAddress extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {
    
    public function execute(){
    	try{
    		$request = $this->getRequest()->getContent();
    		$param = json_decode($request, true);
    		

    		$address = $this->addressRepository->getById($param['entity_id']);
    		$address->setFirstname($param['first_name']);
    		$address->setLastname($param['last_name']);
    		$address->setCountryId($param['country_code']);
    		$address->setRegionId($param['region_code']);    			
    		$address->setPostcode($param['post_code']);
    		$address->setCity($param['city']);
    		$address->setTelephone($param['phone_no']);
    		$address->setCompany($param['company']);
    		$address->setStreet((array)$param['street']);
    		$address->setIsDefaultBilling($param['is_default_billing']);
    		$address->setIsDefaultShipping($param['is_default_shipping']);
    		$this->addressRepository->save($address);

    	  	$jsonArray['status'] =  'success';
            $jsonArray['status_code'] = 200;
            $jsonArray['message'] =  "Address added Succesfully";
        }
        catch (\Exception $e) {
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
