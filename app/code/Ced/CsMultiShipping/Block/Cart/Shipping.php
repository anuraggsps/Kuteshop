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
 * @package   Ced_CsMultiShipping
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
 
namespace Ced\CsMultiShipping\Block\Cart;
 
class Shipping extends \Magento\Checkout\Block\Cart\Shipping
{

    protected $_vendor_rates;    
    protected $_objectManager;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $customerSession, $checkoutSession, $configProvider, $layoutProcessors, $data);
        $this->_objectManager = $objectInterface;        
    }
    
    public function getJsLayout()
    {        
        if($this->_objectManager->get('Ced\CsMultiShipping\Helper\Data')->isEnabled()) {
            return str_replace("Magento_Checkout\/js\/view\/cart\/shipping-rates", "Ced_CsMultiShipping\/js/cart\/shipping-rates", parent::getJsLayout());
        }else{
            return parent::getJsLayout();
        }
    }
    
}
