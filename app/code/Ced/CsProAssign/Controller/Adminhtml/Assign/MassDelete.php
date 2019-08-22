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

namespace Ced\CsProAssign\Controller\Adminhtml\Assign;
class MassDelete extends \Ced\CsMarketplace\Controller\Adminhtml\Vendor
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {        
        $ids = $this->getRequest()->getParam('entity_id');
        $vendor_id = $this->getRequest()->getParam('vendor_id');
        if (!is_array($ids) || empty($ids)) {
            $this->messageManager->addError(__('Please select product(s).'));
        } else {
            try {
               $product = $this->_objectManager->get('Magento\Catalog\Model\Product')
                          ->getCollection()
                          ->addFieldToFilter('entity_id', ['in'=>$ids])->walk('delete');

               $vproductModel = $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')
                ->getCollection()
                ->addFieldToFilter('vendor_id', $vendor_id)
                ->addFieldToFilter('product_id', ['in'=>$ids])->walk('delete');

                $this->messageManager->addSuccessMessage(
                    __('A total of %1 record(s) have been deleted.', count($ids))
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
         $this->_redirect('csmarketplace/vendor/edit/vendor_id/'.$vendor_id);
    }
}
