<?php
namespace Magento\CatalogGraphQl\Model\Resolver\Product\Price;

/**
 * Interceptor class for @see \Magento\CatalogGraphQl\Model\Resolver\Product\Price
 */
class Interceptor extends \Magento\CatalogGraphQl\Model\Resolver\Product\Price implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Pricing\PriceInfo\Factory $priceInfoFactory)
    {
        $this->___init();
        parent::__construct($storeManager, $priceInfoFactory);
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(\Magento\Framework\GraphQl\Config\Element\Field $field, $context, \Magento\Framework\GraphQl\Schema\Type\ResolveInfo $info, ?array $value = null, ?array $args = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'resolve');
        if (!$pluginInfo) {
            return parent::resolve($field, $context, $info, $value, $args);
        } else {
            return $this->___callPlugins('resolve', func_get_args(), $pluginInfo);
        }
    }
}
