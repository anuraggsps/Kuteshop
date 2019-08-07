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
class Deletefromcart extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface{

    public function execute() {
        try {
			$request = $this->getRequest()->getContent();
			$param = json_decode($request, true);
            if($param['item_id'] !='') {
                $quoteItem = $this->_objectManager->get('Magento\Quote\Model\Quote\Item')->load($param['item_id']);
                $productId = $quoteItem->getProductId();
				$quote = $this->_objectManager->get('Magento\Quote\Model\Quote');
				$quote->setStoreId($quoteItem->getStoreId());
				$quote->load($quoteItem->getQuoteId());
				$quote->removeItem($quoteItem->getItemId());
				$quote->collectTotals()->save();
                    
				$jsonArray['data'] = '';
				$jsonArray['msg'] =  __("Cart has been updated.");
				$jsonArray['status'] = 'success';
				$jsonArray['status_code'] = '200';
                
            }else {
				$jsonArray['data'] = '';
				$jsonArray['msg'] = __("No Permission to access api");
				$jsonArray['status'] = 'fail';
				$jsonArray['status_code'] = '403';
            }
        }catch (\Exception $e) {
			$jsonArray['data'] = '';
			$jsonArray['msg'] = $e->getMessage();
			$jsonArray['status'] = 'fail';
			$jsonArray['status_code'] = '201';
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
