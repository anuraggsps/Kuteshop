<?php
namespace Ced\CsMessaging\Controller\Adminhtml\Cadmin\Save;

/**
 * Interceptor class for @see \Ced\CsMessaging\Controller\Adminhtml\Cadmin\Save
 */
class Interceptor extends \Ced\CsMessaging\Controller\Adminhtml\Cadmin\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Ced\CsMessaging\Model\CadminFactory $cadminFactory, \Ced\CsMessaging\Model\CadminMessageFactory $cadminMessageFactory, \Magento\Customer\Model\CustomerFactory $customerFactory, \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate, \Ced\CsMarketplace\Helper\Data $csmarketplaceHelper, \Ced\CsMessaging\Helper\Mail $mailHelper)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $cadminFactory, $cadminMessageFactory, $customerFactory, $localeDate, $csmarketplaceHelper, $mailHelper);
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
