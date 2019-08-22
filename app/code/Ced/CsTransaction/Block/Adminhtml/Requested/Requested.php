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
 * @package     Ced_CsTransaction
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsTransaction\Block\Adminhtml\Requested;
class Requested extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
 
    /**
     * @var \Ced\CsMarketplace\Model\VPaymentsFactory
     */
    protected $_vpaymentFactory;
 
    /**
     * @var \Ced\CsMarketplace\Model\Status
     */
   // protected $_status;
    public $_objectManager;

    /**
     * Requested constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Ced\CsMarketplace\Model\VordersFactory $vordersFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param \Ced\CsMarketplace\Helper\Data $helperData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Ced\CsMarketplace\Model\Vorders $vorders
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param array $data
     */
    public function __construct(
            \Magento\Backend\Block\Template\Context $context,
            \Magento\Backend\Helper\Data $backendHelper,
            \Ced\CsMarketplace\Model\VordersFactory $vordersFactory,
            \Magento\Framework\Module\Manager $moduleManager,
            \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
            \Magento\Sales\Model\Order\Invoice $invoice,
            \Ced\CsMarketplace\Helper\Data $helperData,
            \Magento\Framework\App\ResourceConnection $resource,
            \Ced\CsMarketplace\Model\Vorders $vorders,
            \Magento\Framework\ObjectManagerInterface $objectManager,
            array $data = []
        ) {
            $this->_vordersFactory = $vordersFactory;
            $this->pageLayoutBuilder = $pageLayoutBuilder;
            $this->moduleManager = $moduleManager;
            $this->_resource = $resource;
            $this->_invoice = $invoice;
            $this->_vorders = $vorders;
            $this->_objectManager = $objectManager;
            $this->_csMarketplaceHelper = $helperData;

            parent::__construct($context, $backendHelper, $data);
        }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('postGrid');
        $this->setDefaultSort('created_');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('post_filter');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $vendor_id = $this->getRequest()->getParam('vendor_id',0);
        $collection = $this->_objectManager->get('Ced\CsMarketplace\Model\Vpayment')->getCollection();
        if($vendor_id) {

            $collection->addFieldToFilter('vendor_id',array('eq'=>$vendor_id));
        }
        
        $collection = $this->_objectManager->get('Ced\CsMarketplace\Model\Vpayment\Requested')
                            ->getCollection();
        
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }
 
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
            
		$this->addColumn('created_at', array(
            'header' => __('Request Date'),
            'index' => 'created_at',
            'type' => 'datetime',
            'width' => '100px',
        ));
        
        $this->addColumn('vendor_id', array(
                'header'    => __('Vendor Name'),
                'align'     => 'left',
                'index'     => 'vendor_id',
                'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Vendorname',
                'filter_condition_callback' => array($this, '_vendornameFilter'),
            ));
            
        $this->addColumn('order_id', array(
                'header'    => __('Order IDs#'),
                'align'     => 'left',
                'index'     => 'order_id',
                'renderer'=> 'Ced\CsTransaction\Block\Adminhtml\Requested\Renderer\Orderdesc',
        ));
        
        
        
        $this->addColumn('amount',
            array(
                'header'=> __('Amount To Pay'),
                'index' => 'amount',
                'type'          => 'currency',
                'currency' => 'base_currency'
        ));
        
        $this->addColumn('status', array(
                'header'    => __('Status'),
                'index'     => 'status',
                'filter_index'  => 'status',
                'type'      => 'options',
                'options'   => \Ced\CsMarketplace\Model\Vpayment\Requested::getStatuses(),
                'renderer' =>'Ced\CsTransaction\Block\Adminhtml\Requested\Renderer\Paynow',
                'filter_condition_callback' => array($this, '_requestedFilter'),
            ));
            
        return parent::_prepareColumns();
    }

 
    /**
     * After load collection
     *
     * @return void
     */
    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }


/*    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }
    public function getGridUrl()
    {
        return $this->getUrl('cstransaction/vpayments/index', ['_current' => true]);
    }*/

    protected function _vendornameFilter($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $vendors = $this->_objectManager->get('\Ced\CsMarketplace\Model\VendorFactory')->create()->getCollection()
                        ->addAttributeToFilter('name',['like'=>$value.'%']);
        $vendor_id = array();
        foreach ($vendors as $_vendor) {
                $vendor_id[] = $_vendor->getId();
            }        
        $this->getCollection()->addFieldToFilter('vendor_id',array('eq'=>$vendor_id));
    }

    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addStoreFilter($value);
    }

    public function getGridUrl()
    {
        return $this->getUrl('cstransaction/vpayments/grid', ['_current' => true]);
    }

    protected function _requestedFilter($collection, \Magento\Framework\DataObject $column)
    {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->addFieldToFilter('status',array('eq'=>$column->getFilter()->getValue()));
    }

}
