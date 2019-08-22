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

class Product extends \Magento\Framework\App\Config\Value
{

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \Ced\CsCommission\Helper\Product $commissionProductHelper,
        \Magento\Catalog\Model\Category $catalogCategory,
        array $data = []
    )
    {
        $this->_catalogCategory = $catalogCategory;
        $this->_productHelper = $commissionProductHelper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function _afterLoad()
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
        $cnt = 1;
        foreach ($arr as $k => $val) {
            if (!is_array($val)) {
                unset($arr[$k]);
                continue;
            }
            $sortOrder[$k] = isset($val['priority']) ? $val['priority'] : $cnt++;
        }
        //sort by priority
        array_multisort($sortOrder, SORT_ASC, $arr);
        $this->setValue($arr);
        return $this;
    }

    /**
     * Prepare data before save
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $value = $this->_productHelper->getSerializedOptions($value);
        $this->setValue($value);
        return parent::beforeSave();
    }
}
