<?php
namespace Ced\CsMembership\Controller\Adminhtml\Membership\Save;

/**
 * Interceptor class for @see \Ced\CsMembership\Controller\Adminhtml\Membership\Save
 */
class Interceptor extends \Ced\CsMembership\Controller\Adminhtml\Membership\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, \Magento\Framework\Registry $registerInterface)
    {
        $this->___init();
        parent::__construct($context, $scopeConfig, $registerInterface);
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
