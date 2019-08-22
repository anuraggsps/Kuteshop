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
  * @author       CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsMembership\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{

    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;

        $installer->startSetup();

        /**
         * Create table 'ced_csmembership_membership'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmembership_membership')
        )->addColumn('id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn('qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'Quantity'
        )->addColumn(
        	'name',
        	\Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
        	null,
        	['nullable' => false, 'default' => ''],
        	'Name'  
        )->addColumn(
            'image',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => false, 'default' => ''],
            'Image'  
        )->addColumn(
            'sort_order',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => true, 'default' => '0'],
            'Sort Order'  
        )->addColumn('status',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            1,
            ['unsigned' => true, 'nullable' => false],
            'Status'
        )->addColumn(
            'category_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            null,
            ['nullable' => true, 'default' => ''],
            'Category Ids'  
        )->addColumn(
            'product_limit',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            ['nullable' => true, 'default' => ''],
            'Product Limit'  
        )->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['nullable' => false],
            'Price'
        )->addColumn(
            'special_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['nullable' => false],
            'Special Price'
        )->addColumn(
            'duration',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            5,
            ['unsigned' => true, 'nullable' => false],
            'Duration'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Product id'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'Website id for product to show'
        )->addColumn(
            'store_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'Store id for product to show'
        )->setComment(
            'Membership Plan data'
        );
        $installer->getConnection()->createTable($table);



        /**
         * Create table 'ced_csmembership_subcription'
         */
        $table = $installer->getConnection()->newTable(
            $installer->getTable('ced_csmembership_subscription')
        )->addColumn('id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
            'Id'
        )->addColumn('vendor_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Vendor Id'
        )->addColumn('store',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Store'
        )->addColumn(
            'status',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            10,
            ['nullable' => false, 'default' => ''],
            'Status'  
        )->addColumn(
            'subscription_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false],
            'Subcription id'
        )->addColumn(
            'start_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            ['nullable' => false],
            'Start Date'  
        )->addColumn(
            'end_date',
            \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
            null,
            ['nullable' => false],
            'End Date'  
        )->addColumn(
            'order_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            15,
            ['nullable' => false],
            'Order Id'  
        )->addColumn(
            'payment_name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Payment name'  
        )->addColumn(
            'customer_email',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Customer Email'  
        )->addColumn(
            'transaction_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            50,
            ['nullable' => false],
            'Transaction Id'  
        )->addColumn('name',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            ['unsigned' => true, 'nullable' => false , 'default' => ''],
            'Name'
        )->addColumn(
            'category_ids',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            100,
            ['nullable' => true, 'default' => ''],
            'Category Ids'  
        )->addColumn(
            'product_limit',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            40,
            ['nullable' => true, 'default' => ''],
            'Product Limit'  
        )->addColumn(
            'price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['nullable' => false],
            'Price'
        )->addColumn(
            'special_price',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            '10,2',
            ['nullable' => false],
            'Special Price'
        )->addColumn(
            'duration',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            5,
            ['unsigned' => true, 'nullable' => false],
            'Duration'
        )->addColumn(
            'website_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            11,
            ['unsigned' => true, 'nullable' => false],
            'Website id from where this is purchased'
        )->setComment(
            'Membership Subcription data'
        );
        $installer->getConnection()->createTable($table);

        $installer->endSetup();

    }
}
