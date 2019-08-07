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
class Save extends \Magento\Backend\App\Action {

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $homedesignModel;
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
    Context $context, PageFactory $resultPageFactory,
    \Softprodigy\Minimart\Model\Homedesign $homedesignModel
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->homedesignModel = $homedesignModel;
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
         if ($this->getRequest()->isPost()) {
            $hasStore = $this->getRequest()->getPost('store');
            $hasLayout = $this->getRequest()->getPost('layout');

            if ($hasStore != null and $hasStore >= 0) {
                
                $this->_redirect('*/*/create', ['store' => $hasStore, 'layout' => $hasLayout]);
                return;
            }
            
            try {
                $isedit = false;
                $id = $this->getRequest()->getParam('id', false);
                $model = $this->homedesignModel->load($id);
                if ($model->getId()) {
                    $isedit = true;
                    $id = $model->getId();
                }

                $layout_col = $this->getRequest()->getPost('layout_col');
                $cats = $this->getRequest()->getPost('category');

                if ((int) $layout_col >= count($cats)) {
                    $data = $this->getRequest()->getParams();
                    if (empty($data['section_category'])) {
                        throw new \Exception("Please select section category");
                    }
                    ksort($cats);
                    $categories = array();
                    foreach ($cats as $val) {
                        $categories[] = $val;
                    }
                    $data['cat_ids'] = serialize($categories);
                    $data['main_cat'] = $data['section_category'];

                    $data['updated_at'] = date('Y-m-d H:i:s');

                    if ($isedit) {
                        unset($data['layout_col']);
                        unset($data['store_id']);
                    }
                    
                    if (!$id) {
                        $data['created_at'] = date('Y-m-d H:i:s');
                    }
                    
                    $model->setData($data);
                    
                    if ($id) {
                        $model->setId($id);
                    }
                    
                    $model->save();
                     $this->messageManager->addSuccess('Section has been saved.');
                    $this->_redirect('*/*/index');
                    return;
                } else {
                    throw new \Exception('Invalid Request. Please add valid no of categories only');
                }
            } catch (\Exception $ex) {
                // var_dump($ex->getMessage()); die;
                $this->messageManager->addError($ex->getMessage());
                $data = $this->getRequest()->getParams();
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                if ($isedit) {
                    $this->_redirect('*/*/edit', array('id' => $id));
                    return;
                } else {
                    $this->_redirect('*/*/new', array('store' => $hasStore, 'layout' => $hasLayout));
                    return;
                }
            }
         }
    }

}
