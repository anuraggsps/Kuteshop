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

namespace Ced\CsTransaction\Controller\Vpayments;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlFactory;


class Requestpost extends \Magento\Framework\App\Action\Action {
    /**
     * Requestpost constructor.
     * @param Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param UrlFactory $urlFactory
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Ced\CsMarketplace\Helper\Data $helperData
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param array $data
     */
    public function __construct( Context $context,
        \Magento\Customer\Model\Session $customerSession,
        PageFactory $resultPageFactory,
        UrlFactory $urlFactory,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Ced\CsMarketplace\Helper\Data $helperData,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime, array $data = []) 

    {
        $this->_getSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->csmarketplaceHelper=$helperData;
        $this->datetime = $datetime;
        parent::__construct ( $context );
    }

    /**
     * @return $this|bool
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if(!$this->_getSession->getVendorId()) {
            return false;
        }
        $orderIds = $this->getRequest()->getParam('payment_request');
        if(strlen($orderIds) > 0) {
            $orderIds = explode(',',$orderIds);
        }

        if (!is_array($orderIds)) {
            $this->messageManager->addError(__('Please select amount(s).'));
        } else {
            if (!empty($orderIds)) {
                try {
                    $updated = 0;
                    foreach ($orderIds as $orderId) {
                        $items_model = $this->_objectManager->create('Ced\CsTransaction\Model\Items')->load($orderId);
                            
                        $amount = $items_model->getItemFee();
                        $order_increment_id = $this->_objectManager->create('Ced\CsTransaction\Model\Items')
                                                ->load($orderId)->getOrderIncrementId();
                       
                        $data = array('vendor_id'=>$this->_getSession->getVendorId(), 'order_id'=>$order_increment_id, 'amount'=>$amount, 'status'=>\Ced\CsMarketplace\Model\Vpayment\Requested::PAYMENT_STATUS_REQUESTED,'created_at'=>$this->datetime->date('Y-m-d H:i:s'),'item_id'=>$items_model->getOrderItemId());
                        $items_model->setIsRequested(1)->save();
                        $this->_objectManager->create('\Ced\CsMarketplace\Model\Vpayment\Requested')->addData($data)->save();
                        $updated++;
                    }
                    if ($updated) {
                        $this->messageManager->addSuccess(__('Total of %1 amount(s) have been requested for payment.', $updated));
                    } else {
                         $this->messageManager->addSuccess(__('Payment(s) have been already requested for payment.'));
                    }
                    return $resultRedirect->setPath('cstransaction/vpayments/request');
                } catch (\Exception $e) {
                    $this->messageManager->addError($e->getMessage());
                    return $resultRedirect->setPath('cstransaction/vpayments/request');
                }
            }
        }
        return false;
        
    }
    
}
