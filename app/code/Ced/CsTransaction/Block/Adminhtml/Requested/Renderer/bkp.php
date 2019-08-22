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
 
namespace Ced\CsTransaction\Block\Adminhtml\Requested\Renderer;
class Paynow extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\Options
{
    protected $_objectManager;
    /**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(\Magento\Backend\Block\Context $context,\Magento\Framework\ObjectManagerInterface $objectManager, \Ced\CsOrder\Helper\Data $csorderHelper, \Ced\CsTransaction\Helper\Data $helper, \Ced\CsTransaction\Model\Items $vtorders, \Ced\CsMarketplace\Model\Vorders $vorders, array $data = [])
    {
        $this->_csorderHelper=$csorderHelper;
        $this->helper=$helper;
        $this->_vtorders=$vtorders;
        $this->_vorders = $vorders;
        $this->_objectManager=$objectManager;
        parent::__construct($context, $data);
    }

    /**
     * get pay now button html
     *
     * @return string
     */
    protected function getPayNowButtonHtml($url = '')
    {
       return '<input class="button sacalable save" style="cursor: pointer; background: #ffac47 url("images/btn_bg.gif") repeat-x scroll 0 100%;border-color: #ed6502 #a04300 #a04300 #ed6502;    border-style: solid;    border-width: 1px;    color: #fff;    cursor: pointer;    font: bold 12px arial,helvetica,sans-serif;    padding: 1px 7px 2px;text-align: center !important; white-space: nowrap;" type="button" onclick="setLocation(\''.$url.'\')" value="PayNow">';
    }
    
    /**
     * Get refund button html
     *
     * @return string
     */
    protected function getRefundButtonHtml($url = '',$label = '')
    {
       return '<input class="button sacalable save" style="cursor: pointer; background: #ffac47 url("images/btn_bg.gif") repeat-x scroll 0 100%;border-color: #ed6502 #a04300 #a04300 #ed6502;    border-style: solid;    border-width: 1px;    color: #fff;    cursor: pointer;    font: bold 12px arial,helvetica,sans-serif;    padding: 1px 7px 2px;text-align: center !important; white-space: nowrap;" type="button" onclick="setLocation(\''.$url.'\')" value="RefundNow">';
    }
    
    protected function getPayNowButtonHtml123($url = '')
    {
       return $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => __('PayNow'),
                    'onclick'   => "setLocation('".$url."')",
                    'class'     => 'save'
                    ))->toHtml();
    }
    
    protected function getRefundButtonHtml123($url = '',$label = '')
    {
       return $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => $label,
                    'onclick'   => "setLocation('".$url."')",
                    'class'     => 'go'
                    ))->toHtml();
    }
    /**
    * Return the Order Id Link
    *
    */
    public function render(\Magento\Framework\DataObject $row){
        
        $html = "";

        if($row->getVendorId()!='') {
            if($this->_csorderHelper->isActive()){

                $pending=true;
                $vorderItem=$this->_vtorders;
                $orderIds = explode(',',$row->getOrderId());
                
                $itemIds = '';
                if(count($orderIds) > 0){

                    foreach($orderIds as $orderId){

                        $order = $this->_vorders->load($orderId);
                        $itemIds .= $vorderItem->canPay($row->getVendorId(),$order->getOrderId());
                    }
                }

                if (strlen($itemIds) > 0) {
                    $pending=false;
                    $url =  $this->getUrl('csmarketplace/vpayments/new/', ['vendor_id' => $row->getVendorId(), 'order_ids'=>$itemIds,'type' => \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT]);
                    $html .="&nbsp;".$this->getPayNowButtonHtml($url);
                }
            } else {
                $url =  $this->getUrl('csmarketplace/vpayments/new/', ['vendor_id' => $row->getVendorId(), 'order_ids'=>$row->getOrderIds(),'type' => \Ced\CsMarketplace\Model\Vpayment::TRANSACTION_TYPE_CREDIT]);
                $html .="&nbsp;".$this->getPayNowButtonHtml($url);
            }
        }
        return $html;   
     }
}