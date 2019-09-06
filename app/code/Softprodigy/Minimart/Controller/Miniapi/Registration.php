<?php

namespace Softprodigy\Minimart\Controller\Miniapi;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Model\EmailNotificationInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Stdlib\DateTime;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Request\InvalidRequestException;
/**
 * Description of Homepage
 *
 * @author mannu
 */
class Registration extends \Softprodigy\Minimart\Controller\AbstractAction implements CsrfAwareActionInterface {
    /**
     * Constants for the type of new account email to be sent
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED = 'registered';

    /**
     * Welcome email, when password setting is required
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD = 'registered_no_password';

    /**
     * Welcome email, when confirmation is enabled
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMATION = 'confirmation';

    /**
     * Confirmation email, when account is confirmed
     *
     * @deprecated
     */
    const NEW_ACCOUNT_EMAIL_CONFIRMED = 'confirmed';
    
    public function execute() {
        try {
            $request = $this->getRequest()->getContent();
            $param = json_decode($request, true);
           
           
            $fbid = isset($param['fb_social_id']) ? $param['fb_social_id'] : '';
            $gmId = isset($param['gmail_social_id']) ? $param['gmail_social_id'] : '';
            $custModel = $this->_objectManager->get("Magento\Customer\Model\Customer");
            $cuCollection = $custModel->getCollection();
            $cuCollection->addAttributeToFilter('email', $param['email']);
            $cuCollection->addAttributeToFilter('website_id', $this->_storeManager->getStore()->getWebsiteId());
            $cModel = $cuCollection->getFirstItem();
            if ($cModel->getId()) {
                $cid = $cModel->getId();
                if (!empty($fbid)) {
                    $chkml = md5(md5('fb_' . $param['email']));
                    
                    if ($chkml === $fbid){
                        $cModel->setCustomerSocialId($fbid);
                        $custModel->getResource()->saveAttribute( $cModel, 'customer_social_id');
                    }
                }
                if (!empty($gmId)) {
                    $chkml2 = md5(md5('gmail_' . $param['email']));
                    if ($chkml2 === $gmId){
                        $cModel->setCustomerSocialGmailId($gmId);
                        $custModel->getResource()->saveAttribute( $cModel, 'customer_social_gmail_id');
                    }
                }
                
                $cModel->setId($cid);
              
                $cModel->save(true);
                $cm = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($cid);
                //----------Save device token ------------
                if (isset($param['device_id'])and isset($param['device_type'])) {
                    $token = $param['device_id'];
                    $token_type = $param['device_type'];
                    if (!empty($token) && !empty($token_type)) {
                        $this->setUserToken($param['email'], $token_type, $token, $cid);
                    }
                }
                throw new \Exception(__('This customer email already exists'));
            } else {
                if(!isset($param['password']) or empty($param['password'])){
                    $param['password'] = time();
                }
                $message = '';
                $newCustomer = [
                    'email' => $param['email'],
                    'firstname' => $param['firstname'],
                    'middlename' => isset($param['middlename']) ? $param['middlename'] : '',
                    'lastname' => $param['lastname'],
                    'password' => $param['password'],
                    'website_id' => (int)$this->_storeManager->getStore()->getWebsiteId(),
                ];
                if(isset($param['is_subscribed'])){
                    $newCustomer['is_subscribed'] = 1;
                }
                if (isset($newCustomer['password'])) {
                    $newCustomer['password_confirmation'] = $newCustomer['password'];
                }
                //$result = $sess['client']->call($sess['session_id'], 'customer.create', array($newCustomer));
                $custModel->setData($newCustomer);
                $custModel->save();
                
                $customer = $this->_objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface')->get($custModel->getEmail(), $this->_storeManager->getStore()->getWebsiteId());

                $newPasswordToken = $this->_objectManager->create('Magento\Framework\Math\Random')->getUniqueHash();
        
                $accountManagement = $this->_objectManager->get('Magento\Customer\Api\AccountManagementInterface');
                
                

                $accountManagement->changeResetPasswordLinkToken($customer, $newPasswordToken);
                //$this->sendEmailConfirmation($customer, $accountManagement, '');
                
                if ($this->getRequest()->getParam('is_subscribed', false)) {
                    $this->_objectManager->get('Magento\Newsletter\Model\SubscriberFactory')->create()->subscribeCustomerById($customer->getId());
                }

                $this->_eventManager->dispatch(
                    'customer_register_success',
                    ['account_controller' => $this, 'customer' => $customer]
                );
                
                $confirmationStatus = $accountManagement->getConfirmationStatus($customer->getId());
                if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                   // $email = $this->customerUrl->getEmailConfirmationUrl($custModel->getEmail());
                    // @codingStandardsIgnoreStart
                    $message =  __('You must confirm your account. Please check your email for the confirmation');
                    
                }  
                $message ="Your Account is created Successfully";
                
                //~ $custModel = $this->_objectManager->get("Magento\Customer\Model\Customer");
                //~ $customerObj = $custModel->load($custModel->getId());
                //~ $quote = $this->_quoteLoader->loadByCustomer($custModel->getId());
                //~ $current_quote = $quote->getId();
                
                
					$session = $this->__customerSession;
                    $customer = $this->customerAccountManagement->authenticate($param['email'], $param['password']);
                    $session->setCustomerDataAsLoggedIn($customer);
                    $session->regenerateId();
                    $customer_id = $customer->getId();
					$customerObj = $custModel->load($customer_id);
					$quote = $this->_quoteLoader->loadByCustomer($customerObj);
					$data['quote_id']= $quote->getId();
					
                
                
                
                $data['cust_id'] = $custModel->getId();
                $data['name'] = $param['firstname']." ".$param['lastname'];
                $data['firstname'] = $param['firstname'];
                $data['lastname'] = $param['lastname'];
                $data['email'] = $param['email'];
                $data['language'] = 'english';
                $data['country'] = 'USA';
                //~ $data['quote_id'] = $current_quote;
                
                //----------Save device token ------------
                if (isset($param['device_id'])and isset($param['device_type'])) {
                    $token = $param['device_id'];
                    $token_type = $param['device_type'];
                    if (!empty($token) && !empty($token_type)) {
                        $this->setUserToken($param['email'], $token_type, $token, $data['cust_id']);
                    }
                }
                
                $token = $this->getToken($param['email'],$param['password']);
				$decodedToken = json_decode($token);
				if(isset($decodedToken->message)){
				   $data['token'] = " ";
				}else{
					$data['token'] = $decodedToken;
				}
                
                
            }

             $status_code = 200;
            if($data['cust_id'] == ''){
				$data = [];
				$status_code = 201;
			}
            
            //----------End Save device token ----------- 
            
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
            $jsonArray['status_code'] = 200;
            $jsonArray['status'] = "success";
            $jsonArray['message'] = $message;
			
            //~ $jsonArray['response'] = $data;
            //~ $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
        } catch (\Exception $e) {
		    $jsonArray['data'] = null;
            $jsonArray['status_code'] = "201";
            $jsonArray['status'] = "failure";
            $jsonArray['message'] = $e->getMessage();
			//~ $data['cust_id'] = '';
			//~ $data['message'] = $e->getMessage();
            //~ $jsonArray['response'] = $data;
            //~ $jsonArray['returnCode'] = array('result' => 0, 'resultText' => 'fail');
        }
        $this->getResponse()->setBody(json_encode($jsonArray))->sendResponse();
        die;
    }
    
    protected function sendEmailConfirmation(CustomerInterface $customer, $accountManagement, $redirectUrl)
    {
        try {
            $hash =  $this->_objectManager->create('Magento\Customer\Model\CustomerRegistry')->retrieveSecureData($customer->getId())->getPasswordHash();
            $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED;
            $confirmationStatus = $accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED && $hash != '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_CONFIRMATION;
            } elseif ($hash == '') {
                $templateType = self::NEW_ACCOUNT_EMAIL_REGISTERED_NO_PASSWORD;
            }
            $emaiNotification = $this->_objectManager->get(
                EmailNotificationInterface::class
            );
            $emaiNotification->newAccount($customer, $templateType, $redirectUrl, $customer->getStoreId());
        } catch (MailException $e) {
            // If we are not able to send a new account email, this should be ignored
            $this->logger->critical($e);
        }
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
