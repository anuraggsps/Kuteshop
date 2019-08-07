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
class CartDetail extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() {
        try {

            $param = $this->getRequest()->getParams();
            if (!empty($param['quote_id'])) {
                $data = array();
                $storeid = $this->_storeManager->getStore()->getId();
               // var_dump($storeid); 
                $quoteModel = $this->_objectManager->get('Magento\Quote\Model\Quote')->setStoreId($storeid);
                $quote      = $quoteModel->loadActive($param['quote_id']);
                //var_Dump($quote->getIsActive()); die;
                
                if ($quote->getIsActive()) {
                    $prod = array();
                    $i = 0;
                    $itemDisc = 0;
                    foreach ($quote->getAllVisibleItems() as $item) {
                        ////var_dump($item->getData()); die;
                        $itemDisc += (float) $item->getDiscountAmount();
                        $prod[$i]['item_id'] = $item->getId();
                        $prod[$i]['prod_Id'] = $item->getProductId();
                        $prod[$i]['type_id'] = $item->getProduct()->getTypeId();

                        $prod[$i]['sku'] = $item->getSku();
                        $prod[$i]['name'] = $item->getName();
                        $prod[$i]['price'] = number_format($item->getPrice(), 2);
                        $prod[$i]['qty'] = $item->getQty();
                        if ($item->getProduct()->isSalable())
                            $is_Salable = 1;
                        else
                            $is_Salable = 0;

                        $prod[$i]['isSalable'] = $is_Salable;
                        $imgUrl = '';
                        if ($item->getProduct()->getSmallImage() && (($item->getProduct()->getSmallImage()) != 'no_selection')) {
                            //$imgUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($item->getProduct()->getSmallImage());
                            $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $item->getProduct()->getSmallImage();
                        }

                        $prod[$i]['image'] = $imgUrl;
                        //var_dump(get_class($item->getProduct()->getTypeInstance(true))); die;
                        $_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());

                        /* Each custom option loop */
                        //var_dump($_customOptions); die;
                        $option = array();
                        $j = 0;

                        if (isset($_customOptions['attributes_info']) and ! empty($_customOptions['attributes_info'])) {
                            foreach ($_customOptions['attributes_info'] as $_option) {
                                $option[$j]['label'] = $_option['label'];
                                $option[$j]['value'] = $_option['value'];
                                // Do your further logic here
                                ++$j;
                            }
                        }

                        if (isset($_customOptions['options']) and ! empty($_customOptions['options'])) {
                            foreach ($_customOptions['options'] as $_option) {
                                $option[$j]['label'] = $_option['label'];
                                $option[$j]['value'] = $_option['value'];
                                // Do your further logic here
                                ++$j;
                            }
                        }

                        if (isset($_customOptions['bundle_options']) and ! empty($_customOptions['bundle_options'])) {
                            foreach ($_customOptions['bundle_options'] as $_opts) {
                                $option[$j]['label'] = $_opts['label'];
                                $value = array();
                                foreach ($_opts['value'] as $_optval) {
                                    $value[] = $_optval['qty'] . " x " . $_optval['title'] . ' ' . '<span class="price">' . $this->currencyHelper->currency((float) $_optval['price'] / (float) $_optval['qty'], true, false) . '</span>';
                                }
                                $option[$j]['value'] = implode("<br/>", $value);
                                ++$j;
                            }
                        }

                        if (isset($_customOptions['is_downloadable']) and $_customOptions['is_downloadable'] == true) {
                            $block = $this->_objectManager->get("Magento\Downloadable\Block\Checkout\Cart\Item\Renderer");
                            $block->setItem($item);
                            $block->setProduct($item->getProduct());
                            $links = $block->getLinks();
                            $option[$j]['label'] = $block->getLinksTitle();
                            $value = array();
                            foreach ($links as $link) {
                                $value[] = "<span>" . $block->escapeHtml($link->getTitle()) . "</span>";
                            }
                            $option[$j]['value'] = implode("<br/>", $value);
                            ++$j;
                        }

                        $optionhtml = '';

                        foreach ($option as $_opt) {
                            $optionhtml .= '<b>' . $_opt['label'] . '</b>: ' . $_opt['value'] . '<br/>';
                        }

                        //$helper = Mage::helper('catalog/product_configuration');
                        //$c_option = $helper->getCustomOptions($item);
                        //echo "fff";
                        //echo "<pre>";
                        //print_r($c_option);				

                        $prod[$i]['options'] = $optionhtml;

                        ++$i;
                    }
                    $data['products'] = array_reverse($prod); //$prod;

                    $totals = $quote->getTotals(); //Total object
                    //$subtotal = round($totals["subtotal"]->getValue()); //Subtotal value
                    //$grandtotal = round($totals["grand_total"]->getValue()); //Grandtotal value
                    if (isset($totals['discount']) && $totals['discount']->getValue()) {
                        $discount = $totals['discount']->getValue(); //Discount value if applied
                    } else {
                        $discount = $itemDisc;
                    }
                    if (isset($totals['tax']) && $totals['tax']->getValue()) {
                        $tax = $totals['tax']->getValue(); //Tax value if present
                    } else {
                        $tax = 0.00;
                    }

                    if (isset($totals['shipping']) && $totals['shipping']->getValue()) {
                        $ship_method = $totals['shipping']->getValue(); //Tax value if present
                    } else {
                        $ship_method = 0.00;
                    }


                    $data['grandtotal'] = number_format($quote->getGrandTotal(), 2);
                    $data['subtotal'] = number_format($quote->getSubtotal(), 2);
                    $data['discount'] = number_format($discount, 2);
                    $data['tax'] = number_format($tax, 2);
                    $data['ship_cost'] = number_format($ship_method, 2);
                    $data['ship_method'] = $quote->getShippingAddress()->getShippingMethod();

                    $data['coupon_applied'] = $quote->getCouponCode();


                    $data['quote_id'] = $param['quote_id'];
                    $data['is_virtual'] = $quote->isVirtual();
                    $shipAddress = $billAddress = $shipAddr = $billAddr = array();
                    $shipAddr = $quote->getShippingAddress();
                    $shipAddress = array(
                        'addr_id' => $shipAddr->getId(),
                         
                        'as_html' => $this->formatAddrHtml($shipAddr)
                    );

                    $billAddr = $quote->getBillingAddress();
                    $billAddress = array(
                        'addr_id' => $billAddr->getId(),
                         
                        'as_html' => $this->formatAddrHtml($billAddr)
                    );

                    $data['quote_count'] = $this->cart->getSummaryQty();
                    $data['cust_has_addr'] = false;
                    $custId = $this->getRequest()->getParam('cust_id', false);
                    if($custId){
                        $customerModel = $this->_objectManager->get('Magento\Customer\Model\Customer');
                        $customer = $customerModel->load($custId);
                        $addrcount = count($customer->getAddresses());
                        if($addrcount>0)
							$shipAddress = array(
								'addr_id' => $customer->getDefaultShipping(),
								 
								'as_html' => $this->formatAddrHtml($shipAddr)
							);
							$billAddress = array(
								'addr_id' => $customer->getDefaultBilling(),
								 
								'as_html' => $this->formatAddrHtml($billAddr)
							);
                            $data['cust_has_addr'] = true;
                            
                    }
                    $data['shipping_addr'] = $shipAddress;
                    $data['billing_addr'] = $billAddress;
                    $jsonArray['response'] = $data;
                    $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
                } else {
                    $jsonArray['response'] = __("Cart is de-activated. Try with some other products.");
                    $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
                }
            } else {
                $jsonArray['response'] = __("Please check parameters");
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
