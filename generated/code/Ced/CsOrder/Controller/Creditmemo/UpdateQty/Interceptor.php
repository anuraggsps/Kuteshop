<?php
namespace Ced\CsOrder\Controller\Creditmemo\UpdateQty;

/**
 * Interceptor class for @see \Ced\CsOrder\Controller\Creditmemo\UpdateQty
 */
class Interceptor extends \Ced\CsOrder\Controller\Creditmemo\UpdateQty implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\UrlFactory $urlFactory, \Magento\Framework\Module\Manager $moduleManager, \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader, \Magento\Framework\Registry $registry, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Magento\Framework\Controller\Result\RawFactory $resultRawFactory, \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager, $creditmemoLoader, $registry, $resultJsonFactory, $resultRawFactory, $resultForwardFactory);
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
