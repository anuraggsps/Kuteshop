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

namespace Ced\CsMembership\Block\Adminhtml\Form\Field;

class Defaultcategory extends \Magento\Framework\View\Element\Html\Select
{
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        $this->setExtraParams('style="width: 150px;"');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if (!$this->getOptions()) {
            $collection =$objectManager->create('Magento\Catalog\Model\ResourceModel\Category\Collection');
            $collection->addAttributeToSelect(array('name'))
                ->addFieldToFilter('is_active','1')
                ->load();
            if(count($collection)>0){
                foreach($collection as $category) {
                    if($category->getLevel() == '0' || $category->getLevel() == '1')
                        continue;
                    $id = $category->getId();
                    $name = addslashes($category->getName());
                    $this->addOption($id, $name);
                }
            }
        }
        return parent::_toHtml();
    }
}
