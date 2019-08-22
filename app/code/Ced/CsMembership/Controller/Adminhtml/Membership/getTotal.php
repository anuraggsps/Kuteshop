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

namespace Ced\CsMembership\Controller\Adminhtml\Membership;

class getTotal extends \Magento\Backend\App\Action
{
    
	public function execute()
    { 
            $membershipData = $this->getRequest()->getParams();
            $total = 0;
            $basePrice = $this->_objectManager->get('Ced\CsMembership\Helper\Data')->getBasePrice();
            $categoriesCost = $this->_objectManager->get('Ced\CsMembership\Helper\Data')->getCategoriesCost($membershipData['category_ids']);
            $productCost = $this->_objectManager->get('Ced\CsMembership\Helper\Data')->getProductCost($membershipData['product_limit']);
            $durationCost = $this->_objectManager->get('Ced\CsMembership\Helper\Data')->getDurationCost($membershipData['duration']);
            $total = $basePrice + $categoriesCost + $productCost + $durationCost;
            /*print_r('$basePrice = '.$basePrice);
            print_r('$categoriesCost = '.$categoriesCost);
            print_r('$productCost = '.$productCost);
            print_r('$durationCost = '.$durationCost);


            die;*/
            $this->getResponse()->setBody($this->_objectManager->create('\Magento\Framework\Json\Helper\Data')->jsonEncode($total));
    }
}
