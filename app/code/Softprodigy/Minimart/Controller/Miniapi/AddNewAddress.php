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
class AddNewAddress extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {
    
    public function execute(){
    	try{
		  
			$request = $this->getRequest()->getContent();
			$param = json_decode($request, true);

			if (isset($param['is_default_shipping']) && isset($param['is_default_billing']) && $param['is_default_shipping']!='' && $param['is_default_billing']!='' && $param['is_default_shipping']!=null && $param['is_default_billing']!=null) {
				$defaultshipping=$param['is_default_shipping'];
				$defaultbilling=$param['is_default_billing'];
			}else{
				$defaultshipping='1';
				$defaultbilling='1';
			}
			
			$addresss = $this->_objectManager->get('\Magento\Customer\Model\AddressFactory');
			$address = $addresss->create();

			if (isset($param['region_code']) && $param['region_code']!='' && $param['region_code']!=null) {
			
				$address->setCustomerId($param['user_id'])
				->setFirstname($param['first_name'])
				->setLastname($param['last_name'])
				->setCountryId($param['country_code'])
				->setRegionId($param['region_code'])
				->setPostcode($param['post_code'])
				->setCity($param['city'])
				->setTelephone($param['phone_no'])
				->setFax(null)
				->setCompany($param['company'])
				->setStreet($param['street'])
				->setIsDefaultBilling($defaultbilling)
				->setIsDefaultShipping($defaultshipping)
				->setSaveInAddressBook('1');
				$address->save();
			}elseif(isset($param['region_name']) && $param['region_name']!='' && $param['region_name']!=null){
				$address->setCustomerId($param['user_id'])
				->setFirstname($param['first_name'])
				->setLastname($param['last_name'])
				->setCountryId($param['country_code'])
				->setRegion($param['region_name'])
				->setPostcode($param['post_code'])
				->setCity($param['city'])
				->setTelephone($param['phone_no'])
				->setFax(null)
				->setCompany($param['company'])
				->setStreet($param['street'])
				->setIsDefaultBilling($defaultbilling)
				->setIsDefaultShipping($defaultshipping)
				->setSaveInAddressBook('1');
				$address->save();
			}

			

	 
			$jsonArray['status'] =  'success';
			$jsonArray['status_code'] = 200;
			$jsonArray['message'] =  "Address added Succesfully";
    	} catch (\Exception $e) {
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
