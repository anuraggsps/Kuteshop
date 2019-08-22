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

namespace Ced\CsMultiShipping\Model\Source\Shipping;
 
class Methods extends \Ced\CsMarketplace\Model\System\Config\Source\AbstractBlock
{

    const XML_PATH_CED_CSMULTISHIPPING_SHIPPING_METHODS = 'ced_csmultishipping/shipping/methods';
        
    protected $scopeConfig;
    
    protected $_objectManager;
    
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory, $objectManager);
        $this->scopeConfig = $scopeConfig;
        $this->_objectManager = $objectManager;        
    }
    
    
    /**
     * Retrieve rates data form config.xml
  *
     * @return array
     */
     
    public function getMethods() 
    {
                
        $rates = $this->scopeConfig->getValue(
            self::XML_PATH_CED_CSMULTISHIPPING_SHIPPING_METHODS,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStore()
        );
        
        $allowedmethods = array();
        if(is_array($rates) && count($rates)>0) {
            foreach ($rates as $code => $method){
                if($this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStoreConfig($method['config_path'], $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStore()->getId())) {
                    $allowedmethods[$code] = $rates[$code]; 
                }
            }
        }
        return $allowedmethods;
    }
    /**
     * Retrieve Option values array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $methods = array_keys(self::getMethods());
        $options = array();
        foreach($methods as $method) {
            $method = strtolower(trim($method));
            $options[] = array('value'=>$method,'label'=> __(ucfirst($method)));
        }
        return $options;
    }

}