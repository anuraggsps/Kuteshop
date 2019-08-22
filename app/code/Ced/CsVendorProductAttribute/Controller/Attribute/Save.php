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



class Save extends \Ced\CsVendorProductAttribute\Controller\Attribute
{ 
	/**
     * @var \Magento\Catalog\Model\Product\AttributeSet\BuildFactory
     */
    protected $buildFactory;

    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $filterManager;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $productHelper;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory
     */
    protected $attributeFactory;

    /**
     * @var \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    protected $_objectManager;

    protected $_attributeLabelCache;

    protected $_session;

    protected $attrMode = 'new';

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Catalog\Model\Product\AttributeSet\BuildFactory $buildFactory
     * @param \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     * @param \Magento\Framework\Filter\FilterManager $filterManager
     * @param \Magento\Catalog\Helper\Product $productHelper
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    /*public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Catalog\Model\Product\AttributeSet\BuildFactory $buildFactory,
        \Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory $attributeFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory $validatorFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory,
        \Magento\Framework\Filter\FilterManager $filterManager,
        \Magento\Catalog\Helper\Product $productHelper,
        Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
         UrlFactory $urlFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context, $coreRegistry, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
        $this->buildFactory = $buildFactory;
        $this->filterManager = $filterManager;
        $this->productHelper = $productHelper;
        $this->attributeFactory = $attributeFactory;
        $this->validatorFactory = $validatorFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->_session = $customerSession;
    }*/

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function execute()
    {	        
       	$this->_attributeLabelCache = $this->_objectManager->create('\Magento\Framework\App\Cache\Type\Translate');
        $this->buildFactory = $this->_objectManager->create('\Magento\Catalog\Model\Product\AttributeSet\BuildFactory');
        $this->filterManager = $this->_objectManager->create('\Magento\Framework\Filter\FilterManager');
        $this->productHelper = $this->_objectManager->create('\Magento\Catalog\Helper\Product');
        $this->attributeFactory = $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory');
        $this->validatorFactory = $this->_objectManager->create('\Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\ValidatorFactory');
        $this->groupCollectionFactory = $this->_objectManager->create('\Magento\Framework\App\Cache\Type\Translate');
       // $this->_objectManager = $this->_objectManager->create('\Magento\Framework\App\Cache\Type\Translate');
        $this->_session = $this->_objectManager->create('\Magento\Customer\Model\Session');
    	//custom code start
	    $vendor_id=$this->_session->getVendorId();
	    if(!$vendor_id){
	    	return false;
	    }
	    //$csmarket_vendor = $this->_objectManager->create('\Ced\CsMarketPlace\Model\Vendor')->load($vendor_id);
	    //$vendor_code = $csmarket_vendor->getShop_url();
    	//end

        $data = $this->getRequest()->getPostValue();
       
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $setId = $this->getRequest()->getParam('set');

            $attributeSet = null;
            if (!empty($data['new_attribute_set_name'])) {
                $name = $this->filterManager->stripTags($data['new_attribute_set_name']);
                $name = trim($name);

                try {
                    /** @var $attributeSet \Magento\Eav\Model\Entity\Attribute\Set */
                    $attributeSet = $this->buildFactory->create()
                        ->setEntityTypeId($this->_entityTypeId)
                        ->setSkeletonId($setId)
                        ->setName($name)
                        ->getAttributeSet();
                } catch (\AlreadyExistsException $alreadyExists) {
                    $this->messageManager->addError(__('An attribute set named \'%1\' already exists.', $name));
                    $this->messageManager->setAttributeData($data);
                    return $resultRedirect->setPath('csvendorproductattribute/*/edit', ['_current' => true]);
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving the attribute.'));
                }
            }

            $redirectBack = $this->getRequest()->getParam('back', false);
            /* @var $model \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            $model = $this->attributeFactory->create();

            $attributeId = $this->getRequest()->getParam('attribute_id');

            $attributeCode = $this->getRequest()->getParam('attribute_code');
            $frontendLabel = $this->getRequest()->getParam('frontend_label');
            $attributeCode = $attributeCode ?: $this->generateCode($frontendLabel[0]);
            if (strlen($this->getRequest()->getParam('attribute_code')) > 0) {
                $validatorAttrCode = new \Zend_Validate_Regex(['pattern' => '/^[a-z][a-z_0-9]{0,30}$/']);
                if (!$validatorAttrCode->isValid($attributeCode)) {
                    $this->messageManager->addError(
                        __(
                            'Attribute code "%1" is invalid. Please use only letters (a-z), ' .
                            'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                            $attributeCode
                        )
                    );
                    return $resultRedirect->setPath('csvendorproductattribute/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
                }
            }
            $data['attribute_code'] = $attributeCode;
            $sortorder =$this->getRequest()->getParam('sort_order');
          
            //validate frontend_input
            if (isset($data['frontend_input'])) {
                /** @var $inputType \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype\Validator */
                $inputType = $this->validatorFactory->create();
                if (!$inputType->isValid($data['frontend_input'])) {
                    foreach ($inputType->getMessages() as $message) {
                        $this->messageManager->addError($message);
                    }
                    return $resultRedirect->setPath('csvendorproductattribute/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
                }
            }

            if ($attributeId) {
            	$this->attrMode = 'edit';//new attribute
                $model->load($attributeId);
                if (!$model->getId()) {
                    $this->messageManager->addError(__('This attribute no longer exists.'));
                    return $resultRedirect->setPath('csvendorproductattribute/*/');
                }
                // entity type check
                if ($model->getEntityTypeId() != $this->_entityTypeId) {
                    $this->messageManager->addError(__('We can\'t update the attribute.'));
                    $this->_session->setAttributeData($data);
                    return $resultRedirect->setPath('csvendorproductattribute/*/');
                }

                $data['attribute_code'] = $model->getAttributeCode();
                $data['is_user_defined'] = $model->getIsUserDefined();
                $data['frontend_input'] = $model->getFrontendInput();
            } else {
                /**
                 * @todo add to helper and specify all relations for properties
                 */
                $data['source_model'] = $this->productHelper->getAttributeSourceModelByInputType(
                    $data['frontend_input']
                );
                $data['backend_model'] = $this->productHelper->getAttributeBackendModelByInputType(
                    $data['frontend_input']
                );
            }

            $data += ['is_filterable' => 0, 'is_filterable_in_search' => 0, 'apply_to' => []];

            if (is_null($model->getIsUserDefined()) || $model->getIsUserDefined() != 0) {
                $data['backend_type'] = $model->getBackendTypeByInput($data['frontend_input']);
            }

            $defaultValueField = $model->getDefaultValueByInput($data['frontend_input']);
            if ($defaultValueField) {
                $data['default_value'] = $this->getRequest()->getParam($defaultValueField);
            }

            if (!$model->getIsUserDefined() && $model->getId()) {
                // Unset attribute field for system attributes
                unset($data['apply_to']);
            }

        	//custom code
            $attribute_set_data = $this->getRequest()->getParam('attribute_set_ids');
            $attribute_set_ids = [];
            $setIds = '';
            if(is_array($attribute_set_data) && count($attribute_set_data) > 0){
            	/*foreach ($attribute_set_data as $set_ids) {
            		$exp = explode('::', $set_ids);
            		$attribute_set_ids[$exp[1]] = $exp[0];
            	}*/
            	$attribute_set_ids = $attribute_set_data;
            	$setIds = implode(",",$attribute_set_ids);
            }
            $oldAttrSetIds = $model->getAttributeSetIds();
            $data['attribute_set_ids'] = $setIds;
            //end
            $model->addData($data);

            if (!$attributeId) {
                $model->setEntityTypeId($this->_entityTypeId);
                $model->setIsUserDefined(1);
            }

            $groupCode = $this->getRequest()->getParam('group');
            if ($setId && $groupCode) {
                // For creating product attribute on product page we need specify attribute set and group
                $attributeSetId = $attributeSet ? $attributeSet->getId() : $setId;
                $groupCollection = $attributeSet
                    ? $attributeSet->getGroups()
                    : $this->groupCollectionFactory->create()->setAttributeSetFilter($attributeSetId)->load();
                foreach ($groupCollection as $group) {
                    if ($group->getAttributeGroupCode() == $groupCode) {
                        $attributeGroupId = $group->getAttributeGroupId();
                        break;
                    }
                }
                $model->setAttributeSetId($attributeSetId);
                $model->setAttributeGroupId($attributeGroupId);
            }

            try {
                $model->save();
                //Save data in vendor's table
                $attributeCode = $model->getAttributeCode();
				$attribute_id = $model->getId();
				$vendordata['attribute_id'] = $attribute_id;
				$vendordata['attribute_code'] = $model->getAttributeCode();
				$vendordata['sort_order']= $sortorder;
				$attr_model = $this->_objectManager->create('Ced\CsVendorProductAttribute\Model\Attribute');

                if ($this->attrMode == 'new') {
                	//save data only for new attribute
				    $vendordata['vendor_id']=$vendor_id;
				    $attr_exist = $attr_model->getCollection()->addFieldToFilter('vendor_id',$vendor_id)->addFieldToFilter('attribute_id',$attribute_id);
					if(!count($attr_exist)) {
						$attr_model->addData($vendordata);        
						$attr_model->save();
					}
					
					//Set Attribute in Vendor Attribute Set
					$attr_model->addVendorAttributeToAttributeSet($attribute_set_ids,$attribute_id,$attributeCode,$sortorder); 
                } elseif($this->attrMode == 'edit') {
                	$attrdata = $attr_model->getCollection()->addFieldToFilter('vendor_id',$vendor_id)
                                ->addFieldToFilter('attribute_id',$attribute_id)->getData();
                	if(count($attrdata)>0) {
                		$this->_objectManager->create('Ced\CsVendorProductAttribute\Model\Attribute')->load($attrdata[0]['id'])
                            ->setData('sort_order',$sortorder)->save();
                	}
                	
                	$_oldAttrSetIds = [];
                	if($oldAttrSetIds != null && $oldAttrSetIds != ''){
                		$_oldAttrSetIds = explode(',', $oldAttrSetIds);
                	}
                	$remove_ids = array_diff($_oldAttrSetIds,$attribute_set_ids);
                	if(count($remove_ids))
                		$attr_model->removeVendorAttributeFromGroup($remove_ids,$attribute_id,$attributeCode);

                	$add_ids = array_diff($attribute_set_ids,$_oldAttrSetIds);
                	if(count($add_ids))
                		$attr_model->addVendorAttributeToGroup($add_ids,$attribute_id,$attributeCode,$sortorder);
                	else 
                		$attr_model->addVendorAttributeToAttributeSet($attribute_set_ids,$attribute_id,$attributeCode,$sortorder);
                }
                //End

                $this->messageManager->addSuccess(__('You saved the product attribute.'));

                $this->_attributeLabelCache->clean();
                $this->_session->setAttributeData(false);
                if ($this->getRequest()->getParam('popup')) {
                    $requestParams = [
                        'attributeId' => $this->getRequest()->getParam('product'),
                        'attribute' => $model->getId(),
                        '_current' => true,
                        'product_tab' => $this->getRequest()->getParam('product_tab'),
                    ];
                    if (!is_null($attributeSet)) {
                        $requestParams['new_attribute_set_id'] = $attributeSet->getId();
                    }
                    $resultRedirect->setPath('catalog/product/addAttribute', $requestParams);
                } elseif ($redirectBack) {
                    $resultRedirect->setPath('csvendorproductattribute/*/edit', ['attribute_id' => $model->getId(), '_current' => true]);
                } else {
                    $resultRedirect->setPath('csvendorproductattribute/*/');
                }
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->_session->setAttributeData($data);
                return $resultRedirect->setPath('csvendorproductattribute/*/edit', ['attribute_id' => $attributeId, '_current' => true]);
            }
        }
        return $resultRedirect->setPath('csvendorproductattribute/*/');
    }
}
