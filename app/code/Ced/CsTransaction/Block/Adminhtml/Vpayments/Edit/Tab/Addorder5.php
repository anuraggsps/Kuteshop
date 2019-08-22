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
namespace Ced\CsTransaction\Block\Adminhtml\Vpayments\Edit\Tab;

class Addorder extends \Ced\CsMarketplace\Block\Adminhtml\Vpayments\Edit\Tab\Addorder
{
  protected $_availableMethods = null;
  protected $_vendor;
  protected $_currencyInterface;
  protected $_urlBuilder;
  protected $_formFactory;
  protected $_vorders;
  protected $_objectManager;
  protected $_vtorders;
  protected $_resourceCollection;

    public function __construct(\Magento\Backend\Block\Template\Context $context,
    	\Ced\CsMarketplace\Model\Vendor $vendor,
    	\Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory  $collectionFactory,
    	\Ced\CsMarketplace\Model\Vorders $vorders,
    	\Magento\Framework\Locale\CurrencyInterface $localeCurrency,
    	\Magento\Backend\Model\Url $urlBuilder,
    	\Magento\Framework\Data\FormFactory $formFactory,
    	\Ced\CsTransaction\Model\Items $item,
	    \Ced\CsTransaction\Helper\Data $helper,
	    \Ced\CsMarketplace\Helper\Data $helperData,
	    \Magento\Framework\ObjectManagerInterface $objectManager,
    	array $data = [])
    {

	    $this->_objectManager = $objectManager;  
	    $this->_vorders = $vorders; 
	    $this->_vendor = $vendor;
	    $this->_resourceCollection = $collectionFactory;
	    $this->_formFactory = $formFactory;
	    $this->_currencyInterface = $localeCurrency;
	    $this->_urlBuilder = $urlBuilder;
	    $this->_vtorders = $item;
		$this->helper = $helper;
		$this->_csMarketplaceHelper = $helperData;
		// print_r($this->_resourceCollection->create()->toOptionArray());die('sdf');
    	parent::__construct($context, $objectManager, $vendor, $vorders, $localeCurrency, $urlBuilder, $formFactory, $data);
    	if($this->_objectManager->get('\Ced\CsOrder\Helper\Data')->isActive())
			{
				$this->setTemplate('Ced_CsMarketplace::vpayments/edit/tab/addorder.phtml');

			}
    	
    
    }

	/*public function __construct(\Magento\Backend\Block\Template\Context $context,
	    \Magento\Framework\ObjectManagerInterface $objectManager,
	    \Ced\CsMarketplace\Model\Vendor $vendor,
	    \Ced\CsMarketplace\Model\Vorders $vorders,
	    \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
	    \Magento\Backend\Model\Url $urlBuilder,
	    \Magento\Framework\Data\FormFactory $formFactory,
	    \Ced\CsTransaction\Model\Items $item,
	    \Ced\CsTransaction\Helper\Data $helper,
	    \Ced\CsMarketplace\Helper\Data $helperData,
	    array $data = []){
		    $this->_objectManager = $objectManager;
		    $this->_vendor = $vendor;
		    $this->_vorders = $vorders;
		   	$this->_currencyInterface = $localeCurrency;
		   	$this->_urlBuilder = $urlBuilder;
		    $this->_formFactory = $formFactory;
		    $this->_vtorders = $item;
			$this->helper = $helper;
			$this->_csMarketplaceHelper = $helperData;
		    if($this->_objectManager->get('\Ced\CsOrder\Helper\Data')->isActive())
			{
				//$this->setTemplate('Ced_CsMarketplace::vpayments/edit/tab/addorder.phtml');

			}
			else
			{
				parent::__construct($context, $objectManager, $vendor, $vorders, $localeCurrency, $urlBuilder, $formFactory, $data);
			}
    }*/




	/**
     * Round price
     *
     * @param mixed $price
     * @return double
     */
    public function roundPrice($price)
    {
      return $price;
    }
	/**
     * Available Methods
     *
     * @return array
     */
	  public function availableMethods() {
	    if($this->_availableMethods == null) {
	      $vendorId = $this->getRequest()->getParam('vendor_id',0);
	      $this->_availableMethods = $this->_vendor->getPaymentMethodsArray($vendorId);
	    }
	    return $this->_availableMethods;
	  }
	
	/**
     * Prepare Layout
     */
	protected function _prepareLayout()
    {
        $this->setChild('csmarketplace_continue_button',
            $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')
                ->setData(array(
                    'label'     => __('Continue'),
                    'onclick'   => "setSettings('".$this->getContinueUrl()."','vendor_id')",
                    'class'     => 'save primary'
                    ))
                );
        return parent::_prepareLayout();
    }
	
	/**
     * Get Countinue Url
     * @return string
     */	
  public function getContinueUrl()
    {
    	
        return $this->_urlBuilder->getUrl('*/*/*', array(
            '_current'  => true,
            '_secure' => true,
            'vendor_id' => '{{vendor_id}}', 
        ));
    }
	
	/**
     * get html for buttons
     *
     * @return string
     */
	public function getButtonsHtml()
	{
		$addButtonData = array(
				'label' => __('Add/Remove Amount(s) for Payment'),
				'onclick' => "this.parentNode.style.display = 'none'; document.getElementById('order-search').style.display = ''",
				'class' => 'add',
		);

		return $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Button')->setData($addButtonData)->toHtml();
	}
	
	/**
     * prepare html for notice block
     *
     * @return string
     */
	protected function noticeBlock() {
	    if(count($this->availableMethods()) == 0) {
	      return '<div>
	              <ul class="messages">
	                  <li class="notice-msg">
	                      <ul>
	                          <li>'.__("Can't continue with payment,because vendor did not specify payment method(s).").'</li>
	                      </ul>
	                  </li>
	              </ul>
	            </div>';
	    }
	    return '';
	    
	  }
	
	/**
     * prepare html for search form
     *
     * @return string
     */
	public function getSearchFormHtml() {

		if($this->_objectManager->get('\Ced\CsOrder\Helper\Data')->isActive())
		{  
			$form = $this->_formFactory->create(); 
			$fieldset = $form->addFieldset('form_fields', array('legend'=>__('Beneficiary  Information')));
			$vendorId = $this->getRequest()->getParam('vendor_id',0);
			$message = __("Are you sure to change the vendor Because it will change the Selected Amount(s) for Payment section.");
			$params = $this->getRequest()->getParams();
			$type = isset($params['type']) && in_array($params['type'],array_keys(\Ced\CsMarketplace\Model\Vpayment::getStates()))?$params['type']:\Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT;
			
			   $id = $fieldset->addField('vendor_id', 'select', array(
                'label'     => __('Beneficiary Vendor'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'vendor_id',
                'script'    => 'var cs_ok = 0;',
                'onchange'  => "document.getElementById('order-items').innerHTML=''; document.getElementById('order-search').innerHTML=''; setLocation('".$this->_urlBuilder->getUrl('*/*/*',array('type'=>$type))."vendor_id/'+this.value);",
                'value'   => $vendorId,
                'values' => $this->_resourceCollection->create()->toOptionArray(),//$this->_vendor->getCollection()->toOptionArray(),
                'after_element_html' => '<small>Vendor selection will change the <b>"Selected Amount(s) for Payment"</b> section.</small>',
              ));

									
			$params = $this->getRequest()->getParams();
			
			 $type = isset($params['type']) && in_array(trim($params['type']),array_keys(\Ced\CsMarketplace\Model\Vpayment::getStates()))?trim($params['type']):\Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT;

			$relationIds = isset($params['order_ids'])? explode(',',trim($params['order_ids'])):array();
			$collection = $this->_vtorders->getCollection()->addFieldToFilter('vendor_id',array('eq'=>$vendorId));
			$main_table = $this->_csMarketplaceHelper->getTableKey('main_table');
			$item_fee = $this->_csMarketplaceHelper->getTableKey('item_fee');
			$qty_ready_to_pay = $this->_csMarketplaceHelper->getTableKey('qty_ready_to_pay');
			$qty_ready_to_refund = $this->_csMarketplaceHelper->getTableKey('qty_ready_to_refund');
			$item_commission = $this->_csMarketplaceHelper->getTableKey('item_commission');

			if($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {

				if(!$this->getRequest()->getParam('order_id_for_ship'))
					$collection->addFieldToFilter('qty_ready_to_refund',array('gt'=>0));//deepakhere
				$collection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$item_fee} * {$main_table}.{$qty_ready_to_refund})")));
				$collection->getSelect()->columns(array('commission_fee' => new \Zend_Db_Expr("({$main_table}.{$item_commission} * {$main_table}.{$qty_ready_to_refund})")));
						   
			} else{
				if(!$this->getRequest()->getParam('order_id_for_ship'))
					$collection->addFieldToFilter('qty_ready_to_pay',array('gt'=>0));
				$collection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$item_fee} * {$main_table}.{$qty_ready_to_pay})")));
				$collection->getSelect()->columns(array('commission_fee' => new \Zend_Db_Expr("({$main_table}.{$item_commission} * {$main_table}.{$qty_ready_to_pay})")));
						 
			}
			
			$collection = $collection->addFieldToFilter('id',array('in'=>$relationIds));
			

			$renderer = $this->_objectManager->get('\Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid\Renderer\Orderid');
			
			
			//$collection->getSelect()->columns(array('commission_fee' => new Zend_Db_Expr("({$main_table}.{$item_commission} * {$main_table}.{$qty_ready_to_pay})")));

			$html="";		
			$html.='<div class="entry-edit">
						<div class="entry-edit-head">
							<div id="csmarketplace_add_more" style="float: right;">'.$this->getButtonsHtml().'</div>
							<h4 class="icon-head head-cart">'.__("Selected Amount(s) for Payment").'</h4>
						</div>
						<div class="grid" id="order-items_grid">
							<table cellspacing="0" class="data order-tables">
								<thead>
									<tr class="headings">
										<th class="no-link">'.__("Order ID").'</th>';
			if($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {	
								
				$html.='		            <th class="no-link">'.__("Returning Qty") .'</th>
											 <th class="no-link">'.__("Commission Fee").'</th>
										<th class="no-link">'.__("Vendor Refund").'</th>
										<th class="no-link">'.__("Include Shipping").'</th>
									</tr>
								</thead>
								<tbody>
				';
			}
			else
			{
				
				
				$html.='		            <th class="no-link">'.__("Paying Qty") .'</th>
											 <th class="no-link">'.__("Commission Fee").'</th>
										<th class="no-link">'.("Vendor Payment").'</th>
										<th class="no-link">'.__("Include Shipping").'</th>
									</tr>
								</thead>
								<tbody>
				';
			}
			$amount = 0.00;
			$shippingAmountPrice = 0.00;
			$class = '';
			$arrayShipping=array();

			foreach ($collection as $item)
			{	
				$class = ($class == 'odd')? 'even':'odd';
				$html .= '<tr class="'.$class.'"';
				$html.='>';

				$html .= '<td><center>'.$renderer->render($item).'</center></td>';
				if($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {
					$html .= '<td><center>'.$item->getQtyReadyToRefund().'</center></td>';
				}
				else
				{
					$html .= '<td><center>'.$item->getQtyReadyToPay().'</center></td>';
				}

					$html .= '<td><center>'.$this->_currencyInterface
                    ->getCurrency($item->getCurrency())
                    ->toCurrency($item->getCommissionFee()).'</center></td>';
					$html .= '<td><center>'.$this->_currencyInterface
                    ->getCurrency($item->getCurrency())
                    ->toCurrency($item->getNetVendorEarn());
				
						
				$amount += $item->getNetVendorEarn();


				$html .= '<input id="csmarketplace_vendor_orders_'.$item->getId().'" type="hidden" value="'.$this->roundPrice($this->roundPrice($item->getNetVendorEarn() + $item->getCommissionFee())).'" name="orders['.$item->getOrderId().']['.$item->getId().']"/>';

				$html .= '<input id="csmarketplace_vendor_commission_'.$item->getId().'" type="hidden" value="'.$this->roundPrice($item->getCommissionFee()).'" name="comissionfee['.$item->getOrderId().']['.$item->getId().']"/>';

				

						
				$html .= '</center></td>';
				//print_r($arrayShipping[$item->getParentId()]);
				if(isset($arrayShipping[$item->getParentId()]))
				{

					$html.='<td><center></center></td>';
				}
				else
				{
					$vorder=$this->_vorders->load($item->getParentId());
					//print_r($vorder->getData());
					$shippingAmount=$this->helper->getAvailableShipping($vorder,$type);
					
					$shippingAmountPrice += $shippingAmount;//added

					
					if((float)$shippingAmount!=0)
					{

						$arrayShipping[$item->getParentId()]=$item->getParentId();
						$html.='<td><input onclick="chooseShippingAmount(this,'.$item->getParentId().');" type="checkbox" checked="checked" name="shippingcheck['.$item->getParentId().']" value="1"><input type="text" readonly="true" class="validate-number-range number-range-0-'.$shippingAmount.'" name="shippings['.$item->getParentId().']" value="'.$shippingAmount.'" id="shippings_'.$item->getParentId().'"></td>';
					}
					else
					{
						$html.='<td><center>'.$shippingAmount.'</center></td>';
					}
				}
				$html .= '</tr>';
				
			}
			 
			$html.=       ' </tbody></table>
						   </div>
			</div>
			<script>
			function chooseShippingAmount(e, id){
				var amount = document.getElementById("csmarketplace_vendor_total").value;
					var shippAmount = document.getElementById("shippings_"+id).value;
				if(e.checked){
					document.getElementById("csmarketplace_vendor_total").value = parseFloat(amount) + parseFloat(shippAmount);
				}
				else{
					document.getElementById("csmarketplace_vendor_total").value = parseFloat(amount) - parseFloat(shippAmount);
				}

				
			}
			</script>
			';		

			$amount +=	$shippingAmountPrice;

			$fieldset->addField('csmarketplace_vendor_total', 'text', array(
								  'label'     => __('Total Amount'),
								  'class'     => 'required-entry validate-greater-than-zero',
								  'required'  => true,
								  'name'      => 'total',
								  'value'	  => $this->roundPrice($amount),
								  'readonly'  => 'readonly',
								 /* 'after_element_html' => '<b>['.Mage::app()->getBaseCurrencyCode().']</b><small><i> Readonly field</i>.</small>',*/
								  ));
			
			return array($this->noticeBlock().$form->toHtml(),$html);
		}
		else
		{
			return parent::getSearchFormHtml();
		}
	}
	
	
	/**
     * Prepare html for add order
	 *
     * @return string
     */
	public function getAddOrderBlock() {

		$main_table=$this->_csMarketplaceHelper->getTableKey('main_table');
		$item_fee=$this->_csMarketplaceHelper->getTableKey('item_fee');
		$qty_ready_to_pay=$this->_csMarketplaceHelper->getTableKey('qty_ready_to_pay');
		$qty_ready_to_refund=$this->_csMarketplaceHelper->getTableKey('qty_ready_to_refund');
		$item_commission=$this->_csMarketplaceHelper->getTableKey('item_commission');
		if($this->_objectManager->get('Ced\CsOrder\Helper\Data')->isActive())
		{

			$params = $this->getRequest()->getParams();
			


			//$relationIds = isset($params['order_ids'])?$params['order_ids']:'';
			//$relationIds = explode(',',$relationIds);
			//if(!is_array($relationIds))
				
			//print_r($relationIds);

			$relationIds = isset($params['order_ids'])? $params['order_ids']:array();
			
			$vendorId = $this->getRequest()->getParam('vendor_id',0);
			$params = $this->getRequest()->getParams();
			$type = isset($params['type']) && in_array($params['type'],array_keys(\Ced\CsMarketplace\Model\Vpayment::getStates()))?$params['type']:\Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT;
			$collection = $this->_vtorders
								->getCollection()
								->addFieldToFilter('vendor_id',array('eq'=>$vendorId));

			if($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {
				$collection->addFieldToFilter('qty_ready_to_refund',array('gt'=>0));
				$collection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$item_fee} * {$main_table}.{$qty_ready_to_refund})")));
				$collection->getSelect()->columns(array('commission_fee' => new \Zend_Db_Expr("({$main_table}.{$item_commission} * {$main_table}.{$qty_ready_to_refund})")));
						   
			} else{
				$collection->addFieldToFilter('qty_ready_to_pay',array('gt'=>0));
				$collection->getSelect()->columns(array('net_vendor_earn' => new \Zend_Db_Expr("({$main_table}.{$item_fee} * {$main_table}.{$qty_ready_to_pay})")));
				$collection->getSelect()->columns(array('commission_fee' => new \Zend_Db_Expr("({$main_table}.{$item_commission} * {$main_table}.{$qty_ready_to_pay})")));
						 
			}
			

			$collection = $collection->addFieldToFilter('id',array('in'=>$relationIds)); 

			$renderer = $this->_objectManager->get('Ced\CsTransaction\Block\Adminhtml\Vorder\Items\Grid\Renderer\Orderid');
			
			
			$html="";
			$html.='<table cellspacing="0" class="data order-tables">
								<thead>
									<tr class="headings">
										<th class="no-link">'.__("Order ID").'</th>';
			if($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {							
				$html.='		            <th class="no-link">'.__("Returning Qty") .'</th>
										<!--
											 <th class="no-link">'.__("Commission Fee").'</th>
										<th class="no-link">'.__("Vendor Refund").'</th>
										-->
										<th class="no-link">'.__("Include Shipping").'</th>
									</tr>
								</thead>
								<tbody>
				';
			}
			else
			{
				$html.='		            <th class="no-link">'.__("Paying Qty") .'</th>
				<!--
											 <th class="no-link">'.__("Commission Fee").'</th>
										<th class="no-link">'.__("Vendor Payment").'</th>
										-->
										<th class="no-link">'.__("Include Shipping").'</th>
									</tr>
								</thead>
								<tbody>
				';
			}

			$amount = 0.00;
			$shippingAmountPrice = 0.00;
			$class = '';
			$arrayShipping=array();
			foreach ($collection as $item) {
				$class = ($class == 'odd')? 'even':'odd';
				
				$html .= '<tr class="'.$class.'"';
				$html.='>';

				$html .= '<td><center>'.$renderer->render($item).'</center></td>';
				if($type == \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_DEBIT) {
					$html .= '<td><center>'.$item->getQtyReadyToRefund().'</center></td>';
				}
				else
				{
					$html .= '<td><center>'.$item->getQtyReadyToPay().'</center></td>';
				}
				/*
				$html .= '<td>'.Mage::app()->getLocale()
											->currency($item->getCurrency())
											->toCurrency($item->getCommissionFee()).'</td>';
				$html .= '<td>'.Mage::app()->getLocale()
											->currency($item->getCurrency())
											->toCurrency($item->getNetVendorEarn());
				*/
				$amount += $item->getNetVendorEarn();
				$html .= '<input id="csmarketplace_vendor_orders_'.$item->getId().'" type="hidden" value="'.$this->roundPrice($this->roundPrice($item->getNetVendorEarn() + $item->getCommissionFee())).'" name="orders['.$item->getOrderId().']['.$item->getId().']"/>';
				$html .= '<input id="csmarketplace_vendor_commission_'.$item->getId().'" type="hidden" value="'.$this->roundPrice($item->getCommissionFee()).'" name="comissionfee['.$item->getOrderId().']['.$item->getId().']"/>';
						
				$html .= '</center></td>';

				if(isset($arrayShipping[$item->getParentId()]))
				{
					$html.='<td><center></center></td>';
				}
				else
				{
					$vorder=$this->_vorders->load($item->getParentId());
					$shippingAmount=$this->helper->getAvailableShipping($vorder,$type);
					$shippingAmountPrice += $shippingAmount;//added
					
					if((float)$shippingAmount!=0)
					{
						$arrayShipping[$item->getParentId()]=$item->getParentId();
						$html.='<td><input  onclick="chooseShippingAmount(this,'.$item->getParentId().');" type="checkbox"   checked="checked" name="shippingcheck['.$item->getParentId().']" value="1"><input type="text"  readonly="true" class="validate-number-range number-range-0-'.$shippingAmount.'" name="shippings['.$item->getParentId().']" value="'.$shippingAmount.'"  id="shippings_'.$item->getParentId().'"></td>';
					}
					else
					{
						$html.='<td><center>'.$shippingAmount.'</center></td>';
					}
				}
				$html .= '</tr>';
				
			} 
			$amount += $shippingAmountPrice;
			$html.= '<input type="hidden" id="csmarketplace_fetched_total" value="'.$this->roundPrice($amount).'"/></tbody></table>';
			return $html;
		}
		else
		{
			return parent::getAddOrderBlock();
		}
	}
}
