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
			$orderArray = json_decode($row->getTransCreditSummary(), true);

			if(count($orderArray)>0){
			$html .= '<div class="grid" id="order-items_grid">
						<table cellspacing="0" class="data order-tables">
		 
							<col width="100" />
							<col width="40" />
							<col width="100" />
							<col width="80" />
							<thead>
								<tr class="headings">';
								foreach($orderArray['headers'] as $title ){
								$html.='<th class="no-link">'.__($title).'</th>';
								}
								$html.='<th class="no-link">Total</th>	
								</tr>
							</thead>
							<tbody>';
							$class='';
							$trans_sum = 0;
							foreach($orderArray['values'] as $info){
								$class = ($class == 'odd')? 'even':'odd';
								$html.='<tr class="'.$class.'">';

								foreach($orderArray['headers'] as $key=>$title){
									$value=isset($info[$key])?$info[$key]:'';
									$html.='<td>'. $value.'</td>';
								}
								$total = 0;
								foreach($orderArray['pricing_columns'] as $key){
									$price_valu = isset($info[$key])?$info[$key]:0;
									$total += $price_valu;
								}
								$html.='<td>'.$total.'</td></tr>';
								$trans_sum += $total;
							}
							
			$html.='</tbody></table><div><div><div>';
			$html.='<h3>'.__('Service Tax').' : '.$orderArray['service_tax_amount'].'</h3>';
		}
		return $html;
		}
		return parent::render($row);
	}
 
}
