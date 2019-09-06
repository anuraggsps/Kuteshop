<?php
namespace Ced\CsProAssign\Controller\Adminhtml\Assign\Addproducts;

/**
 * Interceptor class for @see \Ced\CsProAssign\Controller\Adminhtml\Assign\Addproducts
 */
class Interceptor extends \Ced\CsProAssign\Controller\Adminhtml\Assign\Addproducts implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Registry $registry, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $registry, $scopeConfig);
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
