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

namespace Ced\CsMembership\Block\Adminhtml\Membershipfilter\Membershipfilter;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{

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


    /**
     * Add fieldset with general report fields
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/sales');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'filter_form',
                    'action' => $actionUrl,
                    'method' => 'get'
                ]
            ]
        );

        $htmlIdPrefix = 'sales_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Filter By Plans')]);

        $script = $fieldset->addField(
            'member_id',
            'select',
            [
                'name' => 'member_id',
                'onchange'  => "addDropDown(this)",
                'options' => $this->_objectManager->create('Ced\CsMembership\Helper\Data')->getMemberships(),
                'label' => __('Choose Plan Filter')
            ]
        );

        $script->setAfterElementHtml("<script type=\"text/javascript\">
            function addDropDown(data){
                var membershipId = data.value;
                var reloadurl = '". $this->getUrl("csmembership/membership/orderbymembership") ."'+'id/' + membershipId;
                 window.location.href=reloadurl; 
            }
            </script>");

        $form->setUseContainer(true);
        if($id = $this->getRequest()->getParam('id'))
        {
            $form->setValues(array('member_id' => $id));
        }
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
