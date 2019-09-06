<?php
namespace Ced\CsMessaging\Controller\Adminhtml\Vcustomer\Save;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Adminhtml\Vcustomer\Save
 */
class Interceptor extends \Ced\CsMessaging\Controller\Adminhtml\Vcustomer\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory, \Ced\CsMessaging\Model\VcustomerMessageFactory $vcustomerMessageFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Ced\CsMarketplace\Model\VendorFactory $vendorFactory, \Magento\Customer\Model\CustomerFactory $customerFactory, \Ced\CsMessaging\Helper\Mail $mailHelper, \Magento\Framework\UrlInterface $urlBuilder, \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $vcustomerFactory, $vcustomerMessageFactory, $localeDate, $vendorFactory, $customerFactory, $mailHelper, $urlBuilder, $csmarketplaceHelper);
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
