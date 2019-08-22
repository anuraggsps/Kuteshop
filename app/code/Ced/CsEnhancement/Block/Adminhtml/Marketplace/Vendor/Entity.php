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

namespace Ced\CsEnhancement\Block\Adminhtml\Marketplace\Vendor;


class Entity extends \Ced\CsMarketplace\Block\Adminhtml\Vendor\Entity
{
    protected function getAddButtonOptions()
    {
        parent::getAddButtonOptions();
        $importButtonOptions = [
            'label' => __('Import Vendors'),
            'class' => 'default',
            'onclick' => "setLocation('" . $this->getImportUrl() . "')"
        ];
        $this->buttonList->add('import', $importButtonOptions);
    }

    protected function getImportUrl()
    {
        return $this->getUrl('csenhancement/vendor/import' );
    }
}