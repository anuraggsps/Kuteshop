<?php
namespace Ced\CsMarketplace\Model\Adminhtml\Config\Data;

/**
 * Interceptor class for @see \Ced\CsMarketplace\Model\Adminhtml\Config\Data
 */
class Interceptor extends \Ced\CsMarketplace\Model\Adminhtml\Config\Data implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Config\ReinitableConfigInterface $config, \Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Config\Model\Config\Structure $configStructure, \Magento\Framework\DB\TransactionFactory $transactionFactory, \Magento\Config\Model\Config\Loader $configLoader, \Magento\Framework\App\Config\ValueFactory $configValueFactory, \Magento\Store\Model\StoreManagerInterface $storeManager, ?\Magento\Config\Model\Config\Reader\Source\Deployed\SettingChecker $settingChecker = null, array $data = [], ?\Magento\Framework\App\ScopeResolverPool $scopeResolverPool = null, ?\Magento\Store\Model\ScopeTypeNormalizer $scopeTypeNormalizer = null, ?\Magento\Framework\MessageQueue\PoisonPill\PoisonPillPutInterface $pillPut = null)
    {
        $this->___init();
        parent::__construct($config, $eventManager, $configStructure, $transactionFactory, $configLoader, $configValueFactory, $storeManager, $settingChecker, $data, $scopeResolverPool, $scopeTypeNormalizer, $pillPut);
    }

    /**
     * {@inheritdoc}
     */
    public function save()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'save');
        if (!$pluginInfo) {
            return parent::save();
        } else {
            return $this->___callPlugins('save', func_get_args(), $pluginInfo);
        }
    }
}
