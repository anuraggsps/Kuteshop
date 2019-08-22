<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Ced\CsProduct\Plugin\Model\ResourceModel\Attribute;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Status;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilderInterface;
use Magento\Framework\DB\Select;

/**
 * Plugin for OptionSelectBuilderInterface to add stock status filter.
 */
class InStockOptionSelectBuilder
{
    /**
     * CatalogInventory Stock Status Resource Model.
     *
     * @var Status
     */
    private $stockStatusResource;

    public $_objectManager;
    
    /**
     * @param Status $stockStatusResource
     */
    public function __construct(Status $stockStatusResource,\Magento\Framework\ObjectManagerInterface $ob)
    {
        $this->stockStatusResource = $stockStatusResource;
        $this->_objectManager = $ob;
    }

    /**
     * Add stock status filter to select.
     *
     * @param OptionSelectBuilderInterface $subject
     * @param Select $select
     * @return Select
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetSelect(OptionSelectBuilderInterface $subject, Select $select)
    {
        
        $showOutofStock = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStoreConfig('cataloginventory/options/show_out_of_stock');
        if(!$showOutofStock){
            $select->joinInner(
                ['stock' => $this->stockStatusResource->getMainTable()],
                'stock.product_id = entity.entity_id',
                []
            )->where(
                'stock.stock_status = ?',
                \Magento\CatalogInventory\Model\Stock\Status::STATUS_IN_STOCK
            );

        }
        
        
        return $select;
    }
}
