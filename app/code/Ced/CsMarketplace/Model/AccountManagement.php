<?php

namespace Ced\CsMarketplace\Model;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\ValidationResultsInterfaceFactory;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Customer\Model\Config\Share as ConfigShare;
use Magento\Customer\Model\Customer as CustomerModel;
use Magento\Customer\Model\Customer\CredentialsValidator;
use Magento\Customer\Model\Metadata\Validator;
use Magento\Eav\Model\Validator\Attribute\Backend;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObjectFactory as ObjectFactory;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Magento\Framework\Encryption\Helper\Security;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\EmailNotConfirmedException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\InvalidEmailOrPasswordException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\ExpiredException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Framework\Exception\State\InvalidTransitionException;
use Magento\Framework\Exception\State\UserLockedException;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Math\Random;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\StringUtils as StringHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface as PsrLogger;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Framework\Session\SaveHandlerInterface;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;

/**
 * Handle various customer account actions
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class AccountManagement extends \Magento\Customer\Model\AccountManagement
{
   
    /**
     * @deprecated
     */
    const XML_PATH_FORGOT_EMAIL_TEMPLATE = 'vendor/password/forgot_email_template';
    public function __construct(
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Random $mathRandom,
        Validator $validator,
        ValidationResultsInterfaceFactory $validationResultsDataFactory,
        AddressRepositoryInterface $addressRepository,
        CustomerMetadataInterface $customerMetadataService,
        PsrLogger $logger,
        Encryptor $encryptor,
        ConfigShare $configShare,
        StringHelper $stringHelper,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        DataObjectProcessor $dataProcessor,
        \Magento\Customer\Model\CustomerRegistry $customerRegistry,
        Registry $registry,
        CustomerViewHelper $customerViewHelper,
        DateTime $dateTime,
        CustomerModel $customerModel,
        ObjectFactory $objectFactory,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter,
        \Ced\CsMarketplace\Model\EmailNotification $emailNotification,
        CredentialsValidator $credentialsValidator = null,
        DateTimeFactory $dateTimeFactory = null,
        SessionManagerInterface $sessionManager = null,
        SaveHandlerInterface $saveHandler = null,
        CollectionFactory $visitorCollectionFactory = null
    ) {
        $this->eventManager = $eventManager;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->customerRegistry = $customerRegistry;
        $this->validator = $validator;
        $this->validationResultsDataFactory = $validationResultsDataFactory;
        $this->addressRepository = $addressRepository;
        $this->customerMetadataService = $customerMetadataService;
        $this->logger = $logger;
        $this->encryptor = $encryptor;
        $this->configShare = $configShare;
        $this->stringHelper = $stringHelper;
        $this->customerRepository = $customerRepository;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->dataProcessor = $dataProcessor;
        $this->registry = $registry;
        $this->customerViewHelper = $customerViewHelper;
        $this->dateTime = $dateTime;
        $this->customerModel = $customerModel;
        $this->objectFactory = $objectFactory;
        $this->emailNotification=$emailNotification;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
        $this->credentialsValidator =
            $credentialsValidator ?: ObjectManager::getInstance()->get(CredentialsValidator::class);
        $this->dateTimeFactory = $dateTimeFactory ?: ObjectManager::getInstance()->get(DateTimeFactory::class);
        $this->sessionManager = $sessionManager
            ?: ObjectManager::getInstance()->get(SessionManagerInterface::class);
        $this->saveHandler = $saveHandler
            ?: ObjectManager::getInstance()->get(SaveHandlerInterface::class);
        $this->visitorCollectionFactory = $visitorCollectionFactory
            ?: ObjectManager::getInstance()->get(CollectionFactory::class);
    }



    public function initiatePasswordReset($email, $template, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        }
        // load customer by email
        $customer = $this->customerRepository->get($email, $websiteId);

        $newPasswordToken = $this->mathRandom->getUniqueHash();
        $this->changeResetPasswordLinkToken($customer, $newPasswordToken);

        try {
            switch ($template) {
                case AccountManagement::EMAIL_REMINDER:
                    $this->getEmailNotification()->passwordReminder($customer);
                    break;
                case AccountManagement::EMAIL_RESET:
                     $this->emailNotification->passwordResetConfirmation($customer);
                    break;
                default:
                    throw new InputException(__(
                        'Invalid value of "%value" provided for the %fieldName field. '.
                        'Possible values: %template1 or %template2.',
                        [
                            'value' => $template,
                            'fieldName' => 'template',
                            'template1' => AccountManagement::EMAIL_REMINDER,
                            'template2' => AccountManagement::EMAIL_RESET
                        ]
                    ));
            }

            return true;
        } catch (MailException $e) {
            // If we are not able to send a reset password email, this should be ignored
            $this->logger->critical($e);
        }

        return false;
    }

        private function getEmailNotification()
            {
                if (!($this->emailNotification instanceof EmailNotificationInterface)) {
                    return \Magento\Framework\App\ObjectManager::getInstance()->get(
                        EmailNotificationInterface::class
                    );
                } else {
                    return $this->emailNotification;
                }
            }

            public function validateResetPasswordLinkToken($customerId, $resetPasswordLinkToken)
            {
                $this->validateResetPasswordToken($customerId, $resetPasswordLinkToken);
                return true;
            }

            private function validateResetPasswordToken($customerId, $resetPasswordLinkToken)
            {
                if (empty($customerId) || $customerId < 0) {
                    throw new InputException(
                        __(
                            'Invalid value of "%value" provided for the %fieldName field.',
                            ['value' => $customerId, 'fieldName' => 'customerId']
                        )
                    );
                }
                if (!is_string($resetPasswordLinkToken) || empty($resetPasswordLinkToken)) {
                    $params = ['fieldName' => 'resetPasswordLinkToken'];
                    throw new InputException(__('%fieldName is a required field.', $params));
                }

                $customerSecureData = $this->customerRegistry->retrieveSecureData($customerId);
                $rpToken = $customerSecureData->getRpToken();
                $rpTokenCreatedAt = $customerSecureData->getRpTokenCreatedAt();

                if (!Security::compareStrings($rpToken, $resetPasswordLinkToken)) {
                    throw new InputMismatchException(__('Reset password token mismatch.'));
                } elseif ($this->isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)) {
                    throw new ExpiredException(__('Reset password token expired.'));
                }

                return true;
            }

            public function isResetPasswordLinkTokenExpired($rpToken, $rpTokenCreatedAt)
            {
                if (empty($rpToken) || empty($rpTokenCreatedAt)) {
                    return true;
                }

                $expirationPeriod = $this->customerModel->getResetPasswordLinkExpirationPeriod();

                $currentTimestamp = $this->dateTimeFactory->create()->getTimestamp();
                $tokenTimestamp = $this->dateTimeFactory->create($rpTokenCreatedAt)->getTimestamp();
                if ($tokenTimestamp > $currentTimestamp) {
                    return true;
                }

                $hourDifference = floor(($currentTimestamp - $tokenTimestamp) / (60 * 60));
                if ($hourDifference >= $expirationPeriod) {
                    return true;
                }

                return false;
            }
            public function changeResetPasswordLinkToken($customer, $passwordLinkToken)
            {
                if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
                    throw new InputException(
                        __(
                            'Invalid value of "%value" provided for the %fieldName field.',
                            ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
                        )
                    );
                }
                if (is_string($passwordLinkToken) && !empty($passwordLinkToken)) {
                    $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
                    $customerSecure->setRpToken($passwordLinkToken);
                    $customerSecure->setRpTokenCreatedAt(
                        $this->dateTimeFactory->create()->format(DateTime::DATETIME_PHP_FORMAT)
                    );
                    $this->setIgnoreValidationFlag($customer);
                    $this->customerRepository->save($customer);
                }
                return true;
            }
            
    /**
     * Set ignore_validation_flag for reset password flow to skip unnecessary address and customer validation
     *
     * @param Customer $customer
     * @return void
     */
    private function setIgnoreValidationFlag($customer)
    {
        $customer->setData('ignore_validation_flag', true);
    }

}
