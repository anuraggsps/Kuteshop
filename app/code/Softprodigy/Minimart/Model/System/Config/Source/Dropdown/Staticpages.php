<?php

namespace Softprodigy\Minimart\Model\System\Config\Source\Dropdown;

class Staticpages implements \Magento\Framework\Option\ArrayInterface {

    protected $cmspage;

    /**
     * @param \Magento\Cms\Model\Page $cmspage
     */
    public function __construct(\Magento\Cms\Model\Page $cmspage) {
        $this->cmspage = $cmspage;
    }

    public function toOptionArray() {
        $collection = $this->cmspage->getCollection()
                ->addFieldToFilter('is_active', 1);
        
        $pages = array();
        if ($collection->getSize()) {
            foreach ($collection as $page) {
                $pages[] = [
                    'value' => (int) $page->getId(),
                    'label' => $page->getTitle(),
                ];
            }
        }
        return $pages;
    }

}
