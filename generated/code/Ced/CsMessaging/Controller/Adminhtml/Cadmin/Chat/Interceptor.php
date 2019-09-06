<?php
namespace Ced\CsMessaging\Controller\Adminhtml\Cadmin\Chat;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Adminhtml\Cadmin\Chat
 */
class Interceptor extends \Ced\CsMessaging\Controller\Adminhtml\Cadmin\Chat implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Ced\CsMessaging\Model\CadminFactory $cadminFactory)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $cadminFactory);
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
