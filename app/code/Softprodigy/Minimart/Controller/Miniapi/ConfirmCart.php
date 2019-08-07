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
class ConfirmCart extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {
            $param = $this->getRequest()->getParams();

            $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->loadActive($param['quote_id']);
            $prod_ids = array();
            if ($quote->getId()) {
                $cartItems = $quote->getAllItems();
                $isSalable = 1;
                foreach ($cartItems as $item) {
                    
                    $productId = $item->getProductId();
                    $product = $this->productFactory->load($productId);
                   // $stockItem = $product->getStockItem();
                    if (!$product->isSalable()) {
                        $isSalable = 0;
                        $prod_ids[] = $productId;
                    }
                   
                }

                $data['isSalable'] = $isSalable;
                $data['prod_ids'] = $prod_ids;
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
