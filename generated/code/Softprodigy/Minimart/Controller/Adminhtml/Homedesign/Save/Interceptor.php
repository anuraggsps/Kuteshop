<?php
namespace Softprodigy\Minimart\Controller\Adminhtml\Homedesign\Save;

/**
 * Interceptor class for @see \Softprodigy\Minimart\Controller\Adminhtml\Homedesign\Save
 */
class Interceptor extends \Softprodigy\Minimart\Controller\Adminhtml\Homedesign\Save implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Magento\Backend\App\Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Softprodigy\Minimart\Model\Homedesign $homedesignModel)
    {
        $this->___init();
        parent::__construct($context, $resultPageFactory, $homedesignModel);
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
