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
  * @package   Ced_CsOrder
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsMarketplace\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Message\ManagerInterface;

Class SalesQuoteItemSetVendorId implements ObserverInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    protected $request;
    private $messageManager;
    
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
        RequestInterface $request,
        ManagerInterface $messageManager
    ) {
        $this->_objectManager = $objectManager;
        $this->request = $request;
        $this->messageManager = $messageManager;
        
    }

    /**
     * @param Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    { 
        $vProducts = $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts');
        $item = $observer->getQuoteItem(); 
     
        if($vendorId = $vProducts->getVendorIdByProduct($item->getProductId())) {
          /*set vendor id to the quote item*/
          $item->setVendorId($vendorId); 
        }       

    }
}
