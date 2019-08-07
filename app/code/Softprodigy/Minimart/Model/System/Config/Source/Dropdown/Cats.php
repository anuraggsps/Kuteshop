<?php

namespace Softprodigy\Minimart\Model\System\Config\Source\Dropdown;

class Cats implements \Magento\Framework\Option\ArrayInterface {

    protected $categoryModel;
    protected $_storeManager;
    /**
     * @param \Magento\Catalog\Model\Category $category
     */
    public function __construct(\Magento\Catalog\Model\Category $category, \Magento\Store\Model\StoreManagerInterface $storeManager) {
        $this->categoryModel = $category;
        $this->_storeManager = $storeManager;
    }

    public function toOptionArray() {

        $_categories = $this->categoryModel->getCollection()
                ->addAttributeToSelect('*')//or you can just add some attributes
                // ->addAttributeToFilter('level', 2)//2 is actually the first level
                ->addIsActiveFilter();
        $cats = [];
        if ($_categories->getSize()) {
            foreach ($_categories as $_category) {
                if ($_category->getName() == 'Default Category') {
                    continue;
                }
                $cats[] = [
                    'value' => (int) $_category->getId(),
                    'label' => $_category->getName(),
                ];
            }
        }

        return $cats;
    }

    protected function getCategoriesTreeView($storeId = null) {
        // Get category collection
        $categories = $this->categoryModel
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToSort('path', 'asc')
                ->addFieldToFilter('is_active', array('eq' => '1'));
        if (!empty($storeId)) {
            $rootCategoryId = $this->_storeManager->getStore()->getRootCategoryId();
            $categories->setStoreId($storeId);
            $categories->addAttributeToFilter('path', array('like' => "1/{$rootCategoryId}/%"));
        }
        $categories = $categories->load()
                ->toArray();

        // Arrange categories in required array
        $categoryList = array();
        $categoryList[] = array(
            'label' => 'select category--',
            'level' => '',
            'value' => ''
        );

        foreach ($categories as $catId => $category) {
            if (isset($category['name'])) {
                $categoryList[] = array(
                    'label' => $category['name'],
                    'level' => $category['level'],
                    'value' => $catId
                );
            }
        }
        return $categoryList;
    }

    // Return options to system config


    public function toOptions($storeId = null, $asoptarr = false) {

        $options = array();

        $categoriesTreeView = $this->getCategoriesTreeView($storeId);

        foreach ($categoriesTreeView as $value) {
            $catName = $value['label'];
            $catId = $value['value'];
            $catLevel = $value['level'];

            $hyphen = '-';
            for ($i = 1; $i < $catLevel; $i++) {
                $hyphen = $hyphen . "-";
            }

            $catName = $hyphen . $catName;
            if ($asoptarr === false) {
                $options[$catId] = $catName;
            } else {
                $options[] = array(
                    'value' => $catId,
                    'label' => $catName,
                );
            }
        }

        return $options;
    }

}
