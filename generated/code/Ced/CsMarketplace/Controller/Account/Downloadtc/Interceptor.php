<?php
namespace Ced\CsMarketplace\Controller\Account\Downloadtc;

/**
 * Interceptor class for @see \Ced\CsMarketplace\Controller\Account\Downloadtc
 */
class Interceptor extends \Ced\CsMarketplace\Controller\Account\Downloadtc implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\App\Filesystem\DirectoryList $directory, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\UrlFactory $urlFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Module\Manager $moduleManager, \Ced\CsMarketplace\Helper\Data $datahelper)
    {
        $this->___init();
        parent::__construct($context, $customerSession, $directory, $resultPageFactory, $urlFactory, $storeManager, $moduleManager, $datahelper);
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
