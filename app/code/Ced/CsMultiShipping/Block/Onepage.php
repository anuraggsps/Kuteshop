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
namespace Ced\CsMultiShipping\Block;

/**
 * Onepage checkout block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Onepage extends \Magento\Checkout\Block\Onepage
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Magento\Checkout\Model\CompositeConfigProvider $configProvider,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $formKey, $configProvider, $layoutProcessors, $data);
        $this->_objectManager = $objectInterface;
    }


    /**
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
                
        if($this->_objectManager->get('Ced\CsMultiShipping\Helper\Data')->isEnabled()) {
            return str_replace('"Magento_Checkout\/js\/view\/shipping"', '"Ced_CsMultiShipping\/js/view\/shipping"', \Zend_Json::encode($this->jsLayout));
        }else{
            return \Zend_Json::encode($this->jsLayout);
        }
    
    }

}
