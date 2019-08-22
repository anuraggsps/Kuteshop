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

namespace Ced\CsVendorProductAttribute\Controller;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;

abstract class Set extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    
    protected $_storeManager;

    /**
     * Set constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Session $customerSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\Registry $coreRegistry,
        Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        UrlFactory $urlFactory,
        \Magento\Framework\Module\Manager $moduleManager
        )
    {
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
    }
    
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
    	$resultRedirect = $this->resultRedirectFactory->create();
    	$this->_storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
    	$isEnabled = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
    	->getValue(
    			'ced_csmarketplace/general/vpattributes_activation',
    			\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
    			$this->_storeManager->getStore()->getId()
    	);
    	if(!$isEnabled)
    	{
    		return $resultRedirect->setPath('csmarketplace/vendor/index/');
    	}
    	return parent::dispatch($request);
    }

    /**
     * Define in register catalog_product entity type code as entityType
     *
     * @return void
     */
    protected function _setTypeId()
    {
        $this->_coreRegistry->register(
            'entityType',
            $this->_objectManager->create('Magento\Catalog\Model\Product')->getResource()->getTypeId()
        );
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
        //return $this->_authorization->isAllowed('Magento_Catalog::sets');
    }
}
