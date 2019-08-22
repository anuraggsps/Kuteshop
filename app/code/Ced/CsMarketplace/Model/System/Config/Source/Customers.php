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
 * @package     Ced_CsMarketplace
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Model\System\Config\Source;
 
class Customers extends AbstractBlock
{

    /**
     * Retrieve Option values array
     *
     * @return array
     */
    public function toOptionArray($selected = false)
    {
        $options = array();
        $registeredCustomers = array();
        $resource = $this->_objectManager->get('\Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $table = $resource->getTableName('customer_entity');
        if($selected) {
            $select = $connection->select()
                                    ->from($table)
                                    ->where('entity_id = ?', $selected);
            $customers        = $connection->fetchAll($select);
        } else {
            $request = $this->_objectManager->get('Magento\Framework\App\RequestInterface');
            $customer_id = $request->getParam('customer_id');
            
            $select = $connection->select()
                                    ->from($table)
                                    ->where('entity_id = ?', $customer_id);
            $customers        = $connection->fetchAll($select);
        }
      
        foreach($customers as $customer) {
            $options[] = array('value' => $customer['entity_id'], 'label'=>$customer['firstname']." (".$customer['email'].")");
        }
        return $options;
    }

}
