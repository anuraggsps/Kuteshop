<?php
namespace Ced\CsProduct\Block\ConfigurableProduct\Product\Steps\Summary;

/**
 * Interceptor class for @see \Ced\CsProduct\Block\ConfigurableProduct\Product\Steps\Summary
 */
class Interceptor extends \Ced\CsProduct\Block\ConfigurableProduct\Product\Steps\Summary implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\View\Element\Template\Context $context, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function setTemplate($template)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'setTemplate');
        if (!$pluginInfo) {
            return parent::setTemplate($template);
        } else {
            return $this->___callPlugins('setTemplate', func_get_args(), $pluginInfo);
        }
    }
}
