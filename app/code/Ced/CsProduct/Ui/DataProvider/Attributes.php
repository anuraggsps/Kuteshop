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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Ui\DataProvider;

class Attributes extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    protected $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\ConfigurableProduct\Model\ConfigurableAttributeHandler $configurableAttributeHandler,
    	\Magento\Framework\App\Request\Http $http,
        array $meta = [],
        array $data = []
    ) {
    	$this->request = $http;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->configurableAttributeHandler = $configurableAttributeHandler;
        $this->collection = $configurableAttributeHandler->getApplicableAttributes()->setAttributeSetFilter($this->request->getParam('set'));
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->prepareUpdateUrl();
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $attributeIds = $this->collection->getColumnValues('attribute_id');
    	
    	$is_vpattribute = $this->_objectManager->get('Magento\Framework\Module\Manager')->isEnabled('Ced_CsVendorProductAttribute');
    	$otherVendorAttributeIds =[];
    	if($is_vpattribute)
    	{
    		$session = $this->_objectManager->get('Magento\Customer\Model\Session');
    		$vendorId = $session->getVendorId();
    		$otherVendorAttributeIds = $this->_objectManager->create('Ced\CsVendorProductAttribute\Model\Attribute')->getCollection()->addFieldtoFilter('vendor_id',['neq'=>$vendorId])->getColumnValues('attribute_id');
    	}
        $items = [];
        $skippedItems = 0;
        foreach ($this->getCollection()->getItems() as $attribute) {
            if(in_array($attribute->getId(), $otherVendorAttributeIds)){
                $skippedItems++;
                continue;
            }
            if ($this->configurableAttributeHandler->isAttributeApplicable($attribute)) {
                $items[] = $attribute->toArray();
              
            } else {
                $skippedItems++;
               
            }
        }
        
        return [
            'totalRecords' => $this->collection->getSize() - $skippedItems,
            'items' => $items
        ];
    }
    /**
     * @return void
     */
    protected function prepareUpdateUrl()
    {
        if (!isset($this->data['config']['filter_url_params'])) {
            return;
        }
        foreach ($this->data['config']['filter_url_params'] as $paramName => $paramValue) {

            if ('*' == $paramValue) {
                $paramValue = $this->request->getParam($paramName);
            }

            if ($paramValue) {
                $this->data['config']['update_url'] = sprintf(
                    '%s%s/%s/',
                    $this->data['config']['update_url'],
                    $paramName,
                    $paramValue
                );
            }
        }
    }
}
