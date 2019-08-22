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
 * @category  Ced
 * @package   Ced_CsVendorReview
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsVendorReview\Controller\Rating;

class Lists extends \Magento\Framework\App\Action\Action
{
    
    protected $_coreRegistry;
    protected $resultPageFactory;
    

    
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $storeId = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStore()->getId();
        $vendor = $this->_objectManager->get('Ced\CsMarketplace\Model\Vendor')
            ->setStoreId($storeId)->loadByAttribute('entity_id', $id);
        
        if (!$this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->canShow($vendor)) {
            return false;
        } elseif (!$this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->isShopEnabled($vendor)) {
            return false;
        }

        $this->_coreRegistry = $this->_objectManager->get('Magento\Framework\Registry');
        $this->_coreRegistry->register('current_vendor', $vendor);
        
        $isAllowed = $this->_objectManager->get('Ced\CsVendorReview\Helper\Data')->isCustomerAllowed();
        $checkVendorProducts = $this->_objectManager->get('Ced\CsVendorReview\Helper\Data')->checkVendorProduct();
        if($isAllowed){
        	if(!$checkVendorProducts){
        		$this->messageManager->addErrorMessage(__('You cannot review this vendor as you have not purchsed any item from this vendor'));
        		$redirect =  $this->resultRedirectFactory->create();
        		return $redirect->setPath('csmarketplace/vshops');
        	}
        }
        
        
        $resultPage = $this->_objectManager->create('\Magento\Framework\View\Result\PageFactory');

        $resultPage =  $resultPage->create();
        $resultPage->getConfig()->getTitle()->set(__('Customer Review'));

        return $resultPage;
    }
}
