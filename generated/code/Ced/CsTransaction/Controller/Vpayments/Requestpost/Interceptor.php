<?php
namespace Ced\CsTransaction\Controller\Vpayments\Requestpost;

/**
 * Interceptor class for @see \Ced\CsTransaction\Controller\Vpayments\Requestpost
 */
class Interceptor extends \Ced\CsTransaction\Controller\Vpayments\Requestpost implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Framework\App\Action\Context $context, \Magento\Customer\Model\Session $customerSession, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\UrlFactory $urlFactory, \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator, \Ced\CsMarketplace\Helper\Data $helperData, \Magento\Framework\Stdlib\DateTime\DateTime $datetime, array $data = [])
    {
        $this->___init();
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $formKeyValidator, $helperData, $datetime, $data);
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
