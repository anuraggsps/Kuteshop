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

namespace Ced\CsVendorProductAttribute\Controller\Set;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;

class Delete extends \Ced\CsVendorProductAttribute\Controller\Set
{
    /**
     * @var \Magento\Eav\Api\AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;
    
    protected $_urlFactory;

    /**
     * Delete constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository
     * @param Session $customerSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository,
        Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        UrlFactory $urlFactory,
        \Magento\Framework\Module\Manager $moduleManager
    	//\Magento\Framework\UrlInterface $urlInterface
    ) {
        parent::__construct($context, $coreRegistry, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
        $this->attributeSetRepository = $attributeSetRepository;
        $this->_urlFactory = $this->_url;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $setId = $this->getRequest()->getParam('id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $this->attributeSetRepository->deleteById($setId);
            
            //delete from vendor's table
            $attr_set_model = $this->_objectManager->create('\Ced\CsVendorProductAttribute\Model\Attributeset')
            									   ->getCollection()
            									   ->addFieldToFilter('attribute_set_id',$setId)
            									   ->getFirstItem();
            $attr_set_model->delete();
            
            $this->messageManager->addSuccess(__('The attribute set has been removed.'));
            $resultRedirect->setPath('csvendorproductattribute/*/');
        } catch (\Exception $e) {
            $this->messageManager->addError(__('We can\'t delete this set right now.'));
            $resultRedirect->setUrl($this->_redirect->getRedirectUrl($this->_urlFactory->getUrl('*')));
        }
        return $resultRedirect;
    }
}
