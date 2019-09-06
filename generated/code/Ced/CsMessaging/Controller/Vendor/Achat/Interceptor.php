<?php
namespace Ced\CsMessaging\Controller\Vendor\Achat;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Vendor\Achat
 */
class Interceptor extends \Ced\CsMessaging\Controller\Vendor\Achat implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\UrlFactory $urlFactory, \Magento\Framework\Module\Manager $moduleManager, \Ced\CsMessaging\Model\VadminFactory $vadminFactory, \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager, $vadminFactory, $csmarketplaceHelper);
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
