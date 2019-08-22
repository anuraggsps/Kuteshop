<?php
namespace Ced\CsCommission\Controller\Adminhtml\Commission;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
		$id = $this->getRequest()->getParam('id');
		try {
			$banner = $this->_objectManager->get('Ced\CsCommission\Model\Commission')->load($id);
			$banner->delete();
            $this->messageManager->addSuccess(
                __('Delete successfully !')
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        if($this->getRequest()->getParam('popup')){
            $this->_redirect('*/*/', array('popup'=>true));
        }
        else
            $this->_redirect('*/*/');
    }
}
