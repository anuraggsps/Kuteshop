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

namespace Ced\CsMembership\Block;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
 use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Session;

class View extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    protected $_subscription = null;
    protected $_membership;
    public $_objectManager;
     public function __construct(
        Context $context,
		Session $customerSession,
		\Magento\Framework\ObjectManagerInterface $objectManager,		
		UrlFactory $urlFactory
    ){
		 parent::__construct($context,$customerSession,$objectManager,$urlFactory);
		$this->_session = $customerSession;
		$this->urlModel = $urlFactory;
		$this->_objectManager = $objectManager;
        $this->getAllowedCatagories();

       
    }

    public function getMembershipDetails(){ 
        if(!$this->_membership)
            $this->_membership = $this->_objectManager->create('Ced\CsMembership\Model\Membership')->load($this->getRequest()->getParam('id'));            
        return $this->_membership;
    }

    public function getAllowedCatagories()
    {
        
        $data = $this->getMembershipDetails();
        $category_array = array_unique(explode(',',$data->getCategoryIds()));
        $html = '<span>';
        $html = '<span>';
        foreach ($category_array as $value) {
            $_cat = $this->_objectManager->create('Magento\Catalog\Model\Category')->load($value);
            if($_cat->getLevel()=='0' || $_cat->getLevel()=='1')
                continue;
            $html = $html.$_cat->getName().'</br>';
        }
        return $html.'</span>';
    }

    public function getBackUrl()
    {
        
        $refererUrl = $this->getRequest()->getServer('HTTP_REFERER');
        return $refererUrl;
    }

    public function checkAssignedMembership()
    {
        $vendor_id = $this->_session->getvendorId();
        $collection = $this->_objectManager->create('Ced\CsMembership\Model\Subscription')
                                            ->getCollection()
                                            ->addFieldToFilter('vendor_id',$vendor_id)
                                            ->addFieldToFilter('subscription_id',$this->getRequest()->getParam('id'))
                                            ->getData();
        return $collection;

    }
		
}
