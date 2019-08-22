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

namespace Ced\CsMembership\Block\History;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
 use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Session;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Framework\ObjectManagerInterface;

class History extends \Ced\CsMarketplace\Block\Vendor\AbstractBlock
{
    protected $_subscription = null;
    protected $_defaultColumnCount = 3;
    public $_objectManager;
    public $_scopeConfig;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        ObjectManagerInterface $objectManager,
        Session $customerSession,
        UrlFactory $urlFactory,
        array $data = []
    ) {
        $this->_cedCatalogLayer = $layerResolver->get();
        $this->_postDataHelper = $postDataHelper;
        $this->categoryRepository = $categoryRepository;
        $this->urlHelper = $urlHelper;
        $this->_coreRegistry = $context->getRegistry();
        $this->_objectManager = $objectManager;
        $this->customerSession = $customerSession;
       
        parent::__construct(
            $context,
            $customerSession,
            $objectManager,
            $urlFactory
        );
        $collection = $this->_objectManager->create('Ced\CsMembership\Model\Subscription')
                                           ->getCollection()
                                           ->addFieldToFilter('vendor_id',$this->customerSession->getVendorId());
        $this->setCollection($collection);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'membership.pager'
            )->setLimit(10)
            ->setCollection(
                $this->getCollection()
            );

            $this->setChild('pager', $pager);
            $this->getCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

}
