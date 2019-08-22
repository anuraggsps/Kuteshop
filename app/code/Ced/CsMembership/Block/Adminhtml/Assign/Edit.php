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

namespace Ced\CsMembership\Block\Adminhtml\Assign;


class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectInterface;
        parent::__construct($context, $data);
    }

    public function _construct()
    {                        
        $this->_objectId = 'assign_id';
        $this->_blockGroup = 'Ced_CsMembership';
        $this->_controller = 'adminhtml_assign';
                
        parent::_construct();   
        $this->buttonList->update('save', 'label', __('Assign Membership'));
    }
        


    
    
     /**
     * Getter for form header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('Assign Membership');  
    }

}
