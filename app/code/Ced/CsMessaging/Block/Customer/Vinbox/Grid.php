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
 * @package     Ced_CsMessaging
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */
namespace Ced\CsMessaging\Block\Customer\Vinbox;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;


    protected $_gridFactory;

    protected $_objectManager;

    protected $backendHelper;

    protected $_resource;

    protected $session;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(

        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Customer\Model\Session $customerSession,
        \Ced\CsMessaging\Model\ResourceModel\Vcustomer\CollectionFactory $vcustomerCollectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->session = $session;
        $this->_objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
        $this->_resource = $resource;
        $this->customerSession = $customerSession;
        $this->eavConfig = $eavConfig;
        $this->vcustomerCollectionFactory = $vcustomerCollectionFactory;
        parent::__construct($context, $backendHelper, $data);
        $this->setData('area','adminhtml');
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('customervendorgrid');
        $this->setDefaultSort('Asc');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $vcustomerCollection = $this->vcustomerCollectionFactory->create();
        $currentCustomerId = $this->customerSession->getCustomer()->getId();
        $collection = $vcustomerCollection->addFieldToFilter('customer_id',$currentCustomerId);
        $collection->setOrder('updated_at','desc');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {

        $this->addColumn(
            'sender_name',
            [
                'header' => __('Sender'),
                'index' => 'sender_name',
            ]
        );
        $this->addColumn(
            'receiver_name',
            [
                'header' => __('Receiver'),
                'index' => 'receiver_name',
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'type' => 'datetime',
                'header' => __('Created At'),
                'index' => 'created_at',
            ]
        );

        $this->addColumn(
            'updated_at',
            [
                'type' => 'datetime',
                'header' => __('Updated At'),
                'index' => 'updated_at',
            ]
        );

        $this->addColumn(
            'subject',
            [
                'header' => __('Subject'),
                'index' => 'subject',
            ]
        );

        $this->addColumn(
            'receiver_status',
            [
                'header' => __('New Message'),
                'index' => 'receiver_status',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsMessaging\Block\Customer\Vinbox\Renderer\Status'
            ]
        );


        $this->addColumn('action',
            [
                'header' => __('Action'),
                'width' => '50px',
                'type' => 'action',
                'getter' => 'getId',
                'actions' => array(
                    array(
                        'caption' => __('View'),
                        'url' => array(
                            'base' => '*/*/chat',
                        ),
                        'field' => 'id'
                    )
                ),
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
            ]);

        return parent::_prepareColumns();
    }


    public function getGridUrl() {

        return $this->getUrl('*/*/vgrid', array('_secure'=>true, '_current'=>true));
    }

    protected function _prepareFilterButtons()
    {
        $this->setChild(
            'reset_filter_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Reset Filter'),
                    'onclick' => $this->getJsObjectName() . '.resetFilter()',
                    'class' => 'action-reset action-tertiary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-reset'])
        );
        $this->setChild(
            'search_button',
            $this->getLayout()->createBlock(
                'Magento\Backend\Block\Widget\Button'
            )->setData(
                [
                    'label' => __('Search'),
                    'onclick' => $this->getJsObjectName() . '.doFilter()',
                    'class' => 'action-secondary',
                    'area' => 'adminhtml'
                ]
            )->setDataAttribute(['action' => 'grid-filter-apply'])
        );
    }

}