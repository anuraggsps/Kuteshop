<?php
namespace Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import\ExportCsvFormat;

/**
 * Interceptor class for @see \Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import\ExportCsvFormat
 */
class Interceptor extends \Ced\CsEnhancement\Controller\Adminhtml\Vendor\Import\ExportCsvFormat implements \Magento\Framework\Interception\InterceptorInterface
{
    use \Magento\Framework\Interception\Interceptor;

    public function __construct(\Ced\CsEnhancement\Helper\Attribute $attributeHelper, \Ced\CsEnhancement\Helper\Csv $csv, \Magento\Framework\App\Response\Http\FileFactory $fileFactory, \Magento\Backend\App\Action\Context $context)
    {
        $this->___init();
        parent::__construct($attributeHelper, $csv, $fileFactory, $context);
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
