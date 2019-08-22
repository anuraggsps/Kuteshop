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
 * @package     Ced_CsMarketplace
 * @author 		CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMarketplace\Controller\Adminhtml\Vpayments;

use Magento\Framework\App\Filesystem\DirectoryList;

class MassXmlExport extends \Magento\Framework\App\Action\Action
{

    
    public function execute()
    {
        $fileName = 'vendor_transaction.xml';
        $content = $this->_view->getLayout()->createBlock('Ced\CsMarketplace\Block\Adminhtml\Vpayments\Grid');

        return $this->_objectManager->create('\Magento\Framework\App\Response\Http\FileFactory')->create(
                $fileName,
                $content->getExcelFile($fileName),
                DirectoryList::VAR_DIR
        );
    }
    

    
}
