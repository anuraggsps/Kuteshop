<?php
namespace Softprodigy\Minimart\Model;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Pay
 *
 * @author mannu
 */
class Pay extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'minimart_pay';
    
    /**
     * Check whether payment method can be used
     *
     * TODO: payment method instance is not supposed to know about quote
     *
     * @param 2|null $quote
     *
     * @return bool
     */
     
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        $isApi =  $this->_registry->registry('api_req');
        $checkResult =  new DataObject();
        $checkResult->setData('is_available', true);
        $isActive = (bool)(int)$this->getConfigData('active', $quote ? $quote->getStoreId() : null);
        $isAvailable = $isActive && $isApi? true: false;
        $checkResult->setData('is_available', $isAvailable);
        $checkResult->setData('is_denied_in_config', !$isActive);
       //$checkResult->isDeniedInConfig = ; // for future use in observers
        $this->_eventManager->dispatch(
            'payment_method_is_active',
            [
                'result' => $checkResult,
                'method_instance' => $this,
                'quote' => $quote
            ]
        );
        
        return $checkResult->getData('is_available');
    }
}

