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
class Create extends \Magento\Backend\App\Action {

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
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Softprodigy_Minimart::Homedesign');
        $layput = $this->getRequest()->getParam('layout', false);
        if ($layput) {
            $data = $this->getRequest()->getParams();

            $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
        }
        $resultPage->getConfig()->getTitle()->prepend(__('Lets add new section'));
        return $resultPage;
    }

}
