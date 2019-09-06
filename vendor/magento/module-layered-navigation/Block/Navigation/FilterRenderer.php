<?php
/**
 * Catalog layer filter renderer
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\LayeredNavigation\Block\Navigation;

use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;
use Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface;

/**
 * Catalog layer filter renderer
 *
 * @api
 * @since 100.0.2
 */
class FilterRenderer extends Template implements FilterRendererInterface
{
    /**
     * @param FilterInterface $filter
     * @return string
     */
    public function render(FilterInterface $filter)
    {
        $this->assign('filterItems', $filter->getItems());
        //~ echo "<pre>";print_r($this->assign('filterItems', $filter->getItems()));
        $html = $this->_toHtml();
        $this->assign('filterItems', []);
         //~ echo "<pre>";print_r($this->assign('filterItems', $filter->getItems()));
        return $html;
    }
}
