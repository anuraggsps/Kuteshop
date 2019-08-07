<?php

namespace Softprodigy\Minimart\Helper;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Data
 *
 * @author mannu
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $scopeConfig;
    protected $_resouceModel;
    protected $filesystem;
    protected $urlInterface;
    protected $_storeManager;
    protected $homeDesignModel;
    /**
     * 
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\App\ResourceConnection $_resouceModel
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface $_storeManager
     */
    public function __construct(
    \Magento\Framework\App\Helper\Context $context,
            \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
            \Magento\Framework\App\ResourceConnection $_resouceModel, 
            \Magento\Framework\UrlInterface $urlInterface, 
            \Magento\Framework\Filesystem $filesystem, 
            \Magento\Store\Model\StoreManagerInterface $_storeManager,
            \Softprodigy\Minimart\Model\Homedesign $homeDesign
    ) {
        parent::__construct($context);
        $this->scopeConfig = $scopeConfig;
        $this->_resourceModel = $_resouceModel;
        $this->urlInterface = $urlInterface;
        $this->filesystem = $filesystem;
        $this->_storeManager = $_storeManager;
        $this->homeDesignModel = $homeDesign;
    }

    public function getStoreConfig($path) {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    public function getIsEnabled() {
        return (int) $this->getStoreConfig('Softprodigy_Bluedart/general/enabled');
    }

    public function urlInterFace() {
        return $this->urlInterface;
    }

    public function filesystem() {
        return $this->filesystem;
    }

    public function getDirPath($dir) {
        return $this->filesystem->getDirectoryRead($dir)->getAbsolutePath();
    }

    public function getMediaUrl() {
        return $this->_storeManager->getStore()
                        ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getStoreSectionPoistions($storeId) {
        $sectionPositions = ['first'=>'First', 'second'=>'Second', 'third'=>'Third', 'fourth'=>'Fourth', 'fifth'=>'Fifth'];
        $collection = $this->homeDesignModel->getCollection(); 
        $collection->addFieldToSelect('position');
        $collection->addFieldToFilter('store_id', $storeId);
        $collection->getSelect()->group('position');
        $postions = $collection->getColumnValues('position');
        $return = [];
        foreach ($sectionPositions as $key => $val) {
            if (!in_array($key, $postions)) {
                $return[$key] = $val;
            }
        }
        return $return;
    }

}
