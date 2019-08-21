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
class Login extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {

    public function execute() {
        try {
            $request = $this->getRequest()->getContent();
            $param = json_decode($request, true);
            $current_quote = '';
            $customer_id = '';
            $session = $this->__customerSession;
            $column = 'customer_social_id';
            if (isset($param['login_type'])) {
                if ($param['login_type'] == 'gmail') {
                    $column = 'customer_social_gmail_id';
                }
            }
            
            $data = [];
            $respMsg = "Login Unsuccessfull!";
            $respCode = 0;
            $respText = "fail";
            
            $custModel = $this->_objectManager->get("Magento\Customer\Model\Customer");
            if ($this->getRequest()->getParam('social_id')) {
                $cuCollection = $custModel->getCollection();
                //$cuCollection->addAttributeToSelect('customer_social_id');
                $cuCollection->addAttributeToFilter('email', $param['email']);
                $cuCollection->addAttributeToFilter($column, $this->getRequest()->getParam('social_id'));
                //var_dump($cuCollection->getSelect()->__toString()); die;
                $firstRow = $cuCollection->getFirstItem();
                $customer_id = $firstRow->getEntityId();
                if(!$customer_id){
                    throw new \Exception('Sorry! this account is invalid');
                }
                //var_dump($firstRow->getcustomer_social_id()); die;
                $this->_eventManager->dispatch(
                       'customer_login', ['customer' => $firstRow]
                );
            } else {
                
                try {
                    $customer = $this->customerAccountManagement->authenticate($param['email'], $param['password']);
                    $session->setCustomerDataAsLoggedIn($customer);
                    $session->regenerateId();
                    $customer_id = $customer->getId();
                } catch (\Magento\Framework\Exception\EmailNotConfirmedException $e) {
                    $value = $this->customerUrl->getEmailConfirmationUrl($param['email']);
                    $respMsg = __(
                        'This account is not confirmed.' .
                        ' <a href="%1">Click here</a> to resend confirmation email.',
                        $value
                    );
                     
                } catch (\Magento\Framework\Exception\AuthenticationException $e) {
                    $respMsg = __('Invalid login or password.');
                    
                } catch (\Exception $e) {
                    $respMsg = __('Invalid login or password.');
                }
                 
            }

            
            
            //$this->assignQuote(780,$customer_id);
            if (isset($customer_id) && $customer_id != '') {
                //$quoteModel = Mage::getModel('sales/quote')->getCollection();
                //$quoteModel->addFieldToFilter('customer_id',$customer_id);
                //$quoteModel->addFieldToFilter('is_active',1);
                //echo count($quoteModel);
                $customerObj = $custModel->load($customer_id);
                $quote = $this->_quoteLoader->loadByCustomer($customerObj);
                $current_quote = $quote->getId();


                if (isset($param['quote_id'])) {
                    if ($current_quote) {
                        //--------Mergeing Quotes----------
                        $quoteA = $this->_quoteLoader->load($param['quote_id']);
                        $quoteB = $this->_quoteLoader->load($current_quote);

                        $quoteB->merge($quoteA);
                        $quoteB->collectTotals()->save();

                        $quoteA->setIsActive(0);
                        $quoteA->save();
                    } else {
                        $this->assignQuote($param['quote_id'], $customer_id, $custModel);
                    }
                }

                //===================Get Address================
                $bill = array();
                $ship = array();
                $data['ship_to_same'] = 0;
                //$customer = Mage::getModel('customer/customer')->load($params['cust_id']);
                $billingAddress = $customerObj->getPrimaryBillingAddress();
                if ($billingAddress)
                    $bill[] = $billingAddress->getData();

                $shippingAddress = $customerObj->getPrimaryShippingAddress();
                if ($shippingAddress)
                    $ship[] = $shippingAddress->getData();

                if (isset($bill['entity_id']) and isset($ship['entity_id']) and $bill['entity_id'] == $ship['entity_id'])
                    $data['ship_to_same'] = 1;
                

                //~ $data['billing'] = $bill;
                //~ $data['shipping'] = $ship;
                
                //code for customer data
                
					$custModel = $this->_objectManager->get('Magento\Customer\Model\Customer');
					$cuCollection = $custModel->getCollection();
					$cuCollection->addAttributeToFilter('entity_id', $customer_id);
					foreach($cuCollection->getData() as $key=>$value){
						$data['name'] = $value['firstname']." ".$value['lastname']; 
						$data['firstname'] = $value['firstname']; 
						$data['lastname'] = $value['lastname']; 
						$data['email'] = $value['email']; 
						//~ $data['country'] = $value1['country']; 
					}
					$data['language'] = 'english'; 	
					$data['country'] = 'USA'; 	
			   
                
                
                
                
                $respMsg = "You are Logged In Succesfully";
                $respCode = 1;
                $respText = "success";
                //================End get address===============
            }
            //----------Save device token ------------
            if (isset($param['device_id'])and isset($param['device_type'])) {
                $token = $param['device_id'];
                $token_type = $param['device_type'];
                if (!empty($token) && !empty($token_type)) {
                    $this->setUserToken($param['email'], $token_type, $token, $customer_id);
                }
            }
            //----------End Save device token ----------- 
            $data['cust_id'] = $customer_id;
            $data['quote_id'] = $current_quote;
            
            //---------Send Authentication Token-----------
             $dtoken = $this->getToken($param['email'],$param['password']);
             $decodedToken = json_decode($dtoken);
            if(isset($decodedToken->message)){
				if($customer_id){
					$tokenModelFactory = $this->_objectManager->get("Magento\Integration\Model\Oauth\TokenFactory");
					$customerToken = $tokenModelFactory->create();
					$tokenKey = $customerToken->createCustomerToken($customer_id)->getToken();
					$data['token'] = $tokenKey;
				}
				else{
					$data['token'] = " ";
				}
			}else{
				$data['token'] = $decodedToken;
			}
            
            
            
           
            if (!empty($current_quote)) {
                $quote = $this->_quoteLoader->load($current_quote);
                $this->__checkoutSession->setQuoteId($current_quote);
                 
                $this->cart->setQuote($quote);
                $data['quote_count'] = $this->cart->getSummaryQty();
            }
            
            //~ $data['response_msg'] = $respMsg;
            //~ $jsonArray['response'] = $data;
            //~ $jsonArray['returnCode'] = array('result' => $respCode, 'resultText' => $respText);
            $status_code = 200;
            if($data['cust_id'] == ''){
				$data = [];
				$status_code = 201;
			}
            
            $jsonArray['data'] = $data;
            $jsonArray['status'] =  $respText;
            $jsonArray['status_code'] =  $status_code;
            $jsonArray['message']   = $respMsg;
            
        } catch (\Exception $e) {
			$jsonArray['data'] = null;
			$jsonArray['message'] = "Invalid email or password.";//$e->getMessage();
			$jsonArray['status'] =  "failure";
            $jsonArray['status_code'] =  201;
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
