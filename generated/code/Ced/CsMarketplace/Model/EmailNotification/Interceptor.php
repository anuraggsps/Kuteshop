<?php
namespace Ced\CsMarketplace\Model\EmailNotification;

/**
 * Interceptor class for @see \Ced\CsMarketplace\Model\EmailNotification
 */
class Interceptor extends \Ced\CsMarketplace\Model\EmailNotification implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Customer\Model\CustomerRegistry $customerRegistry, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, \Magento\Customer\Helper\View $customerViewHelper, \Magento\Framework\Reflection\DataObjectProcessor $dataProcessor, \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, ?\Magento\Framework\Mail\Template\SenderResolverInterface $senderResolver = null)
    {
        $this->___init();
        parent::__construct($customerRegistry, $storeManager, $transportBuilder, $customerViewHelper, $dataProcessor, $scopeConfig, $senderResolver);
    }

    /**
     * {@inheritdoc}
     */
    public function newAccount(\Magento\Customer\Api\Data\CustomerInterface $customer, $type = 'registered', $backUrl = '', $storeId = 0, $sendemailStoreId = null)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'newAccount');
        if (!$pluginInfo) {
            return parent::newAccount($customer, $type, $backUrl, $storeId, $sendemailStoreId);
        } else {
            return $this->___callPlugins('newAccount', func_get_args(), $pluginInfo);
        }
    }
}
