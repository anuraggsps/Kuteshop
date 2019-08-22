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
 * @license     http://cedcommerce.com/license-agreement.txt
 */
 namespace Ced\CsMembership\Block\Adminhtml\Form\Field;
class Durations extends \Magento\Framework\View\Element\Html\Select
{

		public function setInputName($value)
		{
			return $this->setName($value);
		}

		public function _toHtml()
		{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$this->setExtraParams('style="width: 150px;"');
			if (!$this->getOptions()) {
		
				$this->addOption($objectManager->create('Ced\CsMembership\Model\System\Config\Source\Duration')->toOptionArray(),false);
			}
			return parent::_toHtml();
		}
		
}
