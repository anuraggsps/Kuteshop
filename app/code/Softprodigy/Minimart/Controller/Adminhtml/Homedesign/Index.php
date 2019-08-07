<?php

namespace Softprodigy\Minimart\Controller\Adminhtml\Homedesign;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Description of Index
 *
 * @author root
 */
class Index extends \Magento\Backend\App\Action {

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
    Context $context, PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed() {
        return true; //$this->_authorization->isAllowed('Softprodigy_Minimart::Homedesign');
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute() {

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Softprodigy_Dailydeal::Homedesign');
        $resultPage->addBreadcrumb(__('OnGoBuyo: Design Homepage'), __('Design Homepage'));
        $resultPage->getConfig()->getTitle()->prepend(__('OnGoBuyo: Design Homepage'));
        return $resultPage;
    }

}
