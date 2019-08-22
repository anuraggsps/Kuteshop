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

namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor;


use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Import extends \Magento\Backend\App\Action
{
    protected $resultPageFactory;

    protected $coreRegistry;

    protected $fileHelper;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        \Ced\CsEnhancement\Helper\File $fileHelper,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->coreRegistry = $coreRegistry;
        $this->fileHelper = $fileHelper;
    }

    /**
     * Execute action based on request and return result
     *
     * Note: Request will be added as operation argument in future
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->messageManager->addNoticeMessage(
            $this->fileHelper->getMaxFileSizeMessage()
        );

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Ced_CsEnhancement::import_vendors');
        $resultPage->addBreadcrumb(__('Marketplace'), __('Marketplace'));
        $resultPage->addBreadcrumb(__('Import Vendors'), __('Import Vendors'));
        $resultPage->getConfig()->getTitle()->prepend(__('Import Vendors'));

        return $resultPage;
    }
}