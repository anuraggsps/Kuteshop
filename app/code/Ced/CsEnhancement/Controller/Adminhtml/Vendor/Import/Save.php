<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 8/7/19
 * Time: 7:01 PM
 */

namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import;


use Magento\Framework\Exception\AlreadyExistsException;

class Save extends \Magento\Backend\App\Action
{

    protected $countryCollection;

    protected $regionFactory;

    protected $customerFactory;

    protected $customerResourceModel;

    protected $vendorFactory;

    protected $vendorCollectionFactory;

    protected $vendorResourceModel;

    protected $resourceConnection;

    public function __construct(
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\ResourceModel\Customer $customerResourceModel,
        \Ced\CsMarketplace\Model\VendorFactory $vendorFactory,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor $vendorResourceModel,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory,
        \Magento\Backend\App\Action\Context $context
    )
    {
        parent::__construct($context);
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
        $csvData = $this->getRequest()->getParam('import_data', []);
        $uniqueAttributes = $this->getRequest()->getParam('unique_attribute', '');
        $requiredAttributes = $this->getRequest()->getParam('required_attribute', '');

        if (!empty($csvData) && is_array($csvData)) {
            $result = $this->import(
                $csvData,
                explode(',', $uniqueAttributes),
                explode(',', $requiredAttributes)
            );
        } else {
            $result['errors'][] = __('Invalid Data Supplied');
            $this->messageManager->addErrorMessage(__('Invalid Data Supplied'));
        }

        $this->getResponse()->setBody(json_encode($result));
    }

    public function import($csvData, $uniqueAttributes = [], $requiredAttributes = []) {
        $result = ['errors' => '', 'success' => ''];
        $successCount = 0;
        $connection = $this->resourceConnection->getConnection();
        foreach ($csvData as $row => $rowData) {
            $error = $customer_id =0;
            $publicName = isset($rowData['public_name']) ? $rowData['public_name'] : '';
            $rowData['shop_url'] = isset($rowData['shop_url']) ? $rowData['shop_url'] : $publicName;
            $customerData = $rowData;

            //required check
            foreach ($requiredAttributes as $requiredAttribute) {
                if (empty($rowData[$requiredAttribute])) {
                    $result['errors'][] = __("Error Occurred in row {$row} : value for {$requiredAttribute} is required");
                    $this->messageManager->addErrorMessage(__("Error Occurred in row {$row} : value for {$requiredAttribute} is required"));
                    $error++;
                }
            }

            //unique key check
            foreach ($uniqueAttributes as $uniqueAttribute) {
                if (array_key_exists($uniqueAttribute, $rowData) &&
                    $this->checkForUnique($uniqueAttribute, $rowData[$uniqueAttribute])) {
                    $result['errors'][] = __("Error Occurred in row {$row} : value for {$uniqueAttribute} already exist");
                    $this->messageManager->addErrorMessage(__("Error Occurred in row {$row} : value for {$uniqueAttribute} already exist"));
                    $error++;
                }
            }

            if (!$error && !empty($rowData['email']) && !empty($rowData['shop_url'])) {
                //get Country code
                if (isset($rowData['country_id'])) {
                    if (strlen($rowData['country_id']) > 3) {
                        $countries = $this->countryCollection->create();
                        foreach ($countries as $country) {
                            /** @var \Magento\Directory\Model\Country $country */
                            if (strtolower($rowData['country_id']) == strtolower($country->getName())) {
                                $rowData['country_id'] = $country->getCountryId();
                                break;
                            }
                        }
                    }

                    //get Region code
                    $region_id = isset($rowData['region_id']) ? $rowData['region_id'] : '';
                    $region = isset($rowData['region']) ? $rowData['region'] : $region_id;
                    if ($region) {
                        switch (strlen($region)) {
                            case 2:
                                $regionModel = $this->regionFactory->create()->loadByCode(
                                    $rowData['country_id'],
                                    $region
                                );
                                break;
                            default:
                                $regionModel = $this->regionFactory->create()->loadByName(
                                    $rowData['country_id'],
                                    $region
                                );
                                break;
                        }

                        if ($regionModel->getId()) {
                            $rowData['region_id'] = $regionModel->getId();
                            unset($rowData['region']);
                        }
                    }
                }

                $customerExist = $this->checkCustomerExist($rowData['email'], (isset($rowData['website_id'])?:1));

                if ($customerExist) {
                    unset($customerData['email']);
                    $customerModel = $customerExist;
                } else $customerModel = $this->customerFactory->create();

                $connection->beginTransaction();
                $customerModel->addData($customerData);
                try {
                    $this->customerResourceModel->save($customerModel);
                    $customer_id = $customerModel->getId();
                } catch (\Exception $e) {
                    $result['errors'][] = __("Error Occurred in row {$row} : ") . $e->getMessage();
                    $connection->rollBack();
                }

                if (!empty($customer_id)) {
                    $vendorModel = $this->vendorFactory->create();
                    $vendorModel->setCustomer($customerModel);
                    //$vendorModel->setData($rowData);

                    try {
                        $vendorModel->register($rowData);
                        if ($vendorModel->getData('errors')) {
                            $e = '';
                            foreach ($vendorModel->getData('errors') as $error) {
                                $e = (!empty($e)) ? $e .'<br>'. $error : $error;
                            }

                            throw new \Exception($e);
                        }

                        $vendorModel->save();
                        $this->vendorResourceModel->save($vendorModel);
                        $connection->commit();
                        $successCount++;
                    } catch (AlreadyExistsException $e) {
                        $connection->rollBack();
                        $result['errors'][] = __("Error Occurred in row {$row} : ") . $e->getMessage();
                        $this->messageManager->addErrorMessage(__("Error Occurred in row : {$row}") . $e->getMessage());
                    } catch (\Exception $e) {
                        $connection->rollBack();
                        $result['errors'][] = __("Error Occurred in row {$row} : ") . $e->getMessage();
                        $this->messageManager->addErrorMessage(__("Error Occurred in row : {$row}") . $e->getMessage());
                    }
                }
            }

        }

        if ($successCount > 0) {
            $result['success'][] = __('A total of %s record(s) has been successfully imported', $successCount);
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) has been successfully imported', $successCount));
        }

        return $result;
    }

    protected function checkCustomerExist ($email, $website_id = null) {
        $customer = $this->customerFactory->create();

        if ($website_id)
            $customer->setWebsiteId($website_id);

        $customer->loadByEmail($email);
        return ($customer->getEmail()) ? $customer : false;
    }

    protected function checkForUnique ($attribute, $value) {
        $vendorCollection = $this->vendorCollectionFactory->create();
        $vendorCollection->addFieldToFilter($attribute, ['eq' => $value]);

        return ($vendorCollection && $vendorCollection->getSize() > 0) ?: false;
    }
}