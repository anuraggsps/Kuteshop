<?php
namespace Ced\CsMessaging\Controller\Adminhtml\Vadmin\Save;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Adminhtml\Vadmin\Save
 */
class Interceptor extends \Ced\CsMessaging\Controller\Adminhtml\Vadmin\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Ced\CsMessaging\Model\VadminFactory $vadminFactory, \Ced\CsMessaging\Model\VadminMessageFactory $vadminMessageFactory, \Ced\CsMarketplace\Model\VendorFactory $vendorFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper, \Ced\CsMessaging\Helper\Mail $mailHelper, \Magento\Framework\UrlInterface $urlBuilder)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $vadminFactory, $vadminMessageFactory, $vendorFactory, $localeDate, $csmarketplaceHelper, $mailHelper, $urlBuilder);
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
