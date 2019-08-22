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

namespace Ced\CsVendorProductAttribute\Controller\Attribute; 
 
class Delete extends \Ced\CsVendorProductAttribute\Controller\Attribute 
{
	protected $_objectManager;
	
	protected $_session;
	
	public function __construct(
			\Magento\Backend\App\Action\Context $context,
			\Magento\Framework\Registry $coreRegistry,
			\Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
			\Magento\Customer\Model\Session $customerSession,
			\Magento\Framework\View\Result\PageFactory $resultPageFactory,
			\Magento\Framework\UrlFactory $urlFactory,
			\Magento\Framework\Module\Manager $moduleManager
	) {
		parent::__construct($context, $coreRegistry, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
		$this->_session = $customerSession;
	}
	/**
	 * Delete the Attribute
	 */
	public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $model = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');

            // entity type check
            $model->load($id);
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('We can\'t delete the attribute.'));
                return $this->_redirect('csvendorproductattribute/attribute/index');
            }

            try {
                $model->delete();
                //delete entry from vendor table
                $vendor_attr_model = $this->_objectManager->create('\Ced\CsVendorProductAttribute\Model\Attribute')->getCollection()->addFieldToFilter('attribute_id',$id)->getFirstItem();
                $vendor_attr_model->delete();
                //End
                $this->messageManager->addSuccess(__('You deleted the product attribute.'));
                
                return $this->_redirect('csvendorproductattribute/attribute/index');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath(
                    'csvendorproductattribute/*/edit',
                    ['attribute_id' => $this->getRequest()->getParam('attribute_id')]
                );
            }
        }
        $this->messageManager->addError(__('We can\'t find an attribute to delete.'));
        
        return $this->_redirect('csvendorproductattribute/attribute/index');
    }
}
