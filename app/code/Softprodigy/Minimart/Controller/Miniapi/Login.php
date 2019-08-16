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
            
            $status_code = 200;
            if($data['cust_id'] == ''){
				$data = [];
				$status_code = 201;
			}
			

            //add functionality of addto cart for withoutlogin
            if($status_code == 200){
				if(isset($param['token']) && $param['token'] !=''){
					$guestitemdata = $this->GetitemsForGuestUsers($param['token']);
						if(!empty($guestitemdata)){
							if(!is_object($guestitemdata)){
								foreach($guestitemdata as$itemkey=>$itemdatas){
									if($itemdatas->product_type =='configurable'){
										$optionsarray =array();
										$x=0;
										foreach($itemdatas->product_option->extension_attributes->configurable_item_options as $key=>$value){
											$optionsarray[$x]['option_id'] = $value->option_id;
											$optionsarray[$x]['option_value'] = $value->option_value;
											$x++;
										}
										
										$cartData = [
											'cartItem' => [
											"quote_id" => $data['quote_id'],
											"sku" => $itemdatas->sku,
											"qty" => $itemdatas->qty,
											"product_option"=> array("extension_attributes"=>array("configurable_item_options"=>$optionsarray))
											]
										];
										
										$this->addItemInProduct($cartData,$data['token']);
										$quoteFactory = $this->_objectManager->create('\Magento\Quote\Model\QuoteFactory');
										$quoteids =        $this->GetQuoteIDFromGuestToken($param['token']);
										$currentQuoteObj = $quoteFactory->create()->load($quoteids);
										$currentQuoteObj->setIsActive(false)->save();
									}else{
										//for simple products
										$cartData = [
											'cartItem' => [
											"quote_id" => $data['quote_id'],
											"sku" => $itemdatas->sku,
											"qty" => $itemdatas->qty
											]
										];
										$this->addItemInProduct($cartData,$data['token']);
										$quoteids =        $this->GetQuoteIDFromGuestToken($param['token']);
										$quoteFactory = $this->_objectManager->create('\Magento\Quote\Model\QuoteFactory');
										$currentQuoteObj = $quoteFactory->create()->load($quoteids);
										$currentQuoteObj->setIsActive(false)->save();
									}
								}
							}
						}
				}
			}
            //ends here
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
	
	public function GetitemsForGuestUsers($token){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
		$baseurl = $storeManager->getStore()->getBaseUrl();
		
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $baseurl."rest/V1/guest-carts/".$token."/items",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "GET",
		  CURLOPT_HTTPHEADER => array(
			"Cache-Control: no-cache",
			"Content-Type: application/json",
			"Postman-Token: fd4619b3-813c-4e70-92a1-2263752a76a5"
		  ),
		));
		
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		$reslutzs= (array)json_decode($response);
		//~ echo "<pre>";print_r($reslutzs);die;
		if(isset($reslutzs['items'])){
			return  $reslutzs['items'];
		}else{
			return  $reslutzs;
		}
	}
	
	public function addItemInProduct($cartData,$token){
		
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseurl = $storeManager->getStore()->getBaseUrl();
		$url =$baseurl.'index.php/rest/V1/carts/mine/items';
		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cartData));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer ".$token));
		$result = json_decode(curl_exec($ch));
		return $result;
	}
	
	public function GetQuoteIDFromGuestToken($token){
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
		$resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
		$connection = $resource->getConnection();
		$tableName = $resource->getTableName('quote_id_mask'); //gives table name with prefix

		//Select Data from table
		$sql = "Select * FROM " . $tableName ." where masked_id = '$token'";
		$result = $connection->fetchAll($sql);
		return $result[0]['quote_id'] ;
	}
	
}
