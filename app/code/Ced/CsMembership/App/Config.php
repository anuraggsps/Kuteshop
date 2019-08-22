<?php
/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 *
 * @category    Ced
 * @package     Ced_CsFedexShipping
 * @author       CedCommerce Core Team <connect@cedcommerce.com >
 * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Ced\CsMembership\App;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\App\Config\ScopePool;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Configuration data of carrier
 */
 class Config extends \Magento\Framework\App\Config 
{
    /**
     * Config cache tag
     */
    const CACHE_TAG = 'CONFIG';
    
    
    /**
     * @var \Magento\Framework\App\Config\ScopePool
     */
    public $_coreRegistry = null;
    public $_scopePool;
    public $_objectManager;
    public $_customerSession;
    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;
    
    /**
     * @var ConfigTypeInterface[]
     */
    private $types;
    
    
    const SCOPE_TYPE_DEFAULT = 'default';
    /**
     * @param \Magento\Framework\App\Config\ScopePool $scopePool
     */
    /*public function __construct(
            ScopeCodeResolver $scopeCodeResolver = null,
            ScopePool $scopePool,
             \Magento\Framework\Registry $registry,
            array $types = []
            )
    {
        //$this->_coreRegistry  = $registry;
        $this->_scopePool = $scopePool;
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->types = $types;
    }*/


    public function __construct(
        ScopeCodeResolver $scopeCodeResolver,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        array $types = []
    ) {
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->_eventManager = $eventManager;
        $this->types = $types;
    }

    /**
     * Retrieve config value by path and scope
     *
     * @param string $path
     * @param string $scope
     * @param null|string $scopeCode
     * @return mixed
     */

    public function getValue(
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {

        //print_r($path);echo "</br>";
        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }
        $configPath = $scope;


        if ($scope !== 'default') {
            if (is_numeric($scopeCode) || $scopeCode === null) {
                $scopeCode = $this->scopeCodeResolver->resolve($scope, $scopeCode);
            } else if ($scopeCode instanceof \Magento\Framework\App\ScopeInterface) {
                $scopeCode = $scopeCode->getCode();
            }
            if ($scopeCode) {
                $configPath .= '/' . $scopeCode;
            }
        }

        if ($path) {
            $configPath .= '/' . $path;
        }
        $resource =  \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\ResourceConnection');
        $tablename = $resource->getTableName('core_config_data');
        $connection = $resource->getConnection();
        $sql = "Select * FROM " . $tablename. " Where path = 'ced_csgroup/general/activation'";
        $result = $connection->fetchAll($sql);


        //print_r($this->get('system', $configPath));echo '</br>';

        if((strpos($path,'ced_') !== false) && (\Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\App\State')->getAreaCode() != 'adminhtml')){
            $result = new \Magento\Framework\DataObject();
            //$group_data = $this->_scopePool->getScope($scope, $scopeCode)->getValue($path);
            $this->_eventManager->dispatch('ced_csgroup_config_data_change_after',array('result'=>$result,'path'=>$path,'groupdata'=>$this->get('system', $configPath)));
            if($result->getResult()){
                return $result->getResult();
            }
        }
        if(empty($result)){

            return $this->get('system', $configPath);
        }
        if((!empty($result)) && ($result[0]['value']==0))
        {
            return $this->get('system', $configPath);
        }

        $this->_coreRegistry = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Registry');
        $vendorData = $this->_coreRegistry->registry('vendor');

        if(!$vendorData && !$this->_coreRegistry->registry('current_order_vendor'))
        {
            return $this->get('system', $configPath);
        }
        else
        {
            if($vendorData)
                $groupCode = $vendorData['group'];
            else if( $this->_coreRegistry->registry('current_order_vendor'))
            {
                $groupCode =  $this->_coreRegistry->registry('current_order_vendor')->getGroup();
            }
            else
                return $this->get('system', $configPath);


            $paths=$groupCode."/".$path;
            $configPaths = '';
            $configPaths =$scope;
            if ($scopeCode) {
                $configPaths .= '/' . $scopeCode;
            }
            $configData=$this->get('system', $configPaths.'/'.$paths);

            if($configData!="")
            {
                return $this->get('system',  $configPaths.'/'.$paths);
            }
            else
            {
                return $this->get('system', $configPath);
            }
        }

    }

    /**
     * Get Function
     * @param string $configType
     * @param string $path
     * @param null $default
     * @return array|null
     */
    public function get($configType, $path = '', $default = null)
    {
        $result = null;
        if (isset($this->types[$configType])) {
            $result = $this->types[$configType]->get($path);
        }

        return $result !== null ? $result : $default;
    }
}
