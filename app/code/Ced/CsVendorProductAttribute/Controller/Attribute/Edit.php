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

namespace Ced\CsVendorProductAttribute\Controller\Attribute;

class Edit extends \Ced\CsVendorProductAttribute\Controller\Attribute
{
	/**
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('attribute_id');
        /** @var $model \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
        $model = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')
                    ->setEntityTypeId($this->_entityTypeId);
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This attribute no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('csvendorproductattribute/*/');
            }

            // entity type check
            if ($model->getEntityTypeId() != $this->_entityTypeId) {
                $this->messageManager->addError(__('This attribute cannot be edited.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('csvendorproductattribute/*/');
            }
        }

        // set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getAttributeData(true);
        if (!empty($data)) {
            $model->addData($data);
        }
        $attributeData = $this->getRequest()->getParam('attribute');
        if (!empty($attributeData) && $id === null) {
            $model->addData($attributeData);
        }
         
        $attributedata = $this->_objectManager->get('Ced\CsVendorProductAttribute\Model\Attribute')->getCollection()
                          ->addFieldToFilter('vendor_id', $this->_getSession()->getVendorId())
                          ->addFieldToFilter('attribute_id', $id)->getData();
        if(!empty($attributedata))
        	$this->_coreRegistry->register('sort_order', $attributedata[0]['sort_order']);
        	
        $this->_coreRegistry->register('entity_attribute', $model);

        $item = $id ? __('Edit Product Attribute') : __('New Product Attribute');

        $resultPage = $this->createActionPage($item);
        $resultPage->getConfig()->getTitle()->prepend($id ? $model->getName() : __('New Product Attribute'));
        $resultPage->getLayout()
            ->getBlock('attribute_edit_js')
            ->setIsPopup((bool)$this->getRequest()->getParam('popup'));
        return $resultPage;
    }
}
