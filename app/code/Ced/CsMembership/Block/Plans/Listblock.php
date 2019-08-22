<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Block\Plans;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
 use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;

class Listblock extends 
//\Ced\CsMarketplace\Block\Vendor\AbstractBlock
//\Ced\CsMarketplace\Block\Vshops\ListBlock\ListBlock
\Ced\CsMembership\Block\Plans\ListBlock\ListBlock
{
    protected $_subscription = null;
    protected $_membershipCollection;
    protected $_defaultColumnCount = 3;
    public $_objectManager;
    public $_scopeConfig;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        array $data = []
    ) {
        $this->_cedCatalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->_coreRegistry = $context->getRegistry();
        $this->_objectManager=$objectManager;
        $this->customerSession = $customerSession;
        parent::__construct(
            $context,
            $postDataHelper,
            $layerResolver,
            $categoryRepository,
            $urlHelper,
           /* $context->getRegistry(),*/
            $objectManager,
            $data
        );
    }

    public function setCollection($collection)
    {
        $this->_membershipCollection = $collection;
        return $this;
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function setColumnCount($count = 3)
    {
        return $this->_defaultColumnCount = $count;
    }

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getColumnCount()
    {
        return $this->_defaultColumnCount;
    }

    public function getLoadedMembershipCollection()
    {
        return $this->_getMembershipCollection();
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->_getMembershipCollection()) {
            $limit=$this->getRequest()->getParam('product_list_limit');
            if($limit=="")
                $limit=10;
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'membership.pager'
            )->setLimit($limit)
            ->setCollection(
                $this->_getMembershipCollection()
            );

            $this->setChild('pager', $pager);
            $this->_getMembershipCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    protected function _getMembershipCollection()
    {
    if (is_null($this->_membershipCollection)) {
        $filter = $this->getRequest()->getParams();
         $this->_membershipCollection = $this->_objectManager->create('Ced\CsMembership\Helper\Data')->getMembershipPlans();
        if(isset($filter['product_list_order']) || isset($filter['product_list_dir']))
        {                             
            $this->_membershipCollection = $this->_membershipCollection->setOrder('name',
                                                   $this->getRequest()->getParam('product_list_dir')
                                            );
        }else
            $this->_membershipCollection = $this->_membershipCollection->setOrder('name','asc');    
    }
    return $this->_membershipCollection;
    }   

     protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getMembershipCollection();

        // use sortable parameters
        if ($orders = $this->getAvailableOrders()) {
            $toolbar->setAvailableOrders($orders);
        }
        if ($sort = $this->getSortBy()) {
            $toolbar->setDefaultOrder($sort);
        }
        if ($dir = $this->getDefaultDirection()) {
            $toolbar->setDefaultDirection($dir);
        }
        if ($modes = $this->getModes()) {
            $toolbar->setModes($modes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        
        $this->_getMembershipCollection()->load();

        return parent::_beforeToHtml();
    }

    public function getAssignedMembershipCollection()
    {
        $vendor_id = $this->customerSession->getvendorId();
        $collection = $this->_objectManager->create('Ced\CsMembership\Model\Subscription')->getCollection()->addFieldToFilter('vendor_id',$vendor_id)->addFieldToSelect('subscription_id');
        $subscription = array();
        foreach ($collection as $key => $value) {
            $subscription[] = $value->getSubscriptionId();
        }
        return $subscription;

    }

    public function getAttributeUsedForSortByArray()
    {
        $options = array(__('Name'));
        // foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            // /* @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
            // $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        // }

        return $options;
    }
}
