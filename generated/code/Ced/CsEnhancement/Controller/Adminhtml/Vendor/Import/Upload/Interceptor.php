<?php
namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import\Upload;

/**
 * Interceptor class for @see \Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import\Upload
 */
class Interceptor extends \Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import\Upload implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Ced\CsEnhancement\Helper\Uploader $uploaderHelper, \Magento\Framework\Serialize\Serializer\Json $jsonEncoder, \Magento\Backend\App\Action\Context $context)
    {
        $this->___init();
        parent::__construct($uploaderHelper, $jsonEncoder, $context);
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
