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
  * @package     VendorsocialLogin
  * @author      CedCommerce Core Team <connect@cedcommerce.com>
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license     http://cedcommerce.com/license-agreement.txt
  */

/**
 * VendorsocialLogin 	Twitter Helper
 *
 * @category   	Ced
 * @package    	Ced_VendorsocialLogin
 * @author		CedCommerce Magento Core Team <connect@cedcommerce.com>
 */
namespace Ced\VendorsocialLogin\Helper;

use Magento\Customer\Service\V1\CustomerAccountServiceInterface;


class Twitter extends \Magento\Framework\App\Helper\AbstractHelper

{

/** @var \Magento\Framework\ObjectManager */
    private $_objectManager;
   
    /**

     *

     * @var \Magento\Customer\Model\Session

     */

    protected $_customerSession;

 
	


    /**

     *

     * @var \Magento\Customer\Model\CustomerFactory

     */

    protected $_customerFactory;


  

    /**

     * Twitter client model

     *

     * @var \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client

     */

    protected $_client; 

	/**
		@param \Magento\Store\Model\StoreManagerInterface $storeManager

        @param \Magento\Customer\Model\Session $customerSession

		@param \Magento\Framework\ObjectManagerInterface $objectManager
		
        @param \Magento\Customer\Model\CustomerFactory $customerFactory

        @param \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory

        @param \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client $client

        @param \Magento\Framework\App\Helper\Context $context
		*/

    public function __construct(

        \Magento\Store\Model\StoreManagerInterface $storeManager,

        \Magento\Customer\Model\Session $customerSession,

		\Magento\Framework\ObjectManagerInterface $objectManager,
		
        \Magento\Customer\Model\CustomerFactory $customerFactory,

        \Magento\Framework\HTTP\ZendClientFactory $httpClientFactory,

        \Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client $client,

        \Magento\Framework\App\Helper\Context $context)

    {
	

	    $this->_objectManager =  $objectManager;

        $this->_customerSession = $customerSession;
		
        $this->_customerFactory = $customerFactory;



        $this->_client = $client;

        parent::__construct($context);

    }

	/*
	*	connect existing account with Twitter
	* 	@param int $customerId
	*	@param string $twitterId
	*	@param string $token
	*/
    public function connectByTwitterId(
		$customerId,
        $twitterId,
        $token
        )

    {

		$customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
		$customer->load($customerId);
		$customer->setCedSocialloginGid($twitterId);
		$customer->setCedSocialloginGtoken($token);
		$customer->save();
        $this->_customerSession->setCustomerAsLoggedIn($customer);

    }

	/*
	*	connect new account with Twitter
	*	@param string $email
	*	@param string $firstname
	*	@param string $lastname
	*	@param string $twitterId
	*	@param string $token
	*/
    public function connectByCreatingAccount(
        $email,
        $name,
		$twitterId,
        $token)

    {
	
	 	$name = explode(' ', $name, 2);
        
        if(count($name) > 1) {
            $firstName = $name[0];
            $lastName = $name[1];
        } else {
            $firstName = $name[0];
            $lastName = $name[0];
        }
		
		
		$customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customerDetails = array(
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $email,
            'sendemail' => 0,
            'confirmation' => 0,
			'ced_sociallogin_tid' =>$twitterId,
			'ced_sociallogin_ttoken' =>$token
        );
		$customer->setData($customerDetails);
		$customer->save();
		$customer->sendNewAccountEmail('confirmed', '');
        $this->_customerSession->setCustomerAsLoggedIn($customer);
    }


	/*
	*	login by customer
	*	@param \Magento\Customer\Model\Customer $customer
	*/
    public function loginByCustomer(\Magento\Customer\Model\Customer $customer)

    {

        if($customer->getConfirmation()) {

            $customer->setConfirmation(null);

            $customer->save();

        }

        $this->_customerSession->setCustomerAsLoggedIn($customer);

    }
	
	/*
	*	get customer by twitter id
	*	@param int $twitterId
	*
	*	return \Magento\Customer\Model\Customer $customer
	*/
    public function getCustomersByTwitterId($twitterId)

    {

        $customer = $this->_customerFactory->create();

        $collection = $customer->getResourceCollection()

            ->addAttributeToSelect('*')

            ->addAttributeToFilter('ced_sociallogin_tid', $twitterId)

            ->setPage(1, 1);

        return $collection;

    }

	/*
	*	get customer by email id
	*	@param string $email
	*
	*	return \Magento\Customer\Model\Customer $customer
	*/
    public function getCustomersByEmail($email)

    {

        $customer = $this->_customerFactory->create();

        $collection = $customer->getResourceCollection()

            ->addAttributeToSelect('*')

            ->addAttributeToFilter('email', $email)

            ->setPage(1, 1);

        return $collection;

    }


}