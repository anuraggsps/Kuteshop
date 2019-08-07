<?php

namespace Softprodigy\Minimart\Controller\Miniapi;

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
class CancleOrder extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $params = $this->getRequest()->getParams();
            //$sess = $this->soapconnection();
            //$result = $sess['client']->call($sess['session_id'], 'sales_order.cancel', $params['order_id']);
            $order = $this->_initOrder($params['order_id']);

            if (\Magento\Sales\Model\Order::STATE_CANCELED == $order->getState()) {
                throw new \Exception(__("Order status not changed."));
            }
            try {
                $order->cancel();
                $order->save();
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
                throw new \Exception(__("Order status not changed."));
            }
            if (\Magento\Sales\Model\Order::STATE_CANCELED != $order->getState()) {
                throw new \Exception(__("Order status not changed."));
            }
            
            $jsonArray['response'] = true;
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }
    
    protected function _initOrder($orderIncrementId)
    {
        $order = $this->_objectManager->get("Magento\Sales\Model\Order");

        /* @var $order Mage_Sales_Model_Order */

        $order->loadByIncrementId($orderIncrementId);

        if (!$order->getId()) {
            throw new \Exception(__("Requested order not exists."));
        }

        return $order;
    }
}
