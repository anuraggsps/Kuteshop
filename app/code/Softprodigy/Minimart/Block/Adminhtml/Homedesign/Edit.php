<?php
/**
 * \Softprodigy\Homedesign\Block\Adminhtml\Flashsale\Edit File
 * PHP version 7
 * 
 * @category Block
 * @package  Homedesign
 * @author   Softprodigy Inc <extensions@softprodigy.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.softprodigy.com/store/
 */

namespace Softprodigy\Minimart\Block\Adminhtml\Homedesign;

use Magento\Backend\Block\Widget\Form\Container;
use Magento\Backend\Block\Widget\Context;

/**
 * \Softprodigy\Homedesign\Block\Adminhtml\Flashsale\Edit Class
 * 
 * @category Block
 * @package  Homedesign
 * @author   Softprodigy Inc <extensions@softprodigy.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     https://www.softprodigy.com/store/
 */
class Edit extends Container
{

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Json Encoder Interface
     * 
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * Product Helper
     * 
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_productHelper;

    /**
     * __construct
     * 
     * @param Context                             $context     Widget Context
     * @param \Magento\Framework\Registry         $registry    Magento Registry
     * @param \Magento\Framework\Data\FormFactory $formFactory Form factory
     * @param array                               $data        Array Data
     */
    public function __construct(
            Context $context, 
            \Magento\Framework\Registry $registry, 
            \Magento\Framework\Data\FormFactory $formFactory, 
            array $data = []
    ) {

        $this->_coreRegistry = $registry;
        $this->_formFactory = $formFactory;
        parent::__construct($context, $data);
        //$this->setId('deal_edit');
        //$this->setUseContainer(true);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_homedesign';
        $this->_blockGroup = 'Softprodigy_Minimart';
        
        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save'));
        $this->buttonList->add(
            'saveandcontinue', [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ]
                    ]
                ]
            ], -100
        );
        $this->buttonList->update('delete', 'label', __('Delete'));
    }

    /**
     * Retrieve text for header element
     * 
     * @return string
     */
    public function getHeaderText()
    {
        $newsRegistry = $this->_coreRegistry->registry('current_data');
        if ($newsRegistry->getId()) {
            $newsTitle = $this->escapeHtml($newsRegistry->getTitle());
            return __("Edit Section '%1'", $newsTitle);
        } else {
            return __('Lets Create New Section');
        }
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            /*
            function toggleEditor() {
                if (tinyMCE.getInstanceById('post_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'post_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'post_content');
                }
            };*/
            
        ";
        return parent::_prepareLayout();
    }

    /**
     * Retrieve currently edited product object
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getModelData()
    {
        return $this->_coreRegistry->registry('current_data');
    }

    /**
     * Retrive save URl
     * 
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => null]);
    }

    /**
     * Retrive Save and continue url
     * 
     * @return string
     */
    public function getSaveAndContinueUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true, 'back' => 'edit', 'tab' => '{{tab_id}}', 'active_tab' => null]);
    }
    
    /**
     * Get Selected Tab id
     * 
     * @return string
     */
    public function getSelectedTabId()
    {
        return addslashes(htmlspecialchars($this->getRequest()->getParam('tab')));
    }
    
    /**
     * Get dropdown options for save split button
     *
     * @return array
     */
    protected function _getSaveSplitButtonOptions()
    {
        $options = [];
        if (!$this->getRequest()->getParam('popup')) {
            $options[] = [
                'id' => 'edit-button',
                'label' => __('Save & Edit'),
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '[data-form=edit-deal]'],
                    ],
                ],
                'default' => true,
            ];
        }

        $options[] = [
            'id' => 'new-button',
            'label' => __('Save & New'),
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'saveAndNew', 'target' => '[data-form=edit-deal]'],
                ],
            ],
        ];
        $options[] = [
            'id' => 'close-button',
            'label' => __('Save & Close'),
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save', 'target' => '[data-form=edit-deal]']],
            ],
        ];
        return $options;
    }
 

}
