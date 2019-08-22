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
  * @category  Ced
  * @package   Ced_CsVendorProductAttribute
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsVendorProductAttribute\Model\ResourceModel\Entity\Attribute\Grid;

class Collection extends \Magento\Eav\Model\ResourceModel\Entity\Attribute\Grid\Collection
{
    protected $_session;
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Registry $registryManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_registryManager = $registryManager;
        $this->_session = $customerSession;
        $this->_objectManager = $objectManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $registryManager);
    }

    protected function _initSelect()
    {
        parent::_initSelect();
        $this->setEntityTypeFilter($this->_registryManager->registry('entityType'));
        $vendor_attr_sets = $this->getVendorAttrSets();
        $this->addFieldToFilter('attribute_set_id',['in'=>$vendor_attr_sets]);

        return $this;
    }

    public function getVendorAttrSets()
    {
        $vendor_attr_sets = [];
        $vendor_id=$this->_session->getVendorId();
        if($vendor_id){
            $attrset_model = $this->_objectManager->create('\Ced\CsVendorProductAttribute\Model\Attributeset')->getCollection()->addFieldToFilter('vendor_id',$vendor_id)->addFieldToSelect('attribute_set_id')->getData();
            foreach ($attrset_model as $key => $attrset_id) {
                $vendor_attr_sets[] = $attrset_id['attribute_set_id'];
            }
        }
        return $vendor_attr_sets;
    }
}
