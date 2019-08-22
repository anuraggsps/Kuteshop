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
  * @package   Ced_CsMembership
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsMembership\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;
class Marketplace extends \Ced\CsMarketplace\Helper\Data
{

    
    /**
     * Function for getting Config value of current store
     *
     * @param string $path,
     */
    public function getStoreConfig($path,$storeId=null)
    {
    
        $store=$this->_storeManager->getStore($storeId);

        $patharray=array();
        $patharray=explode('/',$path);
        $key=end($patharray);
        if($key == 'category'){
        
                    $subcriptionLimit = $this->_objectManager->get('Ced\CsMembership\Helper\Data')->getAllowedCategory($path, $store);
                
                      $subcriptionLimit = array_unique($subcriptionLimit);
                      $subcriptionLimit = array_filter($subcriptionLimit);
                      $subcriptionLimit = implode(',',$subcriptionLimit);
                    return $subcriptionLimit;
        }
        
        return $this->_scopeConfigManager->getValue($path, 'store', $store->getCode());
    }

    /**
     * get Product limit
     *
     * @return integer
     */
    public function getVendorProductLimit()
    {
        $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore(null)->getStoreId();
        $subcriptionLimit = $this->_objectManager->create('Ced\CsMembership\Helper\Data')->getLimit($storeId);
        $defaultLimit = $this->_scopeConfigManager->getValue('ced_vproducts/general/limit');
        return $subcriptionLimit + $defaultLimit;
    }
    
   
    

    
}

