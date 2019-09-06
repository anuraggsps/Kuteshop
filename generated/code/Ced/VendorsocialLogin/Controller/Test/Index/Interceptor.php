<?php
namespace Ced\VendorsocialLogin\Controller\Test\Index;

/**
 * Interceptor class for @see \Ced\VendorsocialLogin\Controller\Test\Index
 */
class Interceptor extends \Ced\VendorsocialLogin\Controller\Test\Index implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Customer\Model\ResourceModel\Customer $modelNewsFactory)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $modelNewsFactory);
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
