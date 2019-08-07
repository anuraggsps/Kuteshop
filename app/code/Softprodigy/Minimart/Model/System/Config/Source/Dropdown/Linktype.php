<?php

namespace Softprodigy\Minimart\Model\System\Config\Source\Dropdown;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Linktype
 *
 * @author mannu
 */
class Linktype implements \Magento\Framework\Option\ArrayInterface {

    public function toOptionArray() {
        return array(
            array(
                'value' => '',
                'label' => '--Select Type--'
            ),
            array(
                'value' => 'category',
                'label' => 'Category'
            ),
            array(
                'value' => 'page',
                'label' => 'CMS Page'
            ),
            array(
                'value' => 'product',
                'label' => 'Product'
            ),
            array(
                'value' => 'custom',
                'label' => 'Custom'
            )
        );
    }

}
