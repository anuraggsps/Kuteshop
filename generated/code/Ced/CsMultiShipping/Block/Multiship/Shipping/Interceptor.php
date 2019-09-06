<?php
namespace Ced\CsMultiShipping\Block\Multiship\Shipping;

/**
 * Interceptor class for @see \Ced\CsMultiShipping\Block\Multiship\Shipping
 */
class Interceptor extends \Ced\CsMultiShipping\Block\Multiship\Shipping implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Filter\DataObject\GridFactory $filterGridFactory, \Magento\Multishipping\Model\Checkout\Type\Multishipping $multishipping, \Magento\Tax\Helper\Data $taxHelper, \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency, \Magento\Framework\ObjectManagerInterface $objectInterface, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $filterGridFactory, $multishipping, $taxHelper, $priceCurrency, $objectInterface, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsBoxTextAfter(\Magento\Framework\DataObject $addressEntity)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getItemsBoxTextAfter');
        if (!$pluginInfo) {
            return parent::getItemsBoxTextAfter($addressEntity);
        } else {
            return $this->___callPlugins('getItemsBoxTextAfter', func_get_args(), $pluginInfo);
        }
    }
}
