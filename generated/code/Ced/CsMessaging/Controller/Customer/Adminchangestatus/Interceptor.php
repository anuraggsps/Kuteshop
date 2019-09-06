<?php
namespace Ced\CsMessaging\Controller\Customer\Adminchangestatus;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Customer\Adminchangestatus
 */
class Interceptor extends \Ced\CsMessaging\Controller\Customer\Adminchangestatus implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Ced\CsMessaging\Model\ResourceModel\CadminMessage\CollectionFactory $collectionFactory)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $resultJsonFactory, $collectionFactory);
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
