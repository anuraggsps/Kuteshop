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
                
                
                
                
                $data['cust_id'] = $custModel->getId();
                $data['name'] = $param['firstname']." ".$param['lastname'];
                $data['firstname'] = $param['firstname'];
                $data['lastname'] = $param['lastname'];
                $data['emial'] = $param['email'];
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

            
            
            //----------End Save device token ----------- 
			

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
}
