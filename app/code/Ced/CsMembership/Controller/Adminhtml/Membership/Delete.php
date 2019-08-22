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
use Magento\Backend\App\Action\Context;
class Delete extends \Magento\Backend\App\Action
{
	protected $connection;
    protected $_resource;

    public function __construct(
        Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->_resource = $resource;
        parent::__construct($context);
    }
    
    public function execute()
    {
		if( $this->getRequest()->getParam("id") > 0 ) {
            try {
                $model = $this->_objectManager->create('Ced\CsMembership\Model\Membership');
                $product_id = $model->load($this->getRequest()->getParam("id"))->getProductId();
                $model->setId($this->getRequest()->getParam("id"))->delete();
                $this->_eventManager->dispatch('delete_membership_virtual_product', array('product' => $product_id));
                $this->messageManager->addSuccess(__('Membership is successfully deleted.'));
                $this->_redirect("*/*/");
            } 
            catch (Exception $e) {
                $this->messageManager->addError(__($e->getMessage()));
                $this->_redirect("*/*/edit", array("id" => $this->getRequest()->getParam("id")));
            }
        }
        $this->_redirect("*/*/");
    }
}
