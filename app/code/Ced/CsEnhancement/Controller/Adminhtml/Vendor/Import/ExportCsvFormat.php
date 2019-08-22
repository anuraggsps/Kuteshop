<?php
/**
 * Created by PhpStorm.
 * User: cedcoss
 * Date: 3/7/19
 * Time: 1:51 PM
 */

namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import;


use Ced\CsEnhancement\Helper\Attribute;
use Ced\CsEnhancement\Helper\Csv;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Backend\App\Action;
use Magento\Framework\Exception\NoSuchEntityException;

class ExportCsvFormat extends Action
{
    protected $attributeHelper;

    protected $csv;

    protected $_fileFactory;

    public function __construct(
        Attribute $attributeHelper,
        Csv $csv,
        FileFactory $fileFactory,
        Action\Context $context
    )
    {
        parent::__construct($context);
        $this->attributeHelper = $attributeHelper;
        $this->csv = $csv;
        $this->_fileFactory = $fileFactory;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @throws \Exception
     */
    public function execute()
    {
        $fileName = 'VendorImport';

        $dataRows = $this->getDataRows();
        $createFile = $this->csv->createCsv($fileName, $dataRows);
        $content = [];

        if( !empty($createFile['success']) ) {
            $content['type'] = 'filename'; // must keep filename
            $content['value'] = $createFile['path'];
            $content['rm'] = '1'; //remove csv from var folder
        }
        $csv_file_name = ucfirst($fileName) . 'Format.csv';
        return $this->_fileFactory->create($csv_file_name, $content, DirectoryList::VAR_DIR);
    }

    protected function getDataRows()
    {
        $registrationAttributes = $result = [];
        try {
            $registrationAttributes = $this->attributeHelper->getRegistrationAttributes();
        } catch (NoSuchEntityException $e) {
        }

        $i = 0;
        $customerAttributes = $this->attributeHelper->getCustomerFormAttributes();

        foreach ([$customerAttributes, $registrationAttributes] as $attributes) {
            foreach ($attributes as $attribute) {
                /** @var \Ced\CsMarketplace\Model\Vendor\Attribute $attribute */
                $result[0][$i++] = $attribute->getAttributeCode();
                //$result[1][$i++] = $this->getRegistrationAttributesDummyValue($attribute);
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @return string
     */
    public function getRegistrationAttributesDummyValue($attribute)
    {
        $result = '';
        if (is_object($attribute) && !empty($attribute->getFrontend()->getClass())) {
            $frontendClass = $attribute->getFrontend()->getClass();
            switch ($frontendClass) {
                case 'validate-email'   :
                    $result = 'abc@domain.com';
                    break;

                case 'validate-digits'  :
                case 'validate-number' :
                    $result = '123';
                    break;

                case 'validate-alpha'  :
                    $result = 'abc';
                    break;

                case 'validate-alphanum'  :
                    $result = 'abc123';
                    break;
            }
        }

        return $result;
    }
}