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
class Orderdesc extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

	protected $_frontend = false;
	protected $_objectManager;
	protected $_currencyInterface;

    /**
     * Orderdesc constructor.
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Locale\Currency $localeCurrency
     * @param array $data
     */
	public function __construct(\Magento\Backend\Block\Context $context,
		\Magento\Framework\ObjectManagerInterface $objectManager, 
    	\Magento\Framework\Locale\Currency $localeCurrency,
		array $data = [])
	{
		$this->_objectManager=$objectManager;
   		$this->_currencyInterface = $localeCurrency;
		parent::__construct($context, $data);
	}
	
	public function render(\Magento\Framework\DataObject $row)
	{
		$orderIds=$row->getOrderId();
		$html='';
		if($orderIds!=''){
			$orderIds=explode(',',$orderIds);
			foreach ($orderIds as $orderId){
					$url = 'javascript:void(0);';
					$target = "";
					$html .='<label for="order_id_'.$orderId.'"><b>Order# </b>'.$orderId.'</label><br/>';
			}
		}
		return $html;
	}	
 
}