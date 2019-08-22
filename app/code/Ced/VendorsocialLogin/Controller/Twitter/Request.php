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
 * @category    Ced
 * @package     VendorsocialLogin
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://cedcommerce.com/license-agreement.txt
 */

/**
 * VendorsocialLogin    Twitter controller
 *
 * @category    Ced
 * @package        Ced_VendorsocialLogin
 * @author        CedCommerce Magento Core Team <connect@cedcommerce.com>
 */

namespace Ced\VendorsocialLogin\Controller\Twitter;


use Magento\Framework\App\Action\NotFoundException;


class Request extends \Magento\Framework\App\Action\Action

{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context
    )
    {
        parent::__construct($context);

    }

    /*
    *	execute the code
    */
    public function execute()

    {
        $client = $this->_objectManager->create('Ced\VendorsocialLogin\Model\Twitter\Oauth2\Client');
        $client->fetchRequestToken();

    }

}
