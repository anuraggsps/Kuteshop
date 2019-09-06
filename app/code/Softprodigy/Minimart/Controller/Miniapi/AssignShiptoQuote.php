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
class AssignShiptoQuote extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() {
        //~ try {
            $request = $this->getRequest()->getContent();
		    $params = json_decode($request, true);
			
            $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->loadActive($params['quote_id']);

            if (!$quote->getId()) {
                throw new \Exception(__("Quote does not exist."));
            }

            $result = $this->setShippingMethod($quote, $params['ship_method'],$store =9);
            $data = [];
            if ($result) {
                $totals = $quote->getTotals(); //Total object
                $quote->setTotalsCollectedFlag(false)->collectTotals()->save();
                $address = $quote->getBillingAddress();
                if (!$quote->isVirtual()) {
                    $address = $quote->getShippingAddress();
                }


                foreach ($totals as $key => $obj) {
                    $data[$key] = array('label' => $obj->getTitle(), 'value' => number_format($this->currencyHelper->currency($obj->getValue(), false, false), 2));
                }

                if (isset($data['subtotal'])) {
                    $data['subtotal']['value'] = number_format($this->currencyHelper->currency($quote->getBaseSubtotal(), false, false), 2);
                }

                //$data['discount'] =  number_format($this->currencyHelper->currency($address->getBaseDiscountAmount(), false, false), 2);
                if (isset($totals['discount'])) {
                    $data['discount']['value'] = number_format($this->currencyHelper->currency($address->getBaseDiscountAmount(), false, false), 2);
                } else {
                    $data['discount'] = array('label' => __('Discount'), 'value' => number_format(0, 2));
                }

                if (isset($totals['shipping']) && $totals['shipping']->getValue()) {
                    //  $ship_method = $ship_method = $this->currencyHelper->currency($totals['shipping']->getValue(), false, false); //Tax value if present
                    $ship_method = number_format($this->currencyHelper->currency($address->getBaseShippingAmount(), false, false), 2);
                } else {
                    $ship_method = 0.00;
                }

                $data['grandtotal'] = $data['grand_total'];
                if (isset($data['grandtotal']['value'])) {
                    $data['grandtotal']['value'] = number_format($this->currencyHelper->currency($quote->getBaseGrandTotal(), false, false), 2);
                }

                unset($data['grand_total']);

                $data['ship_charge'] = $data['shipping'];
                $data['ship_charge']['value'] = $ship_method;

                if (isset($data['shipping']))
                    unset($data['shipping']);

                if (isset($data["tax"])) {
                    $data['tax']["value"] = number_format($this->currencyHelper->currency($address->getBaseTaxAmount(), false, false), 2);
                }
            }
					$jsonArray ['data'] = $data;
					$string  = "Set Shipping method succesfully";
					$jsonArray ['message'] = $string;
					$jsonArray ['status_code'] = 200;
					$jsonArray ['status'] = "Success";
        //~ } catch (\Exception $e) {
            //~ $jsonArray['data'] = null;
			//~ $jsonArray['status_code'] = 201;
			//~ $jsonArray['status'] = "Failure";
			//~ $jsonArray['message'] = $e->getMessage();
        //~ }
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
