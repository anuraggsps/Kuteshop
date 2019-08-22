<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsEnhancement
 * @author   	 CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */


namespace Ced\CsEnhancement\Helper;


use Magento\Customer\Model\Customer;
use Magento\Framework\App\Helper\Context;

/**
 * Class Attribute
 * @package Ced\CsEnhancement\Helper
 */
class Attribute extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var Customer
     */
    protected $customer;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory
     */
    protected $attributeCollectionFactory;

    /**
     * @var \Ced\CsMarketplace\Model\VendorFactory
     */
    protected $vendorFactory;

    /**
     * @var \Ced\CsMarketplace\Model\Vendor\AttributeFactory
     */
    protected $vendorAttributeFactory;

    /**
     * @var \Ced\CsEnhancement\Logger\Logger
     */
    protected $logger;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Attribute constructor.
     * @param Customer $customer
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory
     * @param \Ced\CsMarketplace\Model\Vendor\AttributeFactory $vendorAttributeFactory
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Ced\CsEnhancement\Logger\Logger $logger
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Context $context
     */
    public function __construct(
        Customer $customer,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $attributeCollectionFactory,
        \Ced\CsMarketplace\Model\Vendor\AttributeFactory $vendorAttributeFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsEnhancement\Logger\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Context $context
    ) {
        parent::__construct($context);
        $this->customer = $customer;
        $this->attributeCollectionFactory = $attributeCollectionFactory;
        $this->vendorAttributeFactory = $vendorAttributeFactory;
        $this->vendorFactory = $vendorFactory;
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    /**
     * @return mixed
     */
    public function getVendorAttributes()
    {
        return $this->vendorFactory->create()->getVendorAttributes();
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRegistrationAttributes()
    {
        $storeId = $this->storeManager->getStore()->getId();

        $attributes = $this->vendorAttributeFactory->create()
            ->setStoreId($storeId)
            ->getCollection()
            ->addFieldToFilter('use_in_registration', ['gt' => 0])
            ->setOrder('position_in_registration', 'ASC');

        return $attributes;
    }

    /**
     * @return array
     */
    public function getCustomerFormAttributes()
    {
        $attributes = $this->attributeCollectionFactory->create();
        $typeId = $this->customer->getEntityType()->getId();
        $collection = $attributes->addFieldToFilter('entity_type_id', $typeId)->addFieldToFilter('is_required', 1);
        $collection->join(
            ['cfa' => 'customer_form_attribute'],
            "cfa.attribute_id = main_table.attribute_id AND cfa.form_code = 'customer_account_create'",
            []
        );

        return ($collection) ? $collection : [];
    }
}