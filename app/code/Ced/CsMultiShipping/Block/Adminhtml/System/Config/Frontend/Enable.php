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
 * @package   Ced_CsMultiShipping
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
 
namespace Ced\CsMultiShipping\Block\Adminhtml\System\Config\Frontend;
 
class Enable extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $_objectManager;
    
    public function __construct(\Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,    
        array $data = []
    ) {
    
        parent::__construct($context, $data);
        $this->_objectManager = $objectManager;
    }
    
    /**
     * Return element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {    
        
        if($websitecode = $this->getRequest()->getParam('website')) {
            $website = $this->_objectManager->get('Magento\Store\Model\Website')->load($websitecode);
            if($website && $website->getWebsiteId()) {
                $active = $website->getConfig('ced_csmultishipping/general/activation')?0:1;
            }
        }
        else {
            $active = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStoreConfig('ced_csmultishipping/general/activation')?0:1; 
        }
        $html='';
        $html.=$element->getElementHtml();
        $html.='<script>
				if('.$active.'){
					document.getElementById("row_'.$element->getHtmlId().'").style.display="none";
				}
				</script>';    
        return $html;
    }
       
}
