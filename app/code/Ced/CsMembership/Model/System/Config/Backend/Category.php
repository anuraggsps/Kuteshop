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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
/**
 * Backend for serialized array data
 *
 */
namespace Ced\CsMembership\Model\System\Config\Backend;
class Category extends  \Magento\Framework\App\Config\Value
{
    /**
     * Process data after load
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        $arr   = @unserialize($value);

        if(!is_array($arr)) return '';

        foreach ($arr as $k=>$val) {
            if(!is_array($val)) {
                unset($arr[$k]);
                continue;
            }

        }
        $this->setValue($arr);
    }

    /**
     * Prepare data before save
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        $exixting=array();
        foreach ($value as $key => $val) {
          if($key=='__empty')continue; 
          if(in_array(trim($val['category']),$exixting)){
                unset($value[$key]);
            }else{
                array_push($exixting,trim($val['category']));
            }
        }
        $value = serialize($value);
        $this->setValue($value);
    }
}