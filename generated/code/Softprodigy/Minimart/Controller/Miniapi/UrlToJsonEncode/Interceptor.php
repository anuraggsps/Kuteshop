<?php
namespace Softprodigy\Minimart\Controller\Miniapi\UrlToJsonEncode;

/**
 * Interceptor class for @see \Softprodigy\Minimart\Controller\Miniapi\UrlToJsonEncode
 */
class Interceptor extends \Softprodigy\Minimart\Controller\Miniapi\UrlToJsonEncode implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Session\SessionManagerInterface $session, \Softprodigy\Minimart\Helper\Data $__helper, \Magento\Catalog\Model\Config $catalogConfig, \Magento\Catalog\Model\Product $productFactory, \Magento\Catalog\Model\ResourceModel\Product\Collection $_productCollection, \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $_productOptionCollection, \Magento\Cms\Model\Template\FilterProvider $filterProvider, \Magento\Framework\App\ViewInterface $ViewInterface, \Magento\Framework\Pricing\Helper\Data $currencyHelper, \Magento\Framework\UrlInterface $actionBuilder, \Psr\Log\LoggerInterface $logger, \Magento\Quote\Model\Quote $quoteLoader, \Magento\Checkout\Model\Session $__checkoutSession, \Magento\Customer\Model\Session $__customerSession, \Magento\Checkout\Model\Cart $cart, \Magento\Catalog\Api\ProductRepositoryInterface $prodRepInf, \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository, \Magento\Framework\Escaper $escaper, \Magento\Framework\Registry $registry, \Magento\Framework\View\LayoutFactory $layoutFactory, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility, \Magento\Catalog\Model\CategoryFactory $categoryFactory, \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory, \Magento\Catalog\Helper\Image $imageHelper)
    {
        $this->___init();
        parent::__construct($context, $storeManager, $session, $__helper, $catalogConfig, $productFactory, $_productCollection, $_productOptionCollection, $filterProvider, $ViewInterface, $currencyHelper, $actionBuilder, $logger, $quoteLoader, $__checkoutSession, $__customerSession, $cart, $prodRepInf, $customerAccountManagement, $quoteRepository, $escaper, $registry, $layoutFactory, $productCollectionFactory, $catalogProductVisibility, $categoryFactory, $tokenModelFactory, $imageHelper);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        if (!$pluginInfo) {
            return parent::dispatch($request);
        } else {
            return $this->___callPlugins('dispatch', func_get_args(), $pluginInfo);
        }
    }
}