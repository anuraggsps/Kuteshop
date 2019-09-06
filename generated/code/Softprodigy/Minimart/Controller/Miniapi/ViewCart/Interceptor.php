<?php
namespace Softprodigy\Minimart\Controller\Miniapi\ViewCart;

/**
 * Interceptor class for @see \Softprodigy\Minimart\Controller\Miniapi\ViewCart
 */
class Interceptor extends \Softprodigy\Minimart\Controller\Miniapi\ViewCart implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Session\SessionManagerInterface $session, \Softprodigy\Minimart\Helper\Data $__helper, \Magento\Catalog\Model\Config $catalogConfig, \Magento\Catalog\Model\Product $productFactory, \Magento\Catalog\Model\ResourceModel\Product\Collection $_productCollection, \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $_productOptionCollection, \Magento\Cms\Model\Template\FilterProvider $filterProvider, \Magento\Framework\App\ViewInterface $ViewInterface, \Magento\Framework\Pricing\Helper\Data $currencyHelper, \Magento\Framework\UrlInterface $actionBuilder, \Psr\Log\LoggerInterface $logger, \Magento\Quote\Model\Quote $quoteLoader, \Magento\Checkout\Model\Session $__checkoutSession, \Magento\Customer\Model\Session $__customerSession, \Magento\Checkout\Model\Cart $cart, \Magento\Catalog\Api\ProductRepositoryInterface $prodRepInf, \Magento\Customer\Api\AccountManagementInterface $customerAccountManagement, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository, \Magento\Framework\Escaper $escaper, \Magento\Framework\Registry $registry, \Magento\Framework\View\LayoutFactory $layoutFactory, \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility, \Magento\Catalog\Model\CategoryFactory $categoryFactory, \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory, \Magento\Catalog\Helper\Image $imageHelper, \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory, \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository, \Magento\Framework\Encryption\Encryptor $encryptor, \Magento\Catalog\Model\ProductRepository $productRepository, \Magento\Catalog\Model\Product\Attribute\Repository $attributeRepository, \Magento\Customer\Model\CustomerRegistry $customerRegistry, \Magento\Customer\Api\AddressRepositoryInterface $addressRepository, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Shipping\Model\Config $shipconfig, \Magento\Search\Model\AutocompleteInterface $autocomplete, \Magento\Search\Model\QueryFactory $queryFactory, \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency, \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface, \Magento\Quote\Api\CartManagementInterface $cartManagementInterface, \Magento\Directory\Model\CurrencyFactory $currencyFactory)
    {
        $this->___init();
        parent::__construct($context, $storeManager, $session, $__helper, $catalogConfig, $productFactory, $_productCollection, $_productOptionCollection, $filterProvider, $ViewInterface, $currencyHelper, $actionBuilder, $logger, $quoteLoader, $__checkoutSession, $__customerSession, $cart, $prodRepInf, $customerAccountManagement, $quoteRepository, $escaper, $registry, $layoutFactory, $productCollectionFactory, $catalogProductVisibility, $categoryFactory, $tokenModelFactory, $imageHelper, $resourceFactory, $customerRepository, $encryptor, $productRepository, $attributeRepository, $customerRegistry, $addressRepository, $scopeConfig, $shipconfig, $autocomplete, $queryFactory, $priceCurrency, $cartRepositoryInterface, $cartManagementInterface, $currencyFactory);
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
