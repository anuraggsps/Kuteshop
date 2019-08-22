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

namespace Ced\CsProduct\Controller\Vproducts;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;

class Edit extends \Ced\CsMarketplace\Controller\Vproducts\Edit
{
   /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = ['edit'];
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    protected $_productBuilder;
    protected $urlBuilder;
    protected $_customerSession;
    protected $_scopeConfig;
    protected $_storeManager;
    

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Ced\CsProduct\Controller\Product\Builder $productBuilder,
        Session $customerSession,
        \Ced\CsProduct\Controller\Vproducts\Initialization\StockDataFilter $stockFilter,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
         UrlFactory $urlFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
    	\Magento\Framework\Registry $registry
    ) {
        $this->stockFilter = $stockFilter;
        parent::__construct($context,$customerSession,$resultPageFactory,$urlFactory,$moduleManager);
        
        $this->productBuilder = $productBuilder;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_customerSession = $customerSession;
        $this->_scopeConfig = $scopeConfig;
        $this->_storeManager = $storeManager;
        $this->urlBuilder = $urlFactory;
        $this->registry = $registry;
    }

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    
    /**
     * Product edit form
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if(!$this->_objectManager->create('Ced\CsProduct\Helper\Data')->isVendorLoggedIn())
            $this->_redirect('csmarketplace/account/login');
        if(!$this->_scopeConfig->getValue('ced_csmarketplace/general/ced_vproduct_activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->getRequest()->getModuleName()=='csmarketplace'){
        	$resultPage = $this->resultPageFactory->create();
         $resultPage->addHandle('csmarketplace_vproducts_' . $this->getRequest()->getParam('type'));
        	return  $resultPage;

        }
        if($this->_scopeConfig->getValue('ced_csmarketplace/general/ced_vproduct_activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) && $this->getRequest()->getModuleName()=='csmarketplace') {
            return $this->_redirect('csproduct/vproducts/edit');

        }
        if(!$this->_scopeConfig->getValue('ced_csmarketplace/general/ced_vproduct_activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)) {
                if($this->getRequest()->getModuleName()=='csproduct'){
                    return $this->_redirect('csmarketplace/vproducts/edit');

                }
                
        }
        
        $currentstoreId = $this->_storeManager->getStore()->getStoreId();
        $currentStoreData = $this->_storeManager->getStore($currentstoreId);
        $this->registry->register('current_store_data',$currentStoreData);
        
        
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        $productId = (int) $this->getRequest()->getParam('id');
        $store = $this->_storeManager->getStore($storeId);

        $product = $this->productBuilder->build($this->getRequest());
      
        $vendorId = $this->_customerSession->getVendorId();


        $vendorProduct=false;
        if($productId && $vendorId){
            $vendorProduct = $this->_objectManager->get('Ced\CsMarketplace\Model\Vproducts')->isAssociatedProduct($vendorId,$productId);
        }

        if(!$vendorProduct){
            return $this->_redirect('*/vproducts/index');

        }

    if (($productId && !$product->getEntityId())) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('This product doesn\'t exist.'));
            return $resultRedirect->setPath('*/vproducts/index');
        } elseif ($productId === 0) {
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            $this->messageManager->addErrorMessage(__('Invalid product id. Should be numeric value greater than 0'));
            return $resultRedirect->setPath('*/vproducts/index');
        }
    
    	$this->_eventManager->dispatch('catalog_product_edit_action', ['product' => $product]);
        $this->_eventManager->dispatch('csproduct_vproducts_edit_action', ['product' => $product]);
    
    	/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
    	$resultPage = $this->resultPageFactory->create();
    	$resultPage->addHandle('csproduct_vproducts_' . $product->getTypeId());
    	$resultPage->getConfig()->getTitle()->prepend(__('Products'));
    	$resultPage->getConfig()->getTitle()->prepend($product->getName());
    
    	if (!$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->isSingleStoreMode()
    	&&
    	($switchBlock = $resultPage->getLayout()->getBlock('store_switcher'))
    	) {
    		$switchBlock->setDefaultStoreName(__('Default Values'))
    		->setWebsiteIds($product->getWebsiteIds())
    		->setSwitchUrl(
    				$this->urlBuilder->create()->getUrl(
    						'csproduct/*/*',
    						['_current' => true, 'active_tab' => null, 'tab' => null, 'store' => null]
    				)
    		);
    	}
    
    	$block = $resultPage->getLayout()->getBlock('catalog.wysiwyg.js');
    	if ($block) {
    		$block->setStoreId($product->getStoreId());
    	}
    	
    	return $resultPage;
    }
     
}
