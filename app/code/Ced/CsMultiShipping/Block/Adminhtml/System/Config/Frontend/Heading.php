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
 
class Heading extends \Magento\Config\Block\System\Config\Form\Field\Heading
{
    
    protected $_objectManager;
    
    protected $_request;
    
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\RequestInterface $request
    ) {
    
        $this->_request = $request;
        $this->_objectManager = $objectManager;
    }
    
    
    /**
     * Render element html
     *
     * @param  Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $useContainerId = $element->getData('use_container_id');
        $active = 1;
        if($websitecode = $this->_request->getParam('website')) {
            $website = $this->_objectManager->get('Magento\Store\Model\Website')->load($websitecode);
            if($website && $website->getWebsiteId()) {
                $active = $website->getConfig('ced_csmultishipping/general/activation')?1:0;
            }
        }
        else {
            $active = $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getStoreConfig('ced_csmultishipping/general/activation')?1:0; 
        }
        
        $methods = $this->_objectManager->get('Ced\CsMultiShipping\Model\Source\Shipping\Methods')->getMethods();
        $count=0;
        if(count($methods)>0) {
            $count=1; 
        }
        $validation = $active && $count?0:1;

        $html='';
        $html.=sprintf(
            '<tr class="system-fieldset-sub-head" id="row_%s"><td colspan="5"><h4 id="%s">%s</h4></td></tr>',
            $element->getHtmlId(), $element->getHtmlId(), $element->getLabel()
        );
        $html.='<script>
				if('.$validation.'){
					document.getElementById("row_'.$element->getHtmlId().'").style.display="none";
				}
				</script>';
        return $html;
    }
    
       
}
