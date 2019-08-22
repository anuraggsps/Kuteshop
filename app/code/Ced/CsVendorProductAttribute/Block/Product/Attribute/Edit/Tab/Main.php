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

namespace Ced\CsVendorProductAttribute\Block\Product\Attribute\Edit\Tab;

use Magento\Eav\Block\Adminhtml\Attribute\Edit\Main\AbstractMain;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends AbstractMain
{
    protected $_session;
    protected $_objectManager;
    protected $_scopeConfig;
    protected $_storeManager;

    public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\Data\FormFactory $formFactory,
            \Magento\Eav\Helper\Data $eavData,
            \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
            \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
            \Magento\Eav\Block\Adminhtml\Attribute\PropertyLocker $propertyLocker,
            \Magento\Customer\Model\Session $customerSession,
            \Magento\Framework\ObjectManagerInterface $ObjectManager,
            array $data = []
            )
    {
        parent::__construct($context, $registry, $formFactory, $eavData, $yesnoFactory, $inputTypeFactory, $propertyLocker, $data);
        $this->_session = $customerSession;
        $this->_objectManager = $ObjectManager;
    }

    public function _construct()
    {
    	
        parent::_construct();
        $this->setData('area','adminhtml');
    }

    /**
     * Adding product form elements for editing attribute
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function _prepareForm()
    {
        parent::_prepareForm();
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeObject */
        $attributeObject = $this->getAttributeObject();
        /* @var $form \Magento\Framework\Data\Form */
        $form = $this->getForm();
        /* @var $fieldset \Magento\Framework\Data\Form\Element\Fieldset */
        $fieldset = $form->getElement('base_fieldset');
        $fiedsToRemove = ['attribute_code', 'is_unique', 'frontend_class'];

        foreach ($fieldset->getElements() as $element) {
            /** @var \Magento\Framework\Data\Form\AbstractForm $element  */
            if (substr($element->getId(), 0, strlen('default_value')) == 'default_value') {
                $fiedsToRemove[] = $element->getId();
            }
        }
        foreach ($fiedsToRemove as $id) {
            $fieldset->removeField($id);
        }

        $frontendInputElm = $form->getElement('frontend_input');
        

        $response = new \Magento\Framework\DataObject();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_product_attribute_types', ['response' => $response]);
        $_hiddenFields = [];
        foreach ($response->getTypes() as $type) {
            $additionalTypes[] = $type;
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
        }
        $this->_coreRegistry->register('attribute_type_hidden_fields', $_hiddenFields);

        $this->_eventManager->dispatch('product_attribute_form_build_main_tab', ['form' => $form]);

       

        //add custom field
        /*$customOptions = [
            ['value' => '1', 'label' => __('Yes')],
            ['value' => '0', 'label' => __('No')],
        ];*/
        $customOptions = $this->getAttributeSetsOptions();
        $fieldset->addField('attribute_set_ids', 'multiselect', array(
                'name'      => 'attribute_set_ids',
                'label'     => __('Include in Attribute Set'),
                'title'     => __('Include in Attribute Set'),
                'note'      => __('Include this Attribute in Attribute Sets'),
                'values'    => $customOptions,
                'value'     => $this->getAttributeSetValue()
        ));
        
        $fieldset->addField('sort_order', 'text', array(
        		'name'      => 'sort_order',
        		'label'     => __('Sort Order'),
        		'title'     => __('Sort Order'),
        		'value'   =>  $this->_coreRegistry->registry('sort_order')
        
        ));
        //end

        return $this;
    }

    /**
     * Retrieve additional element types for product attributes
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return ['apply' => 'Magento\Catalog\Block\Adminhtml\Product\Helper\Form\Apply'];
    }

    public function getAttributeSetsOptions()
    {
        return $this->_objectManager->get('Ced\CsVendorProductAttribute\Model\Attributeset')->getAllowedAttributeSets();
    }

    public function getAttributeSetValue()
    {
        $request = $this->_objectManager->get('\Magento\Framework\App\Request\Http');
        $attr_id = $request->getParam('attribute_id');
        $model = $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Eav\Attribute')->load($attr_id);
        return $model->getAttributeSetIds();
    }
}