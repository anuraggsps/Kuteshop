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
 * @package   Ced_CsVendorProductAttribute
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsVendorProductAttribute\Model;

class Attribute extends \Magento\Framework\Model\AbstractModel
{
    protected $_objectManager;
    protected $_eavFactory;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager


    )
    {
        parent::__construct($context, $registry);
        $this->_objectManager = $objectManager;

    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsVendorProductAttribute\Model\ResourceModel\Attribute');
    }

    public function getProductAttributes($vendor)
    {
        if (!is_numeric($vendor))
            $vendorId = $vendor->getId();
        else
            $vendorId = $vendor;

        $attributes = $this->getCollection()->addFieldToFilter('vendor_id', array('eq' => $vendorId));
        return $attributes;
    }

    public function addVendorAttributeToAttributeSet($attributeSetIds = array(), $attribute_id, $attributeCode, $sortOrder = 150)
    {
        foreach ($attributeSetIds as $attributeSetId) {
            /** @var \Magento\Catalog\Model\Config $config */
            $config = $this->_objectManager->get('Magento\Catalog\Model\Config');
            $attributeGroupId = $config->getAttributeGroupId($attributeSetId, 'General');
            if(!$attributeGroupId){
                $attributeGroupId = $config->getAttributeGroupId($attributeSetId, 'Product Details');
            }
            if ($attributeSetId != null) {
                $this->_objectManager->get('Magento\Eav\Model\AttributeManagement')->assign('catalog_product', $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder);
            }
        }
    }

    public function addVendorAttributeToGroup($attributeSetIds = array(), $attribute_id, $attributeCode, $sortOrder = 150)
    {
        foreach ($attributeSetIds as $attributeSetId) {

            /** @var \Magento\Catalog\Model\Config $config */
            $config = $this->_objectManager->get('Magento\Catalog\Model\Config');

            $attributeGroupId = $config->getAttributeGroupId($attributeSetId, 'General');
            if(!$attributeGroupId){
                $attributeGroupId = $config->getAttributeGroupId($attributeSetId, 'Product Details');
            }
            if ($attributeSetId != null) {
                $this->_objectManager->get('Magento\Eav\Model\AttributeManagement')->assign('catalog_product', $attributeSetId, $attributeGroupId, $attributeCode, $sortOrder);
            }
        }
    }

    public function removeVendorAttributeFromGroup($attributeSetIds = [], $attribute_id, $attributeCode)
    {

        foreach ($attributeSetIds as $attributeSetId) {

            if ($attributeSetId != null) {
                $this->_objectManager->get('Magento\Eav\Model\AttributeManagement')->unassign($attributeSetId, $attributeCode);
            }
        }

    }
}
