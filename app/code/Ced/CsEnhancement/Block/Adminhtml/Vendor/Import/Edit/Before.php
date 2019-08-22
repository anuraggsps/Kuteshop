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

namespace Ced\CsEnhancement\Block\Adminhtml\Vendor\Import\Edit;


use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Monolog\Handler\IFTTTHandler;

class Before extends \Magento\Backend\Block\Template
{

    const URL_PATH_UPLOAD_IMPORT_FILE = 'csenhancement/vendor_import/upload';
    const URL_PATH_VALIDATE_FILE = 'csenhancement/vendor_import/validate';
    const URL_PATH_EXPORT_VENDORS_CSV = 'csenhancement/vendor_import/exportcsvformat';
    const URL_PATH_IMPORT_VENDORS_CSV = 'csenhancement/vendor_import/save';

    protected $attributeHelper;

    protected $fileHelper;

    protected $vendorCollectionFactory;

    protected $jsonEncoder;

    public function __construct(
        \Ced\CsEnhancement\Helper\Attribute $attributeHelper,
        \Ced\CsEnhancement\Helper\File $fileHelper,
        \Ced\CsMarketplace\Model\ResourceModel\Vendor\CollectionFactory $vendorCollectionFactory,
        \Magento\Framework\Serialize\Serializer\Json $jsonEncoder,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    ) {
        $this->attributeHelper = $attributeHelper;
        $this->fileHelper = $fileHelper;
        $this->vendorCollectionFactory = $vendorCollectionFactory;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * @return float
     */
    public function getMaxSize()
    {
        return $this->fileHelper->getMaxFileSize();
    }

    /**
     * @return string
     */
    public function getFileUploadUrl()
    {
        return $this->getUrl(self:: URL_PATH_UPLOAD_IMPORT_FILE, ['action' => 'upload']);
    }

    /**
     * @return string
     */
    public function getFileDeleteUrl()
    {
        return $this->getUrl(self:: URL_PATH_UPLOAD_IMPORT_FILE, ['action' => 'delete']);
    }

    /**
     * @return string
     */
    public function getExportCsvUrl()
    {
        return $this->getUrl(self:: URL_PATH_EXPORT_VENDORS_CSV);
    }

    /**
     * @return string
     */
    public function getValidateUrl()
    {
        return $this->getUrl(self:: URL_PATH_VALIDATE_FILE);
    }

    /**
     * @return string
     */
    public function getImportUrl()
    {
        return $this->getUrl(self:: URL_PATH_IMPORT_VENDORS_CSV);
    }

    /**
     * @param array $unique_attributes
     * @return string
     */
    public function listVendors($unique_attributes = [])
    {
        $result = [];
        try {
            $data = $this->vendorCollectionFactory->create()
                //->addAttributeToFilter('status', ['eq' => \Ced\CsMarketplace\Model\Vendor::VENDOR_APPROVED_STATUS])
                ->addAttributeToSelect('*');

            foreach( $data as $row ) {
                /** @var \Ced\CsMarketplace\Model\Vendor $row */
                if (!empty($unique_attributes)) {
                    foreach ($unique_attributes as $unique_attribute) {
                        if ($row->getData($unique_attribute))
                            $result[$unique_attribute][] = $row->getData($unique_attribute);
                    }
                }

                if (!isset($unique_attributes['email'])) {
                    $result['email'][] = $row->getEmail();
                }
            }
        } catch (LocalizedException $e) {
        }
        return $this->jsonEncoder->serialize($result);
    }

    public function getRegistrationAttributesSet()
    {
        $registrationAttributes = [];
        try {
            $registrationAttributes = $this->attributeHelper->getRegistrationAttributes();
        } catch (NoSuchEntityException $e) {
        }

        $result = ['attributes' => [], 'required' => [], 'unique' => []];

        $customerAttributes = $this->attributeHelper->getCustomerFormAttributes();

        foreach ([$customerAttributes, $registrationAttributes] as $attributes) {
            foreach ($attributes as $attribute) {
                /** @var \Ced\CsMarketplace\Model\Vendor\Attribute|\Magento\Eav\Model\Entity\Attribute $attribute $attribute */
                $result['attributes'][$attribute->getAttributeCode()] = $attribute->getFrontend()->getClass();

                if ($attribute->getIsRequired())
                    $result['required'][] = $attribute->getAttributeCode();

                if ($attribute->getIsUnique())
                    $result['unique'][] = $attribute->getAttributeCode();
            }
        }

        if (isset($result['attributes']['email']) ) {
            $result['attributes']['email'] = (empty($result['attributes']['email']) ? '' : $result['attributes']['email'] . ' ') . 'validate-email';

            if (!in_array('email', $result['unique'])) $result['unique'][] = 'email';
        }

        $result['headers'] = array_keys($result['attributes']);
        return $result;
    }

    public function jsonEncodeData($data = [])
    {
        return $this->jsonEncoder->serialize($data);
    }
}