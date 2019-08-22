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
 * @package     Ced_CsProduct
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsProduct\Controller\Vproducts;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
class Save extends \Ced\CsMarketplace\Controller\Vproducts\Save
{
    /**
     * @var Initialization\Helper
     */
    protected $initializationHelper;

    /**
     * @var \Magento\Catalog\Model\Product\Copier
     */
    protected $productCopier;

    /**
     * @var \Magento\Catalog\Model\Product\TypeTransitionManager
     */
    protected $productTypeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * The greatest value which could be stored in CatalogInventory Qty field
     */

    const MAX_QTY_VALUE = 99999999.9999;

    /**
     * Array of actions which can be processed without secret key validation
     *
     * @var array
     */
    protected $_publicActions = array('edit');
    
    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    protected $categoryLinkManagement;
    
     /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    protected $mode = '';

    protected $storeManager = null;
    
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        Session $customerSession,
        \Magento\Catalog\Model\Product\TypeTransitionManager $productTypeManager,
        \Magento\Catalog\Model\Product\Copier $productCopier,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        UrlFactory $urlFactory,
        \Magento\Backend\Model\View\Result\ForwardFactory $resultForwardFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
    	\Magento\Store\Model\StoreManagerInterface $storeManager,
    	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        
        parent::__construct($context, $customerSession, $resultPageFactory, $urlFactory, $moduleManager);
        $this->productBuilder = $productBuilder;
        $this->storeManager = $storeManager;
        $this->resultPageFactory = $resultPageFactory;
        $this->productCopier = $productCopier;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->_session = $context->getSession();
        $this->productTypeManager = $productTypeManager;
        $this->scopeConfig = $scopeConfig;
        $this->productRepository = $productRepository;
       
    }


    /**
     * Save product action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _initProduct()
    {

        $storeId = $this->getRequest()->getParam('store');
        if (!$this->scopeConfig->getValue('ced_csmarketplace/general/ced_vproduct_activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)) {
            return parent::_initProduct();
            //return;
        }

        $productId = $this->getRequest()->getParam('id');
        if ($productId) {
            $this->mode = \Ced\CsMarketplace\Model\Vproducts::EDIT_PRODUCT_MODE;
        } else {
            $this->mode = \Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE;
        }
        
        return $this->mode;
    }

    public function execute()
    {
        $storeId = $this->getRequest()->getParam('store');
        if (!$this->scopeConfig->getValue('ced_csmarketplace/general/ced_vproduct_activation', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId)) {
            return parent::execute();
        }
       
        if ($this->scopeConfig->getValue('ced_csmarketplace/general/ced_vproduct_activation', 'store', $this->getStoreManager()->getStore()->getId())) {
            $vendorId = $this->_getSession()->getVendorId();

            if (!$vendorId) {
                return $this->_redirect('*/*/index', ['store' => $storeId]);

            }
            
            $storeId = $this->getRequest()->getParam('store', 0);
            $store = $this->getStoreManager()->getStore($storeId);
            $this->getStoreManager()->setCurrentStore($store->getCode());
            $redirectBack = $this->getRequest()->getParam('back', false);
            $productId = $this->getRequest()->getParam('id');
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getPostValue();
            $productAttributeSetId = $this->getRequest()->getParam('set');
            $productTypeId = $this->getRequest()->getParam('type');
            $this->initializationHelper = $this->_objectManager->create('Ced\CsProduct\Controller\Vproducts\Initialization\Helper');

            $vendorProduct = false;

            if ($productId && $vendorId) {

                $vendorProduct = $this->_objectManager->get('Ced\CsMarketplace\Model\Vproducts')->isAssociatedProduct($vendorId, $productId);
                if (!$vendorProduct) {
                    return $this->_redirect('*/*/', ['store' => $storeId]);
                }
            } else {

                if (count($this->_objectManager->get('Ced\CsMarketplace\Model\Vproducts')->getVendorProductIds($this->_getSession()->getVendorId())) >= $this->_objectManager->get('Ced\CsMarketplace\Helper\Data')->getVendorProductLimit()) {

                    $this->messageManager->addError(__('Product Creation limit has Exceeded'));
                    return $this->_redirect('*/*/index', ['store' => $storeId]);
                }
                if (!$this->validateSetAndType()) {

                    return $this->_redirect('*/*/new', ['_current' => true]);

                }
            }

            if ($data) {
                $this->_initProduct();

                try {

                    $product = $this->initializationHelper->initialize($this->productBuilder->build($this->getRequest()));

                    $this->productTypeManager->processProduct($product);
                    if (isset($data['product'][$product->getIdFieldName()])) {
                    	throw new \Magento\Framework\Exception\LocalizedException(
                    			__('The product was unable to be saved. Please try again.')
                    	);
                    }
                    
                    
                    $originalSku = $product->getSku();
                    $canSaveCustomOptions = $product->getCanSaveCustomOptions();
                    $product->save();
                    
                    $this->handleImageRemoveError($data, $product->getId());
                    $this->getCategoryLinkManagement()->assignProductToCategories(
                    		$product->getSku(),
                    		$product->getCategoryIds()
                    );
                    $productId = $product->getEntityId();
                    $productAttributeSetId = $product->getAttributeSetId();
                    $productTypeId = $product->getTypeId();
                    $extendedData = $data;
                    $extendedData['can_save_custom_options'] = $canSaveCustomOptions;
                    $this->copyToStores($extendedData, $productId);
                    
                    $this->_objectManager->get('\Magento\Framework\Registry')->register('saved_product', $product);
                    $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')->saveProduct($this->mode);
                    
                     
                     $this->_eventManager->dispatch('csmarketplace_vendor_new_product_creation', [
                    		'product' => $product,
                    		'vendor_id' => $this->_getSession()->getVendorId(),
                    		]); 
                    
                    
                    $this->messageManager->addSuccessMessage(__('You saved the product.'));
                    $this->getDataPersistor()->clear('catalog_product');
                    
                    $this->_eventManager->dispatch(
                    		'controller_action_catalog_product_save_entity_after',
                    		['controller' => $this, 'product' => $product]
                    );
               

                    if ($redirectBack === 'duplicate') {
                        $newProduct = $this->productCopier->copy($product);
                        $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')->processPostSave(\Ced\CsMarketplace\Model\Vproducts::NEW_PRODUCT_MODE, $newProduct, $productData = array());
                        $this->messageManager->addSuccess(__('You duplicated the product.'));
                    }
                    $this->getDataPersistor()->clear('catalog_product');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {

                    $this->messageManager->addError($e->getMessage());
                    $this->_session->setProductData($data);
                    $redirectBack = $productId ? true : 'new';
                } catch (\Exception $e) {
                  
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                    $this->messageManager->addError($e->getMessage());
                    $this->_session->setProductData($data);
                    $redirectBack = $productId ? true : 'new';
                }

            } else {
                $this->messageManager->addErrorMessage('No data to save');
                return $this->_redirect('csproduct/*/', ['store' => $storeId]);
            }

            if ($redirectBack === 'new') {
                return $this->_redirect('csproduct/*/new', ['set' => $productAttributeSetId, 'type' => $productTypeId]);
            } elseif ($redirectBack === 'duplicate' && isset($newProduct)) {
                $this->_redirect(
                    'csproduct/*/edit',
                    ['id' => $newProduct->getEntityId(), 'back' => null, '_current' => true]
                );
            } elseif ($redirectBack) {
                return $this->_redirect('csproduct/*/edit', ['id' => $productId, '_current' => true, 'set' => $productAttributeSetId]);
            } else {
                return $this->_redirect('csproduct/*/', ['store' => $storeId]);
            }
        }

        return $this->_redirect('csproduct/*/', ['store' => $storeId]);
    }


    /**
     * @param string $type
     * @param int $set
     * @return bool
     */

    public function validateSetAndType($type = '', $set = 0)
    {
       

        $allowedType = $this->_objectManager->get('Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Type')->getAllowedType($this->getStoreManager()->getStore()->getId());
        $allowedSet = $this->_objectManager->get('Ced\CsMarketplace\Model\System\Config\Source\Vproducts\Set')->getAllowedSet($this->getStoreManager()->getStore()->getId());

        $secretkey = time();

        if ($type == '')
            $type = $this->getRequest()->getParam('type', $secretkey);

        if ($set == 0)
            $set = (int)$this->getRequest()->getParam('set', 0);

        if ($type == $secretkey || (in_array($type, $allowedType) && in_array($set, $allowedSet))) {
            return true;
        }
        return false;
    }
    
    /**
     * Notify customer when image was not deleted in specific case.
     *
     * TODO: temporary workaround must be eliminated in MAGETWO-45306
     *
     * @param array $postData
     * @param int $productId
     * @return void
     */
    private function handleImageRemoveError($postData, $productId)
    {
    	if (isset($postData['product']['media_gallery']['images'])) {
    		$removedImagesAmount = 0;
    		foreach ($postData['product']['media_gallery']['images'] as $image) {
    			if (!empty($image['removed'])) {
    				$removedImagesAmount++;
    			}
    		}
    		if ($removedImagesAmount) {
    			$expectedImagesAmount = count($postData['product']['media_gallery']['images']) - $removedImagesAmount;
    			$product = $this->productRepository->getById($productId);
    			$images = $product->getMediaGallery('images');
    			if (is_array($images) && $expectedImagesAmount != count($images)) {
    				$this->messageManager->addNoticeMessage(
    						__('The image cannot be removed as it has been assigned to the other image role')
    				);
    			}
    		}
    	}
    }
    
    /**
     * Do copying data to stores
     *
     * If the 'copy_from' field is not specified in the input data,
     * the store fallback mechanism will automatically take the admin store's default value.
     *
     * @param array $data
     * @param int $productId
     * @return void
     */
    protected function copyToStores($data, $productId)
    {
    	if (!empty($data['product']['copy_to_stores'])) {
    		foreach ($data['product']['copy_to_stores'] as $websiteId => $group) {
    			if (isset($data['product']['website_ids'][$websiteId])
    					&& (bool)$data['product']['website_ids'][$websiteId]) {
    						foreach ($group as $store) {
    							if (isset($store['copy_from'])) {
    								$copyFrom = $store['copy_from'];
    								$copyTo = (isset($store['copy_to'])) ? $store['copy_to'] : 0;
    								if ($copyTo) {
    									$this->_objectManager->create(\Magento\Catalog\Model\Product::class)
    									->setStoreId($copyFrom)
    									->load($productId)
    									->setStoreId($copyTo)
    									->setCanSaveCustomOptions($data['can_save_custom_options'])
    									->setCopyFromView(true)
    									->save();
    								}
    							}
    						}
    					}
    		}
    	}
    }
    
    /**
     * Get categoryLinkManagement in a backward compatible way.
     *
     * @return \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    private function getCategoryLinkManagement()
    {
    	if (null === $this->categoryLinkManagement) {
    		$this->categoryLinkManagement = \Magento\Framework\App\ObjectManager::getInstance()
    		->get(\Magento\Catalog\Api\CategoryLinkManagementInterface::class);
    	}
    	return $this->categoryLinkManagement;
    }
    
    /**
     * Get storeManager in a backward compatible way.
     *
     * @return StoreManagerInterface
     * @deprecated 101.0.0
     */
    private function getStoreManager()
    {
    	if (null === $this->storeManager) {
    		$this->storeManager = \Magento\Framework\App\ObjectManager::getInstance()
    		->get(\Magento\Store\Model\StoreManagerInterface::class);
    	}
    	return $this->storeManager;
    }
    
    /**
     * Retrieve data persistor
     *
     * @return DataPersistorInterface|mixed
     * @deprecated 101.0.0
     */
    protected function getDataPersistor()
    {
    	if (null === $this->dataPersistor) {
    		$this->dataPersistor = $this->_objectManager->get(DataPersistorInterface::class);
    	}
    
    	return $this->dataPersistor;
    }
    
    /**
     * Persist media gallery on error, in order to show already saved images on next run.
     *
     * @param ProductInterface $product
     * @param array $data
     * @return array
     */
    private function persistMediaData(ProductInterface $product, array $data)
    {
    	$mediaGallery = $product->getData('media_gallery');
    	if (!empty($mediaGallery['images'])) {
    		foreach ($mediaGallery['images'] as $key => $image) {
    			if (!isset($image['new_file'])) {
    				//Remove duplicates.
    				unset($mediaGallery['images'][$key]);
    			}
    		}
    		$data['product']['media_gallery'] = $mediaGallery;
    		$fields = [
    		'image',
    		'small_image',
    		'thumbnail',
    		'swatch_image',
    		];
    		foreach ($fields as $field) {
    			$data['product'][$field] = $product->getData($field);
    		}
    	}
    
    	return $data;
    }
    
   
}
