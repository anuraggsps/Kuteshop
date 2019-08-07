<?php

namespace Softprodigy\Minimart\Block\Adminhtml;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Homedesign
 *
 * @author root
 */
class Homedesign extends \Magento\Backend\Block\Widget\Grid\Container {

    /**
     * Constructor
     * 
     * @return void
     */
    protected function _construct() {
        
        $this->_blockGroup = 'Softprodigy_Minimart';
        $this->_controller = 'adminhtml_homedesign';
        $this->_headerText = __('Homepage Sections');
        $this->_addButtonLabel = __('Add New Section to your app');
        parent::_construct();
        $this->updateButton('add', 'onclick', 'setLocation(\'' . $this->getUrl("*/*/create") . '\');');
    }

    /**
     * PrepareLayout
     */
    protected function _prepareLayout() {
        parent::_prepareLayout();
    }

}
