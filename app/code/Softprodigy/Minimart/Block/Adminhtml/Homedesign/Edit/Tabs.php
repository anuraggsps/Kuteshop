<?php
namespace Softprodigy\Minimart\Block\Adminhtml\Homedesign\Edit;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Accordion;
use Magento\Backend\Block\Widget\Tabs as WigetTabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
/**
 * Description of Tabs
 *
 * @author mannu
 */
class Tabs  extends WigetTabs
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;

    /**
     * Adminhtml catalog
     *
     * @var Catalog
     */
    protected $_helperCatalog = null;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @var InlineInterface
     */
    protected $_translateInline;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param Manager $moduleManager
     * @param CollectionFactory $collectionFactory
     * @param Catalog $helperCatalog
     * @param Data $catalogData
     * @param Registry $registry
     * @param InlineInterface $translateInline
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Registry $registry,
        InlineInterface $translateInline,
        array $data = []
    ) {
        
        $this->_coreRegistry = $registry;
        $this->_translateInline = $translateInline;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
        
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('deal_info_tabs');
        $this->setDestElementId('edit_form');
    }

     

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareLayout()
    {
        $nstore = $this->getRequest()->getParam('store', false);
        $layout = $this->getRequest()->getParam('layout', false);
        $id = $this->getRequest()->getParam('id', false);
         
        if((empty($layout) or empty($nstore)) and empty($id)){
            $this->addTab(
                'layout_store_sel',
                [
                    'label' => __('Section information'), 
                    'title' => __('Section information'), 
                    'content' => $this->getLayout()->createBlock("Softprodigy\Minimart\Block\Adminhtml\Homedesign\Edit\Tab\Storeinfo")->toHtml(),
                ]
            );
        } else {
            $this->addTab(
                'section_fill_form',
                [
                    'label' => __('Section information'), 
                    'title' => __('Section information'), 
                    'content' => $this->getLayout()->createBlock("Softprodigy\Minimart\Block\Adminhtml\Homedesign\Edit\Tab\Infoform")->toHtml(),
                ]
            );
        }
        
        return parent::_prepareLayout();
    }
    
    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        return parent::_beforeToHtml();
    }
    
    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->_translateInline->processResponseBody($html);
        return $html;
    }

    /**
     * @param string $parentTab
     * @return string
     
    public function getAccordion($parentTab)
    {
        $html = '';
        foreach ($this->_tabs as $childTab) {
            if ($childTab->getParentTab() === $parentTab->getId()) {
                $html .= $this->getChildBlock('child-tab')->setTab($childTab)->toHtml();
            }
        }
        return $html;
    }*/
}
