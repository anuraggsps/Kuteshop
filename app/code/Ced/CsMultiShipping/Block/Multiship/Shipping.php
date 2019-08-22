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
 * @category  Ced
 * @package   Ced_CsMultiShipping
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMultiShipping\Block\Multiship;


class Shipping extends \Magento\Multishipping\Block\Checkout\Shipping
{
    protected $_objectManager;    
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory,
        \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping,
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    ) {
        parent::__construct($context, $filterGridFactory, $multishipping, $taxHelper, $priceCurrency, $data);        
        $this->_objectManager = $objectInterface;
        
    }

    
    protected function _prepareLayout()
    {
        if(!$this->_objectManager->get('Ced\CsMultiShipping\Helper\Data')->isEnabled()) {
            $this->setTemplate('Magento_Multishipping::checkout/shipping.phtml');
        }  
        return parent::_prepareLayout();
    }
    
    public function getSelectedMethod($address)
    {
        $selectedMethod = str_replace("vendor_rates_", '', $address->getShippingMethod());
        $selectedMethods = explode(\Ced\CsMultiShipping\Model\Shipping::METHOD_SEPARATOR, $selectedMethod);
        return $selectedMethods;
    }

    /**
     * @param Address $address
     * @return mixed
     */
    public function getShippingRates($address)
    {               
        if(!$this->_objectManager->get('Ced\CsMultiShipping\Helper\Data')->isEnabled()) {
            return parent::getShippingRates($address);
        }
        $groups = $address->getGroupedAllShippingRates();

        $rates = array();
        foreach($groups as $code => $_rates){            
            if($code == 'vendor_rates') {                
                foreach ($_rates as $rate) {
                    if (!$rate->isDeleted()) {
                        if (!isset($rates[$rate->getCarrier()])) {
                            $rates[$rate->getCarrier()] = array();
                        }
                        $rates[$rate->getCarrier()][] = $rate;
                    }
                }
            }
        } 
        return $rates;
    }
    
    public function getRatesByVendor($address)
    {
        
        $addrs_mthd = $address->getGroupedAllShippingRates();
        $groups = array();

        foreach($addrs_mthd as $code => $rateCollection){          
            foreach($rateCollection as $rate){
                if($rate->isDeleted()) { 
                    continue; 
                }
                if($rate->getCarrier() == 'vendor_rates') { 
                    continue; 
                }

                $tmp = explode(\Ced\CsMultiShipping\Model\Shipping::SEPARATOR, $rate->getCode());
                
                $vendorId = isset($tmp[1])? $tmp[1] : "admin";
                $vendor = $this->_objectManager->create('Ced\CsMarketplace\Model\Vendor');
                if($vendorId && $vendorId!="admin") {
                    $vendor = $vendor->load($vendorId);
                }
                
                if(!isset($groups[$vendorId])) { 
                    $groups[$vendorId] = array(); 
                }
        
                $groups[$vendorId]['title'] = $vendor->getId()? $vendor->getPublicName(): $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStore()->getWebsite()->getName();
                
                if(!isset($groups[$vendorId]['rates'])) { 
                    $groups[$vendorId]['rates'] = array(); 
                }
                $groups[$vendorId]['rates'][] = $rate;
            }          
        }
        return $groups;
    }
    

}
