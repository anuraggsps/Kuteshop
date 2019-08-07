<?php

namespace Softprodigy\Minimart\Block\Adminhtml\Homedesign;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use \Softprodigy\Minimart\Model\ResourceModel\Homedesign\CollectionFactory AS sectionCollection;

/**
 * Description of Grid
 *
 * @author mannu
 */
class Grid extends \Magento\Backend\Block\Widget\Grid\Extended {

     
    protected $_collectionFactory;
    protected $_objectManager;
    protected $_dealHelper;

    /**
     * @var \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface
     */
    protected $pageLayoutBuilder;
    protected $_resourceModel;

    /**
     * 
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param sectionCollection $collectionFactory
     * @param \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder
     * @param \Magento\Framework\App\ResourceConnection $_resouceModel
     * @param \Softprodigy\Minimart\Helper\Data $dealHelper
     * @param array $data
     */
    public function __construct(
    \Magento\Backend\Block\Template\Context $context, 
            \Magento\Backend\Helper\Data $backendHelper, 
            sectionCollection $collectionFactory, 
            \Magento\Framework\View\Model\PageLayout\Config\BuilderInterface $pageLayoutBuilder, 
            \Magento\Framework\App\ResourceConnection $_resouceModel, 
            \Softprodigy\Minimart\Helper\Data $dealHelper, array $data = []
    ) {
        //var_dump($collectionFactory); exit;
        $this->_collectionFactory = $collectionFactory;
        $this->_resourceModel = $_resouceModel;
        $this->pageLayoutBuilder = $pageLayoutBuilder;
        $this->_dealHelper = $dealHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct() {
        parent::_construct();
        $this->setId('ogbHomedesignGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(false);
        //$this->setUseAjax(true);
    }

    /**
     * Prepare collection
     *
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection() {

        $collection = $this->_collectionFactory->create();
        $collection->getSelect()->order('id DESC');

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns() {

        $this->addColumn(
                'id', [
            'header' => __('Id'),
            'index' => 'id',
                ]
        );

        $this->addColumn(
                'store_id', [
            'header' => __('Store View'),
            'index' => 'store_id',
            'type' => 'store',
            'store_all' => false,
            'store_view' => true,
            'sortable' => true,
            'skipEmptyStoresLabel' => true,
            'filter_condition_callback' => [$this, '_filterStoreCondition']
                ]
        );

        $this->addColumn(
                'title', [
            'header' => __('Title'),
            'index' => 'title',
            'align' => 'center',
                //'type' => 'text',
                ]
        );

        $this->addColumn(
                'layout_col', [
            'header' => __('Section Layout'),
            'align' => 'center',
            'type' => 'options',
            'index' => 'layout_col',
            'options' => ['13' => '13 Blocks', '9' => '9 Blocks']
                ]
        );

        $this->addColumn(
                'status', [
            'header' => __('Status'),
            'align' => 'center',
            'type' => 'options',
            'index' => 'status',
            'options' => ['active' => 'Enabled', 'inactive' => 'Disabled']
                ]
        );

        $this->addColumn(
                'position', [
            'header' => __('Section position'),
            'align' => 'center',
            'type' => 'options',
            'index' => 'position',
            'options' => ['first' => 'First', 'second' => 'Second', 'third' => 'Third', 'fourth' => 'Fourth', 'fifth' => 'Fifth']
                ]
        );



        $this->addColumn(
                'created_at', [
            'header' => __('Created On'),
            'index' => 'created_at',
            'type' => 'datetime',
            'header_css_class' => 'col-date',
            'column_css_class' => 'col-date'
                ]
        );
        $this->addColumn(
                'last_modified', [
            'header' => __('Last Modified'),
            'index' => 'last_modified',
            'type' => 'datetime',
            'header_css_class' => 'col-date',
            'column_css_class' => 'col-date'
                ]
        );

        return parent::_prepareColumns();
    }

    /**
     * After load collection
     *
     * @return void
     */
    protected function _afterLoadCollection() {
        // $this->getCollection()->walk('afterLoad');
        // parent::_afterLoadCollection();
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column) {
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * Filter store condition
     *
     * @param \Magento\Framework\Data\Collection $collection
     * @param \Magento\Framework\DataObject $column
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _filterStoreCondition($collection, \Magento\Framework\DataObject $column) {
        if (!($value = $column->getFilter()->getValue())) {
            return;
        }

        $this->getCollection()->getSelect()->having('store_id=' . $value);
    }

    /**
     * Row click url
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row) {
        
        return $this->getUrl('*/*/edit', ['id' => $row->getId()]);
    }

}
