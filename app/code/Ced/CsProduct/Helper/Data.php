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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Helper;
use Magento\Customer\Model\Session; 

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	protected $_customerSession;
    protected $_cacheTypeList;
     protected $_cacheFrontendPool;
	public function __construct(
					\Magento\Framework\App\Helper\Context $context,
					\Magento\Framework\ObjectManagerInterface $objectManager,
					Session $customerSession,
					\Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
                    \Magento\Framework\App\Cache\Frontend\Pool $cacheFrontendPool,
					\Magento\Framework\App\Request\Http $http
							)
    {
		$this->_objectManager = $objectManager;
		$this->_scopeConfigManager = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
		 $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
		parent::__construct($context);
		$this->_customerSession = $customerSession;
		$this->request = $http;
    }
	
	public function isVendorLoggedIn(){
		//$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
		$vendorId = $this->_customerSession->getVendorId();
		if(!$vendorId) {
            return false;
		}
		return true;
	}
	public function getSimpleUrl()
	{  
		$url = $this->_objectManager->create('\Magento\Framework\UrlInterface');
		$Uri = $url->getUrl('csproduct/vproducts/new',array('set'=>$this->getFirstAttributeSet(),'type'=>'simple'));
		return $Uri;
	}
	public function getConfigurableUrl()
	{
		$url = $this->_objectManager->create('\Magento\Framework\UrlInterface');
		$Uri = $url->getUrl('csproduct/vproducts/new',array('set'=>$this->getFirstAttributeSet(),'type'=>'configurable'));
		return $Uri;
	}
	public function getBundleUrl()
	{
		$url = $this->_objectManager->create('\Magento\Framework\UrlInterface');
		$Uri = $url->getUrl('csproduct/vproducts/new',array('set'=>$this->getFirstAttributeSet(),'type'=>'bundle'));
		return $Uri;
	}
	public function getVirtualUrl()
	{
		$url = $this->_objectManager->create('\Magento\Framework\UrlInterface');
		$Uri = $url->getUrl('csproduct/vproducts/new',array('set'=>$this->getFirstAttributeSet(),'type'=>'virtual'));
		return $Uri;
	}
	public function getDownloadableUrl()
	{
		$url = $this->_objectManager->create('\Magento\Framework\UrlInterface');
		$Uri = $url->getUrl('csproduct/vproducts/new',array('set'=>$this->getFirstAttributeSet(),'type'=>'downloadable'));
		return $Uri;
	}
	public function getGroupedUrl()
	{
		$url = $this->_objectManager->create('\Magento\Framework\UrlInterface');
		$Uri = $url->getUrl('csproduct/vproducts/new',array('set'=>$this->getFirstAttributeSet(),'type'=>'grouped'));
		return $Uri;
	}
	
	public function getWizardUrl(){
		$url = $this->_objectManager->create('\Magento\Framework\UrlInterface');
		$Uri = $url->getUrl('csproduct/vproducts/wizard',array('set'=>$this->request->getParam('set')));
		return $Uri;
	}
	
	public function cleanCache()
    {
        $types = array('config', 'layout', 'block_html', 'full_page');
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
        return true;
    }
    
    public function isActive()
    { 
        return (boolean)$this->_scopeConfigManager->getValue('ced_csmarketplace/general/ced_vproduct_activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    public function getFirstAttributeSet(){
        $default = '4';
        $activeAttributeSets = $this->getActiveAttributeSet();
        if(is_array($activeAttributeSets)){
            $default = current($activeAttributeSets);    
        }
        return $default;
    }
    public function getActiveAttributeSet()
    {
        $val =  $this->_scopeConfigManager->getValue(
            'ced_vproducts/general/set'
        );
        if($val){
            $val = explode(',', $val);
        }
        return $val;
    }
}