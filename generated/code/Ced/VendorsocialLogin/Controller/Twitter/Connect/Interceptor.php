<?php
namespace Ced\VendorsocialLogin\Controller\Twitter\Connect;

/**
 * Interceptor class for @see \Ced\VendorsocialLogin\Controller\Twitter\Connect
 */
class Interceptor extends \Ced\VendorsocialLogin\Controller\Twitter\Connect implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client $client, \Ced\VendorsocialLogin\Helper\Twitter $helperTwitter, \Magento\Customer\Model\Account\Redirect $accountRedirect)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $client, $helperTwitter, $accountRedirect);
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
