<?php
namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import;

/**
 * Interceptor class for @see \Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import
 */
class Interceptor extends \Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\Registry $coreRegistry, \Ced\CsEnhancement\Helper\File $fileHelper, \Magento\Framework\View\Result\PageFactory $resultPageFactory)
    {
        $this->___init();
        parent::__construct($context, $coreRegistry, $fileHelper, $resultPageFactory);
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
