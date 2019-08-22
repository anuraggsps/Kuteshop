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
 
namespace Ced\CsMultiShipping\Model;
 
class Shipping extends \Magento\Shipping\Model\Shipping
{
    const SEPARATOR = '~';
    
    const METHOD_SEPARATOR = ':';
    
    protected $_objectManager;    
    protected $_helper;
    protected $_register;
    protected $_request;
    
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Shipping\Model\Shipment\RequestFactory $shipmentRequestFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\Math\Division $mathDivision,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Magento\Framework\App\RequestInterface $request
    ) {
        parent::__construct($scopeConfig, $shippingConfig, $storeManager, $carrierFactory, $rateResultFactory, $shipmentRequestFactory, $regionFactory, $mathDivision, $stockRegistry);
        
        $this->_request = $request;
        $this->_objectManager = $objectInterface;
        $this->_helper = $this->_objectManager->get('Ced\CsMultiShipping\Helper\Data');
        $this->_register = $this->_objectManager->get('Magento\Framework\Registry');
    }
    
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if(!$this->_helper->isEnabled()) {
            return parent::collectRates($request);
        }
        $quotes = array();
        $vendorActiveMethods = array(); 
        $vendorAddressDetails = array();
        foreach($request->getAllItems() as $item) {
            if($vendorId = $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')->getVendorIdByProduct($item->getProduct())) {
                $vendor = $this->_objectManager->create('Ced\CsMarketplace\Model\Vendor')->load($vendorId);
                if($vendor && $vendor->getId()) {
                    $this->_register->register('current_order_vendor', $vendor);
                }        
                if(isset($vendorActiveMethods[$vendorId]) && count($vendorActiveMethods[$vendorId])>0 
                    && isset($vendorAddress[$vendorId]) && count($vendorAddress[$vendorId])>0
                ) {
                    $activeMethods = $vendorActiveMethods[$vendorId];
                    $vendorAddress = $vendorAddressDetails[$vendorId];
                }
                else{
                    $activeMethods = $this->_helper->getActiveVendorMethods($vendorId);
                    $vendorAddress = $this->_helper->getVendorAddress($vendorId);
                }
                if(count($activeMethods)>0 && $this->_helper->validateAddress($vendorAddress)
                    && $this->_helper->validateSpecificMethods($activeMethods)
                ) {
                    if(!isset($quotes[$vendorId])) { 
                        $quotes[$vendorId] = array(); 
                    }
                    $quotes[$vendorId][] = $item;
                    if(!isset($vendorActiveMethods[$vendorId])) {
                        $vendorActiveMethods[$vendorId]=$activeMethods; 
                    }
                    if(!isset($vendorAddressDetails[$vendorId])) {
                        $vendorAddressDetails[$vendorId]=$vendorAddress; 
                    }
                } else {
                    $quotes['admin'][] = $item;
                }
                if($this->_register->registry('current_order_vendor')!=null) {
                    $this->_register->unregister('current_order_vendor');
                }
            }
            else {
                $quotes['admin'][] = $item;
            }    
        }
        if($this->_register->registry('current_order_vendor')!=null) {
            $this->_register->unregister('current_order_vendor');
        }       
        $origRequest = clone $request;
        $last_count = 0;
        $prod_model = $this->_objectManager->get('Magento\Catalog\Model\Product');
        if($this->_objectManager->get('Magento\Checkout\Model\Session')->getInvalidItem()) {
            $this->_objectManager->get('Magento\Checkout\Model\Session')->unsInvalidItem(); 
        }
            
        foreach ($quotes as $vendorId => $items){
            $request = clone $origRequest;
            $request->setVendorId($vendorId);
            $request->setVendorItems($items);
            $request->setAllItems($items);
            $request->setPackageWeight($this->getItemWeight($request, $items));
            $request->setPackageQty($this->getItemQty($request, $items));
            $request->setPackageValue($this->getItemSubtotal($request, $items));
            $request->setBaseSubtotalInclTax($this->getItemSubtotal($request, $items));
            $vendorcarriers=array();
            if($vendorId!='admin') {
                $vendor = $this->_objectManager->create('Ced\CsMarketplace\Model\Vendor')->load($vendorId);
                if($vendor && $vendor->getId()) {
                    $this->_register->register('current_order_vendor', $vendor);
                }
                $vendorCarriers = array_keys($vendorActiveMethods[$vendorId]);
                $vendorAddress = array();
                $vendorAddress = $vendorAddressDetails[$vendorId];
                if(isset($vendorAddress['country_id'])) {
                    $request->setOrigCountry($vendorAddress['country_id']); 
                }
                if(isset($vendorAddress['region'])) {
                    $request->setOrigRegionCode($vendorAddress['region']); 
                }
                if(isset($vendorAddress['region_id'])) {
                    $origRegionCode = $vendorAddress['region_id']; 
                    if (is_numeric($origRegionCode)) {
                        $origRegionCode = $this->_objectManager->get('Magento\Directory\Model\Region')->load($origRegionCode)->getCode();
                    }
                    $request->setOrigRegionCode($origRegionCode);
                }
                if(isset($vendorAddress['postcode'])) {
                    $request->setOrigPostcode($vendorAddress['postcode']); 
                }
                if(isset($vendorAddress['city'])) {
                    $request->setOrigCity($vendorAddress['city']); 
                }
            }
                
            $storeId = $request->getStoreId();
            if (!$request->getOrig()) {
                $request
                    ->setCountryId($this->_objectManager->create('Ced\CsMarketplace\Helper\Data')->getStoreConfig(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_COUNTRY_ID, $storeId))
                    ->setRegionId($this->_objectManager->create('Ced\CsMarketplace\Helper\Data')->getStoreConfig(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_REGION_ID, $storeId))
                    ->setCity($this->_objectManager->create('Ced\CsMarketplace\Helper\Data')->getStoreConfig(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_CITY, $storeId))
                    ->setPostcode($this->_objectManager->create('Ced\CsMarketplace\Helper\Data')->getStoreConfig(\Magento\Shipping\Model\Config::XML_PATH_ORIGIN_POSTCODE, $storeId));
            }
            
            $limitCarrier = $request->getLimitCarrier();
            if(!is_array($limitCarrier)){
                if($limitCarrier=='vendor' || $limitCarrier=='vendor_rates'){
                    $limitCarrier='';
                }
            }
            if (!$limitCarrier) {
                $carriers = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStoreConfig('carriers', $storeId);
                foreach ($carriers as $carrierCode => $carrierConfig) {
                    if($vendorId!='admin') {
                        if(!in_array($carrierCode, $vendorCarriers)) {
                            continue; 
                        }
                        $request->setVendorShippingSpecifics($vendorActiveMethods[$vendorId][$carrierCode]);
                    }
                    $this->collectCarrierRates($carrierCode, $request);
                }
            } else {
                if (!is_array($limitCarrier)) {
                    $limitCarrier = array($limitCarrier);
                }
                foreach ($limitCarrier as $carrierCode) {
                    if($vendorId!='admin') {
                        if(!in_array($carrierCode, $vendorCarriers)) {
                            continue; 
                        }
                    }
                    $carrierConfig = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStoreConfig('carriers/' . $carrierCode, $storeId);
                    if (!$carrierConfig) {
                        continue;
                    }
                    $this->collectCarrierRates($carrierCode, $request);
                }                
            }
            if($this->_register->registry('current_order_vendor')!=null) {
                $this->_register->unregister('current_order_vendor');
            }
            
            $total_count = count($this->getResult()->getAllRates());
            $current_count = $total_count - $last_count;
            $last_count = $total_count;
            if($current_count < 1) {
                $prod_name = array();
                $prod_name = $this->_objectManager->get('Magento\Checkout\Model\Session')->getInvalidItem();
                foreach ($items as $item) {
                    $prod_name[] = $prod_model->load($item->getProductId())->getName();
                }
                $this->_objectManager->get('Magento\Checkout\Model\Session')->setInvalidItem($prod_name);        
            }            
        }
        
        $shippingRates = $this->getResult()->getAllRates();
        $newVendorRates = array();
        foreach ($this->ratesByVendor($shippingRates) as $vendorId => $rates) {
            if(!sizeof($newVendorRates)) {
                foreach($rates as $rate){
                     $newVendorRates[$rate->getCarrier().'_'.$rate->getMethod()] = $rate->getPrice();
                }
            }else{
                $tmpRates = array();
                foreach($rates as $rate){
                    foreach($newVendorRates as $code => $shippingPrice){
                        $tmpRates[$code.self::METHOD_SEPARATOR.$rate->getCarrier().'_'.$rate->getMethod()] = $shippingPrice + $rate->getPrice();
                    }
                }
                $newVendorRates = $tmpRates;
            }
        }
            
        foreach($newVendorRates as $code=>$shippingPrice){
            $method = $this->_objectManager->create('Magento\Quote\Model\Quote\Address\RateResult\Method');
            $method->setCarrier('vendor_rates');
            $carrier_title = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStoreConfig('ced_csmultishipping/general/carrier_title', $storeId);
            $method->setCarrierTitle(__($carrier_title));                 
            $method->setMethod($code);
            $carrier_title = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStoreConfig('ced_csmultishipping/general/method_title', $storeId);
            $method->setMethodTitle(__($carrier_title));                 
            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);
            $this->getResult()->append($method);
        }

        return $this;
    }
    
    /**
     * Group shipping rates by each vendor.
     *
     * @param unknown $shippingRates
     */
    public function ratesByVendor($shippingRates)
    {
        $rates = array();
        foreach($shippingRates as $rate){
            if(!$rate->getVendorId()) {
                $rate->setVendorId("admin"); 
            }
            if(!isset($rates[$rate->getVendorId()])) {
                $rates[$rate->getVendorId()] = array();
            }
            $rates[$rate->getVendorId()][] = $rate;
        }
        ksort($rates);
        return $rates;
    }
    
    /**
     * Retrieve item quantity by id
     *
     * @param  int $itemId
     * @return float|int
     */
    public function getItemQty($request,$items)
    {
        $qty = 0;
        foreach ($items as $item) {
            if($item->getTypeId() == 'virtual'){
               continue;
            }
            $qty += $item->getQty();
        }
        return $qty;
    }
    
    /**
     * Retrieve item quantity by id
     *
     * @param  int $itemId
     * @return float|int
     */
    public function getItemWeight($request,$items)
    {
        $qty = 0;
        foreach ($items as $item) {
            if($item->getTypeId() == 'virtual'){
               continue;
            }
            $qty += $item->getQty()*$item->getWeight();
        }
        return $qty;
    }
    
     
    /**
     * Retrieve item Base subtotal by id
     *
     * @param  int $itemId
     * @return float|int
     */
    public function getItemSubtotal($request,$items)
    {
        $row_total = 0;
        foreach ($items as $item) {
            $row_total += $item->getBaseRowTotalInclTax();
        }
        return $row_total;
    }
}
