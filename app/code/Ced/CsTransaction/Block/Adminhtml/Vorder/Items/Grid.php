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
namespace Ced\CsTransaction\Block\Adminhtml\Vorder\Items;
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{

	/**
     * Constructor
     */
	protected $moduleManager;
    protected $_vpaymentFactory;
    public $_objectManager;
	protected $_vtorders;

	public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Ced\CsTransaction\Model\Items $vtorders,
        \Ced\CsMarketplace\Model\Vorders $vorders,
        \Ced\CsMarketplace\Helper\Data $helperData,
        \Ced\CsOrder\Model\Invoice $invoice,
        array $data = []
    ) {
		
        $this->_objectManager = $objectManager;
        $this->_vtorders=$vtorders;
        $this->_vorders = $vorders;
        $this->_invoice = $invoice;
        $this->_csMarketplaceHelper = $helperData;
        parent::__construct($context, $backendHelper, $data);
    }

	public function _construct()
    {
	  parent::_construct();
	  $this->setId('vorder_itemsGrid');
	  $this->setDefaultSort('created_at');
	  $this->setDefaultDir('DESC');
	  $this->setSaveParametersInSession(true);
	  $this->setUseAjax(true);
    }
	
	/**
	 * Get current store
	 * @return Mage_Core_Model_Store
	 */
	/* public function getStore() {
		$storeId = (int) Mage::app()->getRequest()->getParam('store', 0);
        if($storeId)
			return Mage::app()->getStore($storeId);
		else 
			return Mage::app()->getStore();
	 }*/

	/**
     * Prepare Mass Action
     *
     * @return object
     */
	protected function _prepareMassaction(){
		return $this;
	}

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
	  protected function _addColumnFilterToCollection($column)
	  {
	  	if ($this->getCollection()) {
	  		$field = ( $column->getFilterIndex() ) ? $column->getFilterIndex() : $column->getIndex();
	  		if ($column->getFilterConditionCallback()) {
	  			call_user_func($column->getFilterConditionCallback(), $this->getCollection(), $column);
	  		} else {
	  			$cond = $column->getFilter()->getCondition();
	  			if ($field && isset($cond)) {
	  				$this->getCollection()->addFieldToFilter($field , $cond);
	  			}
	  		}
	  	}
	  	return $this;
	  }

    /**
     * @param bool $flag
     * @return $this
     */
	protected function _prepareCollection($flag = false){

		$vendor_id = $this->getRequest()->getParam('vendor_id',0);
		if(!$flag) {
			$orderTable = $this->_resource->getTableName('sales/order');
			if( $this->_objectManager->get('\Ced\CsOrder\Helper\Data')->isActive()){
				$collection = $this->_vtorders->getCollection();
				if($vendor_id) {
					$collection->addFieldToFilter('vendor_id',array('eq'=>$vendor_id));
				}
				$main_table=$this->_csMarketplaceHelper->getTableKey('main_table');
				$item_fee=$this->_csMarketplaceHelper->getTableKey('item_fee');
				$qty_ready_to_pay=$this->_csMarketplaceHelper->getTableKey('qty_ready_to_pay');
				$collection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$item_fee} * {$main_table}.{$qty_ready_to_pay})")));
				//$collection->getSelect()->columns(array('net_vendor_return' => new \Zend_Db_Expr("({$main_table}.{$item_fee} * {$main_table}.{$qty_ready_to_refund})")));
				//$collection->getSelect()->columns(array('net_vendor_return' => new \Zend_Db_Expr("({$main_table}.{$amount_ready_to_refund}")));
				$collection->getSelect()->joinLeft($orderTable , 'main_table.order_id ='.$orderTable.'.increment_id',array('*'));
				$this->setCollection($collection);
			
			}
			else
			{
				$collection = $this->_vorders->getCollection();
				if($vendor_id) {
					$collection->addFieldToFilter('vendor_id',array('eq'=>$vendor_id));
				}
				$main_table=$this->_csMarketplaceHelper->getTableKey('main_table');
				$order_total=$this->_csMarketplaceHelper->getTableKey('order_total');
				$shop_commission_fee=$this->_csMarketplaceHelper->getTableKey('shop_commission_fee');
				$collection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$order_total} - {$main_table}.{$shop_commission_fee})")));
				
				$collection->getSelect()->join($orderTable ,'main_table.order_id LIKE  CONCAT("%",'.$orderTable.".increment_id".' ,"%")',array('*'));
				$this->setCollection($collection);
			}
		}
		
		//return $this;
		return parent::_prepareCollection();
	}
	/**
     * Prepare columns
     *
     * @return object
     */
  protected function _prepareColumns(){
	
		if($this->_objectManager->get('\Ced\CsOrder\Helper\Data')->isActive()){
			$this->addColumn('created_at', array(
				'header' => __('Purchased On'),
				'index' => 'created_at',
				'type' => 'datetime',
				'width' => '100px',
			));
			
			$this->addColumn('increment_id', array(
				'header'    => __('Order ID#'),
				'align'     => 'left',
				'index'     => 'increment_id',
				'filter_index'  => 'increment_id',
				'renderer' => 'Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid\Renderer\Orderid'
				)); 
			/*$this->addColumn('vendor_id', array(
				'header'    => __('Vendor Name'),
				'align'     => 'left',
				'index'     => 'vendor_id',
				'renderer' => 'Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid\Renderer\Vendorname',
				'filter_condition_callback' => array($this, '_vendornameFilter'),
				));*/
			
			$this->addColumn('qty_ordered',array(
				'header'=> __('Qty Ordered'),
				'index' => 'qty_ordered',
				
				));
			$this->addColumn('qty_paid',array(
			'header'=> __('Qty Paid'),
			'index' => 'qty_paid',
			
			));
			$this->addColumn('qty_refunded',array(
			'header'=> __('Qty Refunded'),
			'index' => 'qty_refunded',
			
			));
		
			$this->addColumn('qty_ready_to_pay',array(
					'header'=> __('Qty Ready To Pay'),
					'index' => 'qty_ready_to_pay',
				
					 'currency' =>'currency',
			));
			
			$this->addColumn('qty_ready_to_refund',array(
					'header'=> __('Qty Ready To Refund'),
					'index' => 'qty_ready_to_refund',
				
					 'currency' =>'currency',
			));		
			$this->addColumn('net_vendor_earn',array(
					'header'=> __('Amount Ready To Pay'),
					'index' => 'net_vendor_earn',
					'type'          => 'currency',
					 'currency' =>'currency',
			));
			$this->addColumn('amount_ready_to_refund',array(
					'header'=> __('Amount Ready To Return'),
					'index' => 'amount_ready_to_refund',
					'type'          => 'currency',
					 'currency' =>'currency',
			));

			
			
			 $this->addColumn('order_payment_state', array(
				'header'    => __('Order Payment State'),
				'index'     => 'order_payment_state',
				'filter_index'  => 'order_payment_state',
				'type'      => 'options',
				'options'   =>  $this->_invoice->getStates(),
			));
			
			/*$this->addColumn('payment_state', array(
				'header'    => __('Vendor Payment State'),
				'index'     => 'payment_state',
				'filter_index'  => 'payment_state',
				'type'      => 'options',
				'options'   => $this->_vtorders->getStates(),
				'renderer' => 'Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid\Renderer\Paynow',
			));*/
			
		  /* $this->addColumn('action',
				array(
					'header'    => Mage::helper('cstransaction')->__('Action'),
					'width'     => '50px',
					'type'      => 'action',
					'getter'     => 'getId',
					'renderer' => 'Ced_CsTransaction_Block_Adminhtml_Vorder_Items_Grid_Renderer_View',
					'filter'    => false,
					'sortable'  => false
			)); */
			return parent::_prepareColumns();
		}
		else
		{
			
			$this->addColumn('relation_id', array(
				'header'    => __('ID'),
				'align'     =>'right',
				'width'     => '50px',
				'index'     => 'id'
				));
			$this->addColumn('order_id', array(
				'header'    =>__('Order ID#'),
				'align'     => 'left',
				'index'     => 'order_id',
				'filter_index'  => 'order_id',
				'renderer' => 'Ced\CsMarketplace\Block\Adminhtml\Vorders\Grid\Renderer\Orderid'
				)); 
			$this->addColumn('row_total',array(
				'header'=> __('G.T.'),
				'index' => 'row_total',
				'type'          => 'currency',
				 'currency' =>'currency',
				));
			
				
			$this->addColumn('shop_commission_fee',array(
				'header'=> __('Commission Fee'),
				'index' => 'shop_commission_fee',
				'type'          => 'currency',
				 'currency' =>'currency',
				
				)); 
			$this->addColumn('net_vendor_earn',array(
					'header'=> __('Vendor Payment'),
					'index' => 'net_vendor_earn',
					'type'          => 'currency',
					 'currency' =>'currency',
			));
            return parent::_prepareColumns();
		}
 	 }
  
	/**
     * Prepare Layout
     *
     * @return object
     */
	  protected function _prepareLayout()
	  {
		  $head = $this->getLayout()->getBlock('head');
		  if(is_object($head)){
			$this->getLayout()->getBlock('head')->addJs('ced/cstransaction/adminhtml/popup.js');
		  }
		  return parent::_prepareLayout();
	  }

    /**
     * @param $collection
     * @param $column
     * @return $this
     */
	  protected function _vendornameFilter($collection, $column){
        if (!$value = $column->getFilter()->getValue()) {
            return $this;
        }
        $vendorIds =   $this->_objectManager->get('Ced\CsMarketplace\Model\vendor')->getCollection()
        ->addAttributeToFilter('name', array('like' => '%'.$column->getFilter()->getValue().'%'))
        ->getAllIds();
 
        if(count($vendorIds)>0)
            $this->getCollection()->addFieldToFilter('vendor_id', array('in', $vendorIds));
        else{
            $this->getCollection()->addFieldToFilter('vendor_id');
        }   
            return $this;
    }
		/**
		 * Get Row Url
		 *
		 * @return string
		 */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
 
    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return 'javascript:void(0);';
    }
}
