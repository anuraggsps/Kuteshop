<?php
namespace Magiccart\Shopbrand\Block\Product\GridProduct;

/**
 * Interceptor class for @see \Magiccart\Shopbrand\Block\Product\GridProduct
 */
class Interceptor extends \Magiccart\Shopbrand\Block\Product\GridProduct implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Catalog\Block\Product\Context $context, \Magento\Framework\Url\Helper\Data $urlHelper, \Magento\Framework\ObjectManagerInterface $objectManager, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility, \Magento\CatalogInventory\Helper\Stock $stockFilter, \Magento\CatalogInventory\Model\Configuration $stockConfig, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $urlHelper, $objectManager, $productCollectionFactory, $catalogProductVisibility, $stockFilter, $stockConfig, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getImage');
        if (!$pluginInfo) {
            return parent::getImage($product, $imageId, $attributes);
        } else {
            return $this->___callPlugins('getImage', func_get_args(), $pluginInfo);
        }
    }
}
