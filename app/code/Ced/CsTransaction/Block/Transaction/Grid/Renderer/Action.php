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
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsTransaction\Block\Transaction\Grid\Renderer;

class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {
	protected $_vproduct;
	public function __construct(\Magento\Backend\Block\Context $context,
			\Magento\Framework\ObjectManagerInterface $objectManager, 
		array $data = []
	) {
		$this->_objectManager = $objectManager;
		parent::__construct ( $context, $data );
	}
	public function render(\Magento\Framework\DataObject $row) {
		$html ='';
		$model = $this->_objectManager->create( 'Ced\CsTransaction\Model\Items' )->load($row->getId());
		if($model->getIsRequested()==1 && $model->getItemPaymentState() == \Ced\CsTransaction\Model\Items::STATE_READY_TO_PAY){
			$html.= __('Requested');
		}
		elseif($model->getItemPaymentState() == \Ced\CsTransaction\Model\Items::STATE_PAID){
			$html.= __('Paid');
		}
		elseif($model->getQtyOrdered()==$model->getQtyRefunded()){
			$html.= __('Cancelled');
		}
		elseif($model->getQtyOrdered()==$model->getQtyReadyToPay()+$model->getQtyRefunded()){
		    
		    $url =$this->getUrl('cstransaction/vpayments/requestpost',array('payment_request'=>$row->getId()));
			$html.=$this->getRequestButtonHtml($url);
		}else{
			
			$html.= __('Not Allowed');
		}
		return $html;
	}
	
	protected function getRequestButtonHtml($url = '')
	{
		return '<input class="button scalable save" style="cursor: pointer; background: #ffac47 url("images/btn_bg.gif") repeat-x scroll 0 100%;border-color: #ed6502 #a04300 #a04300 #ed6502;    border-style: solid;    border-width: 1px;    color: #fff;    cursor: pointer;    font: bold 12px arial,helvetica,sans-serif;    padding: 1px 7px 2px;text-align: center !important; white-space: nowrap;" type="button" onclick="setLocation(\''.$url.'\')" value="Request">';
	}
	
	public function getVendorId(){
		return $this->_objectManager->get('Magento\Customer\Model\Session')->getVendorId();
	}
}
