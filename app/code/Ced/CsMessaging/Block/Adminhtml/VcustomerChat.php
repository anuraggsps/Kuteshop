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
 * @package     Ced_CsMessaging
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMessaging\Block\Adminhtml;

/**
 * Class VcustomerChat
 * @package Ced\CsMessaging\Block\Adminhtml
 */
class VcustomerChat extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Ced\CsMessaging\Model\VcustomerFactory $vcustomerFactory,
        \Ced\CsMessaging\Model\ResourceModel\VcustomerMessage\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        array $data = []
    )
    {
        $this->_objectManager = $objectManager;
        $this->urlBuilder = $urlBuilder;
        $this->vcustomerFactory = $vcustomerFactory;
        $this->collectionFactory = $collectionFactory;
        $this->customerFactory = $customerFactory;
        $this->vendorFactory = $vendorFactory;
        parent::__construct($context, $data);
    }


    public function getThreadData($id)
    {
        $vcustomerThread = $this->vcustomerFactory->create();
        $vcustomerThread->load($id);
        return $vcustomerThread;
    }


    /**
     * @param $threadId
     * @return mixed
     */
    public function getChatCollection($threadId)
    {
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('thread_id',$threadId);
        return $collection;
    }

    /**
     * @param $vId
     * @return mixed
     */
    public function getVendorById($vId)
    {
        $vendor= $this->vendorFactory->create();
        return $vendor->load($vId);
    }

    /**
     * @param $vId
     * @return mixed
     */
    public function getCustomerById($vId)
    {
        $customer = $this->customerFactory->create();
        return $customer->load($vId);
    }
}
