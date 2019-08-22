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

namespace Ced\CsMembership\Block\Adminhtml\Assign\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    protected function _construct()
    {
		
        parent::_construct();
        $this->setId('membership_plan_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Assign Information'));
    }

    protected function _beforeToHtml()
	{
		$this->addTab('vorders', array(
				 'label'     => __('Assign Info'),
				  'title'     => __('Assign Info'),
				  'content'   => $this->getLayout()->createBlock('Ced\CsMembership\Block\Adminhtml\Assign\Edit\Tab\Assign')->toHtml(),
		));
		return parent::_beforeToHtml();
	}
}