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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model;

use Magento\Framework\Api\AttributeValueFactory;

class SetVendorOrder extends \Ced\CsMarketplace\Model\AbstractModel
{

    protected $_objectManager;
    protected $_eventManager;

    /**
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Magento\Framework\Event\ManagerInterface $eventManager
    )
    {
        $this->_objectManager = $objectInterface;
        $this->_eventManager = $eventManager;

        $this->helper = $this->_objectManager->get('\Ced\CsMarketplace\Helper\Data');
    }

    public function setVendorOrder($order)
    {
        try {
            $vorder = $this->_objectManager->create('Ced\CsMarketplace\Model\Vorders')->getCollection()
                        ->addFieldToFilter('order_id', $order->getIncrementId())->getFirstItem();
           
            if ($vorder->getId()) {
                return $this;
            }
            $baseToGlobalRate = $order->getBaseToGlobalRate() ? $order->getBaseToGlobalRate() : 1;
            $vendorsBaseOrder = [];
            $vendorQty = [];
            
            foreach ($order->getAllItems() as $key => $item) {
                $vendor_id = $item->getVendorId();
                if ($vendor_id) {
                    if ($item->getHasChildren() && $item->getProductType() != 'configurable') {
                        continue;
                    }

                    $price = $item->getBaseRowTotal()
                        + $item->getBaseTaxAmount()
                        + $item->getBaseHiddenTaxAmount()
                        + $item->getBaseWeeeTaxAppliedRowAmount()
                        + $item->getBaseDiscountTaxCompensationAmount()
                        - $item->getBaseDiscountAmount();

                    $vendorsBaseOrder[$vendor_id]['order_total'] = isset($vendorsBaseOrder[$vendor_id]['order_total']) ? ($vendorsBaseOrder[$vendor_id]['order_total'] + $price) : $price;
                    $vendorsBaseOrder[$vendor_id]['item_commission'][$item->getQuoteItemId()] = $price;
                    $vendorsBaseOrder[$vendor_id]['order_items'][] = $item;
                    $vendorQty[$vendor_id] = isset($vendorQty[$vendor_id]) ? $vendorQty[$vendor_id] + $item->getQtyOrdered() : $item->getQtyOrdered();
                    $logData = $item->getData();
                    unset($logData['product']);
                }
            }
        } catch (\Exception $e) {
            $this->helper->logException($e); 
            throw new \Magento\Framework\Exception\LocalizedException(__('Error Occured While Placing The Order'));
        }

        foreach ($vendorsBaseOrder as $vendorId => $baseOrderTotal) {

            try {
                $qty = isset($vendorQty[$vendorId]) ? $vendorQty[$vendorId] : 0;
                $vorder = $this->_objectManager->create('Ced\CsMarketplace\Model\Vorders');
                $vorder->setVendorId($vendorId);
                $vorder->setCurrentOrder($order);
                $vorder->setOrderId($order->getIncrementId());
                $vorder->setCurrency($order->getOrderCurrencyCode());
                $vorder->setOrderTotal($this->_objectManager->get('Magento\Directory\Helper\Data')->currencyConvert($baseOrderTotal['order_total'], $order->getBaseCurrencyCode(), $order->getOrderCurrencyCode()));
                $vorder->setBaseCurrency($order->getBaseCurrencyCode());
                $vorder->setBaseOrderTotal($baseOrderTotal['order_total']);
                $vorder->setBaseToGlobalRate($baseToGlobalRate);
                $vorder->setProductQty($qty);
                $billingaddress = $order->getBillingAddress()->getData();
                if (isset ($billingaddress ['middlename'])) {
                    $billing_name = $billingaddress ['firstname'] . " " . $billingaddress ['middlename'] . " " . $billingaddress ['lastname'];
                } else {
                    $billing_name = $billingaddress ['firstname'] . " " . $billingaddress ['lastname'];
                }
                $vorder->setBillingName($billing_name);
                $vorder->setBillingCountryCode($order->getBillingAddress()->getData('country_id'));
                if ($order->getShippingAddress()) {
                    $vorder->setShippingCountryCode($order->getShippingAddress()->getData('country_id'));
                }
                $vorder->setItemCommission($baseOrderTotal['item_commission']);

                $vorder->collectCommission();
                $this->_eventManager->dispatch(
                    'ced_csmarketplace_vorder_shipping_save_before',
                    ['vorder' => $vorder]
                );
                $helper = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data');
                $vorder->save();
                $notificationData = ['vendor_id'=>$vendorId,'reference_id'=>$vorder->getId(),'title'=>'New Order '.$vorder->getOrderId(),'action'=>$helper->getUrl('csmarketplace/vorders/view',['order_id'=>$vorder->getId()])];
                $helper->setNotification($notificationData);
                $this->_objectManager->get('Ced\CsMarketplace\Helper\Mail')
                    ->sendOrderEmail($order, \Ced\CsMarketplace\Model\Vorders::ORDER_NEW_STATUS, $vendorId, $vorder);
            } catch (\Exception $e) {
                $this->helper->logException($e); 
                throw new \Magento\Framework\Exception\LocalizedException(__('Error Occured While Placing The Order'));
            }
        }
        return $this;
    }

    public function creditMemoOrder($order)
    {
        try {
            if ($order->getState() == \Magento\Sales\Model\Order::STATE_CLOSED || ((float)$order->getBaseTotalRefunded() && (float)$order->getBaseTotalRefunded() >= (float)$order->getBaseTotalPaid())) {
                $vorders = $this->_objectManager->create('Ced\CsMarketplace\Model\Vorders')->getCollection()
                            ->addFieldToFilter('order_id', ['eq' => $order->getIncrementId()]);
                if (count($vorders) > 0) {
                    foreach ($vorders as $vorder) {
                        if ($vorder->canCancel()) {
                            $vorder->setOrderPaymentState(\Magento\Sales\Model\Order\Invoice::STATE_CANCELED);
                            $vorder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_CANCELED);
                            $vorder->save();
                        } elseif ($vorder->canMakeRefund()) {
                            $vorder->setPaymentState(\Ced\CsMarketplace\Model\Vorders::STATE_REFUND);
                            $vorder->save();
                        }
                    }
                }
            }
            return $this;
        } catch (\Exception $e) {
            $this->helper->logException($e); 
            throw new \Magento\Framework\Exception\LocalizedException(__('Error Occured While Placing The Order'));
        }
    }

}

