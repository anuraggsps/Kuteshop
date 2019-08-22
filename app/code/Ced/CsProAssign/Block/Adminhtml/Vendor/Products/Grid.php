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
  * @category  Ced
  * @package   Ced_CsProAssign
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsProAssign\Block\Adminhtml\Vendor\Products;

class Grid extends \Ced\CsMarketplace\Block\Adminhtml\Vproducts\Grid
{
    public static $grid_dynamic = 0;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Ced\CsMarketplace\Model\VproductsFactory $vproductsFactory,
        \Ced\CsMarketplace\Model\Vproducts $vproducts,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder,
        array $data = []
    ) {
 
        parent::__construct($context, $backendHelper, $vproductsFactory, $vproducts, $objectManager, $moduleManager, $pageLayoutBuilder, $data);
        $vendor_id = $this->getRequest()->getParam('vendor_id', 0);
        $this->setId('proassign_vproductGrids_'.$vendor_id);
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->_vproducts = $vproducts;
    }
    
    protected function _prepareColumns()
    {  
        parent::_prepareColumns();
        $this->removeColumn('set_name');
         $this->addColumnAfter(
             'remove',
             [
                'header' => __('Remove'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Remove'),
                        'url' => [
                            'base' => 'csassign/assign/remove',
                            'params'=> ['vendor_id'=>$this->getRequest()->getParam('vendor_id')]
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
             ], 'status'
         );
         return $this;
    }
    
    public function getGridUrl() 
    {
        return $this->getUrl('csassign/assign/vproductsgrid', array('_secure'=>true, '_current'=>true));
    }
    
    protected function _prepareMassaction()
    { 
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Magento_Catalog::product/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('entity_id');   
        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $this->getUrl('csassign/assign/massDelete',array('vendor_id'=>$this->getRequest()->getParam('vendor_id'))),
                'confirm' => __('Are you sure?')
            )
        );
	      $statuses = $this->_vproducts->getMassActionArray();
  	
  	    $this->getMassactionBlock()->addItem('status', array(
  			 'label'=> __('Change status'),
  			 'url'  => $this->getUrl('csassign/assign/massStatus/', array('_current'=>true,'vendor_id'=>$this->getRequest()->getParam('vendor_id'))),
  			 'additional' => array(
  					 'visibility' => array(
  							 'name' => 'status',
  							 'type' => 'select',
  							
  							 'label' => __('Status'),
  							'default'=>'-1',
  							 'values' =>$statuses,
  					 )
  			 )
  	 ));

    $this->getMassactionBlock()->addItem(
            'remove', array(
                'label'    => __('Remove'),
                'url'      => $this->getUrl('csassign/assign/massremove',array('vendor_id'=>$this->getRequest()->getParam('vendor_id'))),
                'confirm'  => __('Are you sure?')
            )
        );
        return $this;
    }
}
