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
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Plugin\Product;

class Collection
{
    public $_objectManager;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $ob,
        \Magento\Framework\App\State $state
    ){
	   $this->_objectManager = $ob;
	   $this->_state = $state;
	}
	

    public function afterAddAttributeToSort(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject, $result)
    {
        //$registry = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Registry');
       // print_r($registry->registry('vendorPanel'));die('----');
        if($this->_state->getAreaCode() == 'adminhtml'){
            $subject->addAttributeToFilter('sku', ['nlike' => '%membership%']);
        }
        return $result;
    }
}