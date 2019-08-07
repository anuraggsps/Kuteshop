<?php

namespace Softprodigy\Minimart\Controller\Adminhtml\Homedesign;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Softprodigy\Minimart\Model\Homedesign AS homedesignModel;

/**
 * Description of Index
 *
 * @author root
 */
class Edit extends \Magento\Backend\App\Action {

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $homedesignModel;
    protected $_coreRegistry;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
    Context $context, PageFactory $resultPageFactory, homedesignModel $homedesignModel, \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->homedesignModel = $homedesignModel;
        $this->_coreRegistry = $registry;
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

        $Id = $this->getRequest()->getParam('id');
        $model = $this->homedesignModel->load($Id);

        if ($Id && !$model->getId()) {
            $this->messageManager->addError(__('This Section no longer exists.'));
            /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/index');
        }
        if($model->getId()){
            $this->_coreRegistry->register('section_data', $model);
        } 
        $resultPage = $this->resultPageFactory->create();

        $resultPage->setActiveMenu('Softprodigy_Minimart::Homedesign');

        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Section: %1', $model->getTitle()) : __('Lets add new section'));
        return $resultPage;
    }

}
