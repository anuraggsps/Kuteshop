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
class GetStateByCountry extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {
    
    public function execute(){

    $request = $this->getRequest()->getContent();
    $param = json_decode($request, true);
    $o = \Magento\Framework\App\ObjectManager::getInstance(); 
    $region = $o->create('Magento\Directory\Model\Country')
                        ->loadByCode($param['country_code'])->getRegions();


    $jsonArray['data'] = $region->getData();
    $jsonArray['status'] =  'success';
    $jsonArray['status_code'] =  200;
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