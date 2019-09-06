<?php
namespace Ced\CsCommission\Model\Backend\Vendor\Rate\Category;

/**
 * Interceptor class for @see \Ced\CsCommission\Model\Backend\Vendor\Rate\Category
 */
class Interceptor extends \Ced\CsCommission\Model\Backend\Vendor\Rate\Category implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\App\Config\ScopeConfigInterface $config, \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList, ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource, ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection, \Ced\CsCommission\Helper\Category $commissioncategoryHelper, \Magento\Catalog\Model\Category $catalogCategory, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $commissioncategoryHelper, $catalogCategory, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function afterSave()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'afterSave');
        if (!$pluginInfo) {
            return parent::afterSave();
        } else {
            return $this->___callPlugins('afterSave', func_get_args(), $pluginInfo);
        }
    }
}
