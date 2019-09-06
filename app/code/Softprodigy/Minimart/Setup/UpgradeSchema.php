<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Softprodigy\Minimart\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{
		public function upgrade( SchemaSetupInterface $setup, ModuleContextInterface $context ) {
			$installer = $setup;
			$installer->startSetup();
		
			if (version_compare($context->getVersion(), '2.0.3') < 0){

				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$customerSetup = $objectManager->create('Softprodigy\Minimart\Setup\CustomerSetup');
				$customerSetup->installAttributes($customerSetup);

			}
		
			$installer->endSetup();
		}
}
 
