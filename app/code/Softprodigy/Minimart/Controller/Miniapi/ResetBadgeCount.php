<?php

namespace Softprodigy\Minimart\Controller\Miniapi;

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
class ResetBadgeCount extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        
        $jsonArray = array();
        try {
            $deviceId = $this->getRequest()->getParam('deviceId', false);
            $newCount = $this->getRequest()->getParam('badge', 0);
             
            if ($newCount >= 0 and $deviceId) {
                $tokeninfo = $this->_objectManager->get('Softprodigy\Minimart\Model\Deviceinfo')->load($deviceId, 'token');
                $tid = $tokeninfo->getId();
                $tokeninfo->setBadgeCount($newCount);
                $tokeninfo->setId($tid);
                $tokeninfo->save();
                $jsonArray['response'] = __('badge has been updated');
                $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
            } else {
                throw new \Exception("Invalid parameters");
            }
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        } 
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }
    
}
