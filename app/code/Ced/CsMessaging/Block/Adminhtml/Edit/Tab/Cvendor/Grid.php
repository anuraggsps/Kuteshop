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
namespace Ced\CsMessaging\Block\Adminhtml\Edit\Tab\Cvendor;

/**
 * Class Grid
 * @package Ced\CsMessaging\Block\Adminhtml\Edit\Tab\Cvendor
 */
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
        \Ced\CsMessaging\Model\ResourceModel\Vcustomer\CollectionFactory $collectionFactory,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->session = $session;
        $this->_objectManager = $objectManager;
        $this->moduleManager = $moduleManager;
        $this->_resource = $resource;
        $this->customerSession = $customerSession;
        $this->eavConfig = $eavConfig;
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
        parent::__construct($context, $backendHelper, $data);
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

    public function getCustomerId()
    {
        $currentCustomerId = $this->registry->registry('current_customer_id');
        return $currentCustomerId;
    }

    protected function _prepareCollection()
    {
        if ($currentCustomerId = $this->getRequest()->getParam('customer_id'))
        {
            $currentCustomerId = $currentCustomerId;
        } else {
            $currentCustomerId = $this->getCustomerId();
        }
        $collection = $this->collectionFactory->create();
        $collection = $collection->addFieldToFilter('customer_id',$currentCustomerId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns() {
        $this->addColumn(
            'id',
            [
                'header' => __('Id'),
                'index' => 'id',
            ]
        );

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
                'header' => __('Status'),
                'index' => 'receiver_status',
                'filter' => false,
                'sortable' => false,
                'renderer' => 'Ced\CsMessaging\Block\Adminhtml\Edit\Tab\Cvendor\Renderer\Status'
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
                            'base' => 'csmessaging/vcustomer/chat',
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

        return $this->getUrl('csmessaging/customer/vinboxgrid', array('customer_id'=>$this->getCustomerId(), '_current'=>true));
    }

}