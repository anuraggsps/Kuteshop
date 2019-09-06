<?php
namespace Ced\CsMarketplace\Controller\Account\CreatePassword;

/**
 * Interceptor class for @see \Ced\CsMarketplace\Controller\Account\CreatePassword
 */
class Interceptor extends \Ced\CsMarketplace\Controller\Account\CreatePassword implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Ced\CsMarketplace\Model\AccountManagement $accountManagement)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $resultPageFactory, $accountManagement);
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
