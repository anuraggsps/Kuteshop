<?php
namespace Ced\CsMessaging\Controller\Customer\Vcompose;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Customer\Vcompose
 */
class Interceptor extends \Ced\CsMessaging\Controller\Customer\Vcompose implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\UrlInterface $urlBuilder, \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper, \Ced\CsMarketplace\Model\VendorFactory $vendorFactory, \Ced\CsMarketplace\Model\Session $vendorSession)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $resultPageFactory, $urlBuilder, $csmarketplaceHelper, $vendorFactory, $vendorSession);
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
