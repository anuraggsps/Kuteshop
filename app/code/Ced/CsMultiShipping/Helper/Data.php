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
 
namespace Ced\CsMultiShipping\Helper;
 
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_helper;
    
    protected $_objectManager;
    
    public function __construct(\Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
    
        $this->_objectManager = $objectManager;
        parent::__construct($context);
        $this->_helper = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data');
    }
    
    public function isEnabled($storeId = 0)
    {
        if($storeId == 0) {
            $storeId = $this->_helper->getStore()->getId(); 
        }
        return $this->_helper->getStoreConfig('ced_csmultishipping/general/activation', $storeId);
    }
    
    public function getConfigValue($key='',$vendorId=0)
    {
        $value=false;
        if(strlen($key)>0 && $vendorId) {
            $key_tmp = $this->_helper->getTableKey('key');
            $vendor_id_tmp  = $this->_helper->getTableKey('vendor_id');
            $vsettings = $this->_objectManager->get('Ced\CsMarketplace\Model\Vsettings')
                ->loadByField(array($key_tmp,$vendor_id_tmp), array($key,(int)$vendorId));
            if($vsettings && $vsettings->getSettingId()) {
                $value = $vsettings->getValue(); 
            }
        }
        return $value;
    }
    
    public function getActiveVendorMethods($vendorId=0)
    {
        $methods = $this->_objectManager->get('Ced\CsMultiShipping\Model\Source\Shipping\Methods')->getMethods();
        $VendorMethods=array();
        if(count($methods) >0 ) {
            $vendorShippingConfig = $this->getShippingConfig($vendorId);
            foreach($methods as $code => $method) {
                $model=$this->_objectManager->create($method['model']);
                $key = strtolower(\Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel::SHIPPING_SECTION.'/'.$code.'/active');
                if(isset($vendorShippingConfig[$key]['value']) && $vendorShippingConfig[$key]['value']) {
                    $fields = $model->getFields();
                    if (count($fields) > 0) {
                        foreach ($fields as $id=>$field) {
                            $key = strtolower(\Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel::SHIPPING_SECTION.'/'.$code.'/'.$id);
                            if(isset($vendorShippingConfig[$key])) {
                                $VendorMethods[$code][$id] = $vendorShippingConfig[$key]['value']; 
                            }
                        }
                    }
                }
            }
            return $VendorMethods;
        }
        else { 
            return $VendorMethods; 
        }
    }
    
    public function getVendorMethods($vendorId = 0)
    {
        $methods = $this->_objectManager->get('Ced\CsMultiShipping\Model\Source\Shipping\Methods')->getMethods();
        $VendorMethods=array();
        if(count($methods) >0 ) {
            $vendorShippingConfig = $this->getShippingConfig($vendorId);
            foreach($methods as $code=>$method) {

                $model=$this->_objectManager->get($method['model']);
                $fields = $model->getFields();
                if (count($fields) > 0) {
                    foreach ($fields as $id=>$field) {
                        $key = strtolower(\Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel::SHIPPING_SECTION.'/'.$code.'/'.$id);
                        if(isset($vendorShippingConfig[$key])) {
                            $VendorMethods[$code][$id] = $vendorShippingConfig[$key]['value']; 
                        }
                    }
                }
            }
            return $VendorMethods;
        }
        else {
            return $VendorMethods; 
        }
    }
    
    public function getVendorAddress($vendorId=0)
    {
        $VendorAddress = array();
        $model= $this->_objectManager->get('Ced\CsMultiShipping\Model\Vsettings\Shipping\Address');
        $vendorShippingConfig = $this->getShippingConfig($vendorId);
        $fields = $model->getFields();
        if (count($fields) > 0) {
            foreach ($fields as $id=>$field) {
                $key = strtolower(\Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel::SHIPPING_SECTION.'/address/'.$id);
                if(isset($vendorShippingConfig[$key]) && strlen($vendorShippingConfig[$key]['value'])>0) {
                    $VendorAddress[$id] = $vendorShippingConfig[$key]['value']; 
                }
            }
        }
        return $VendorAddress;
    }
    
    public function getShippingConfig($vendorId=0)
    {
        $values=array();
        if($vendorId) {
            
            $group = $this->_helper->getTableKey('group');
            $vendor_id = $this->_helper->getTableKey('vendor_id');
            $vsettings = $this->_objectManager->create('Ced\CsMarketplace\Model\Vsettings')->getCollection()
                ->addFieldToFilter($group, array('eq'=> \Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods\AbstractModel::SHIPPING_SECTION))
                ->addFieldToFilter($vendor_id, array('eq'=>$vendorId));
            if($vsettings && count($vsettings->getData())>0) {
                foreach($vsettings->getData() as $index => $value){
                    $values[$value['key']]=$value;
                }
            }
        }
        return $values;
    }
    
    public function saveShippingData($section, $groups, $vendor_id)
    {
        foreach ($groups as $code=>$values) {
            if(is_array($values) && count($values)>0) {
                foreach ($values as $name=>$value) {
                    $serialized = 0;
                    $key = strtolower($section.'/'.$code.'/'.$name);
                    if (is_array($value)) {
                        $value = implode(",", $value);
                        //$value = serialize($value);
                        //$serialized = 1;
                    }
                    $key_tmp = $this->_helper->getTableKey('key');
                    $vendor_id_tmp = $this->_helper->getTableKey('vendor_id');
                    $setting = $this->_objectManager->create('Ced\CsMarketplace\Model\Vsettings')
                        ->loadByField(array($key_tmp,$vendor_id_tmp), array($key,$vendor_id));
                    if ($setting && $setting->getId()) {
                        $setting->setVendorId($vendor_id)
                            ->setGroup($section)
                            ->setKey($key)
                            ->setValue($value)
                            ->setSerialized($serialized)
                            ->save();
                    } else {
                        $setting = $this->_objectManager->create('Ced\CsMarketplace\Model\Vsettings');
                        $setting->setVendorId($vendor_id)
                            ->setGroup($section)
                            ->setKey($key)
                            ->setValue($value)
                            ->setSerialized($serialized)
                            ->save();
                    }
                }
            }
        }
    }
    
    public function validateAddress($vendorAddress=array())
    {
        $flag=true;
        if(!isset($vendorAddress['country_id']) || !isset($vendorAddress['city']) || !isset($vendorAddress['postcode'])) {
            return false; 
        }
        if(!isset($vendorAddress['region_id']) &&!isset($vendorAddress['region'])) {
            return false; 
        }
        if(isset($vendorAddress['country_id']) && !$vendorAddress['country_id']) {
            return false; 
        }
        if(isset($vendorAddress['region_id']) && !$vendorAddress['region_id']) {
            return false; 
        }
        if(isset($vendorAddress['region']) && !$vendorAddress['region']) {
            return false; 
        }
        if(!isset($vendorAddress['city']) && !$vendorAddress['city']) {
            return false; 
        }
        if(!isset($vendorAddress['postcode']) && !$vendorAddress['postcode']) {
            return false; 
        }
        return $flag;
    }
    
    public function validateSpecificMethods($activeMethods)
    {
        if(count($activeMethods)>0) {
            $methods = $this->_objectManager->get('Ced\CsMultiShipping\Model\Source\Shipping\Methods')->getMethods();
            foreach ($activeMethods as $method => $methoddata){
                if(isset($methods[$method]['model'])) {
                    $model = $this->_objectManager->get($methods[$method]['model'])->validateSpecificMethod($activeMethods[$method]);
                    if(!$model) {
                        return false; 
                    }
                }            
            }
            return true;
        }
        else {
            return false; 
        }
    }
}
