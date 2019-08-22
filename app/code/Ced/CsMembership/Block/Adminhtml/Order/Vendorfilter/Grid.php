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

namespace Ced\CsMembership\Block\Adminhtml\Order\Vendorfilter;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
 
    /**
     * @var \\Ced\CsMarketplace\Model\VendorFactory
     */
    protected $_vendorFactory;
    
    protected $_websiteFactory;
    
    protected $_group;
    
    protected $_status;
 
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsMarketplace\Model\VendorFactory $vendorFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsMembership\Model\SubscriptionFactory $membershipFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        array $data = []
    ){
        $this->_membershipFactory = $membershipFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('membershipvendorbyGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('vendor_filter');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_membershipFactory->create()->getCollection();
        $filter = $this->getRequest()->getParams();
        if(isset($filter['id']) && $filter['id'] != ''){
            $collection->addFieldToFilter('vendor_id',$this->getRequest()->getParam('id'));
        }
                        
        $this->setCollection($collection);
        return  parent::_prepareCollection();
    }
 
    protected function _addColumnFilterToCollection($column)
    {
        return parent::_addColumnFilterToCollection($column);
    }
 
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn('id', [
                'header'    => __('ID #'),
                'align'     =>'right',
                'index'     => 'id',
                "width"     => "80px",
                'type'      => 'text'
            ]
        );

        $this->addColumn("name", [
                "header" => __("Title"),
                "index" => "name",
                "width" => "100px"
                ]
        );

        $this->addColumn("product_limit", [
                "header" => __("Product Limit"),
                "index" => "product_limit",
                "width" => "100px"
                ]
        );
                
        $this->addColumn("category_ids", [
                "header" => __("Allowed Category"),
                'renderer' => 'Ced\CsMembership\Block\Adminhtml\Order\Vendorfilter\Renderer\Category',
                "index" => "category_ids",
                ]
        );

        $this->addColumn('start_date', [
                'header'    => __('Start on'),
                'index'     => 'start_date',
                "width"     => "100px",
                'type'      => 'date'
            ]
        );

        $this->addColumn('end_date', [
                'header'    => __('End on'),
                'index'     => 'end_date',
                "width"     => "100px",
                'type'      => 'date'
            ]
        );

        $store = $this->_getStore();
        $this->addColumn(
            'price',
            [
                'header' => __('Package Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        $this->addColumn(
            'special_price',
            [
                'header' => __('Package Special Price'),
                'type' => 'price',
                'currency_code' => $store->getBaseCurrency()->getCode(),
                'index' => 'special_price',
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );


        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'width' => '70px',
                'options' => \Ced\CsMembership\Block\Adminhtml\Order\Vendorfilter\Grid::getStatus()
            ]
        );
        
        
 
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
 
        return parent::_prepareColumns();
    }
 

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }
 
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/ordergrid', ['_current' => true]);
    }


    static function getStatus()
    {
        return array('running' => 'running','expire' => 'expire');
    }
}
