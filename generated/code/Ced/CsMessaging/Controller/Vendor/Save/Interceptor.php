<?php
namespace Ced\CsMessaging\Controller\Vendor\Save;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Vendor\Save
 */
class Interceptor extends \Ced\CsMessaging\Controller\Vendor\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\UrlFactory $urlFactory, \Magento\Framework\Module\Manager $moduleManager, \Ced\CsMessaging\Model\VcustomerMessageFactory $vcustomerMessageFactory, \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Magento\Customer\Model\CustomerFactory $customerFactory, \Ced\CsMessaging\Model\VadminFactory $vadminFactory, \Ced\CsMessaging\Model\VadminMessageFactory $vadminMessageFactory, \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper, \Ced\CsMessaging\Helper\Mail $mailHelper, \Magento\Framework\Json\Helper\Data $jsonHelper, \Ced\CsMessaging\Helper\Data $helper)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager, $vcustomerMessageFactory, $vcustomerFactory, $localeDate, $customerFactory, $vadminFactory, $vadminMessageFactory, $csmarketplaceHelper, $mailHelper, $jsonHelper, $helper);
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
