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
class Form extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
   
    protected $_systemStore;

    public $_objectManager;

    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Magento\Framework\Registry $registerInterface,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->_objectManager = $objectInterface;
        $this->_coreRegistry = $registerInterface;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    
    protected function _prepareForm()
    {
		
        $model = $this->_coreRegistry->registry('csmembership_member_data');
		$isElementDisabled = false;
        
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('page_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('Membership Plan Information')));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array('name' => 'id'));
        }

		$script = $fieldset->addField(
            'name',
            'text',
            array(
                'name' => 'name',
                'label' => __('Name'),
                'title' => __('Name'),
                'required' => true
            )
        );
		$fieldset->addField(
            'price',
            'text',
            array(
                'name' => 'price',
                'label' => __('Price'),
                'title' => __('Price'),
                'required' => true,
                'onchange'  =>'getTotal()',
                'class' => 'validate-number'    
            )
        );
        $fieldset->addField(
            'product_limit',
            'text',
            array(
                'name' => 'product_limit',
                'label' => __('Product Limit'),
                'title' => __('Product Limit'),
                'onchange'  =>'getTotal()',
                'required' => true,
                'class'     => 'validate-number validate-zero-or-greater'
            )
        );
		$fieldset->addField(
            'sort_order',
            'text',
            array(
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => true,
                'class'     => 'validate-number'
                
            )
        );
        $fieldset->addField(
            'qty',
            'text',
            array(
                'name' => 'qty',
                'label' => __('No. of Subscription Allowed'),
                'title' => __('No. of Subscription Allowed'),
                'onchange'  =>'getTotal()',
                'required' => true,
                'class'     => 'validate-number'
                
            )
        );
        $fieldset->addField(
            'special_price',
            'text',
            array(
                'name' => 'special_price',
                'label' => __('Special Price'),
                'title' => __('Special Price'),
                'onchange'  =>'getTotal()',
                'class' => 'validate-number'    
            )
        );
        if($this->getRequest()->getParam('id')){
            $fieldset->addField(
                'duration',
                'select',
                array(
                    'name' => 'duration',
                    'label' => __('Duration'),
                    'title' => __('Duration'),
                    'required' => true,
                    'onchange'  =>'getTotal()',
                    'class'     =>'required-entry validate-select',
                    'values'    => $this->_objectManager->create('Ced\CsMembership\Model\System\Config\Source\Duration')->durationArray($this->_coreRegistry->registry("csmembership_data")->getGroupType()),
                    'after_element_html' => '<br><small>If option is blank then set duration price in Config<small>'   
                )
            );
        }
        else
        {
            $fieldset->addField(
                'duration',
                'select',
                array(
                    'name' => 'duration',
                    'label' => __('Duration'),
                    'title' => __('Duration'),
                    'required' => true,
                    'values'    => $this->_objectManager->create('Ced\CsMembership\Model\System\Config\Source\Duration')->durationArray($this->getRequest()->getParam('group_type')),
                    'onchange' =>'getTotal()',
                    'class' =>'required-entry validate-select',
                    'after_element_html' => '<br><small>If option is blank then set duration price in Config<small>'   
                )
            );
        }
        $fieldset->addField(
            'status',
            'select',
            array(
                'name' => 'status',
                'label' => __('Status'),
                'title' => __('Status'),
                'required' => true,
                'values'    => \Ced\CsMembership\Block\Adminhtml\Membership\Grid::getValueArray3(),
                'class'     =>'required-entry validate-select',
                'onchange'  =>'getTotal()'
            )
        );
        $fieldset->addField(
            'website_id',
            'select',
            array(
                'name' => 'website_id',
                'label' => __('Website'),
                'title' => __('Website'),
                'required' => true,
                'values'    => \Ced\CsMembership\Block\Adminhtml\Membership\Edit\Tab\Form::getWebSites(),
                'class'     =>'required-entry validate-select',
                'onchange'  =>'getTotal()'
            )
        );
        $fieldset->addField(
            'image',
            'image',
            array(
                'name' => 'image',
                'label' => __('Image'),
                'title' => __('Image'),
                'class'     => 'required required-entry required-file'
            )
        );
        $fieldset->addField(
            'product_id',
            'hidden',
            array(
                'name' => 'product_id'
            )
        );
        $cat = $fieldset->addField(
            'category_ids',
            'hidden',
            array(
                'name' => 'category_ids'
            )
        );

        $script->setAfterElementHtml("<script type=\"text/javascript\">
            function getTotal(){
                var product_limit=parseInt(document.getElementById('page_product_limit').value);
                var category_ids=document.getElementById('product_categories').value;
                var price=document.getElementById('page_price').value;
                var special_price=document.getElementById('page_special_price').value;
                var duration=parseInt(document.getElementById('page_duration').value);   
                var reloadurl = '". $this->_objectManager->get('\Magento\Backend\Helper\Data')->getUrl('csmembership/membership/getTotal', array('_current' => true))."';
                if(product_limit){
                    new Ajax.Request(reloadurl, {
                        method: 'get',
                        parameters: {product_limit:product_limit,category_ids:category_ids,price:price,special_price:special_price,duration:duration },
                        onComplete: function(stateform) {
                            var response=stateform.responseText;
                            var result=isNumber(response);
                            if(result)
                                document.getElementById('page_price').value=response;
                            //else
                                //alert('Session Expires');
                            
                        }
                    });
                }
            }
            function isNumber(n) {
                              return !isNaN(parseFloat(n)) && isFinite(n);
                            }
            </script>");

        $cat->setAfterElementHtml($this->getLayout()->createBlock("Ced\CsMembership\Block\Adminhtml\Membership\Edit\Tab\Categories")->toHtml());
        
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '2' : '1');
        }

        if($this->_coreRegistry->registry("csmembership_group_data"))
        {
            $form->setValues($this->_coreRegistry->registry("csmembership_group_data"));
        }
        else
        {
            $form->setValues($this->_coreRegistry->registry("csmembership_data")->getData());
        }
        $this->setForm($form);

        return parent::_prepareForm();   
    }

    
    public function getTabLabel()
    {
        return __('Membership Information');
    }

    
    public function getTabTitle()
    {
        return __('Membership Information');
    }

    public function canShowTab()
    {
        return true;
    }

   
    public function isHidden()
    {
        return false;
    }


    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     *getWebsites
     *@return $websites
     */
    public function getWebSites(){
        $websites = array();
        $website_collection = $this->_objectManager->create('Magento\Store\Model\ResourceModel\Website\Collection');
        foreach($website_collection as $website) {
          array_push($websites,array('value'=> $website->getId(),'label'=> __($website->getName())));
        }
        return $websites;
    }
}
