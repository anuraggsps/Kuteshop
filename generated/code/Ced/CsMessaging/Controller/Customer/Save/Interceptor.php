<?php
namespace Ced\CsMessaging\Controller\Customer\Save;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Customer\Save
 */
class Interceptor extends \Ced\CsMessaging\Controller\Customer\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Module\Manager $moduleManager, \Magento\Framework\UrlInterface $urlBuilder, \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory, \Ced\CsMessaging\Model\VcustomerMessageFactory $vcustomerMessageFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Ced\CsMarketplace\Model\VendorFactory $vendorFactory, \Ced\CsMessaging\Model\CadminFactory $cadminFactory, \Ced\CsMessaging\Model\CadminMessageFactory $cadminMessageFactory, \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper, \Ced\CsMessaging\Helper\Mail $mailHelper, \Ced\CsMessaging\Helper\Data $helper, \Magento\Framework\Json\Helper\Data $jsonHelper)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $resultPageFactory, $moduleManager, $urlBuilder, $vcustomerFactory, $vcustomerMessageFactory, $localeDate, $vendorFactory, $cadminFactory, $cadminMessageFactory, $csmarketplaceHelper, $mailHelper, $helper, $jsonHelper);
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
