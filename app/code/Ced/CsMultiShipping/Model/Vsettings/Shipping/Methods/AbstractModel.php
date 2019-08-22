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


 namespace Ced\CsMultiShipping\Model\Vsettings\Shipping\Methods;
 
class AbstractModel extends \Ced\CsMarketplace\Model\FlatAbstractModel
{
    const SHIPPING_SECTION = 'shipping';
    protected $_code = '';
    protected $_fields = array();
    protected $_codeSeparator = '-';
    
    /**
     * Get current store
     *
     * @return Mage_Core_Model_Store
     */
     
    public function getStore() 
    {
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        if($storeId) {
            return $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStore($storeId); 
        }
        else { 
            return $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStore(); 
        }
    }
     
    /**
     * Get current store
     *
     * @return Mage_Core_Model_Store
     */
     
    public function getStoreId() 
    {
        return $this->getStore()->getId();
    }
    
    
    /**
     * Get the code
     *
     * @return string
     */
    public function getCode() 
    {
        return $this->_code;
    }
    
    /**
     * Get the code separator
     *
     * @return string
     */
    public function getCodeSeparator() 
    {
        return $this->_codeSeparator;
    }
    
    /**
     *  Retreive input fields
     *
     * @return array
     */
    public function getFields() 
    {
        $this->_fields = array();
        $this->_fields['active'] = array('type'=>'select',
                                        'values'=>array(array('label'=>__('Yes'), 'value'=>1),
                                        array('label'=>__('No'), 'value'=>0))
                                    );
        return $this->_fields;
    }
    
    /**
     * Retreive labels
     *
     * @param  string $key
     * @return string
     */
    public function getLabel($key) 
    {
        switch($key) {
        case 'active' : 
            return __('Active'); break;
        default : 
            return __($key); break;
        }
    }
    
    public function validateSpecificMethod($methodData)
    {
        if(count($methodData)>0) {
            return true;
        }
        else {
            return false; 
        }
    }
}
