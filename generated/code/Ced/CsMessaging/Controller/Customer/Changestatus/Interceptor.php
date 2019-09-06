<?php
namespace Ced\CsMessaging\Controller\Customer\Changestatus;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Customer\Changestatus
 */
class Interceptor extends \Ced\CsMessaging\Controller\Customer\Changestatus implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory, \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $vcustomerMessageCollFactory)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $resultJsonFactory, $vcustomerMessageCollFactory);
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
