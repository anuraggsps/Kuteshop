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
 * @package     Ced_CsEnhancement
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsEnhancement\Helper;


use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\App\Filesystem\DirectoryList;

class Csv extends AbstractHelper
{
    protected $filesystem;

    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        Context $context
    )
    {
        parent::__construct($context);
        $this->filesystem = $filesystem;
    }

    /**
     * @param $filename
     * @param array $csv_row
     * @param string $folder
     * @return array
     */
    public function createCsv($filename, $csv_row = [], $folder = 'csenhancement' ){
        try {
            $filename .= '.csv';
            $filePath = $folder .'/'. $filename;

            $directory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
            $directory->create($folder);

            /* Open file */
            $stream = $directory->openFile($filePath, 'w+');
            $stream->lock();

            foreach ($csv_row as $row) {
                $stream->writeCsv($row);
            }

            $stream->unlock();
            $stream->close();

            $return = [
                'success' => true,
                'path' => $filePath,
                'filename' => $filename,
            ];
        } catch (FileSystemException $e) {
            $return = [ 'success' => false, 'error_code' => $e->getCode(), 'error' => $e->getMessage() ];
        }

        return $return;
    }
}