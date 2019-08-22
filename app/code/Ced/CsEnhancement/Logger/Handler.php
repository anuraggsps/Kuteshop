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
 * @package   Ced_CsEnhancement
 * @author    CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsEnhancement\Logger;


use Magento\Framework\Filesystem\DriverInterface;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    /**
     * Handler constructor.
     * @param DriverInterface $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Magento\Customer\Model\Session $session
     * @param null $filePath
     */
    public function __construct(
        DriverInterface $filesystem,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Magento\Customer\Model\Session $session,
        $filePath = null
    ) {

        $date = $timezone->date();
        $vendor_id = $session->getVendorId();
        $this->fileName = '/var/log/Ced/CsEnhancement/' . ((!empty($vendor_id)) ? $vendor_id . DIRECTORY_SEPARATOR: '') . $date->format('y-m-d') . '.log';
        parent::__construct($filesystem, $filePath);
    }
}