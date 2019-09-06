<?php
namespace Ced\CsVendorProductAttribute\Controller\Set\Edit;

/**
 * Interceptor class for @see \Ced\CsVendorProductAttribute\Controller\Set\Edit
 */
class Interceptor extends \Ced\CsVendorProductAttribute\Controller\Set\Edit implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\UrlFactory $urlFactory, \Magento\Framework\Module\Manager $moduleManager)
    {
        $this->___init();
        parent::__construct($context, $coreRegistry, $resultPageFactory, $customerSession, $urlFactory, $moduleManager);
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
