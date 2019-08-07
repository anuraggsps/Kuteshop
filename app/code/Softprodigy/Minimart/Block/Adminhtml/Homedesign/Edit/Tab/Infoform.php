<?php

namespace Softprodigy\Minimart\Block\Adminhtml\Homedesign\Edit\Tab;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Magento\Backend\Block\Widget\Tab\TabInterface;
use Magento\Store\Model\System\Store;

/**
 * Description of Form
 *
 * @author mannu
 */
class Infoform extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface {

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    protected $_storeManager;
    protected $helper;
    protected $sysConfCats;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Backend\Model\Session
     */
    protected $_session;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Data\FormFactory $formFactory, \Magento\Framework\Json\EncoderInterface $jsonEncoder, \Magento\Store\Model\StoreManagerInterface $storeManager, Store $systemStore, \Magento\Backend\Model\Session $session, \Softprodigy\Minimart\Helper\Data $helper,
    \Softprodigy\Minimart\Model\System\Config\Source\Dropdown\Cats $sysConfCats
    ,array $data = []
    ) {

        $this->_jsonEncoder = $jsonEncoder;
        $this->_session = $session;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_storeManager = $storeManager;
        $this->_systemStore = $systemStore;
        $this->helper = $helper;
        $this->sysConfCats = $sysConfCats;
        $this->setUseContainer(true);
        $this->setTemplate("homedesign/edit/tab/form.phtml");
    }

    public function getCurrentModel() {
        if (!$this->getData('section_data') instanceof \Softprodigy\Minimart\Model\Homedesign) {
            $this->setData('section_data', $this->_coreRegistry->registry('section_data'));
        }
        return $this->getData('section_data');
    }

    /**
     * Form preparation
     *
     * @return void
     */
    protected function _prepareForm() {
        /** @var \Magento\Framework\Data\Form $form */
        $data = $this->getDailydeal();
        $form = $this->_formFactory->create();

        $disabled = false; //($data->getStatus() && $data->getStatus() == 4) ? true : false;
        //var_dump($disabled); die;
        //$form->addField('new_deal_messages', 'note', []);

        $fieldset = $form->addFieldset('new_deal_form_fieldset', ['legend' => __('Section information and Elements')]);

        $fieldset->addField('store', 'hidden', [
            'name' => 'store_id',
        ]);

        $fieldset->addField('layout', 'hidden', array(
            'name' => 'layout_col',
        ));

        $reg = $this->_coreRegistry->registry('section_data');
        if ($reg and $reg->getId() and $reg->getLayoutCol()) {
            $fieldset->addField('store_id', 'select', [
                'name' => '',
                'label' => __('Applied on Store'),
                'title' => __('Store View'),
                'required' => false,
                'values' => $this->_systemStore->getStoreValuesForForm(false, false),
                'disabled' => true
            ]);

            $fieldset->addField('layout_col', 'select', [
                'name' => '',
                'label' => __('Section Layout'),
                'class' => 'required-entry',
                'required' => false,
                'options' => ['13' => '13 Blocks', '9' => '9 Blocks'],
                'disabled' => true
            ]);
        }


        $fieldset->addField('title', 'text', [
            'name' => 'title',
            'label' => __('Section Title'),
            'required' => false,
            'note' => 'Leave empty if you want to use category title.'
        ]);

        $fieldset->addField('main_cat', 'select', [
            'name' => 'section_category',
            'label' => __('Section Category'),
            'class' => 'required-entry',
            'required' => true,
            'options' => $this->getCategoryOptionVals()
        ]);

        $storeid = $this->getRequest()->getParam('store');
        $fieldset->addField('position', 'select', [
            'name' => 'position',
            'label' => __('Section Position'),
            'class' => 'required-entry',
            'required' => true,
            'options' => $this->helper->getStoreSectionPoistions($storeid)
        ]);

        $fieldset->addField('status', 'select', [
            'name' => 'status',
            'label' => __('Status'),
            'class' => 'required-entry',
            'required' => true,
            'options' => array('active' => 'Enable', 'inactive' => 'Disable')
        ]);

        if ($this->_coreRegistry->registry('section_data') and $this->_coreRegistry->registry('section_data')->getData()) {
            $form->setValues($this->_coreRegistry->registry('section_data')->getData());
        } else if ($this->_backendSession->getFormData()){
            $form->setValues($this->_backendSession->getFormData());
        }

        $this->setForm($form);
    }

    public function getCategoryOptions() {
        $storeid = $this->getRequest()->getParam('store');
        return $this->sysConfCats->toOptions($storeid, true);
    }

    public function getCategoryOptionVals() {
        $storeid = $this->getRequest()->getParam('store');
        return $this->sysConfCats->toOptions($storeid);
    }
    public function getDesignData(){
        return $this->_coreRegistry->registry('section_data');
    }
    protected function getBackendSession() {
        return $this->_session;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel() {
        return __('Select Store Info');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle() {
        return __('Select Store Info');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab() {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden() {
        return false;
    }

}
