<?php
namespace Sebwite\SmartSearch\Controller\Search\Ajax\Suggest;

/**
 * Interceptor class for @see \Sebwite\SmartSearch\Controller\Search\Ajax\Suggest
 */
class Interceptor extends \Sebwite\SmartSearch\Controller\Search\Ajax\Suggest implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Search\Model\AutocompleteInterface $autocomplete, \Magento\Search\Model\QueryFactory $queryFactory, \Magento\Search\Helper\Data $searchHelper)
    {
        $this->___init();
        parent::__construct($context, $autocomplete, $queryFactory, $searchHelper);
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(\Magento\Framework\App\RequestInterface $request)
    {
        $pluginInfo = $this->pluginList->getNext($this->subjectType, 'dispatch');
        if (!$pluginInfo) {
            return parent::dispatch($request);
        } else {
            return $this->___callPlugins('dispatch', func_get_args(), $pluginInfo);
        }
    }
}
