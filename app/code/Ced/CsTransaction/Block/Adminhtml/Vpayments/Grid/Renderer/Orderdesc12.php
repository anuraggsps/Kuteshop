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
namespace Ced\CsTransaction\Block\Adminhtml\Vpayments\Grid\Renderer;
class Orderdesc extends \Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid\Renderer\Orderdesc
{
	
	protected $_frontend = false;
	protected $_objectManager;
	protected $_currencyInterface;
	/**
	 * @param \Magento\Backend\Block\Context $context
	 * @param array $data
	 */
	public function __construct(\Magento\Backend\Block\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager, 
    	\Magento\Framework\Locale\Currency $localeCurrency,
    	\Ced\CsTransaction\Model\Items $vtorders,
		array $data = [])
	{
		
		$this->_objectManager=$objectManager;
   		$this->_currencyInterface = $localeCurrency;
   		$this->_vtorders= $vtorders;
		parent::__construct($context, $objectManager, $localeCurrency, $data);
	}
	public function render(\Magento\Framework\DataObject $row)
	{
		if($this->_objectManager->get('Ced\CsOrder\Helper\Data')->isActive()){

			$amountDesc = $row->getItem_wise_amount_desc();
			$html='';
			$amountDesc = json_decode($amountDesc,true);
			if(is_array($amountDesc)){
				foreach ($amountDesc as $incrementId=>$amounts){

					if(is_array($amounts)){
						foreach($amounts as $item_id=>$baseNetAmount){
							if(is_array($baseNetAmount))
									return;
							$url = 'javascript:void(0);';
							$target = "";
							
							$amount = $this->_currencyInterface->getCurrency($row->getBaseCurrency())->toCurrency($baseNetAmount);
							$vorder = $this->_objectManager->get('\Magento\Sales\Model\Order')->load($incrementId);
							$incrementId=$vorder->getIncrementId();
							
							if ($this->_frontend && $vorder && $vorder->getId()) {
								$url =  $this->getUrl("csmarketplace/vorders/view/",array('increment_id'=>$incrementId));
								$target = "target='_blank'";
								$html .='<label for="order_id_'.$incrementId.'"><b>Order# </b>'."<a href='". $url . "' ".$target." >".$incrementId."</a>".'</label><br/>';
							}
							else 
							{
								$item=$this->_vtorders->load($item_id);
								$html .='<label for="order_id_'.$incrementId.'"><b>Order# </b>'.$incrementId.' : '.$item->getSku().'</label><br/>';
							}
						}
					}
					
				}
				
			}
			else
			{
				$amountDesc = $row->getAmountDesc();
				if($amountDesc != ''){
					
					$amountDesc = json_decode($amountDesc,true);
					if(is_array($amountDesc)){
						foreach ($amountDesc as $incrementId=>$baseNetAmount){
								if(is_array($baseNetAmount))
									return;
								$url = 'javascript:void(0);';
								$target = "";
								$amount =$this->_currencyInterface->getCurrency($row->getBaseCurrency())->toCurrency($baseNetAmount);
								$vorder = $this->_objectManager->get('\Magento\Sales\Model\Order')->load($incrementId);
								if ($this->_frontend && $vorder && $vorder->getId()) {
									$url = $this->getUrl("csmarketplace/vorders/view/",array('increment_id'=>$incrementId));
									$target = "target='_blank'";
									$html .='<label for="order_id_'.$incrementId.'"><b>Order# </b>'."<a href='". $url . "' ".$target." >".$incrementId."</a>".'</label><br/>';
								}
								else 
									$html .='<label for="order_id_'.$incrementId.'"><b>Order# </b>'.$incrementId.'</label><br/>';
						}
					}
				}
			}
			if($vendorId = $this->getRequest()->getParam('id')){
				$html.=$this->getDetails($row);
			}
			if($vendorId = $this->getRequest()->getParam('payment_id')){
				$html.=$this->getDetails($row);
			}
			
		return $html;
		}
		return parent::render($row);
	}

	public function getDetails($row){
		$amountDesc = $row->getItem_wise_amount_desc();
		$orderArray = json_decode($amountDesc,true);
		$html="";
			if(count($orderArray)>0){
				$html .= '<div class="grid" id="order-items_grid">
						<table cellspacing="0" class="data order-tables" style="width:50%; float:right" border="1">
		 
							<col width="100" />
							<col width="40" />
							<col width="100" />
							<col width="80" />
							<thead>
								<tr class="headings" style="background-color: rgb(81, 73, 67); color: white;">';
								
								$html.='<th class="no-link"><center>'.__("Order Id").'</center></th>
										<th class="no-link"><center>'.__("Order Total").'</center></th>
										<th class="no-link"><center>'.__("Commission Fee").'</center></th>
										<th class="no-link"><center>'.__("Net Earned").'</center></th>	
								</tr>
							</thead>
							<tbody>';
							$class='';
							$trans_sum = 0;


							/*foreach($orderArray as $info){*/
								

								foreach($orderArray as $key=>$value){
									$class = ($class == 'odd')? 'even':'odd';
								$html.='<tr class="'.$class.'">';
									foreach ($value as $key1 => $value1) {
										
										$html.='<td><center>'.$this->getVendorOrderId($key1).'</center></td>
												<td><center>'.$value1.'</center></td>
												<td><center>'.$this->getVendorItemCommission($key1).'</center></td>
												<td><center>'.($value1-$this->getVendorItemCommission($key1)).'</center></td></tr>';
										
									}
									//$value=isset($info[$key])?$info[$key]:'';
								}
							/*	$total = 0;
								foreach($orderArray as $key=>$value){
									foreach ($value as $key1 => $value1) {
										$total +=($value1-$this->getVendorItemCommission($key1));
										
									}
								}
								$trans_sum += $total;
							}*/
							
				$html.='</tbody></table><div><div><div>';
				//$html.='<span><h3>Total:'.$trans_sum.'</h3></span>';
				return $html;
				//$html.='<h3>'.__('Service Tax').' : '.$orderArray['service_tax_amount'].'</h3>';
			}
	}

	public function getVendorItemCommission($orderid){
		$vorder=$this->_objectManager->get('Ced\CsTransaction\Model\Items')->load($orderid);
		
		foreach ($vorder as $key => $value) {
			$order_commission=$value['item_commission'];
		}
		return $order_commission;

	}
	public function getVendorOrderId($orderid){
		$vorder=$this->_objectManager->get('Ced\CsTransaction\Model\Items')->load($orderid);
		foreach ($vorder as $key => $value) {
			$order_increment_id=$value['order_increment_id'];
		}
		return $order_increment_id;

	}

	public function formatPrice($price)
    {
    	if(!$price)
    		return "-";
    	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    	$baseCurrency = $this->_directoryHelper->getBaseCurrencyCode();
    	return $objectManager->get('\Magento\Framework\Locale\CurrencyInterface')
                    ->getCurrency($baseCurrency)
                    ->toCurrency($price);
    }
 
}
