<?php
namespace Ced\CsMessaging\Controller\Vendor\Ccompose;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Vendor\Ccompose
 */
class Interceptor extends \Ced\CsMessaging\Controller\Vendor\Ccompose implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\UrlFactory $urlFactory, \Magento\Framework\Module\Manager $moduleManager, \Ced\CsMessaging\Model\VcustomerMessageFactory $vcustomerMessageFactory, \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory, \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager, $vcustomerMessageFactory, $vcustomerFactory, $csmarketplaceHelper);
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
