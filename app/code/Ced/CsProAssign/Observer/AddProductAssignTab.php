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
namespace Ced\CsProAssign\Observer; 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

Class AddProductAssignTab implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    protected $request;
    private $messageManager;
    
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
        RequestInterface $request,
        ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
    
        $this->_objectManager = $objectManager;
        $this->request = $request;
        $this->messageManager = $messageManager;
        $this->_scopeConfig = $scopeConfig;
    }
    /**
     *Product Assignment Tab
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {   
        if($this->_scopeConfig->getValue('ced_csmarketplace/general/csproassignactivation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE)){
          $onj=$this->_objectManager->get('Magento\Framework\View\Element\Context');
          $block=$observer->getEvent()->getTabs();
          $block->removeTab('vproducts');
          $block->addTab(
              'assign_product', array(
              'label'     => __('Vendor Products'),
              'title'     => __('Vendor Products'),
              'after'     => 'payment_details',
              'content'   => $onj->getLayout()->createBlock('Ced\CsProAssign\Block\Adminhtml\AddPro')->toHtml().$onj->getLayout()->createBlock('Ced\CsProAssign\Block\Adminhtml\Vendor\Products\Grid')->toHtml(),
                )
          );
        }
    }
}    

