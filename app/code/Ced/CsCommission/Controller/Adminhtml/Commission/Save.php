<?php
namespace Ced\CsCommission\Controller\Adminhtml\Commission;
use Magento\Framework\App\Filesystem\DirectoryList;
class Save extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
	public function execute()
    {
		

        $data = $this->getRequest()->getParams();
        if ($data) {
            $model = $this->_objectManager->create('Ced\CsCommission\Model\Commission');

			$id = $this->getRequest()->getParam('id');
            if ($id) {
                $model->load($id);
            }
			if($type = $this->_session->getCedtype()){
                $data['type'] = $type;
                $data['type_id'] = $this->_session->getCedtypeid();
            }
            if($vendorId = $this->_session->getCedVendorId()){
                $data['vendor'] = $vendorId;
            }

            switch ($data['method']) {
                case "fixed":
                    $data['fee'] = round($data['fee'], 2);
                    break;
                case "percentage":
                    $data['fee'] = min((int)$data['fee'], 100);
                    break;
            }
            $model->setData($data);
			
            try {
                $model->save();
                $this->messageManager->addSuccess(__('The Commission Has been Saved.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    if($this->getRequest()->getParam('popup')){
                        $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true,'popup'=>true));
                    }
                    else
                        $this->_redirect('*/*/edit', array('id' => $model->getId(), '_current' => true));
                    return;
                }
                if($this->getRequest()->getParam('popup')){
                    $this->_redirect('*/*/', array('popup'=>true));
                }
                else
                    $this->_redirect('*/*/');
                return;
            } catch (\Magento\Framework\Model\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the Commission.'));
            }

            $this->_getSession()->setFormData($data);
            
            if($this->getRequest()->getParam('popup')){
                $this->_redirect('*/*/', array('id' => $this->getRequest()->getParam('id'),'popup'=>true));
            }
            else
                $this->_redirect('*/*/', array('id' => $this->getRequest()->getParam('id')));
            return;
        }

        if($this->getRequest()->getParam('popup')){
            $this->_redirect('*/*/', array('popup'=>true));
        }
        else
            $this->_redirect('*/*/');
    }
}
