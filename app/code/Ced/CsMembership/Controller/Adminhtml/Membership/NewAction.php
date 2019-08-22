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
use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
class NewAction extends \Magento\Backend\App\Action
{
	public function __construct(
		\Magento\Backend\App\Action\Context $context,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registerInterface
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registerInterface;
        parent::__construct($context);
    }

    public function execute()
    {
    	$prodcutLimit = $this->_scopeConfig->getValue('ced_vproducts/general/limit', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    	$isAllcat = $this->_scopeConfig->getValue('ced_vproducts/general/category_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    	if($isAllcat){
					$categories = $this->_scopeConfig->getValue('ced_vproducts/general/category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
				}else{
					$category = $this->_objectManager->create('\Magento\Catalog\Model\Category');
					$catTree = $category->getTreeModel()->load();
					$catIds = $catTree->getCollection()->getAllIds();
					$categories = implode(',', $catIds);
				}
		$response = array(
		    	'product_limit' => $prodcutLimit,
		    	'product_categories' => $categories
		    	); 
		$this->_coreRegistry->register('csmembership_group_data',$response);
		$this->_coreRegistry->register('csmembership_category',$categories);
        $this->_forward('edit');
    }
}
