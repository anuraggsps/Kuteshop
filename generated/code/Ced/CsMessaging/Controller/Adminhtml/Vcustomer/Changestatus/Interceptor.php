<?php
namespace Ced\CsMessaging\Controller\Adminhtml\Vcustomer\Changestatus;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Adminhtml\Vcustomer\Changestatus
 */
class Interceptor extends \Ced\CsMessaging\Controller\Adminhtml\Vcustomer\Changestatus implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $vcustomerMessageCollFactory)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $resultJsonFactory, $vcustomerMessageCollFactory);
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
