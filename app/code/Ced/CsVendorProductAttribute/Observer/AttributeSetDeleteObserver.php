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
  * @category     Ced
  * @package      Ced_CsVendorProductAttribute
  * @author       CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright    Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsVendorProductAttribute\Observer;

use Magento\Framework\Event\ObserverInterface;

class AttributeSetDeleteObserver implements ObserverInterface
{
    /**
	 * @var \Magento\Framework\ObjectManagerInterface
	 */
    protected $_objectManager;
	
	public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * Delete Attribute Set From Vendor's table
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $attribute_set = $observer->getEvent()->getObject();
        $attribute_set_id = $attribute_set->getId();
        $attr_set_model = $this->_objectManager->create('\Ced\CsVendorProductAttribute\Model\Attributeset')->getCollection()
                            ->addFieldToFilter('attribute_set_id', $attribute_set_id)->getFirstItem();
        $attr_set_model->delete();
        return $this;
    }
}
