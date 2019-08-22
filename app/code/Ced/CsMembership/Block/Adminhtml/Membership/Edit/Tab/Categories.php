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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Block\Adminhtml\Membership\Edit\Tab;
use Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Session;

class Categories extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
	protected $_filtercollection;
	public $_objectManager;
	protected $_type;
	/**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
	/**
	 * Get set collection of products
	 *
	 */
	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 */
	public function __construct(
			\Magento\Framework\View\Element\Template\Context $context,
			\Magento\Framework\ObjectManagerInterface $objectManager,
			\Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type $type,
			\Magento\Framework\Module\Manager $moduleManager,
			Session $customerSession,	
			UrlFactory $urlFactory,
			\Magento\Framework\Registry $registerInterface
	) {
		parent::__construct($context,$customerSession,$objectManager,$urlFactory);
		$this->setTemplate('csmembership/categories.phtml');
		$this->_context = $context;	
		$this->_objectManager = $objectManager;
		$this->moduleManager = $moduleManager;
		$this->_type = $type;
		$this->_coreRegistry = $registerInterface;
		$vendorId=$this->getVendorId();
		
		$collection = $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')->getVendorProducts('',$vendorId,0);
		
		if(count($collection)>0){
			$products=array();
			$statusarray=array();
			foreach($collection as $data){
				array_push($products,$data->getProductId());
				$statusarray[$data->getProductId()]=$data->getCheckStatus();
			}
			$currentStore=$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore(null)->getId();
			$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->setCurrentStore(\Magento\Store\Model\Store::DEFAULT_STORE_ID);
			$productcollection =$this->_objectManager->get('Magento\Catalog\Model\Product')->getCollection();
			
			$storeId=0;
			if($this->getRequest()->getParam('store')){
				$websiteId=$this->_objectManager->get('Magento\Store\Model\Store')->load($this->getRequest()->getParam('store'))->getWebsiteId();
				if($websiteId){
					if(in_array($websiteId,$this->_objectManager->get('Ced\CsMarketplace\Model\Vproducts')->getAllowedWebsiteIds())){
						$storeId=$this->getRequest()->getParam('store');
					}
				}
			}
			
			$productcollection->addAttributeToSelect('*')->addAttributeToFilter('entity_id',array('in'=>$products))->addAttributeToSort('entity_id', 'DESC');
			
			if($storeId){
				$productcollection->addStoreFilter($storeId);
				$productcollection->joinAttribute('name', 'catalog_product/name', 'entity_id', null, 'inner', $storeId);
				$productcollection->joinAttribute('status', 'catalog_product/status', 'entity_id', null, 'inner', $storeId);
				$productcollection->joinAttribute('price', 'catalog_product/price', 'entity_id', null, 'left', $storeId);
				$productcollection->joinAttribute('thumbnail', 'catalog_product/thumbnail', 'entity_id', null, 'left', $storeId);
			}
			
			if ($this->moduleManager->isEnabled('Magento_CatalogInventory')) {
				$productcollection->joinField('qty',
						'cataloginventory_stock_item',
						'qty',
						'product_id=entity_id',
						'{{table}}.stock_id=1',
						'left');
			}
			$productcollection->joinField('check_status','ced_csmarketplace_vendor_products', 'check_status','product_id=entity_id',null,'left');
			
			$params = $this->_objectManager->get('Ced\CsMarketplace\Model\Session')->getData('product_filter');
			
			if(isset($params) && is_array($params) && count($params)>0){
				foreach($params as $field=>$value){
					if($field=='store'||$field=='store_switcher'||$field=="__SID")
						continue;
					if(is_array($value)){
						if(isset($value['from']) && urldecode($value['from'])!=""){
							$from = urldecode($value['from']);
							$productcollection->addAttributeToFilter($field, array('gteq'=>$from));
						}
						if(isset($value['to'])  && urldecode($value['to'])!=""){
							$to = urldecode($value['to']);
							$productcollection->addAttributeToFilter($field, array('lteq'=>$to));
						}
					}else if(urldecode($value)!=""){
						$productcollection->addAttributeToFilter($field, array("like"=>'%'.urldecode($value).'%'));
					}
				}
			}
			
			$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->setCurrentStore($currentStore);
			$productcollection->setStoreId($storeId);
			if($productcollection->getSize()>0){
				$this->_filtercollection=$productcollection;
				$this->setVproducts($this->_filtercollection);
			}
		}
	}
	
	/**
	 * prepare product list layout
	 *@return Ced_CsMarketplace_Block_Vproducts
	 */
	protected function _prepareLayout() {
		parent::_prepareLayout();
		if($this->_filtercollection){
			if($this->_filtercollection->getSize()>0){
				if($this->getRequest()->getActionName()=='index'){
					$pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','custom.pager');
					$pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'all'=>'all'));
					$pager->setCollection($this->_filtercollection);
					$this->setChild('pager', $pager);
				}
			}
		}
		return $this;
	}
	
	/**
	 * Get pager HTML
	 *
	 */
	public function getPagerHtml() {
		return $this->getChildHtml('pager');
	}
	
	/**
	 * get Edit product url
	 *
	 */
	public function getEditUrl($product)
	{
		return $this->getUrl('*/*/edit', array('_nosid'=>true,'id' => $product->getId(),'type'=>$product->getTypeId(),'store'=>$this->getRequest()->getParam('store',0)));
	}
	
	public function getTypes()
	{
		return $this->_type->toOptionArray(false,true);
	}
	
	/**
	 * get Product Type url
	 *
	 */
	public function getProductTypeUrl()
	{
		return $this->getUrl('*/*/new/',array('_nosid'=>true));
	}
	
	/**
	 * get Delete url
	 *
	 */
	public function getDeleteUrl($product)
	{
		return $this->getUrl('*/*/delete', array('_nosid'=>true,'id' => $product->getId()));
	}
	
	/**
	 * back Link url
	 *
	 */
	public function getBackUrl()
	{
		return $this->getUrl('*/*/index');
	}
	
	/**
	 * get Category IDs
	 *
	 */

	public function getCategoryIds()
    {
        $catId_json = $this->getProduct();
        $category_array=array_unique(explode(',', $catId_json));
        if($this->_coreRegistry->registry("csmembership_category")){
            $cat_array = array_unique(explode(',',$this->_coreRegistry->registry("csmembership_category")));
            return $cat_array;
            }
        return $category_array;
    }

    /**
     * Forms string out of getCategoryIds()
     *
     * @return string
     */
    public function getIdsString()
    {
        return implode(',', $this->getCategoryIds());
    }

    /**
     * Retrieve currently edited product
     *
     */
    public function getProduct()
    {   
        if($this->getRequest()->getParam('id')){
            return $this->_objectManager->create('Ced\CsMembership\Model\Membership')->load($this->getRequest()->getParam('id'))->getData('category_ids');
        }
        
    }
	
	
}
