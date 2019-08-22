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


namespace Ced\CsMembership\Block\Plans\ListBlock;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Collection\AbstractCollection;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\ObjectManagerInterface;

/**
 * Product list
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ListBlock extends \Magento\Framework\View\Element\Template 
{
    /**
     * Default toolbar block name
     *
     * @var string
     */
    protected $_defaultToolbarBlock = 'Ced\CsMembership\Block\Plans\ListBlock\Toolbar';

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_cedCatalogLayer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var $_storeManager
     */
    protected $_storeManager;
    
    /**
     * @var $_vshop
     */
    protected $_vshop;
   
    /**
     * @var \ObjectManagerInterface $_objectManager
     */
    public $_objectManager;

    /**
     * @var $_carriers
     */
    protected $_carriers = null;

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var Vendor Collection
     */
    protected $_vendorCollection;
    
    /**
     * @var \Magento\Framework\Registry $_coreRegistry
     */
    public  $_coreRegistry = null;
    
    /**
     * Estimate Rates
     * @var array
     */
    protected $_rates = array();
    
    protected $_checkout = null;

    protected $_quote    = null;
    /**
     * @param Context $context
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param \Magento\Framework\Url\Helper\Data $urlHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        $this->_cedCatalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->_coreRegistry = $context->getRegistry();
        $this->_objectManager=$objectManager;
        parent::__construct(
            $context,
            $data
        );
    }
    
    /**
     * Retrieve loaded category collection
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getLoadedVendorCollection()
    {
        return $this->_getMembershipCollection();
    }
    
    public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
    
    /**
     * Prepare Sort By fields from Category Data for Vshops
     *
     * @param Mage_Catalog_Model_Category $category
     * @return Mage_Catalog_Block_Product_List
     */
    public function prepareSortableFields() {
        if (!$this->getAvailableOrders()) {
            $this->setAvailableOrders($this->_getConfig()->getAttributeUsedForSortByArray());
        }
        $cedAvailableOrders = $this->getAvailableOrders();
        if (!$this->getSortBy()) {
            if ($defaultSortBy = $this->_getConfig()->getDefaultSortBy()) {
                if (isset($cedAvailableOrders[$defaultSortBy])) {
                    $this->setSortBy($defaultSortBy);
                }
            }
        }
        return $this;
    }

    /**
     * Get catalog layer model
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        return $this->_cedCatalogLayer;
    }   

    /**
     * Retrieve current view mode
     *
     * @return string
     */
    public function getMode()
    {
        $currentMode = $this->getChildBlock('toolbar')->getCurrentMode();
        return $currentMode;
    }

    /**
     * Need use as _prepareLayout - but problem in declaring collection from
     * another block (was problem with search result)
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $toolbar = $this->getToolbarBlock();

        // called prepare sortable parameters
        $collection = $this->_getMembershipCollection();

        // use sortable parameters
        $cedorders = $this->getAvailableOrders();
        if ($cedorders) {
            $toolbar->setAvailableOrders($cedorders);
        }
        $cedsort = $this->getSortBy();
        if ($cedsort) {
            $toolbar->setDefaultOrder($cedsort);
        }
        $ceddir = $this->getDefaultDirection();
        if ($ceddir) {
            $toolbar->setDefaultDirection($ceddir);
        }
        $cedmodes = $this->getModes();
        if ($cedmodes) {
            $toolbar->setModes($cedmodes);
        }

        // set collection to toolbar and apply sort
        $toolbar->setCollection($collection);

        $this->setChild('toolbar', $toolbar);
        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection',
            ['collection' => $this->_getMembershipCollection()]
        );

        $this->_getMembershipCollection()->load();

        return parent::_beforeToHtml();
    }

    /**
     * Retrieve Toolbar block
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function getToolbarBlock()
    {
        $cedBlockName = $this->getToolbarBlockName();
        if ($cedBlockName) {
            $cedBlock = $this->getLayout()->getBlock($cedBlockName);
            if ($cedBlock) {
                return $cedBlockName;
            }
        }
        $cedBlockName = $this->getLayout()->createBlock($this->_defaultToolbarBlock, uniqid(microtime()));
        return $cedBlockName;
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        $cedAdditionalHtml = $this->getChildHtml('additional');
        return $cedAdditionalHtml;
    }

    /**
     * Retrieve list toolbar HTML
     *
     * @return string
     */
    public function getToolbarHtml()
    {
        $cedToolbar = $this->getChildHtml('toolbar');
        return $cedToolbar;
    }

    /**
     * @param Set AbstractCollection $collection
     * @return $this
     */
    public function setCollection($collection)
    {
        $this->_productCollection = $collection;
        return $this;
    }

    public function getAttributeUsedForSortByArray()
    {
        $options = array(__('Name'));
      

        return $options;
    }

}
