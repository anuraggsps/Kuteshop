<?php
namespace Ced\CsVendorProductAttribute\Controller\Set\Delete;

/**
 * Interceptor class for @see \Ced\CsVendorProductAttribute\Controller\Set\Delete
 */
class Interceptor extends \Ced\CsVendorProductAttribute\Controller\Set\Delete implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Magento\Eav\Api\AttributeSetRepositoryInterface $attributeSetRepository, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\UrlFactory $urlFactory, \Magento\Framework\Module\Manager $moduleManager)
    {
        $this->___init();
        parent::__construct($context, $coreRegistry, $attributeSetRepository, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
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
