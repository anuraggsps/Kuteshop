<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement (EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://cedcommerce.com/license-agreement.txt
 *
 * @category    Ced
 * @package     Ced_CsEnhancement
 * @author   	 CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (https://cedcommerce.com/)
 * @license      https://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsEnhancement\Helper;


use Magento\Framework\App\Helper\Context;

/**
 * Class File
 * @package Ced\CsEnhancement\Helper
 */
class File extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\File\Size
     */
    protected $fileSize;

    /**
     * @var \Ced\CsEnhancement\Logger\Logger
     */
    protected $logger;

    /**
     * File constructor.
     * @param \Magento\Framework\File\Size $fileSize
     * @param \Ced\CsEnhancement\Logger\Logger $logger
     * @param Context $context
     */
    public function __construct(
        \Magento\Framework\File\Size $fileSize,
        \Ced\CsEnhancement\Logger\Logger $logger,
        Context $context
    ) {
        parent::__construct($context);
        $this->fileSize = $fileSize;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getMaxFileSizeMessage()
    {
        $maxImageSize = $this->fileSize->getFileSizeInMb($this->getMaxFileSize());
        return __('Make sure your file isn\'t more than %1 MB.', (($maxImageSize) ? $maxImageSize : 100));
    }

    /**
     * @return float
     */
    public function getMaxFileSize()
    {
        return $this->fileSize->getMaxFileSize();
    }
}