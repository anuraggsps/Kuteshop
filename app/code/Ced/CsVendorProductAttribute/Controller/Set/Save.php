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

class Save extends \Ced\CsVendorProductAttribute\Controller\Set
{
    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    protected $_session;
    
    protected $_urlFactory;

    /**
     * Save constructor.
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param Session $customerSession
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        UrlFactory $urlFactory,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        parent::__construct($context, $coreRegistry, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
        $this->layoutFactory = $layoutFactory;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_session = $customerSession;
        $this->_urlFactory = $this->_url;
    }

    /**
     * Retrieve catalog product entity type id
     *
     * @return int
     */
    protected function _getEntityTypeId()
    {
        if ($this->_coreRegistry->registry('entityType') === null) {
            $this->_setTypeId();
        }
        return $this->_coreRegistry->registry('entityType');
    }

    /**
     * Save attribute set action
     *
     * [POST] Create attribute set from another set and redirect to edit page
     * [AJAX] Save attribute set data
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $vendor_id=$this->_session->getVendorId();
        if(!$vendor_id){
            return false;
        }

        $entityTypeId = $this->_getEntityTypeId();
        $hasError = false;
        $attributeSetId = $this->getRequest()->getParam('id', false);
        $isNewSet = $this->getRequest()->getParam('gotoEdit', false) == '1';

        /* @var $model \Magento\Eav\Model\Entity\Attribute\Set */
        $model = $this->_objectManager->create('Magento\Eav\Model\Entity\Attribute\Set')
            ->setEntityTypeId($entityTypeId);

        /** @var $filterManager \Magento\Framework\Filter\FilterManager */
        $filterManager = $this->_objectManager->get('Magento\Framework\Filter\FilterManager');

        try {
            if ($isNewSet) {
                //filter html tags
                $name = $filterManager->stripTags($this->getRequest()->getParam('attribute_set_name'));
                $model->setAttributeSetName(trim($name));
            } else {
                
                if ($attributeSetId) {
                    $model->load($attributeSetId);
                }
                if (!$model->getId()) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('This attribute set no longer exists.')
                    );
                }
                /* $data = $this->_objectManager->get('Magento\Framework\Json\Helper\Data')
                    ->jsonDecode($this->getRequest()->getPost('data')); */

                //filter html tags
                /* $data['attribute_set_name'] = $filterManager->stripTags($data['attribute_set_name']);
                $model->organizeData($data); */
                $name = $filterManager->stripTags($this->getRequest()->getParam('attribute_set_name'));

                $model->setAttributeSetName(trim($name));
            }

            $model->validate();
            if ($isNewSet) {
                $model->save();
                $model->initFromSkeleton($this->getRequest()->getParam('skeleton_set'));
            }
            $model->save();

            //Save Data in Vendor Table            
            $attr_set_model = $this->_objectManager->create('\Ced\CsVendorProductAttribute\Model\Attributeset');
            $attribute_set_id = $model->getId();
            $vendordata['attribute_set_id'] = $attribute_set_id;
            $attribute_set_model = $model->load($attribute_set_id);
       
            $vendordata['attribute_set_code'] = $attribute_set_model->getAttributeSetName();
	    //$vendordata['attribute_set_code'] = trim($name);
            
            //save data only for new attribute
            if($isNewSet) {
                $vendordata['vendor_id']=$vendor_id;
                $attr_set_model->setData($vendordata);
                $attr_set_model->save();
            }else{
                $attr_set_model->load($vendordata['attribute_set_id'], 'attribute_set_id');
                $attr_set_model->setAttributeSetCode($vendordata['attribute_set_code'])->save();
            }
            //End

            $this->messageManager->addSuccess(__('You saved the attribute set.'));
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            $hasError = true;
        } catch (\Exception $e) {
            $this->messageManager->addException($e, __('Something went wrong while saving the attribute set.'));
            $hasError = true;
        }
		
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($isNewSet) {
            if ($this->getRequest()->getPost('return_session_messages_only')) {
                /** @var $block \Magento\Framework\View\Element\Messages */
                $block = $this->layoutFactory->create()->getMessagesBlock();
                $block->setMessages($this->messageManager->getMessages(true));
                $body = [
                    'messages' => $block->getGroupedHtml(),
                    'error' => $hasError,
                    'id' => $model->getId(),
                ];
                return $this->resultJsonFactory->create()->setData($body);
            } else {
                if ($hasError) {
                    $resultRedirect->setPath('csvendorproductattribute/*/add');
                } else {
                    //$resultRedirect->setPath('csvendorproductattribute/*/edit', ['id' => $model->getId()]);
                    $resultRedirect->setPath('csvendorproductattribute/*/');
                }
                return $resultRedirect;
            }
        } else {
            $response = [];
            if ($hasError) {
                $layout = $this->layoutFactory->create();
                $layout->initMessages();
                $response['error'] = 1;
                $response['message'] = $layout->getMessagesBlock()->getGroupedHtml();
            } else {
                $response['error'] = 0;
                $response['url'] = $this->_urlFactory->getUrl('csvendorproductattribute/*/');
            }
            //return $this->resultJsonFactory->create()->setData($response);
            $resultRedirect->setPath('csvendorproductattribute/*/');
            return $resultRedirect;
        }
    }
}
