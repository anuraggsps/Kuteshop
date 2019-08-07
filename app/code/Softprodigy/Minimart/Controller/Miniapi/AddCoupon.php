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
class AddCoupon extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        $return  = '';
        $result =0;
        try {
           
            $param = $this->getRequest()->getParams();
            $couponCode = $this->getRequest()->getParam('coupon_code');
            
            $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->load($param['quote_id']);
            if ($quote->getIsActive()) {
                $this->cart->setQuote($quote);
                $cartQuote = $this->cart->getQuote();
                $oldCouponCode = $cartQuote->getCouponCode();

                $codeLength = strlen($couponCode);
                if (!$codeLength && !strlen($oldCouponCode)) {
                    $jsonArray['response'] = __("This Coupon is already applied.");
                    $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
                    $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
                    die;
                }

                try {
                    $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

                    $itemsCount = $cartQuote->getItemsCount();
                    if ($itemsCount) {
                        $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                        $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                        $this->quoteRepository->save($cartQuote);
                    }

                    if ($codeLength) {
                        $escaper = $this->_objectManager->get('Magento\Framework\Escaper');
                        if (!$itemsCount) {
                            if ($isCodeLengthValid) {
                                $coupon = $this->_objectManager->get('\Magento\SalesRule\Model\CouponFactory')->create();
                                $coupon->load($couponCode, 'code');
                                if ($coupon->getId()) {
                                    $this->__checkoutSession->getQuote()->setCouponCode($couponCode)->save();
                                    $return = __(
                                                    'You used coupon code "%1".', $escaper->escapeHtml($couponCode)
                                            );
                                   $result =1;
                                } else {
                                    $return = __(
                                                    'The coupon code "%1" is not valid.', $escaper->escapeHtml($couponCode)
                                            );
                                    
                                }
                            } else {
                                $return = __(
                                                'The coupon code "%1" is not valid.', $escaper->escapeHtml($couponCode)
                                        );
                                
                            }
                        } else {
                            if ($isCodeLengthValid && $couponCode == $cartQuote->getCouponCode()) {
                                $return = __(
                                                'You used coupon code "%1".', $escaper->escapeHtml($couponCode)
                                        );
                                $result =1;
                            } else {
                                $return = __(
                                                'The coupon code "%1" is not valid.', $escaper->escapeHtml($couponCode)
                                        );
                               
                                $this->cart->save();
                            }
                        }
                    } else {
                        $return = __('You canceled the coupon code.');
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $return = __($e->getMessage());
                } catch (\Exception $e) {
                    $return = __('We cannot apply the coupon code.');
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                }
            } else {
                $return = __("Cart is de-activated.");
            }
            $jsonArray['response'] = $return;
            $jsonArray['returnCode'] = array('result' => $result, 'resultText' => ($result==1? 'success': 'fail'));
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }

        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }

}
