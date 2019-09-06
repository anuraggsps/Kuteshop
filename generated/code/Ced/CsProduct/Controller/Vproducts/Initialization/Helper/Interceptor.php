<?php
namespace Ced\CsProduct\Controller\Vproducts\Initialization\Helper;

/**
 * Interceptor class for @see \Ced\CsProduct\Controller\Vproducts\Initialization\Helper
 */
class Interceptor extends \Ced\CsProduct\Controller\Vproducts\Initialization\Helper implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\RequestInterface $request, \Magento\Store\Model\StoreManagerInterface $storeManager, \Ced\CsProduct\Controller\Vproducts\Initialization\StockDataFilter $stockFilter, \Magento\Catalog\Model\Product\Initialization\Helper\ProductLinks $productLinks, \Magento\Backend\Helper\Js $jsHelper, \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter, ?\Magento\Catalog\Api\Data\ProductCustomOptionInterfaceFactory $customOptionFactory = null, ?\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory $productLinkFactory = null, ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository = null, ?\Magento\Catalog\Model\Product\LinkTypeProvider $linkTypeProvider = null, ?\Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper\AttributeFilter $attributeFilter = null)
    {
        $this->___init();
        parent::__construct($request, $storeManager, $stockFilter, $productLinks, $jsHelper, $dateFilter, $customOptionFactory, $productLinkFactory, $productRepository, $linkTypeProvider, $attributeFilter);
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(\Magento\Catalog\Model\Product $product)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'initialize');
        if (!$pluginInfo) {
            return parent::initialize($product);
        } else {
            return $this->___callPlugins('initialize', func_get_args(), $pluginInfo);
        }
    }
}
