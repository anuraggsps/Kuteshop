<?php

namespace Softprodigy\Minimart\Model\System\Config\Source\Dropdown;

class Values implements \Magento\Framework\Option\ArrayInterface {

    public function toOptionArray() {
        return array(
            array(
                'value' => 1,
                'label' => '1 Level'
            ),
            array(
                'value' => 2,
                'label' => '2 Level'
            ),
            array(
                'value' => 3,
                'label' => '3 Level'
            )
        );
    }

}
