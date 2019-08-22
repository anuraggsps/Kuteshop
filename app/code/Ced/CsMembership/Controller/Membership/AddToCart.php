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

namespace Ced\CsMembership\Controller\Membership; 
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\UrlFactory;
class AddToCart extends \Ced\CsMarketplace\Controller\Vendor
{
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        UrlFactory $urlFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Framework\Stdlib\StringUtils $stringutil
    ) {
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);   
        $this->_formKeyValidator = $formKeyValidator;
        $this->_strUtil = $stringutil;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
		$vendorId = $this->_getSession()->getVendorId();
		$model = $this->_objectManager->create('Ced\CsMembership\Helper\Data');
		$existing = $model->getExistingSubcription($vendorId);
		$existing_new = array();
		foreach($existing as $value)
		{
			$existing_new[] = $value['subscription_id'];
		}
		if(in_array($id, $existing_new))
		{
				$this->messageManager->addError('You have alrady subscribed this package');
				$this->_redirect('*/*/index');		
            	return;
		}else
		{
	   		$productId = $this->_objectManager->create('Ced\CsMembership\Helper\Data')->getProductIdByMembershipId($id);
	   		try{
	                $cart = $this->_objectManager->create('Magento\Checkout\Model\Cart');
	                $cart->truncate();//clear existing cart items
	                //$cart->init();
	                $cart->addProductsByIds(array('0'=>$productId));
	                $cart->save();
	                $this->messageManager->addSuccess('Membership package has been added to cart.');
	            }
	            catch(\Exception $e){
	               	$this->messageManager->addError($e->getMessage());
			 		$this->_redirect('*/*/index');
					return;
	            } 
			$this->_redirect('checkout/cart');
   			}
    }
}
 
