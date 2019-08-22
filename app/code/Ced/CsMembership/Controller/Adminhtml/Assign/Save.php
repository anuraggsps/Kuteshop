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

namespace Ced\CsMembership\Controller\Adminhtml\Assign;
use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Save extends \Magento\Backend\App\Action
{
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registerInterface
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registerInterface;
        parent::__construct($context);
    }

    public function execute()
    {
    	$post_data=$this->getRequest()->getPostValue(); 
        if(isset($post_data['selected_vendor'])){
            $membershipId  = $this->getRequest()->getParam('id');
            $selectedVendors = $this->getRequest()->getParam('selected_vendor');
            $qtyModel = $this->_objectManager->create('Ced\CsMembership\Model\Membership')->load($membershipId);
            $prvqty = $qtyModel->getQty();
            if($prvqty >= count($selectedVendors)){
                $result = $this->_objectManager->get('Ced\CsMembership\Helper\Data')->assignMembership($membershipId,$selectedVendors);
                if($result)
                {
                    $qtyModel = $this->_objectManager->create('Ced\CsMembership\Model\Membership')->load($membershipId);
                    $prvqty = $qtyModel->getQty();
                    $newqty = $prvqty-count($selectedVendors);
                    $qtyModel->setQty($newqty);
                    $qtyModel->save();
                    $this->messageManager->addSuccess(__("Package successfully assigned to Vendors."));
                    $this->_redirect('*/*/');
                }
            }else{
                $this->messageManager->addError(__("Package is not Sufficient for Selected Qty of Vendors."));
                $this->_redirect('*/*/');
            }
        }else{
            $this->messageManager->addError(__("Please select one or more vendors."));
            $this->_redirect("*/*/");
        }
    }
}
