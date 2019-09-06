<?php
namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import\Validate;

/**
 * Interceptor class for @see \Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import\Validate
 */
class Interceptor extends \Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import\Validate implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Directory\Model\ResourceModel\Country\Collection $countryCollection, \Magento\Directory\Model\RegionFactory $regionFactory, \Magento\Framework\App\ResourceConnection $resourceConnection, \Magento\Customer\Model\CustomerFactory $customerFactory, \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel, \Ced\CsMarketplace\Model\Vendor $vendorFactory, \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResourceModel, \Ced\CsMarketplace\Model\ResourceModel\Vendor\Collection $vendorCollectionFactory, \Magento\Framework\File\Csv $csv, \Magento\Framework\Serialize\Serializer\Json $jsonEncoder, \Magento\Backend\App\Action\Context $context)
    {
        $this->___init();
        parent::__construct($countryCollection, $regionFactory, $resourceConnection, $customerFactory, $customerResourceModel, $vendorFactory, $vendorResourceModel, $vendorCollectionFactory, $csv, $jsonEncoder, $context);
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
