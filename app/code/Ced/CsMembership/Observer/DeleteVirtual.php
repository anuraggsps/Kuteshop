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

namespace Ced\CsMembership\Observer; 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
Class DeleteVirtual implements ObserverInterface
{
	
	protected $_objectManager;
	protected $_quoteFactory;
	protected $_advanceFactory;
	protected $_object;
    protected $_coreRegistry = null;
    protected $frontController;
    protected $request;
	
	public function __construct(		
			\Magento\Framework\DataObject $object,
			\Magento\Framework\ObjectManagerInterface $objectManager,
			\Magento\Quote\Model\QuoteFactory $quoteFactory,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\App\FrontControllerInterface $frontController,
            \Magento\Framework\App\Request\Http $request
	)
    {
    	$this->_object = $object;
        $this->_objectManager = $objectManager;
        $this->_quoteFactory = $quoteFactory;
        $this->_coreRegistry = $registry;
        $this->frontController = $frontController;
        $this->request = $request;
    }
	
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $product_id = $observer->getEvent()->getProduct();
        if($product_id){
           try{    
                $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id)->delete();
            }catch(Exception $e){
                echo $e->getMessage(); 
            } 
        }         
    }	
}