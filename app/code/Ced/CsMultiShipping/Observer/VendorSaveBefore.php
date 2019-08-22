<?php 

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMultiShipping
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsMultiShipping\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

Class VendorSaveBefore implements ObserverInterface
{
    
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    protected $_quoteFactory;
    
    public function __construct(        
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\QuoteFactory $quoteFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_quoteFactory = $quoteFactory;
    }
    
    /**
     * Adds catalog categories to top menu
     *
     * @param  \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    { 
        if($this->_objectManager->get('Ced\CsMultiShipping\Helper\Data')->isEnabled()) {
            $vorder = $observer->getEvent()->getvorder();
            if (!$vorder->getId()) {
                $order = $vorder->getOrder();
                $quoteId = $order->getQuoteId();
                if ($quoteId) {
                    $quote = $this->_quoteFactory->create()->load($quoteId);
                    if ($quote && $quote->getId()) {
                        $addresses = $quote->getAllShippingAddresses();
                        foreach ($addresses as $address) {
                            if ($address) {
                                $shippingMethod = $address->getShippingMethod();
                                if (substr($shippingMethod, 0, 12) == 'vendor_rates') {
                                    $shippingMethod = str_replace('vendor_rates_', '', $shippingMethod);
                                }
                                $shippingMethods = explode(\Ced\CsMultiShipping\Model\Shipping::METHOD_SEPARATOR, $shippingMethod);
                                $vendorId = 0;
                                foreach ($shippingMethods as $method) {
                                    $rate = $address->getShippingRateByCode($method);
                                    $methodInfo = explode(\Ced\CsMultiShipping\Model\Shipping::SEPARATOR, $method);
                                    if (sizeof($methodInfo)!= 2) {
                                        continue;
                                    }
                                    $vendorId = isset($methodInfo [1])?$methodInfo[1]:"admin";
                                        
                                    if ($vendorId == $vorder->getVendorId()) {
                                        $vorder->setShippingAmount($this->_objectManager->get('Magento\Directory\Helper\Data')->currencyConvert($rate->getPrice(), $order->getBaseCurrencyCode(), $order->getOrderCurrencyCode()));
                                        $vorder->setBaseShippingAmount($rate->getPrice());
                                        $vorder->setCarrier($rate->getCarrier());
                                        $vorder->setCarrierTitle($rate->getCarrierTitle());
                                        $vorder->setMethod($rate->getMethod());
                                        $vorder->setMethodTitle($rate->getMethodTitle());
                                        $vorder->setCode($method);
                                        $vorder->setShippingDescription($rate->getCarrierTitle()."-".$rate->getMethodTitle());
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }            
        
    }
   
        
}
