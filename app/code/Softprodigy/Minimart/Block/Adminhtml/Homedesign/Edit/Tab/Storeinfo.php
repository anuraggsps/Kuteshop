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
class Storeinfo extends \Magento\Backend\Block\Widget\Form\Generic implements TabInterface {
    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;
    protected $_storeManager;
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
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Store $systemStore,    
        \Magento\Backend\Model\Session $session,
        array $data = []
    ) {
        
        $this->_jsonEncoder = $jsonEncoder;
        $this->_session = $session;
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_storeManager = $storeManager;
        $this->_systemStore   = $systemStore;
        $this->setUseContainer(true);
    }
    
    public function getCurrentModel() {
        if (!$this->getData('current_data') instanceof \Softprodigy\Minimart\Model\Homedesign) {
            $this->setData('current_data', $this->_coreRegistry->registry('current_data'));
        }
        return $this->getData('current_data');
    }
    

    /**
     * Form preparation
     *
     * @return void
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $data = $this->getDailydeal();
        $form = $this->_formFactory->create();
         
        $disabled = false;//($data->getStatus() && $data->getStatus() == 4) ? true : false;
        //var_dump($disabled); die;
        //$form->addField('new_deal_messages', 'note', []);

        $fieldset = $form->addFieldset('new_deal_form_fieldset', ['legend' => __('Select Store and proceed to add new section')]);
       
        
        $featured = [13 => '13 Blocks', 9 => '9 Blocks'];
        $fieldset->addField('layout', 'select',  [
            'label' => __('Section Layout'),
            'title' => __('Section Layout'),
            'name' => 'layout',
            'value' => $data['layout'],
            'required' => true,
            'values' => $featured 
        ]);
         
        if ($this->_storeManager->isSingleStoreMode()) {
            $fieldset->addField(
                    'store_id', 
                    'hidden', 
                    [
                        'name' => 'store',
                        'value' => $this->_storeManager->getStore(true)->getId(),
                        'disabled' => $disabled
                    ]
                );
            $data['store_id'] = $this->_storeManager->getStore(true)->getId();
        } else {
            $fieldset->addField(
                'store_id',
                'select',
                [
                    'name' => 'store',
                    'label' => __('Apply on Store'),
                    'title' => __('Apply on Store'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, false)
                ]
            );
        }
         
        if ($this->_coreRegistry->registry('current_data') and $this->_coreRegistry->registry('current_data')->getData()){
            $form->setValues($this->_coreRegistry->registry('current_data')->getData());
        }
        
        $this->setForm($form);
    }
    
    protected function getBackendSession(){
        return $this->_session;
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Select Store Info');
    }
 
    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Select Store Info');
    }
 
    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }
 
    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }

}
