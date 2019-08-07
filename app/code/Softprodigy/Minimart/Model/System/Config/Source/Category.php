<?php

namespace Softprodigy\Minimart\Model\System\Config\Source;

class Category implements \Magento\Framework\Option\ArrayInterface {

    protected $categoryModel;

    /**
     * @param \Magento\Catalog\Model\Category $category
     */
    public function __construct(\Magento\Catalog\Model\Category $category) {
        $this->categoryModel = $category;
    }

    // get all Category List

    function getCategoriesTreeView() {
        // Get category collection
        $categories = $this->categoryModel
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSort('path', 'asc')
                ->addIsActiveFilter()
                ->addFieldToFilter('is_active', ['eq' => '1'])
                ->load()
                ->toArray();

        // Arrange categories in required array
        $categoryList = [];
        $categoryList[] = [
            'label' => 'All or Any--',
            'level' => '',
            'value' => ''
        ];

        foreach ($categories as $catId => $category) {
            if (isset($category['name'])) {
                $categoryList[] = [
                    'label' => $category['name'],
                    'level' => $category['level'],
                    'value' => $catId
                ];
            }
        }
        return $categoryList;
    }

    // Return options to system config


    public function toOptionArray() {

        $options = array();

        $categoriesTreeView = $this->getCategoriesTreeView();

        foreach ($categoriesTreeView as $value) {
            $catName = $value['label'];
            $catId = $value['value'];
            $catLevel = $value['level'];

            $hyphen = '-';
            for ($i = 1; $i < $catLevel; $i++) {
                $hyphen = $hyphen . "-";
            }

            $catName = $hyphen . $catName;

            $options[] = [
                'label' => $catName,
                'value' => $catId
            ];
        }

        return $options;
    }

}
