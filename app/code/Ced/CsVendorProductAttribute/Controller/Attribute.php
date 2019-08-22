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
  * @category   Ced
  * @package    Ced_CsVendorProductAttribute
  * @author     CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright  Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license    http://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsVendorProductAttribute\Controller;

use Magento\Framework\View\Result\PageFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;

abstract class Attribute extends \Ced\CsMarketplace\Controller\Vendor
{
    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    protected $_attributeLabelCache;

    /**
     * @var string
     */
    protected $_entityTypeId;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    protected $_session;
    
    protected $_storeManager;

    /**
     * Attribute constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        Session $customerSession,
        PageFactory $resultPageFactory,
         UrlFactory $urlFactory,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
        $this->_session = $customerSession;
    }

    /**
     * Dispatch request
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
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
		if (!$isEnabled) {
			return $resultRedirect->setPath('csmarketplace/vendor/index/');
		}

        $this->_entityTypeId = $this->_objectManager->create('Magento\Eav\Model\Entity')
                                ->setType(\Magento\Catalog\Model\Product::ENTITY)->getTypeId();
        return parent::dispatch($request);
    }

    /**
     * @param \Magento\Framework\Phrase|null $title
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function createActionPage($title = null)
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        if ($this->getRequest()->getParam('popup')) {
            if ($this->getRequest()->getParam('product_tab') == 'variations') {
                $resultPage->addHandle(['popup', 'catalog_product_attribute_edit_product_tab_variations_popup']);
            } else {
                $resultPage->addHandle(['popup', 'catalog_product_attribute_edit_popup']);
            }
            $pageConfig = $resultPage->getConfig();
            $pageConfig->addBodyClass('attribute-popup');
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Product Attributes'));
        return $resultPage;
    }

    /**
     * Generate code from label
     *
     * @param string $label
     * @return string
     */
    protected function generateCode($label)
    {
        $code = substr(
            preg_replace(
                '/[^a-z_0-9]/',
                '_',
                $this->_objectManager->create('Magento\Catalog\Model\Product\Url')->formatUrlKey($label)
            ),
            0,
            30
        );
        $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']);
        if (!$validatorAttrCode->isValid($code)) {
            $code = 'attr_' . ($code ?: substr(md5(time()), 0, 8));
        }
        return $code;
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;
        //return $this->_authorization->isAllowed('Magento_Catalog::attributes_attributes');
    }
}
