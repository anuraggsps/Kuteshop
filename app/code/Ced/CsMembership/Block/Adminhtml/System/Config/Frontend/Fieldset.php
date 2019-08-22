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
 * @package   Ced_CsMembership
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */
 
namespace Ced\CsMembership\Block\Adminhtml\System\Config\Frontend;
 
class Fieldset  extends \Magento\Config\Block\System\Config\Form\Fieldset
{

    protected $_objectManager;
    
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\View\Helper\Js $jsHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        array $data = []
    ) {
        parent::__construct($context, $authSession, $jsHelper, $data);
        $this->_objectManager = $objectManager;
    }
    
    /**
     * Render fieldset html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
    
        $this->setElement($element);
        $html = $this->_getHeaderHtml($element);
        if($websitecode = $this->getRequest()->getParam('website')) {
            $website = $this->_objectManager->get('Magento\Store\Model\Website')->load($websitecode);
            if($website && $website->getWebsiteId()) {
                $active = $website->getConfig('ced_csmembership/general/activation')?1:0;
            }
        }
        else {
            $active = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStoreConfig('ced_csmembership/general/activation')?1:0; 
        }
        $validation = $active ?0:1;
        
        foreach ($element->getElements() as $field) {
            if ($field instanceof \Magento\Framework\Data\Form\Element\Fieldset) {
                $html .= '<tr id="row_' . $field->getHtmlId() . '"><td colspan="4">' . $field->toHtml() . '</td></tr>';
            } else {
                $html .= $field->toHtml();
            }
        }
        
        $html .= $this->_getFooterHtml($element);
        $html.='<script>
        		var enable=0;
				
				if('.$validation.'){
					document.getElementById("'.$element->getHtmlId().'").style.display="none";
					document.getElementById("'.$element->getHtmlId().'-state").previousElementSibling.style.display="none";
					document.getElementById("'.$element->getHtmlId().'-state").style.display="none";
				}
				</script>';
        return $html;
    }
}
