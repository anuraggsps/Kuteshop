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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Controller\Adminhtml\Membership;
use Magento\Backend\App\Action;
use Magento\Framework\App\Config\ScopeConfigInterface;
class Save extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registerInterface
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_coreRegistry = $registerInterface;
        parent::__construct($context);
    }

    public function execute()
    {
        $post_data=$this->getRequest()->getPostValue();
        if($this->getRequest()->isPost()){
            $redirectBack   = $this->getRequest()->getParam('back',false);
            if (isset($post_data['image']['delete']) && $post_data['image']['delete'] == 1) {
                if ($post_data['image']['value'] != '')
                    //$this->removeFile($post_data['image']['value']);
                    $post_data['image'] = '';
                }
            if (!empty($post_data['image']['value'])) {
                $post_data['image'] = $post_data['image']['value'];
            } 
           
            /*
            *upload image
            */
            if(!empty($_FILES['image']['name']) && strlen($_FILES['image']['name'])>0) {
                try 
                {
                    $fieldName = $_FILES['image']['name'];
                    $mediaDirectory = $this->_objectManager->get('Magento\Framework\Filesystem')->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
                    $path = $mediaDirectory->getAbsolutePath('ced/csmembership/images');
                    $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader', array('fileId' => "image"));
                    $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png')); 
                    $uploader->setAllowRenameFiles(false);
                    $uploader->setFilesDispersion(false);
                    $fileData = $uploader->validateFile(); 
                    $extension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                    $fileName = $fieldName;
                    $flag = $uploader->save($path, $fileName);
                    $post_data['image'] ='ced/csmembership/images/'.$_FILES['image']['name'];
                    if(strlen($post_data['image']) == 0){
                        $post_data['image']='noimage';
                    }
                }
                catch(\Exception $e) 
                {
                    $this->messageManager->addError("Image Not Uploaded");
                    $this->_redirect("*/*/");
                    return;
                }       
            }
            $mode = '';
            $editid = $this->getRequest()->getParam('id');
            if($editid){
                $mode = \Ced\CsMembership\Model\Membership::PRODUCT_EDIT;
            }else{
                $mode = \Ced\CsMembership\Model\Membership::PRODUCT_NEW;
            }

            $product_id = new \Magento\Framework\DataObject();
            /*
            *to create associates virtual product
            */ 
            try{
                $this->_eventManager->dispatch('create_membership_virtual_product', array('result'=>$product_id,'mode'=>$mode,'editid'=>$editid,'postdata'=>$post_data));   
                if($product_id->getResult()){
                    $post_data['product_id']=$product_id->getResult();
                    $post_data['store'] = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore(null)->getStoreId();
                    if(!$this->getRequest()->getParam('id'))
                        if(isset($post_data['category']))
                            $post_data['category_ids'] =  implode(',', $post_data['category']);
                   
                    $model = $this->_objectManager->create('Ced\CsMembership\Model\Membership')
                                ->addData($post_data)
                                ->setId($this->getRequest()->getParam("id"));
                    $model->save();
                    //print_r($model->getData());die;
                    $this->messageManager->addSuccess(__('Membership Plan Saved Successfully.')); 
                }  
            }catch(\Exception $e){
                $this->messageManager->addError(__($e->getMessage()));
                $this->_redirect("*/*/");
                return;
            } 
             
            if ($redirectBack) {
              //  $model = $this->_objectManager->create('Ced\CsMembership\Model\Membership')->load($editid);
                $this->_redirect('*/*/edit', array(
                                    'id'    =>  $model->getId(),
                                    '_current'=>true
                ));
                return;
            }    
            $this->_redirect("*/*/");
        }else{
            $this->_redirect("*/*/");
        }
    }
}
