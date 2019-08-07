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
class UpdateCart extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        $params = $this->getRequest()->getParams();
        $jsonArray = []; 
        
        try {
            
            $prodArr[$params['item_id']] = [
                'qty' => $params['qty']
            ];
            
            $this->getRequest()->setParam('cart', $prodArr);
            
            
            $cartData = $this->getRequest()->getParam('cart');
            
            if (is_array($cartData)) {
                
                $filter = new \Zend_Filter_LocalizedToNormalized(
                    ['locale' => $this->_objectManager->get('Magento\Framework\Locale\ResolverInterface')->getLocale()]
                );
                foreach ($cartData as $index => $data) {
                    if (isset($data['qty'])) {
                        $cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
                    }
                }
                
                if (!$this->cart->getCustomerSession()->getCustomerId() && $this->cart->getQuote()->getCustomerId()) {
                    $this->cart->getQuote()->setCustomerId(null);
                }
                
                $cartData = $this->cart->suggestItemsQty($cartData);
                
                $this->cart->updateItems($cartData);
                foreach($this->cart->getQuote()->getAllVisibleItems() as $item){
                    $item->save();
                }
                $this->cart->save();
            }
            
            $jsonArray['response'] =  __('Your shopping cart has been updated.');
            $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
            
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
             
            $jsonArray['response'] =  $this->_objectManager->get('Magento\Framework\Escaper')->escapeHtml($e->getMessage());
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('We can\'t update the shopping cart.'));
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            
            $jsonArray['response'] =  __('We can\'t update the shopping cart.');
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }

        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }
    
}
