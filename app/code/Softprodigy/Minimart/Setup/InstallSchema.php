<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Softprodigy\Minimart\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface {

    /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;
    private $moduleInf;
    private $_storemanager;
    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(\Magento\Framework\Setup\ModuleDataSetupInterface $moduleInf, \Magento\Store\Model\StoreManagerInterface $_storemanager,EavSetupFactory $eavSetupFactory) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleInf = $moduleInf;
        $this->_storemanager = $_storemanager;
    }

    private function setupAttibutes($setup, $context) {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleInf]);
        /**
         * Add attributes to the eav/attribute
         */
        $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY, 'customer_social_id', [
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
                ]
        );

        $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY, 'customer_social_gmail_id', [
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
                ]
        );
        
        $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY, 'customer_social_twitter_id', [
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
                ]
        );
         
        $eavSetup->addAttribute(
                \Magento\Customer\Model\Customer::ENTITY, 'customer_social_linkedin_id', [
            'type' => 'text',
            'visible' => false,
            'required' => false,
            'user_defined' => false
                ]
        );
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context) {
        $installer = $setup;
        /**
         * Create table 'dailydeal'
         * 
         */
        /* ->addColumn(
          'sold',
          \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
          null,
          ['nullable' => false, 'default'=> '0'],
          'deal sold'
          ) */
        if (!$installer->tableExists('minimart_user_device_token')) {
            $table = $installer->getConnection()->newTable(
                            $installer->getTable('minimart_user_device_token')
                    )->addColumn(
                            'id', \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'row Id'
                    )->addColumn(
                            'customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => true], 'customer id'
                    )->addColumn(
                            'customer_email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100, ['nullable' => false], 'customer email'
                    )->addColumn(
                            'type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, ['nullable' => true], 'device_type'
                    )->addColumn(
                            'token', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => true], 'token'
                    )->addColumn(
                            'badge_count', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => true, "default" => '0'], 'badge_count'
                    )->addColumn(
                            'created', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'created'
                    )->addColumn(
                            'modified', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => \Magento\Framework\DB\Ddl\Table::TIMESTAMP_INIT], 'modified at'
                    )->setComment(
                    'minimart_user_device_token'
            );
            $installer->getConnection()->createTable($table);
        }
        ////2
        if (!$installer->tableExists('minimart_user_notification')) {
            $table2 = $installer->getConnection()->newTable(
                            $installer->getTable('minimart_user_notification')
                    )->addColumn(
                            'id', \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'row Id'
                    )->addColumn(
                            'customer_id', \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER, null, ['nullable' => true], 'customer id'
                    )->addColumn(
                            'device_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, ['nullable' => true], 'device_type'
                    )->addColumn(
                            'customer_email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100, ['nullable' => true], 'customer_email'
                    )->addColumn(
                            'msg', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100, ['nullable' => false], 'msg'
                    )->addColumn(
                            'offer_item_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, ['nullable' => true], 'offer_item_type'
                    )->addColumn(
                            'offer_item_value', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => true], 'offer_item_value'
                    )->addColumn(
                            'status', \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT, 2, ['nullable' => false], 'status'
                    )->addColumn(
                            'created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'created_at'
                    )->addColumn(
                            'sent_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false, 'default' => "0000-00-00 00:00:00"], 'sent_at'
                    )->setComment(
                    'minimart_user_notification'
            );

            $installer->getConnection()->createTable($table2);
        }

        //3
        if (!$installer->tableExists('minimart_notification_history')) {
            $table3 = $installer->getConnection()->newTable(
                            $installer->getTable('minimart_notification_history')
                    )->addColumn(
                            'id', \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'row Id'
                    )->addColumn(
                            'customer_email', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100, ['nullable' => false], 'customer_email'
                    )->addColumn(
                            'type_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false], 'type_id'
                    )->addColumn(
                            'msg', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 100, ['nullable' => false], 'msg'
                    )->addColumn(
                            'order_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, ['nullable' => true], 'order_id'
                    )->addColumn(
                            'quote_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 50, ['nullable' => true], 'quote_id'
                    )->addColumn(
                            'is_offer', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 2, ['nullable' => false], 'is_offer'
                    )->addColumn(
                            'item_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 20, ['nullable' => false], 'item_type'
                    )->addColumn(
                            'item_value', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => false], 'item_value'
                    )->addColumn(
                            'created_at', \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'created_at'
                    )->setComment(
                    'minimart_notification_history'
            );

            $installer->getConnection()->createTable($table3);
        }
        //4
        if (!$installer->tableExists('minimart_mydownloadable_item')) {
            $table4 = $installer->getConnection()->newTable(
                            $installer->getTable('minimart_mydownloadable_item')
                    )->addColumn(
                            'id', \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'row Id'
                    )->addColumn(
                            'link_hash', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false], 'link_hash'
                    )->addColumn(
                            'cust_id', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false], 'cust_id'
                    )->addColumn(
                            'new_hash', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false], 'new_hash'
                    )->setComment(
                    'minimart_mydownloadable_item'
            );

            $installer->getConnection()->createTable($table4);
        }
        
        //5
        if (!$installer->tableExists('minimart_homepage_design')) {
            $table5 = $installer->getConnection()->newTable(
                            $installer->getTable('minimart_homepage_design')
                    )->addColumn(
                            'id', \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'row Id'
                    )->addColumn(
                            'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT, null, ['nullable' => false], 'store_id'
                    )->addColumn(
                            'title', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false], 'title'
                    )->addColumn(
                            'layout_col', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 3, ['nullable' => false], 'layout_col'
                    )->addColumn(
                            'main_cat', \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT, null, ['nullable' => false], 'main_cat'
                    )->addColumn(
                            'cat_ids', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => false], 'cat_ids'
                    )->addColumn(
                            'position', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 10, ['nullable' => false], 'position'
                    )->addColumn(
                            'status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 15, ['nullable' => false], 'status'
                    )->addColumn(
                            'created_at',  \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'created_at'
                    )->addColumn(
                            'last_modified',  \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => true], 'last updated'
                    )->setComment(
                    'minimart_homepage_design'
            );

            $installer->getConnection()->createTable($table5);
        }
        //6
        if (!$installer->tableExists('minimart_ogb_slider_settings')) {
            $table6 = $installer->getConnection()->newTable(
                            $installer->getTable('minimart_ogb_slider_settings')
                    )->addColumn(
                            'id', \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT, null, ['identity' => true, 'nullable' => false, 'primary' => true], 'row Id'
                    )->addColumn(
                            'store_id', \Magento\Framework\DB\Ddl\Table::TYPE_BIGINT, null, ['nullable' => false], 'store_id'
                    )->addColumn(
                            'position', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 5, ['nullable' => false], 'position'
                    )->addColumn(
                            'link_type', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 3, ['nullable' => false], 'link_type'
                    )->addColumn(
                            'link_value', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => false], 'link_value'
                    )->addColumn(
                            'image', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 255, ['nullable' => false], 'image'
                    )->addColumn(
                            'buttons', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, null, ['nullable' => false], 'buttons'
                    )->addColumn(
                            'status', \Magento\Framework\DB\Ddl\Table::TYPE_TEXT, 15, ['nullable' => false], 'status'
                    )->addColumn(
                            'created_at',  \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => false], 'created_at'
                    )->addColumn(
                            'last_modified',  \Magento\Framework\DB\Ddl\Table::TYPE_TIMESTAMP, null, ['nullable' => true], 'last updated'
                    )->setComment(
                    'minimart_ogb_slider_settings'
            );

            $installer->getConnection()->createTable($table6);
        }
        
        $this->setupAttibutes($setup, $context);
        
        $installer->endSetup();
        
        //==========Sending email ===================
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        
        $to = "sales@ongobuyo.com";
        $subject = "New Installation of OnGo-Buyo Magento2 Connector";
        $host = $this->_storemanager->getStore()->getBaseUrl();
        $message = "
        <html>
                <head>
                        <title>New Installation Magento2</title>
                </head>
                <body>
                        <p>OnGo-Buyo connector installed at - " . $host . "</p>
                </body>
        </html>
        ";

        //mail($to, $subject, $message, $headers);
    }

}
