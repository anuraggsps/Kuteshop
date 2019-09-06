<?php
namespace Ced\CsMessaging\Controller\Adminhtml\Cadmin\Changestatus;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Adminhtml\Cadmin\Changestatus
 */
class Interceptor extends \Ced\CsMessaging\Controller\Adminhtml\Cadmin\Changestatus implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory $collectionFctory)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $resultJsonFactory, $collectionFctory);
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
