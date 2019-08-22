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
 * @category  Ced
 * @package   Ced_CsMultiShipping
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMultiShipping\Model\Cart;

/**
 * Quote shipping method data.
 */
class ShippingMethodConverter extends \Magento\Quote\Model\Cart\ShippingMethodConverter
{
    
    protected $_objectManager;

    /**
     * Constructs a shipping method converter object.
     *
     * @param \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory Shipping method factory.
     * @param \Magento\Store\Model\StoreManagerInterface             $storeManager              Store manager interface.
     * @param \Magento\Tax\Helper\Data                               $taxHelper                 Tax data helper.
     */
    public function __construct(
        \Magento\Quote\Api\Data\ShippingMethodInterfaceFactory $shippingMethodDataFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Magento\Tax\Helper\Data $taxHelper
    ) {
        parent::__construct($shippingMethodDataFactory, $storeManager, $taxHelper);
        $this->_objectManager = $objectInterface;
    }

    /**
     * Converts a specified rate model to a shipping method data object.
     *
     * @param  string                                  $quoteCurrencyCode The quote currency code.
     * @param  \Magento\Quote\Model\Quote\Address\Rate $rateModel         The rate model.
     * @return \Magento\Quote\Api\Data\ShippingMethodInterface Shipping method data object.
     */
    public function modelToDataObject($rateModel, $quoteCurrencyCode)
    {
        /**
 * @var \Magento\Directory\Model\Currency $currency 
*/
        if(!$this->_objectManager->get('Ced\CsMultiShipping\Helper\Data')->isEnabled()) {
            return parent::modelToDataObject($rateModel, $quoteCurrencyCode);
        }
        $store = $this->storeManager->getStore();
        $currency = $store->getBaseCurrency();
        $errorMessage = $rateModel->getErrorMessage();
        $vendorId = 'admin';
        if($rateModel->getCarrier() != 'vendor_rates'){
            $tmp = explode(\Ced\CsMultiShipping\Model\Shipping::SEPARATOR, $rateModel->getCode());
            $vendorId = isset($tmp[1]) ? $tmp[1] : "admin";
        }
        $vendor = $this->_objectManager->create('Ced\CsMarketplace\Model\Vendor');
        if($vendorId && $vendorId != "admin") {
            $vendor = $vendor->load($vendorId);                       
        }
        $carrier_title = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')
            ->getStoreConfig('ced_csmultishipping/general/carrier_title', $store->getId());

        $title = $vendor->getId()? $vendor->getPublicName(): $carrier_title;
        
        return $this->shippingMethodDataFactory->create()
            ->setCarrierCode($rateModel->getCarrier())
            ->setMethodCode($rateModel->getMethod())
            ->setCarrierTitle($title)
            ->setMethodTitle($rateModel->getMethodTitle())
            ->setAmount($currency->convert($rateModel->getPrice(), $quoteCurrencyCode))
            ->setBaseAmount($rateModel->getPrice())
            ->setAvailable(empty($errorMessage))
            ->setErrorMessage(empty($errorMessage) ? false : $errorMessage)
            ->setPriceExclTax($currency->convert($this->getShippingPriceWithFlag($rateModel, false), $quoteCurrencyCode))
            ->setPriceInclTax($currency->convert($this->getShippingPriceWithFlag($rateModel, true), $quoteCurrencyCode));
            
    }
    
    private function getShippingPriceWithFlag($rateModel, $flag)
    {
        return $this->taxHelper->getShippingPrice(
            $rateModel->getPrice(),
            $flag,
            $rateModel->getAddress(),
            $rateModel->getAddress()->getQuote()->getCustomerTaxClassId()
        );
    }

}
