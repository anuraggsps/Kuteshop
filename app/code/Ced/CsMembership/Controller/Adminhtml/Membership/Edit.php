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

namespace Ced\CsMembership\Controller\Adminhtml\Membership;
use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Edit extends \Magento\Backend\App\Action
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
        $id = $this->getRequest()->getParam('id');
		
        $model = $this->_objectManager->create('Ced\CsMembership\Model\Membership');
		
		$registryObject = $this->_objectManager->get('Magento\Framework\Registry');
		
        if ($id) {
            $model->load($id);
            $this->_coreRegistry->register("csmembership_data",$model);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This row no longer exists.'));
                $this->_redirect('*/*/');
                return;
            }
        }
        else
        {
            $this->_coreRegistry->register("csmembership_data",$model);
        }
        
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }
		$registryObject->register('csmembership_member_data', $model);
		$this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
		$this->_view->getPage()->getConfig()->getTitle()->set((__('Manage Membership Plan')));
        $this->_view->renderLayout();
    }

    /**
     * ACL check
     *
     * @return bool
     */
    protected function _isAllowed()
    {

        switch ($this->getRequest()->getControllerName()) {
        case 'rating' : 
            return $this->ratingAcl(); break;
        default : 
            return $this->_authorization->isAllowed('Ced_CsMarketplace::csmarketplace'); break;
        }
    }

    /**
     * ACL check for Rating
     *
     * @return bool
     */
    protected function ratingAcl() 
    {
        
        switch ($this->getRequest()->getActionName()) {
        default: 
            return $this->_authorization->isAllowed('Ced_CsVendorReview::manage_rating'); break;
        }
    }
}
