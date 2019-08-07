<?php

namespace Softprodigy\Minimart\Controller\Miniapi;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Homepage
 *
 * @author mannu
 */
class CategoryList extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {

    public function execute() {
        try {
            //-------------Mobile theme color------------
            /* $subs = $this->checkPackageSubcription();
             */
            $subs = [];
            $subs['subs_closed'] = false;
            $subs['active_package'] = 'Gold';

            $pkgtype = $this->pkgCode[$subs['active_package']];

            $catIds = $this->__helper->getStoreConfig('minimart/minimart_registration/categories');
            
            //code for getBestsellerProducts
				$collection = $this->_objectManager->get('\Magento\Sales\Model\ResourceModel\Report\Bestsellers\CollectionFactory')->create()->setModel('Magento\Catalog\Model\Product');
		
				//$collection->setPageSize(10)->setCurPage(1);
				$producIds = array();
				foreach ($collection as $product) {
					$producIds[] = $product->getProductId();
				}

				$collection = $this->_productCollectionFactory->create();
				$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
				$collection = $this->_addProductAttributesAndPrices(
					$collection
				)->addStoreFilter()->addAttributeToFilter('entity_id', array('in' => $producIds));
				foreach ($collection as $item) {
					print_r($item->getData());
				}die;
				  
            //ends here
			
			// code for fetured list products
				//~ $collection = $this->_productCollectionFactory->create();
				//~ $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
				//~ $collection->addAttributeToFilter('featured', '1')
							//~ ->addStoreFilter()
							//~ ->addAttributeToSelect('*')
							//~ ->addMinimalPrice()
							//~ ->addFinalPrice()
							//~ ->addTaxPercents()
							//~ ->setPageSize(10)->setCurPage(1);;
				//~ foreach ($collection as $item) {
					//~ print_r($item->getData());
				//~ }
			//ends
			
			// code for random products
				$collection = $this->_productCollectionFactory->create();
				$collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

				$collection = $this->_addProductAttributesAndPrices(
					$collection
				)->addStoreFilter();

				$collection->getSelect()->order('rand()');
				// getNumProduct
				$collection->setPageSize(10)->setCurPage(1);
				foreach ($collection as $item) {
					print_r($item->getData());
				}die;
			// ends here
            //~ $custID = $this->getRequest()->getParam('cust_id', false);
            //~ if ($custID) {
                //~ $customer = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($custID);
                //~ $custCats = $customer->getCustCategory();
                //~ if ($customer->getId() and ! empty($custCats)) {
                    //~ $catIds = explode(",", $customer->getCustCategory());
                //~ }
            //~ }

            //~ if (empty($catIds) || (isset($catIds[0]) and empty($catIds[0]))) {
                //~ $catIds = array();
            //~ }


            //~ $result = $this->getCategorytree(null, null, $catIds);
            //$result = $this->getCategorytree();
             //~ echo "<pre>";print_r($result);die;
            //~ $data = array();
            //~ if (empty($catIds) and $result['children'][0]['is_active'] == 1) {
                //~ $data = $result['children'];

                //~ //$this->recur_html_decode_nav($data);
                //~ $jsonArray['response'] = $data;
                //~ $jsonArray['pkg_type'] = $pkgtype;
                //~ $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
            //~ } else if (!empty($catIds)) {
                //~ $data  = $result['children'];
                //~ //$this->recur_html_decode_nav($data);
                //~ $jsonArray['response'] = $data;
                //~ $jsonArray['pkg_type'] = $pkgtype;
                //~ $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
            //~ } else {
                //~ $jsonArray['response'] = $data;
                //~ $jsonArray['pkg_type'] = $pkgtype;
                //~ $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
            //~ }

            //~ $jsonArray['response'] = $data;
            //~ $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }

        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }
	
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool{
        return true;
    }
}
