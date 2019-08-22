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
 * @package   Ced_CsMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMembership\Model;
 
class Membership extends \Magento\Framework\Model\AbstractModel
{
	const PRODUCT_EDIT	= 'edit';
    const PRODUCT_NEW	= 'new';
    const DEFAULT_SORT_BY = 'name';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Ced\CsMembership\Model\ResourceModel\Membership');
    }

   /* public function getMembershipPlans()
    {   
        $data = $this->_objectManager->create('Ced\CsMembership\Model\Membership')
                                     ->getCollection()
                                     ->addFieldToFilter('status',\Ced\CsMembership\Model\Status::STATUS_ENABLED)
                                     //->addFieldToFilter('group_type',trim(strtolower($groupCode)))
                                     ;                      
        return $data;    
    }*/

   

    public function getAttributeUsedForSortByArray()
    {
        $options = array(
            self::DEFAULT_SORT_BY  => __('Name')
        );
        // foreach ($this->getAttributesUsedForSortBy() as $attribute) {
            // /* @var $attribute Mage_Eav_Model_Entity_Attribute_Abstract */
            // $options[$attribute->getAttributeCode()] = $attribute->getStoreLabel();
        // }

        return $options;
    }
}
 