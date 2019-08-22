<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsEnhancement
 * @author   	 CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import;


use Magento\Backend\App\Action;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\File\Csv;

/**
 * Class Validate
 * @package Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import
 */
class Validate extends Action
{

    /**
     * @var Csv
     */
    protected $csv;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $jsonEncoder;

    protected $countryCollection;

    protected $regionFactory;

    protected $customerFactory;

    protected $customerResourceModel;

    protected $vendorFactory;

    protected $vendorCollectionFactory;

    protected $vendorResourceModel;

    protected $resourceConnection;

    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\Collection/*Factory*/ $countryCollection,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel,
        \Ced\CsMarketplace\Model\Vendor/*Factory*/ $vendorFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResourceModel,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\Collection/*Factory*/ $vendorCollectionFactory,
        Csv $csv,
        \Magento\Framework\Serialize\Serializer\Json $jsonEncoder,
        Action\Context $context
    )
    {
        parent::__construct($context);
        $this->csv = $csv;
        $this->jsonEncoder = $jsonEncoder;
        $this->countryCollection = $countryCollection;
        $this->regionFactory = $regionFactory;
        $this->customerFactory = $customerFactory;
        $this->customerResourceModel = $customerResourceModel;
        $this->vendorFactory = $vendorFactory;
        $this->vendorResourceModel = $vendorResourceModel;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     */
    public function execute()
    {
        $csvData = [];
        try {
            $filePath = $this->getRequest()->getParam('path');
            $csvData = $this->csv->getData($filePath);
        } catch (\Exception $e) {
        }

        $this->getResponse()->setBody($this->jsonEncoder->serialize($csvData));
    }

    public function import($csvData, $uniqueAttributes = [], $requiredAttributes = []) {
        $successCount = 0;
        if (!empty($csvData)) {
            $connection = $this->resourceConnection->getConnection();
            foreach ($csvData as $row => $rowData) {
                $error = 0;
                $publicName = isset($rowData['public_name']) ? $rowData['public_name'] : '';
                $shopUrl = isset($rowData['shopUrl']) ? $rowData['shopUrl'] : $publicName;

                //required check
                foreach ($requiredAttributes as $requiredAttribute) {
                    if (empty($rowData[$requiredAttribute])) {
                        $this->messageManager->addErrorMessage(__("Error Occurred in row {$row} : value for {$uniqueAttribute} is required"));
                        $error++;
                    }
                }

                //unique key check
                foreach ($uniqueAttributes as $uniqueAttribute) {
                    if (array_key_exists($uniqueAttribute, $rowData) &&
                        $this->checkForUnique($uniqueAttribute, $rowData[$uniqueAttribute])) {
                        $this->messageManager->addErrorMessage(__("Error Occurred in row {$row} : value for {$uniqueAttribute} already exist"));
                        $error++;
                    }
                }

                if (!$error && !empty($rowData['email']) && !empty($shopUrl)) {
                    //get Country code
                    if (isset($rowData['country_id'])) {
                        if (strlen($rowData['country_id']) > 3) {
                            $countries = $this->countryCollection/*->create()*/;
                            foreach ($countries as $country) {
                                /** @var \Magento\Directory\Model\Country $country */
                                if (strtolower($rowData['country_id']) == strtolower($country->getName())) {
                                    $rowData['country_id'] = $country->getCountryId();
                                    break;
                                }
                            }
                        }

                        //get Region code
                        if (isset($rowData['region_id'])) {
                            switch (strlen($rowData['region_id'])) {
                                case 2:
                                    $regionModel = $this->regionFactory->create()->loadByCode(
                                        $rowData['country_id'],
                                        $rowData['region_id']
                                    );
                                    break;
                                default:
                                    $regionModel = $this->regionFactory->create()->loadByName(
                                        $rowData['country_id'],
                                        $rowData['region_id']
                                    );
                                    break;
                            }

                            if ($regionModel->getId()) {
                                $rowData['region_id'] = $regionModel->getId();
                                unset($rowData['region']);
                            }
                        }
                    }

                    $customerExist = $this->checkCustomerExist($rowData['email'], (isset($rowData['website_id'])?:null));
                    $customerModel = ($customerExist) ? $customerExist : $this->customerFactory->create();

                    $connection->beginTransaction();
                    $customerModel->setData($rowData);
                    try {
                        $this->customerResourceModel->save($customerModel);
                        $customer_id = $customerModel->getId();
                    } catch (\Exception $e) {
                        $connection->rollBack();
                    }

                    if (!empty($customer_id)) {
                        $vendorModel = $this->vendorFactory/*->create()*/;
                        $vendorModel->setCustomer($customerModel);
                        //$vendorModel->setData($rowData);
                        $vendorModel->register($rowData);

                        try {
                            if ($vendorModel->getData('errors')) {
                                $e = '';
                                foreach ($vendorModel->getData('errors') as $error) {
                                    $e = (!empty($e)) ? $e .'<br>'. $error : $error;
                                }

                                throw new \Exception($e);
                            }

                            $this->vendorResourceModel->save($vendorModel);
                            $connection->commit();
                            $successCount++;
                        } catch (AlreadyExistsException $e) {
                            $connection->rollBack();
                            $this->messageManager->addErrorMessage(__("Error Occurred in row : {$row}") . $e->getMessage());
                        } catch (\Exception $e) {
                            $connection->rollBack();
                            $this->messageManager->addErrorMessage(__("Error Occurred in row : {$row}") . $e->getMessage());
                        }
                    }
                }
            }
        }
    }

    protected function checkCustomerExist ($email, $website_id = null) {
        $customer = $this->customerFactory->create();

        if ($website_id)
            $customer->setWebsiteId($website_id);

        $customer->loadByEmail($email);
        return ($customer->getEmail()) ? $customer : false;
    }

    protected function checkForUnique ($attribute, $value) {
        $vendorCollection = $this->vendorCollectionFactory/*->create()*/;
        $vendorCollection->addFieldToFilter($attribute, ['eq' => $value]);

        return ($vendorCollection && $vendorCollection->getSize() > 0) ?: false;
    }
}