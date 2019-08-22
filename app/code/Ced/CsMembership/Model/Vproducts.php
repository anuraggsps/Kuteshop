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

namespace Ced\CsMembership\Model;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Model\AbstractExtensibleModel;

class Vproducts extends AbstractExtensibleModel
{
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectInterface,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,        
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_objectManager=$objectInterface;
        $this->_registry=$this->_objectManager->get('Magento\Framework\Registry');
        $this->_customerSession=$this->_objectManager->get('Ced\CsMarketplace\Model\Session');
        
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
    }
    
    /**
     * Get products count in category
     *
     * @param  unknown_type $category
     * @return unknown
     */
    public function getProductCount($categoryId)
    {
        $resource=$this->_objectManager->get('\Magento\Framework\App\ResourceConnection');
        $productTable =$resource->getTableName('catalog_category_product');
        $readConnection = $resource->getConnection('read');
        $select = $readConnection->select();
        $select->from(
            array('main_table'=>$productTable),
            array(new \Zend_Db_Expr('COUNT(main_table.product_id)'))
        )
            ->where('main_table.category_id = ?', $categoryId)
            ->group('main_table.category_id');
        $counts =$readConnection->fetchOne($select);
    
        return intval($counts);
    }


    
    
}

