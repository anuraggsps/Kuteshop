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
  * @package   Ced_CsVendorProductAttribute
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsVendorProductAttribute\Model;

class Attributeset  extends \Magento\Framework\Model\AbstractModel
{
  /**
   * @var \Magento\Framework\App\Config\ScopeConfigInterface
  */
  protected $_scopeConfig;

  /**
   * @var \Magento\Framework\ObjectManagerInterface
  */
  protected $_objectManager;

  /**
   * @var \Magento\Store\Model\StoreManagerInterface
  */
  protected $_storeManager;

  protected $_session;

  public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $_storeManager,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_storeManager = $_storeManager;
        $this->_session = $customerSession;
        parent::__construct($context, $registry);
  }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsVendorProductAttribute\Model\ResourceModel\Attributeset');
    }
    
    public function getProductAttributeSets($vendor)
    {
    	if(!is_numeric($vendor))
    		$vendorId = $vendor->getId();
    	else
    		$vendorId = $vendor;
    	 
    	$attributeSets = $this->getCollection()->addFieldToFilter('vendor_id',array('eq'=>$vendorId));
    	return $attributeSets;
    }

    public function getAllowedAttributeSets($vendor_id = null)
    {
        if($vendor_id == null)
          $vendor_id = $this->_session->getVendorId();

        $vendor_attr_sets = [];
        if($vendor_id){
            $vendor_attrset = $this->getProductAttributeSets($vendor_id)->getData();
           $admin_allowed_sets = $this->getAllowedAttributeSetsByAdmin();
            $attrset_model = array_merge($vendor_attrset,$admin_allowed_sets);
            foreach ($attrset_model as $key => $attrset_id) {
                $vendor_attr_sets[] = ['value' => $attrset_id['attribute_set_id'], 'label' => $attrset_id['attribute_set_code']];
            }
        }
        return $vendor_attr_sets;
    }

    public function getAllowedAttributeSetsByAdmin()
    {
        if($this->_scopeConfig == null)
            $this->_scopeConfig = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');        

        $allowedSet = explode(',',$this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStoreConfig('ced_vproducts/general/set',$this->_storeManager->getStore()->getId()));
        $allowed_attr_sets = array();
        foreach ($allowedSet as $setId) {
            $set = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set')->load($setId)->getData();

           if($set!=NULL){
              $allowed_attr_sets[] = array('attribute_set_id'=> $set['attribute_set_id'], 'attribute_set_code'=> $set['attribute_set_name']);
           }
            
        }
        return $allowed_attr_sets;
    }
}
