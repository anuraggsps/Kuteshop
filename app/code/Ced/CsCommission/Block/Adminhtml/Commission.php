<?php
namespace Ced\CsCommission\Block\Adminhtml;
class Commission extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
		
        $this->_controller = 'adminhtml_commission';/*block grid.php directory*/
        $this->_blockGroup = 'Ced_CsCommission';
        $this->_headerText = __('Category Wise Commission');
        $this->_addButtonLabel = __('Add Commission'); 
        parent::_construct();
		
    }


    /**
     * @return string
     */
    public function getCreateUrl()
    {
        if($this->getRequest()->getParam('popup')  )
        {
            return $this->getUrl('*/*/new',['popup'=>true]);
        }
        else
        {
            return $this->getUrl('*/*/new');
        }
    }
}
