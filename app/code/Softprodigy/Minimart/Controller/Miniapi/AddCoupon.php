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
class AddCoupon extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() {
		$result  = 0;
        $return  = '';
        try {
           
            $request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
            $couponCode = $param['coupon_code'];
            
            $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->load($param['quote_id']);
            if ($quote->getIsActive()) {
                $this->cart->setQuote($quote);
                $cartQuote = $this->cart->getQuote();
                $oldCouponCode = $cartQuote->getCouponCode();

                $codeLength = strlen($couponCode);
                if (!$codeLength && !strlen($oldCouponCode)) {
                    $jsonArray['status'] =  'success';
					$jsonArray['status_code'] = 200; 
					$jsonArray['message'] =   __("This Coupon is already applied.");
                   
                    $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
                    die;
                }

                try {
                    $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;
                    $itemsCount = $cartQuote->getItemsCount();
                 
                    if ($itemsCount && $isCodeLengthValid == 1) {
                        $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                        $cartQuote->setCouponCode($couponCode)->collectTotals();
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
            $jsonArray['data'] = [];
				if( $result ==1){
				    //---------------Get updated total-------------
					$totals = $quote->getTotals(); //Total object
						$data['coupon_code'] = '';
						$discount = "0";
						if($quote->getCouponCode() !=''){
							$data['coupon_code'] = $quote->getCouponCode();
							$data['is_discount'] = 1;
							$discount = $quote->getSubtotal()-$quote->getSubtotalWithDiscount() ; //Discount value if applied
						}
						
						if (isset($totals['tax']) && $totals['tax']->getValue()) {
							$tax = $totals['tax']->getValue(); //Tax value if present
						} else {
							$tax = 0.00;
						}

						if (isset($totals['shipping']) && $totals['shipping']->getValue()) {
							$ship_method = $totals['shipping']->getValue(); //shipping if present
						} else {
							$ship_method = 0.00;
						}
					$data['grandtotal'] = number_format($quote->getGrandTotal(), 2);
					$data['subtotal'] = number_format($quote->getSubtotal(), 2);
					$data['discount'] = number_format($discount, 2);
					$data['tax'] = number_format($tax, 2);
					$jsonArray['data'] = $data;
					//------------End -Get updated total------------
				}
            
            
				$jsonArray['status'] =  'success';
				$jsonArray['status_code'] = 200; 
				$jsonArray['message'] =  $return;
        } catch (\Exception $e) {
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
