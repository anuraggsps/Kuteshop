<?php
namespace Ced\CsProduct\Controller\Vproducts\Edit;

/**
 * Interceptor class for @see \Ced\CsProduct\Controller\Vproducts\Edit
 */
class Interceptor extends \Ced\CsProduct\Controller\Vproducts\Edit implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Ced\CsProduct\Controller\Product\Builder $productBuilder, \Magento\Customer\Model\Session $customerSession, \Ced\CsProduct\Controller\Vproducts\Initialization\StockDataFilter $stockFilter, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\UrlFactory $urlFactory, \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory, \Magento\Framework\Module\Manager $moduleManager, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Registry $registry)
    {
        $this->___init();
        parent::__construct($context, $productBuilder, $customerSession, $stockFilter, $resultPageFactory, $urlFactory, $resultForwardFactory, $moduleManager, $scopeConfig, $storeManager, $registry);
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
