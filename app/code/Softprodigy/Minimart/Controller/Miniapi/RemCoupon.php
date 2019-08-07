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
class RemCoupon extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $param = $this->getRequest()->getParams();
            $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->load($param['quote_id']);
            if ($quote->getIsActive()) {
                $data = array();
                
                $result = $this->removeCoupon($quote);
                
                $data['removed'] = $result;
                
                //---------------Get updated total-------------
                $totals = $quote->getTotals(); //Total object
                if (isset($totals['discount']) && $totals['discount']->getValue()) {
                    $discount = $totals['discount']->getValue(); //Discount value if applied
                } else {
                    $discount = 0.00;
                }
                if (isset($totals['tax']) && $totals['tax']->getValue()) {
                    $tax = $totals['tax']->getValue(); //Tax value if present
                } else {
                    $tax = 0.00;
                }
                $data['grandtotal'] = number_format($quote->getGrandTotal(), 2);
                $data['subtotal'] = number_format($quote->getSubtotal(), 2);
                $data['discount'] = number_format($discount, 2);
                $data['tax'] = number_format($tax, 2);
                //------------End -Get updated total------------

                $jsonArray['response'] = $data;
                $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
            } else {
                $jsonArray['response'] = __("Cart is de-activated. Try with some other products.");
                $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
            }
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

   
}
