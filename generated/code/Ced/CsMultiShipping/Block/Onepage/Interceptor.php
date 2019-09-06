<?php
namespace Ced\CsMultiShipping\Block\Onepage;

/**
 * Interceptor class for @see \Ced\CsMultiShipping\Block\Onepage
 */
class Interceptor extends \Ced\CsMultiShipping\Block\Onepage implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, \Magento\Framework\Data\Form\FormKey $formKey, \Magento\Checkout\Model\CompositeConfigProvider $configProvider, \Magento\Framework\ObjectManagerInterface $objectInterface, array $layoutProcessors = [], array $data = [])
    {
        $this->___init();
        parent::__construct($context, $formKey, $configProvider, $objectInterface, $layoutProcessors, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getJsLayout()
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'getJsLayout');
        if (!$pluginInfo) {
            return parent::getJsLayout();
        } else {
            return $this->___callPlugins('getJsLayout', func_get_args(), $pluginInfo);
        }
    }
}
