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

namespace Ced\CsMembership\Block\Plans;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
 use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Session;

class Transaction extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    protected $_subscription = null;
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
		$collection = $this->_objectManager->create('Ced\CsMembership\Model\Subscription')
											->getCollection()
											->addFieldToFilter('status','running')
											->addFieldToFilter('vendor_id',$this->_getSession()->getVendorId());
        $this->setCollection($collection);
       
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'sales.order.history.pager'
            )->setLimit(10)
            ->setCollection(
                $this->getCollection()
            );

            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }


    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
		
}
