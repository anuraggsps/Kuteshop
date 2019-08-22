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
 * @package   Ced_CsProAssign
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProAssign\Controller\Adminhtml\Assign;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Massremove extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $modelFactory;
    protected $_scopeConfig;
    protected $_storeManager;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }

    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {
        $enable = $this->_scopeConfig->getValue(
            'ced_csmarketplace/general/csproassignactivation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $vendor_id = $this->getRequest()->getParam('vendor_id');
        if ($enable) {
            $ids = $this->getRequest()->getParam('entity_id');
            if (!is_array($ids) || empty($ids)) {
                $this->messageManager->addError(__('Please select product(s).'));
            } else {
                $vproductModel = $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')
                    ->getCollection()
                    ->addFieldToFilter('vendor_id', $vendor_id)
                    ->addFieldToFilter('product_id', ['in' => $ids])->walk('delete');
                $this->messageManager->addSuccess(__("Product(s) was successfully removed from vendor"));
            }
        }
        $this->_redirect('csmarketplace/vendor/edit/vendor_id/' . $vendor_id);
    }
}


