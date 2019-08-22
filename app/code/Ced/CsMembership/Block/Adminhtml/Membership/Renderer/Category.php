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
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Block\Adminhtml\Membership\Renderer;
 
class Category extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer {
 
 	protected $_objectManager;
	/**
     * @param \Magento\Backend\Block\Context $context
     * @param array $data
     */
    public function __construct(
    	\Magento\Backend\Block\Context $context,
    	\Magento\Framework\ObjectManagerInterface $objectManager, 
    	array $data = []
    )
    {
		$this->_objectManager = $objectManager;
        parent::__construct($context, $data);
    }

	public function render(\Magento\Framework\DataObject $row) {
		  $catId_json =  $row->getData($this->getColumn()->getIndex());	
      	  $category_array=array_unique(explode(',',$catId_json));
	      $html='<span>';
	      foreach ($category_array as $value) {
	      		$_cat = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($value);
	          if($_cat->getLevel()=='0' || $_cat->getLevel()=='1')
	            continue;
	         	$html=$html.$_cat->getName().'</br>';
	      	}
	      	return $html.'</span>';
	}
}