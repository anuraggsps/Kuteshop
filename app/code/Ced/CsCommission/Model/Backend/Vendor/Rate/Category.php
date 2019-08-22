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
 * @package     Ced_CsCommission
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

/**
 * Backend for serialized array data
 *
 */

namespace Ced\CsCommission\Model\Backend\Vendor\Rate;

use Magento\Framework\App\ObjectManager;

class Category extends \Magento\Framework\App\Config\Value
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Catalog\Model\Category $catalogCategory
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Ced\CsCommission\Helper\Category $commissioncategoryHelper,
        \Magento\Catalog\Model\Category $catalogCategory,
        array $data = []
    )
    {
        $this->_catalogCategory = $catalogCategory;
        $this->_categoryHelper = $commissioncategoryHelper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Process data after load
     */
    public function afterLoad()
    {
        $value = $this->getValue();
        $arr = '';
        if ($value != '') {
            $arr = json_decode($value, true);
        }
        if (!is_array($arr)) {
            return '';
        }
        $sortOrder = [];
        foreach ($arr as $k => $val) {
            if (!is_array($val)) {
                unset($arr[$k]);
                continue;
            }
            $sortOrder[$k] = $val['priority'];
        }
        //sort by order
        array_multisort($sortOrder, SORT_ASC, $arr);
        return $this->setValue($arr);
    }

    /**
     * Prepare data before save
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->_categoryHelper->getSerializedOptions($value);
        $this->setValue($value);
        return parent::beforeSave();
    }
}
