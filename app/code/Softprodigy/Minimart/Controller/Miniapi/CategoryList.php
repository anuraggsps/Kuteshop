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
class CategoryList extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() {
        try {
            //-------------Mobile theme color------------
            /* $subs = $this->checkPackageSubcription();
             */
            $subs = [];
            $subs['subs_closed'] = false;
            $subs['active_package'] = 'Gold';

       			//~ $request = $this->getRequest()->getContent();
				//~ $param = json_decode($request, true);
            $catIds = $this->__helper->getStoreConfig('minimart/minimart_registration/categories');

            $custID =1;//$this->getRequest()->getParam('cust_id', false);
            if ($custID) {
                $customer = $this->_objectManager->get('Magento\Customer\Model\Customer')->load(1);
                $custCats = $customer->getCustCategory();
                if ($customer->getId() and ! empty($custCats)) {
                    $catIds = explode(",", $customer->getCustCategory());
                }
            }

            if (empty($catIds) || (isset($catIds[0]) and empty($catIds[0]))) {
                $catIds = array();
            }


            $result = $this->getCategorytree(null, null, $catIds);
            //$result = $this->getCategorytree();
             
            $data = array();
            if (empty($catIds) and $result['children'][0]['is_active'] == 1) {
                $data = $result['children'];

                //$this->recur_html_decode_nav($data);
                $jsonArray['data'] = $data;
				$jsonArray['status'] =  'success';
				$jsonArray['status_code'] = 200; 
				$jsonArray['message'] =  "Get Data Succesfully";
            } else if (!empty($catIds)) {
                $data  = $result['children'];
                //$this->recur_html_decode_nav($data);
                $jsonArray['data'] = $data;
				$jsonArray['status'] =  'success';
				$jsonArray['status_code'] = 200; 
				$jsonArray['message'] =  "Get Data Succesfully";
            } else {
				$jsonArray['data'] = null;
				$jsonArray['status'] =  "failure";
				$jsonArray['status_code'] =  201;	
            }

                $jsonArray['data'] = $data;
				$jsonArray['status'] =  'success';
				$jsonArray['status_code'] = 200; 
				$jsonArray['message'] =  "Get Data Succesfully";
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
