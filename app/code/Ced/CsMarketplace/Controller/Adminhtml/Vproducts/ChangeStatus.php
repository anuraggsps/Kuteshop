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
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Controller\Adminhtml\Vproducts;

use Magento\Framework\Controller\ResultFactory;

class ChangeStatus extends \Magento\Framework\App\Action\Action
{

    /**
     * Update product(s) status action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {    

        $checkstatus = $this->getRequest()->getParam('status');   
        $reason =  $this->getRequest()->getParam('reason');
        
        if ($this->getRequest()->getParam('id') > 0 && $checkstatus != '') {
            try{
                if($reason){
                  $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')->load($this->getRequest()->getParam('id'),'product_id')->setReason($reason)->save();
                 }else{
                 	$this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')->load($this->getRequest()->getParam('id'),'product_id')->setReason('')->save();
                 }
                 $errors = $this->_objectManager->get('Ced\CsMarketplace\Model\Vproducts')
                 ->changeVproductStatus(array($this->getRequest()->getParam('id')), $checkstatus);
                if($errors['success']) {
                    $this->messageManager->addSuccessMessage(__("Status changed Successfully")); 
                }
                if($errors['error']) {
                    $this->messageManager->addError(__("Can't process approval/disapproval for the Product.The Product's vendor is disapproved or not exist.")); 
                }
            }catch(\Exception $e){
                $this->messageManager->addError(__($e->getMessage()));
            }
        }        
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
