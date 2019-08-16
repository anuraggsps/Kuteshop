<?php

namespace Softprodigy\Minimart\Controller;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\ViewInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Framework\Locale\Bundle\CurrencyBundle as CurrencyBundle;
/**
 * Description of AbstractAction
 *
 * @author mannu
 */
abstract class AbstractAction extends \Magento\Framework\App\Action\Action {

    public $sessionid;
    public $writeConnection;
    public $readConnection;
    public $pkgCode;

    const Basic_Package = 'Basic';
    const Silver_Package = 'Silver';
    const Gold_Package = 'Gold';
    const Basic_Exd_Package = 'Extended';

    protected $activePackage;
    protected $__helper;

    /**
     * Default ignored attribute codes per entity type
     *
     * @var array
     */
    protected $_ignoredAttributeCodes = array(
        'global' => array('entity_id', 'attribute_set_id', 'entity_type_id')
    );

    /**
     * Attributes map array per entity type
     *
     * @var google
     */
    protected $_attributesMap = array(
        'global' => []
    );

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $productFactory;
    protected $_filterProvider;
    protected $session;
    protected $_productCollection;
    protected $_productOptionCollection;
    protected $_catalogConfig;
    protected $viewInterface;
    protected $currencyHelper;
    protected $actionBuilder;
    protected $logger;
    protected $_quoteLoader;
    protected $__checkoutSession;
    protected $__customerSession;
    protected $cart;
    protected $productRepositoryInf;
    protected $customerAccountManagement;
    protected $quoteRepository;
    protected $registry;
    protected $escaper;
    protected $layoutFactory;
	protected $_productCollectionFactory;
	protected $_catalogProductVisibility;
	protected $_categoryFactory;
	protected $_ratingFactory;
	protected $_reviewFactory;
	
    /**
     * @param Context $context
     */
    public function __construct(
    \Magento\Framework\App\Action\Context $context, \Magento\Store\Model\StoreManagerInterface $storeManager, \Magento\Framework\Session\SessionManagerInterface $session, \Softprodigy\Minimart\Helper\Data $__helper, \Magento\Catalog\Model\Config $catalogConfig, \Magento\Catalog\Model\Product $productFactory, \Magento\Catalog\Model\ResourceModel\Product\Collection $_productCollection, \Magento\Catalog\Model\ResourceModel\Product\Option\Collection $_productOptionCollection, \Magento\Cms\Model\Template\FilterProvider $filterProvider, ViewInterface $ViewInterface, \Magento\Framework\Pricing\Helper\Data $currencyHelper, \Magento\Framework\UrlInterface $actionBuilder, Logger $logger, \Magento\Quote\Model\Quote $quoteLoader, \Magento\Checkout\Model\Session $__checkoutSession, \Magento\Customer\Model\Session $__customerSession, \Magento\Checkout\Model\Cart $cart, \Magento\Catalog\Api\ProductRepositoryInterface $prodRepInf, AccountManagementInterface $customerAccountManagement, \Magento\Quote\Api\CartRepositoryInterface $quoteRepository, \Magento\Framework\Escaper $escaper, \Magento\Framework\Registry $registry, \Magento\Framework\View\LayoutFactory $layoutFactory,\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,\Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,\Magento\Catalog\Model\CategoryFactory $categoryFactory,\Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,\Magento\Catalog\Helper\Image $imageHelper,\Magento\Review\Model\ReviewFactory $reviewFactory,\Magento\Review\Model\Rating $ratingFactory
    ) {
        parent::__construct($context);
        $this->__helper = $__helper;
        $this->_storeManager = $storeManager;
        $this->productFactory = $productFactory;
        $this->_filterProvider = $filterProvider;
        $this->session = $session;
        $this->_productCollection = $_productCollection;
        $this->_productOptionCollection = $_productOptionCollection;
        $this->_catalogConfig = $catalogConfig;
        $this->viewInterface = $ViewInterface;
        $this->currencyHelper = $currencyHelper;
        $this->actionBuilder = $actionBuilder;
        $this->logger = $logger;
        $this->_quoteLoader = $quoteLoader;
        $this->__checkoutSession = $__checkoutSession;
        $this->__customerSession = $__customerSession;
        $this->cart = $cart;
        $this->productRepositoryInf = $prodRepInf;
        $this->customerAccountManagement = $customerAccountManagement;
        $this->quoteRepository = $quoteRepository;
        $this->registry = $registry;
        $this->escaper = $escaper;
        $this->layoutFactory = $layoutFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_categoryFactory = $categoryFactory;
        $this->_tokenModelFactory = $tokenModelFactory;
        $this->imageHelper = $imageHelper;
        $this->_reviewFactory = $reviewFactory;
        $this->_ratingFactory = $ratingFactory;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws NotFoundException
     */
    public function dispatch(RequestInterface $request) {
        $this->_afterDispatch($request);
        parent::dispatch($request);
    }

    protected function _afterDispatch(RequestInterface $request) {

        $salt = $this->__helper->getStoreConfig('minimart/minimart_registration/ogb_api_key');
        $paramSalt = $request->getParam('salt');
        $storeId = $request->getParam('cstore', '');

        if (!empty($storeId) and $storeId!=null and $storeId!='null')
            $this->_storeManager->setCurrentStore($storeId);

        $skip = array('minimart_miniapi_sendnotify');
        $cra = strtolower($request->getFullActionName());

        $this->activePackage = null;
        $this->pkgCode = array(self::Basic_Package => 101, self::Basic_Exd_Package => 1011, self::Silver_Package => 201, self::Gold_Package => 301);

        $subs = [];
        $subs['subs_closed'] = false;
        $subs['active_package'] = 'Gold';
        $subscriptionExpire = ($subs and is_array($subs)) ? $subs['subs_closed'] : false;
        if ($subs['active_package']) {
            $this->activePackage = $subs['active_package'];
        }

        if (!empty($request->getParam('cust_id', false))) {
            $customerObj = $this->_objectManager->get('Magento\Customer\Model\Customer')->load($request->getParam('cust_id'));
            $this->__customerSession->setCustomer($customerObj);
        }

        if (!empty($request->getParam('quote_id', false))) {
            $quoteObj = $this->_objectManager->get('Magento\Quote\Model\Quote')->loadActive($request->getParam('quote_id'));
            $this->__checkoutSession->replaceQuote($quoteObj);
        }

        $formkey = $this->_objectManager->get("Magento\Framework\View\Element\FormKey");

        $request->setParam('form_key', $formkey->getFormKey());
    }

    /**
     * Return customer session object
     *
     * @return \Magento\Customer\Model\Session
     */
    protected function _getCustomerSession() {
        return $this->__customerSession;
    }

    protected function checkPackageSubcription() {

        /* $hashKey = $this->__helper->getStoreConfig('minimart/minimart_registration/ogb_api_key');
          $store_url = $this->_storeManager->getStore()->getBaseUrl(); //$this->__helper->getStoreConfig('minimart/minimart_registration/ogb_api_invoice_id');

          $fields = array
          (
          'key' => $hashKey,
          'store_url' => $store_url,
          );

          $requestUrl = 'https://www.ongobuyo.com/ogb_connection.php';

          $ch = curl_init();
          curl_setopt($ch, CURLOPT_URL, $requestUrl);
          curl_setopt($ch, CURLOPT_POST, true);
          //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
          curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
          curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
          $result = curl_exec($ch);
          curl_close($ch);
          //var_dump($result); die;
          $response = json_decode($result, true);
          if ($response and ! empty($response)) {
          $resp = array(
          'subs_closed' => $response['sub_expire'],
          'remain_interval' => $response['interval'],
          'active_package' => $response['package_name']
          );
          } else {
          $resp = array(
          'subs_closed' => false,
          'remain_interval' => 900,
          'active_package' => self::Basic_Package
          );
          } */
        $resp = array(
            'subs_closed' => false,
            'remain_interval' => 900,
            'active_package' => self::Gold_Package
        );
        return $resp;
    }

    protected function getVisitorId() {
        $vistor = $this->session->getVisitorData();
        return isset($vistor['visitor_id']) ? $vistor['visitor_id'] : '';
    }

    protected function _addProductAttributesAndPrices($collection) { //-------Copy function from block -  Mage_Catalog_Block_Product_Abstract
        return $collection
                        ->addMinimalPrice()
                        ->addFinalPrice()
                        ->addTaxPercents()
                        ->addAttributeToSelect('*')
                        ->addUrlRewrite();
    }

    protected function newProducts($limit, $page, $entityIds = null) {
        if (empty($page)) {
            $page = 1;
        }

        $todayStartOfDayDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d', strtotime('-1 day')) . "00:00:00"));

        $todayEndOfDayDate = date('Y-m-d H:i:s', strtotime(date('Y-m-d') . "23:59:59"));


        /** @var $collection Magento/Catalog/Model/ResourceModel/Product/Collection */
        $n_collection = $this->_productCollection;

        $visibility = $this->_objectManager->get("Magento\Catalog\Model\Product\Visibility");

        $n_collection->setVisibility($visibility->getVisibleInCatalogIds());


        $n_collection = $this->_addProductAttributesAndPrices($n_collection)
                ->addStoreFilter()
                ->addAttributeToSelect('image')
                ->addAttributeToFilter('news_from_date', array('or' => array(
                        0 => array('date' => true, 'to' => $todayEndOfDayDate),
                        1 => array('is' => new \Zend_Db_Expr('null')))
                        ), 'left')
                ->addAttributeToFilter('news_to_date', array('or' => array(
                        0 => array('date' => true, 'from' => $todayStartOfDayDate),
                        1 => array('is' => new \Zend_Db_Expr('null')))
                        ), 'left')
                ->addAttributeToFilter(
                        array(
                            array('attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')),
                            array('attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null'))
                        )
                )
                ->addAttributeToFilter('status', 1)
                ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        //->addAttributeToFilter('type_id',array('in'=>array('simple')));//--will comment later in next version----------
        //->addAttributeToFilter('entity_id',array('nin' => $entityIds));//--will comment later in next version----------
        if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {
            $n_collection->addAttributeToFilter('type_id', array('in' => array('simple')));
        } else if ($this->activePackage == self::Silver_Package) {
            $n_collection->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable', 'virtual')));
        }
        //->addAttributeToFilter('type_id',array('in'=>array('simple'))); //--will comment later in next version----------
        if ($this->activePackage == self::Basic_Package) {
            if (empty($entityIds)) {
                $Option = $this->_productOptionCollection->addFieldToSelect('product_id');
                $Option->getSelect()->group('main_table.product_id');
                $entityIds = new \Zend_Db_Expr($Option->getSelect()->__toString());
            }
            $n_collection->addAttributeToFilter('entity_id', array('nin' => $entityIds)); //--will comment later in next version----------
        }

        $param = $this->getRequest()->getParams();
        if (isset($param['sort']) and ! empty($param['sort'])) {
            $sortArray = explode("_", $param['sort']);
            $order = $sortArray[0];
            $direction = strtoupper($sortArray[1]);
            $n_collection->addAttributeToSort($order, $direction);
            //$collection->getSelect()->order("$order $direction");
        } else {
            $n_collection->addAttributeToSort('news_from_date', 'desc');
        }

        //var_dump($n_collection->getSelect()->__toString()); die;

        $colcBkp = clone $n_collection;
        $product['count'] = count($colcBkp);

        $n_collection = $n_collection->setPageSize($limit)->setCurPage($page)->load();

        $product['collection'] = $n_collection;

        //->setPageSize($limit)
        //->setCurPage($page);
        //$n_collection->getSelect()->where('e.entity_id not in(?)', $entityIds);//--will comment later in next version----------   

        return $product;
    }

    protected function getBestsellerProducts($limit, $page, $entityIds = null) {

        if (empty($page)) {
            $page = 1;
        }

        $storeId = (int) $this->_storeManager->getStore()->getId();

        $date = new \Zend_Date();
        $toDate = $date->setDay(1)->getDate()->get('Y-MM-dd');
        $fromDate = $date->subMonth(1)->getDate()->get('Y-MM-dd');

        $collection = $this->_productCollection;
        $collection->addAttributeToSelect($this->_catalogConfig->getProductAttributes())
                ->addStoreFilter()
                ->addPriceData()
                ->addTaxPercents()
                ->addMinimalPrice()
                ->addFinalPrice()
                ->addUrlRewrite();

        $collection->addAttributeToFilter('status', 1);
        $collection->addAttributeToSelect('image');
        $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);

        if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {
            $collection->addAttributeToFilter('type_id', array('in' => array('simple')));
        } else if ($this->activePackage == self::Silver_Package) {
            $collection->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable', 'virtual')));
        }

        if ($this->activePackage == self::Basic_Package) {
            if (empty($entityIds)) {
                $Option = $this->_productOptionCollection->addFieldToSelect('product_id');
                $Option->getSelect()->group('main_table.product_id');
                $entityIds = new \Zend_Db_Expr($Option->getSelect()->__toString());
            }
            $collection->addAttributeToFilter('entity_id', array('nin' => $entityIds)); //--will comment later in next version----------
        }

        $param = $this->getRequest()->getParams();
        if (isset($param['sort']) and ! empty($param['sort'])) {
            $sortArray = explode("_", $param['sort']);
            $order = $sortArray[0];
            $direction = strtoupper($sortArray[1]);
            $collection->addAttributeToSort($order, $direction);
            //$collection->getSelect()->order("$order $direction");
        }

        $colcBkp = clone $collection;
        $product['count'] = count($colcBkp);

        $collection = $collection->setPageSize($limit)->setCurPage($page)->load();
        $collection->getSelect()
                ->joinLeft(
                        array('aggregation' => $collection->getResource()->getTable('sales/bestsellers_aggregated_monthly')), "e.entity_id = aggregation.product_id AND aggregation.store_id={$storeId} AND aggregation.period BETWEEN '{$fromDate}' AND '{$toDate}'", array('SUM(aggregation.qty_ordered) AS sold_quantity')
                )
                ->group('e.entity_id')
                ->order(array('sold_quantity DESC', 'e.created_at'));
        //$collection->getSelect()->where('e.entity_id not in(?)', $entityIds);//--will comment later in next version----------    		
        /*
          Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
          Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
         */
        $collection->setVisibility($this->_objectManager->get("Magento\Catalog\Model\Product\Visibility")->getVisibleInCatalogIds());
        $product['collection'] = $collection;

        return $product;
    }

    protected function getFeatured($limit, $page, $entityIds = null) {
        if (empty($page)) {
            $page = 1;
        }
        //echo $page;
        $cat_ids = $this->__helper->getStoreConfig('minimart/homepage_settings/feat_cat');
        $productCollection = [];
        $product = [];
        if ($cat_ids) {
            $cat_id = explode(',', $cat_ids);
            $productCollection = $this->_productCollection;
            $productCollection->joinField(
                            'category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left'
                    )
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                    ->addAttributeToFilter('category_id', array('in' => $cat_id));

            $productCollection->getSelect()->group('e.entity_id');

            if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {
                $productCollection->addAttributeToFilter('type_id', array('in' => array('simple')));
            } else if ($this->activePackage == self::Silver_Package) {
                $productCollection->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable', 'virtual')));
            }

            if ($this->activePackage == self::Basic_Package) {
                if (empty($entityIds)) {
                    $Option = $this->_productOptionCollection->addFieldToSelect('product_id');
                    $Option->getSelect()->group('main_table.product_id');
                    $entityIds = new \Zend_Db_Expr($Option->getSelect()->__toString());
                }
                $productCollection->addAttributeToFilter('entity_id', array('nin' => $entityIds));
            }

            $productCollection->addMinimalPrice()
                    ->addFinalPrice();

            $param = $this->getRequest()->getParams();
            if (isset($param['sort']) and ! empty($param['sort'])) {
                $sortArray = explode("_", $param['sort']);
                $order = $sortArray[0];
                $direction = strtoupper($sortArray[1]);
                $productCollection->addAttributeToSort($order, $direction);
                //$collection->getSelect()->order("$order $direction");
            } else {
                $productCollection->addAttributeToSort('created_at', 'desc');
            }

            $productCollection->addAttributeToFilter('status', 1);

            $colcBkp = clone $productCollection;
            $product['count'] = count($colcBkp);


            $offset = $limit * ($page - 1);
            $productCollection->getSelect()->limit($limit, $offset);
            //var_dump($productCollection->getSelect()->__toString()); die; 
            //$productCollection->setPageSize($limit)->setCurPage($page);
            $productCollection = $productCollection->load();

            $product['collection'] = $productCollection;
        }

        return $product;
    }

    protected function getCustomCategoryProducts($limit, $page, $sysconfig, $entityIds = null) {
        if (empty($page)) {
            $page = 1;
        }
        //echo $page;
        $cat_ids = $this->__helper->getStoreConfig('minimart/homepage_settings/cust_cat_' . $sysconfig . '_value');
        $productCollection = [];
        $product = [];
        if ($cat_ids) {
            $cat_id = explode(',', $cat_ids);
            $productCollection = $this->_productCollection
                    ->joinField(
                            'category_id', 'catalog/category_product', 'category_id', 'product_id = entity_id', null, 'left'
                    )
                    ->addAttributeToSelect('*')
                    ->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
                    //->addAttributeToFilter('type_id',array('in'=>array('simple')))//--will comment later in next version----------
                    ->addAttributeToFilter('category_id', array('in' => $cat_id));

            $productCollection->getSelect()->group('e.entity_id');

            if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {
                $productCollection->addAttributeToFilter('type_id', array('in' => array('simple')));
            } else if ($this->activePackage == self::Silver_Package) {
                $productCollection->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable', 'virtual')));
            }

            if ($this->activePackage == self::Basic_Package) {
                if (empty($entityIds)) {
                    $Option = $this->_productOptionCollection->addFieldToSelect('product_id');
                    $Option->getSelect()->group('main_table.product_id');
                    $entityIds = new \Zend_Db_Expr($Option->getSelect()->__toString());
                }
                $productCollection->addAttributeToFilter('entity_id', array('nin' => $entityIds));
            }

            $activeChildProducts = $this->getAllProductsOfChilds();
            if (!empty($activeChildProducts)) {
                $productCollection->addAttributeToFilter('entity_id', array('in' => $activeChildProducts));
            }

            $productCollection->addMinimalPrice()
                    ->addFinalPrice();

            $param = $this->getRequest()->getParams();
            if (isset($param['sort']) and ! empty($param['sort'])) {
                $sortArray = explode("_", $param['sort']);
                $order = $sortArray[0];
                $direction = strtoupper($sortArray[1]);
                $productCollection->addAttributeToSort($order, $direction);
                //$collection->getSelect()->order("$order $direction");
            } else {
                $productCollection->addAttributeToSort('created_at', 'desc');
            }

            $productCollection->addAttributeToFilter('status', 1);

            $colcBkp = clone $productCollection;
            $product['count'] = count($colcBkp);


            $offset = $limit * ($page - 1);
            $productCollection->getSelect()->limit($limit, $offset);
            //var_dump($productCollection->getSelect()->__toString()); die; 
            //$productCollection->setPageSize($limit)->setCurPage($page);
            $productCollection = $productCollection->load();

            $product['collection'] = $productCollection;
        }

        return $product;
    }

    public function skipifFilterAndNinStock($product, $callbackparam) {
        $productStockRepositry = $this->_objectManager->create("Magento\CatalogInventory\Model\Stock\StockItemRepository");
        $productStock = $productStockRepositry->get($product->getId());

        $isinStock = $productStock ? $productStock->getIsInStock() : 0;
        if ($callbackparam['hadLFilter'] == true and $isinStock == 0) {
            return true;
        }
        return false;
    }

    protected function collectionDetail($collection, $ifCondForItem = null, $callbackparam = null) {
        $products = [];
        $n = 0;

        /* For price_html */
        /* $layout = $this->viewInterface;
          $update = $layout->getLayout()->getUpdate();
          $update->addHandle('catalog_category_view');
          $layout->loadLayoutUpdates();
          $layout->generateLayoutXml();
          $layout->generateLayoutBlocks(); */

        foreach ($collection as $prod) {
            if (!empty($ifCondForItem)) {
                $iftrue = $this->{$ifCondForItem}($prod, $callbackparam);
                if ($iftrue) {
                    continue;
                }
            }
            $imageUrl = '';
            $products[$n]['product_id'] = $prod->getId();
            $products[$n]['type_id'] = $prod->getTypeId();
            $products[$n]['name'] = $prod->getName();
            $products[$n]['final_price'] = number_format($this->currencyHelper->currency($prod->getFinalPrice(), false, false), 2);
            $products[$n]['price'] = number_format($this->currencyHelper->currency($prod->getPrice(), false, false), 2);
            //~ $products[$n]['minimal_price'] = $this->getMinimalPrice($prod);

            $products[$n]['inWishlist'] = '';
            try {
                $customer_id = $this->getRequest()->getParam('cust_id');
                $witemid = $this->checkInWishilist($prod->getId(), $customer_id);
                $products[$n]['inWishlist'] = $witemid;
            } catch (\Exception $ex) {
                $products[$n]['inWishlist'] = "";
            }
            if (!$products[$n]['inWishlist']) {
                $products[$n]['inWishlist'] = "";
            }

            if ($prod->getImage() && (($prod->getImage()) != 'no_selection')) {
                $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $prod->getImage();
            }

            if (empty($imageUrl)) {
                if ($prod->getThumbnail() && (($prod->getThumbnail()) != 'no_selection')) {
                    $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $prod->getThumbnail();
                }
            }

            $products[$n]['price_html'] = ''; //strip_tags($layout->getLayout()->getBlock('product_list')->getPriceHtml($prod, true));
            /* End For price_html */
            if ($prod->getTypeId() == 'grouped') {
                $minprice = '';
                $oModel = $this->_objectManager->get("Magento\GroupedProduct\Model\Product\CatalogPrice");
                $minprice = $oModel->getCatalogPrice($prod);
                $products[$n]['final_price'] = number_format($minprice, 2);
            } else if ($prod->getTypeId() == 'bundle') {
                $tierprice = [];
                $oModel = $this->_objectManager->get("Magento\Bundle\Model\Product\Price");
                $tierprice = $oModel->getTotalPrices($prod);
                if (is_array($tierprice)) {
                    $products[$n]['price_html'] = __('From %1 - Upto %2', $this->currencyHelper->currency($tierprice[0], true, false), $this->currencyHelper->currency($tierprice[1], true, false)); //number_format($tierprice,2);
                } else {
                    $products[$n]['final_price'] = number_format($this->currencyHelper->currency($tierprice, false, false), 2);

                    $prod->setFinalPrice($tierprice);
                }
            }

            $products[$n]['final_disc'] = "0";
            if (floatval($prod->getPrice()) > 0 && floatval($prod->getFinalPrice()) < floatval($prod->getPrice()))
                $products[$n]['final_disc'] = number_format(100 - (floatval($prod->getFinalPrice()) / floatval($prod->getPrice()) * 100), 2);


            $products[$n]['image'] = $imageUrl; //$product->getImageUrl();
            $products[$n]['in_stock'] = $prod->isSalable();
            $products[$n]['created'] = $prod->getCreatedAt();

            $RatingOb = $this->_objectManager->get('Magento\Review\Model\Rating')->getEntitySummary($prod->getId());
            $ratings = $RatingOb->getCount() > 0 ? ($RatingOb->getSum() / $RatingOb->getCount()) : false;
            if ($ratings == false) {
                $ratings = 0;
            }
            $products[$n]['rating'] = number_format($ratings, 2);
            $products[$n]['is_new_prod'] = strval((int) $this->isProductNew($prod));
            ++$n;
        }

        return $products;
    }

    /**
     * Retrives store id from store code, if no store id specified,
     * it use seted session or admin store
     *
     * @param string|int $store
     * @return int
     */
    protected function _getStoreId($store = null) {
        try {
            $storeId = $this->_storeManager->getStore($store)->getId();
        } catch (Mage_Core_Model_Store_Exception $e) {
            throw new \Exception(__('Store Does not exist'));
        }

        return $storeId;
    }

    /**
     * Retrieve category tree
     *
     * @param int $parent
     * @param string|int $store
     * @return array
     */
    protected function getCategorytree($parentId = null, $store = 1, $catIds = [], $additionalCols = []) {
        if (is_null($parentId) && !is_null($store)) {
            $parentId = $this->_storeManager->getStore($this->_getStoreId($store))->getRootCategoryId();
        } elseif (is_null($parentId)) {
            $parentId = $this->_storeManager->getStore()->getRootCategoryId();
        }

        /* @var $tree Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree */
        $tree = $this->_objectManager->get('Magento\Catalog\Model\ResourceModel\Category\Tree')->load();

        $root = $tree->getNodeById($parentId);

        if ($root && $root->getId() == 1) {
            $root->setName(__('Root'));
        }

        $collection = $this->_objectManager->get('Magento\Catalog\Model\Category')->getCollection()
                ->setStoreId($this->_getStoreId($store))
                ->addAttributeToSelect('name')
                ->addAttributeToSelect('image')
                ->addAttributeToSelect('is_active');

        if (!empty($additionalCols)) {
            foreach ($additionalCols as $_coln) {
                if (is_string($_coln['col']) and ! is_numeric($_coln['col'])) {
                    $collection->addAttributeToSelect($_coln['col']);
                }
            }
        }

        $tree->addCollectionData($collection, true);
        $inCats = [];

        if (!empty($catIds)) {
            $inCats = $this->getCatChildsArray($catIds);
            $inCats = array_merge($inCats, array($parentId));
        }
        // var_Dump($this->_nodeToArray($root, $inCats)); die;
        return $this->_nodeToArray($root, $inCats, $additionalCols);
    }

    protected function getCatChildsArray($confingCats = []) {
        // $catIds = $this->__helper->getStoreConfig('minimart/minimart_registration/categories');
        //$confingCats = explode(',', $catIds);
        $catCollection = $this->_objectManager->get('Magento\Catalog\Model\Category')->getCollection();

        if (!empty($confingCats))
            $catCollection->addAttributeToFilter('entity_id', array('in' => $confingCats));

        $categories = [];
        $categories = array_merge($categories, $confingCats);

        foreach ($catCollection as $_rootcat) {

            $categories = array_merge($categories, $this->getChildCategories($_rootcat));
        }

        return $categories;
    }

    protected function getChildCategories($parent) {

        $cat_model = $this->_objectManager->get('Magento\Catalog\Model\Category');

        $categories = $cat_model->load($parent->getId())->getChildrenCategories();

        $ret_arr = [];
        foreach ($categories as $cat) {
            $ret_arr[] = $cat->getId();
            $ret_arr = array_merge($ret_arr, $this->getChildCategories($cat));
        } // foreach sonu

        return $ret_arr;
    }

    protected function getAllProductsOfChilds() {
        $resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection');
        $cats = $this->getCatChildsArray();
        $inArr = implode(",", $cats);
        if (!empty($inArr)) {
            $adapter = $resource->getConnection();
            $select = $adapter->select()
                    ->from($resource->getTableName('catalog/category_product'), 'product_id')
                    ->where('category_id IN (' . $inArr . ')');
            //echo $select->__toString(); die;
            return $adapter->fetchCol($select);
        } else {
            return [];
        }
    }

    /**
     * Convert node to array
     *
     * @param Varien_Data_Tree_Node $node
     * @return array
     */
    protected function _nodeToArray(\Magento\Framework\Data\Tree\Node $node, $inCats = null, $additionalCols = []) {
        // Only basic category data

        $result = [];
        
        if (empty($inCats) || in_array($node->getId(), $inCats)) {
            $result['category_id'] = $node->getId();
            $result['parent_id'] = $node->getParentId();
            $result['name'] = $node->getName();
            $result['is_active'] = $node->getIsActive();
            $result['position'] = $node->getPosition();
            $result['level'] = $node->getLevel();
           
            if (!empty($additionalCols)) {
                $cat = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($node->getId());
                foreach ($additionalCols as $_col) {
                    $append = '';
                    if (isset($_col['type']) and $_col['type'] == 'image' and $cat->getData($_col['col'])) {
                        $append = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/';
                    }

                    $result[$_col['col']] = $append . $cat->getData($_col['col']);
                }
            }
            $result['children'] = [];
           
            $result['view_all'] = "0";
            if (count($node->getChildren()->getNodes()) > 0) {
                $result['view_all'] = "1";
            }
        }
		
        foreach ($node->getChildren() as $child) {
            $childs = $this->_nodeToArray($child, $inCats, $additionalCols);
            if (!empty($childs)) {
                $result['children'][] = $childs;
            }
        }
        return $result;
    }

    protected function recur_html_decode_nav(&$categories) {
        foreach ($categories as &$category) {
            if ($category['is_active'] == 1) {
                $category['name'] = html_entity_decode($category['name']);
                if (isset($category['children']) and ! empty($category['children'])) {
                    $childrens = $category['children'];
                    $category['children'] = $this->arrange_into_one_child($childrens);
                }
            }
        }
        return $categories;
    }

    protected function arrange_into_one_child($categories, &$array = []) {
        foreach ($categories as &$category) {
            if ($category['is_active'] == 1) {
                $category['name'] = html_entity_decode($category['name']);
                $childs = isset($category['children']) ? $category['children'] : false;

                if (isset($category['children']))
                    unset($category['children']);

                $category['children'] = [];

                $array[] = $category;

                if ($childs and ! empty($childs)) {
                    $this->arrange_into_one_child($childs, $array);
                }
            }
        }

        return $array;
    }

    /**
     * getWishListInstance for customer
     * @param type $customer
     * @return type
     */
    protected function getWishListInstance($customer) {
        $wishlist = $this->_objectManager->get('Magento\Wishlist\Model\Wishlist')
                ->loadByCustomerId($customer->getId(), true);
        //$wishListInstance->setCustomer($customer);
        return $wishlist;
    }

    /**
     * getWishListById
     * @param type $item_id
     * @return type
     */
    protected function getWishListById($item_id) {
        $wishlist = $this->_objectManager->get('Magento\Wishlist\Model\Wishlist');
        $wishlist->load($item_id);
        return $wishlist;
    }

    protected function checkInWishilist($_productid, $customer_id) {
        $witemid = '';
        if ($customer_id and $_productid) {
            $wishlist = $this->_objectManager->get('Magento\Wishlist\Model\Item')->getCollection();
            $wishlist->addFieldToSelect('wishlist_item_id');
            $wishlist->getSelect()
                    ->join(array('t2' => 'wishlist'), 'main_table.wishlist_id = t2.wishlist_id', array('wishlist_id', 'customer_id'))
                    ->where('main_table.product_id = ' . $_productid . ' AND t2.customer_id=' . $customer_id);
            $witemid = $wishlist->getFirstItem()->getWishlistItemId();
        }
        return $witemid;
    }

    protected function render_flat_nav($categories, &$return = [], $prepend = '') {
        foreach ($categories as $category) {
            $returnSample = ['category_id' => $category['category_id'], 'name' => $prepend . "" . html_entity_decode($category['name'])];
            $return[] = $returnSample;
            if (isset($category['children']) and ! empty($category['children'])) {
                $this->render_flat_nav($category['children'], $return, html_entity_decode($category['name']) . "- ");
            }
        }
        return $return;
    }

    protected function getCategoryListArray() {

        $catIds = $this->__helper->getStoreConfig('minimart/minimart_registration/categories');

        $result = $this->getCategorytree();

        $data = [];
        if (empty($catIds) and $result['children'][0]['is_active'] == 1) {
            $data = $result['children'][0]['children'];
        } else if (!empty($catIds)) {
            $data = $result['children'][0]['children'];
        }

        return $data;
    }

    public function setUserToken($email, $token_type, $token, $customer_id = null) {

        $model = $this->_objectManager->get('Softprodigy\Minimart\Model\Deviceinfo')->load($email, 'customer_email');
        $rowId = $model->getId();

        if ($rowId) {
            if (!empty($customer_id))
                $model->setCustomerId($customer_id);

            $model->setToken($token);
            $model->setType($token_type);
            $model->setId($rowId);
        }else {
            $model->setData([
                'customer_id' => (!empty($customer_id)) ? $customer_id : 0,
                'customer_email' => $email,
                'type' => $token_type,
                'token' => $token,
                'modified' => date('Y-m-d H:i:s'),
                'badge_count' => 0
            ]);
        }
        $model->save();

        //die; 
        return true;
    }

    protected function assignQuote($quote_id, $customer_id, $cusModel) {
        $quote = $this->_quoteLoader->load($quote_id);
        if (!empty($customer_id)) {
            $customerObj = $cusModel->load($customer_id);
            
            $newCustomerDataObject = $this->_objectManager->create("Magento\Customer\Api\Data\CustomerInterface");

            $onbejctHelper = $this->_objectManager->create('Magento\Framework\Api\DataObjectHelper');
            $onbejctHelper->populateWithArray(
                    $newCustomerDataObject, $customerObj->getData(), '\Magento\Customer\Api\Data\CustomerInterface'
            );


            $quote->assignCustomer($newCustomerDataObject)->setCustomerId($customerObj->getId())->save();
            
            //$quote->assignCustomer($customerObj);
            $quote->save();
        }
    }

    protected function removeCoupon($quote) {
        $return = '';

        try {


            $couponCode = '';


            if ($quote->getIsActive()) {
                $this->cart->setQuote($quote);
                $cartQuote = $this->cart->getQuote();
                $oldCouponCode = $cartQuote->getCouponCode();

                $codeLength = strlen($couponCode);
                if (!$codeLength && !strlen($oldCouponCode)) {
                    $return = __("This Coupon is already applied.");
                    return $return;
                }

                try {
                    $isCodeLengthValid = $codeLength && $codeLength <= \Magento\Checkout\Helper\Cart::COUPON_CODE_MAX_LENGTH;

                    $itemsCount = $cartQuote->getItemsCount();
                    if ($itemsCount) {
                        $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                        $cartQuote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals();
                        $this->quoteRepository->save($cartQuote);
                    }
                    $return = __('You canceled the coupon code.');
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $return = __($e->getMessage());
                } catch (\Exception $e) {
                    $return = __('We cannot apply the coupon code.');
                    $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                }
            } else {
                $return = __("Cart is de-activated.");
            }
        } catch (\Exception $e) {
            $return = $e->getMessage();
        }

        return $return;
    }

    protected function getStoreCountries() {
        $collection = $this->_objectManager->get('Magento\Directory\Model\Country')->getResourceCollection()
                ->loadByStore();
        $result = [];
        foreach ($collection as $country) {
            /* @var $country Mage_Directory_Model_Country */
            $name = $country->getName(); // Loading name in default locale
            $arr = $country->toArray(array('country_id', 'iso2_code', 'iso3_code', 'name'));
            $arr['name'] = $name;
            $result[] = $arr;
        }
        return $result;
    }

    protected function getCounrtyRegion($country_code) {

        $country = $this->_objectManager->get('Magento\Directory\Model\Country')->loadByCode($country_code);


        if (!$country->getId()) {
            throw new \Exception(__("Country does not exists"));
        }

        $result = [];
        foreach ($country->getRegions() as $region) {
            $region->getName();
            $result[] = $region->toArray(array('region_id', 'code', 'name'));
        }
        return $result;
    }

    protected function _init_curlReq($requestUrl, $params, $type = 'get', $authHeader = []) {
        $authHeader = array_merge($authHeader, ['Accept: application/json', 'Content-Type: application/json']);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        if ($type === 'post')
            curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $authHeader);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    protected function getQuoteShippingMethodsList($quote, $store = null) {
        $quoteShippingAddress = $quote->getShippingAddress();
        //$quoteShippingAddress->setSaveInAddressBook(1);
        
        if (is_null($quoteShippingAddress->getId())) {

            throw new \Exception(__("Can not make operation because of customer shipping address is not set"));
        }
        $ratesResult = [];
        try {
            $quoteShippingAddress->setCollectShippingRates(true);
            
            $quoteShippingAddress->collectShippingRates()->save();
            // var_dump($quote->getShippingAddress()->getSaveInAddressBook()); die;
            $shippingMethodManagementService = $this->_objectManager->create(
                    'Magento\Quote\Api\ShippingMethodManagementInterface'
            );

            $shippingMethods = $shippingMethodManagementService->getList($quote->getId());
            foreach ($shippingMethods as $shippingMethod) {
            
                $ratesResult[] = [
                    \Magento\Quote\Api\Data\ShippingMethodInterface::KEY_CARRIER_CODE => $shippingMethod->getCarrierCode(),
                    \Magento\Quote\Api\Data\ShippingMethodInterface::KEY_METHOD_CODE => $shippingMethod->getMethodCode(),
                    \Magento\Quote\Api\Data\ShippingMethodInterface::KEY_CARRIER_TITLE => $shippingMethod->getCarrierTitle(),
                    \Magento\Quote\Api\Data\ShippingMethodInterface::KEY_METHOD_TITLE => $shippingMethod->getMethodTitle(),
                    \Magento\Quote\Api\Data\ShippingMethodInterface::KEY_SHIPPING_AMOUNT => $shippingMethod->getAmount(),
                    \Magento\Quote\Api\Data\ShippingMethodInterface::KEY_BASE_SHIPPING_AMOUNT => $shippingMethod->getBaseAmount(),
                    \Magento\Quote\Api\Data\ShippingMethodInterface::KEY_AVAILABLE => $shippingMethod->getAvailable(),
                    \Magento\Quote\Api\Data\ShippingMethodInterface::KEY_ERROR_MESSAGE => null,
                    \Magento\Quote\Api\Data\ShippingMethodInterface::KEY_PRICE_EXCL_TAX => $shippingMethod->getPriceExclTax(),
                    \Magento\Quote\Api\Data\ShippingMethodInterface::KEY_PRICE_INCL_TAX => $shippingMethod->getPriceInclTax(),
                ];
            }

            if (count($ratesResult) == 0) {
                throw new \Exception(__("Can not receive list of shipping methods."));
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
            throw new \Exception(__("Can not receive list of shipping methods."));
        }
        return $ratesResult;
    }

    protected function getActivPaymentMethod($quote) {
        $params = $this->getRequest()->getParams();

        $store = $quote ? $quote->getStoreId() : null;
        $methods = [];
        //$allMEthods = $this->_objectManager->create("Magento\Payment\Helper\Data")->getStoreMethods($store, $quote);
        $payMethodManagementService = $this->_objectManager->create(
                'Magento\Checkout\Model\PaymentInformationManagement'
        );

        $methods = $payMethodManagementService->getPaymentInformation($quote->getId());

        /* foreach($allMEthods as $method) {

          $canuse = $this->_canUseMethod($quote, $method);

          if ($canuse && $method->isApplicableToQuote(
          $quote, \Magento\Payment\Model\Method\AbstractMethod::CHECK_ZERO_TOTAL
          )) {
          $methods[] = $method;
          }
          } */

        $ispayuen = false;
        foreach ($methods->getPaymentMethods() as $_method):

            if (!$_method->canUseCheckout() or ! $_method->isAvailable())
                continue;

            $_code = $_method->getCode();
            if ($_code == 'payucheckout_shared') {
                $ispayuen = true;
            }

            $arrMethods[] = array(
                'title' => $_method->getTitle(),
                'code' => $_code,
                'instructions' => $_method->getInstructions(),
                'cc_types' => $this->_getPaymentMethodAvailableCcTypes($_method),
                'data' => ''
            );
        endforeach;
        return array('methods' => $arrMethods, 'is_payu' => $ispayuen);
    }

    /**
     * Check payment method model
     *
     * @param Mage_Payment_Model_Method_Abstract $method
     * @return bool
     */
    protected function _canUseMethod($quote, $method) {
        return $method && $method->canUseCheckout() && $method->isApplicableToQuote($quote, \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_COUNTRY | \Magento\Payment\Model\Method\AbstractMethod::CHECK_USE_FOR_CURRENCY | \Magento\Payment\Model\Method\AbstractMethod::CHECK_ORDER_TOTAL_MIN_MAX);
    }

    protected function _getPaymentMethodAvailableCcTypes($method) {
        $ccTypes = $this->_objectManager->create("Magento\Payment\Model\Config")->getCcTypes();
        $methodCcTypes = explode(',', $method->getConfigData('cctypes'));
        foreach ($ccTypes as $code => $title) {
            if (!in_array($code, $methodCcTypes)) {
                unset($ccTypes[$code]);
            }
        }
        if (empty($ccTypes)) {
            return null;
        }

        return $ccTypes;
    }

    protected function setShippingMethod($quote, $shippingMethod, $store = null) {

        $quoteShippingAddress = $quote->getShippingAddress();
        if (is_null($quoteShippingAddress->getId())) {
            throw new \Exception(__("Can not make operation because of customer shipping address is not set"));
        }




        try {
            
            $quoteShippingAddress->setCollectShippingRates(true)
                        ->collectShippingRates();
            $quote->getShippingAddress()->setShippingMethod($shippingMethod);
            $found = $quote->getShippingAddress()->requestShippingRates();

            if ($found){
                $quote->setTotalsCollectedFlag(false);
                $quote->collectTotals()->save();
            } else{
                throw new \Exception(__("Shipping method is not available"));
            }
        } catch (\Exception $e) {
                
            $this->logger->debug($e->__toString());
            throw new \Exception(__("Can not make operation because of customer shipping address is not set"));
        }

        return true;
    }

    /**
     * @param  $quote
     * @return bool
     */
    public function prepareCustomerForQuote(&$quote) {
        $isNewCustomer = false;
        switch ($quote->getCheckoutMethod()) {
            case \Magento\Checkout\Model\Type\Onepage::METHOD_GUEST:
                $this->_prepareGuestQuote($quote);
                break;
            case \Magento\Checkout\Model\Type\Onepage::METHOD_REGISTER:
                $this->_prepareNewCustomerQuote($quote);
                $isNewCustomer = true;
                break;
            default:
                $this->_prepareCustomerQuote($quote);
                break;
        }

        return $isNewCustomer;
    }

    /**
     * Prepare quote for guest checkout order submit
     *
     * @param $quote
     * @return 
     */
    protected function _prepareGuestQuote(&$quote) {
        $quote->setCustomerId(null)
                ->setCustomerEmail($quote->getBillingAddress()->getEmail())
                ->setCustomerIsGuest(true)
                ->setCustomerGroupId(\Magento\Customer\Model\Group::NOT_LOGGED_IN_ID);
        return $this;
    }

    /**
     * Prepare quote for customer registration and customer order submit
     *
     * @param  $quote
     * @return 
     */
    protected function _prepareNewCustomerQuote(&$quote) {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        //$customer = Mage::getModel('customer/customer');
        $customer = $quote->getCustomer();
        /* @var $customer Magento\Customer\Model\Customer */
        $customerBilling = $billing->exportCustomerAddress();
        $customer->addAddress($customerBilling);
        $billing->setCustomerAddress($customerBilling);
        $customerBilling->setIsDefaultBilling(true);
        if ($shipping && !$shipping->getSameAsBilling()) {
            $customerShipping = $shipping->exportCustomerAddress();
            $customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
            $customerShipping->setIsDefaultShipping(true);
        } else {
            $customerBilling->setIsDefaultShipping(true);
        }

        $this->_objectManager->create('Magento\Framework\DataObject\Copy')->copyFieldsetToTarget('checkout_onepage_quote', 'to_customer', $quote, $customer);
        $customer->setPassword($customer->decryptPassword($quote->getPasswordHash()));
        $quote->setCustomer($customer)
                ->setCustomerId(true);

        return $this;
    }

    /**
     * Prepare quote for customer order submit
     *
     * @param  $quote
     * @return 
     */
    protected function _prepareCustomerQuote(&$quote) {
        $billing = $quote->getBillingAddress();
        $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

        $customer = $quote->getCustomer();
        
        if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
            $customerBilling = $billing->exportCustomerAddress();
           // $customer->addAddress($customerBilling);
            $billing->setCustomerAddress($customerBilling);
            $billing->setCustomerAddressId($customerBilling->getId());
        }
        if ($shipping && ((!$shipping->getCustomerId() && !$shipping->getSameAsBilling()) || (!$shipping->getSameAsBilling() && $shipping->getSaveInAddressBook()))) {
            $customerShipping = $shipping->exportCustomerAddress();
            //$customer->addAddress($customerShipping);
            $shipping->setCustomerAddress($customerShipping);
            $shipping->setCustomerAddressId($customerShipping->getId());
        }

        if (isset($customerBilling) && !$customer->getDefaultBilling()) {
            $customerBilling->setIsDefaultBilling(true);
        }
        if ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
            $customerShipping->setIsDefaultShipping(true);
        } else if (isset($customerBilling) && !$customer->getDefaultShipping()) {
            $customerBilling->setIsDefaultShipping(true);
        }
        $quote->setCustomer($customer);

        return $this;
    }

    /**
     * Involve new customer to system
     *
     * @return $this
     */
    protected function _involveNewCustomer($quote) {
        $customer = $quote->getCustomer();
        $confirmationStatus = $this->_objectManager->create("Magento\Customer\Api\AccountManagementInterface")->getConfirmationStatus($customer->getId());
        if ($confirmationStatus === \Magento\Customer\Model\AccountManagement::ACCOUNT_CONFIRMATION_REQUIRED) {
            $url = $this->_objectManager->get('Magento\Customer\Model\Url')->getEmailConfirmationUrl($customer->getEmail());

            // @codingStandardsIgnoreStart
            return __(
                    'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.', $url
            );
            // @codingStandardsIgnoreEnd
        }
        return $this;
    }

    protected function _formatPrice($order, $amount, $currency = null) {
        return $order->getBaseCurrency()->formatTxt(
                        $amount, $currency ? array('currency' => $currency) : []
        );
    }

    protected function _appendTransactionToMessage($transaction, $message) {
        if ($transaction) {
            $txnId = is_object($transaction) ? $transaction->getTxnId() : $transaction;
            $message .= ' ' . __('Transaction ID: "%s".', $txnId);
        }
        return $message;
    }

    protected function _prependMessage($preparedMessage, $messagePrependTo) {
        if ($preparedMessage) {
            if (is_string($preparedMessage)) {
                return $preparedMessage . ' ' . $messagePrependTo;
            } elseif (is_object($preparedMessage) && ($preparedMessage instanceof \Magento\Sales\Model\Order_Status_History)
            ) {
                $comment = $preparedMessage->getComment() . ' ' . $messagePrependTo;
                $preparedMessage->setComment($comment);
                return $comment;
            }
        }
        return $messagePrependTo;
    }

    protected function saveLinkPurchaged($orderItem) {
        $product = $orderItem->getProduct();
        if ($product && $product->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $this;
        }
        $linkPModel = $this->_objectManager->get('Magento\Downloadable\Model\Link\Purchased');
        if ($linkPModel->load($orderItem->getId(), 'order_item_id')->getId()) {
            return $this;
        }
        if (!$product) {
            $product = $this->productFactory
                    ->setStoreId($orderItem->getOrder()->getStoreId())
                    ->load($orderItem->getProductId());
        }
        if ($product->getTypeId() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            $links = $product->getTypeInstance(true)->getLinks($product);
            if ($linkIds = $orderItem->getProductOptionByCode('links')) {
                $linkPurchased = $this->_objectManager->get('Magento\Downloadable\Model\Link\Purchased');
                $this->_objectManager->create('Magento\Framework\DataObject\Copy')->copyFieldsetToTarget(
                        'downloadable_sales_copy_order', 'to_downloadable', $orderItem->getOrder(), $linkPurchased
                );
                $this->_objectManager->create('Magento\Framework\DataObject\Copy')->copyFieldsetToTarget(
                        'downloadable_sales_copy_order_item', 'to_downloadable', $orderItem, $linkPurchased
                );
                $linkSectionTitle = (
                        $product->getLinksTitle() ?
                                $product->getLinksTitle() : $this->__helper->getStoreConfig(\Magento\Downloadable\Model\Link::XML_PATH_LINKS_TITLE)
                        );
                $linkPurchased->setLinkSectionTitle($linkSectionTitle)
                        ->save();
                foreach ($linkIds as $linkId) {
                    if (isset($links[$linkId])) {
                        $linkPurchasedItem = $this->_objectManager->get('Magento\Downloadable\Model\Link\Purchased\Item')
                                ->setPurchasedId($linkPurchased->getId())
                                ->setOrderItemId($orderItem->getId());

                        $this->_objectManager->create('Magento\Framework\DataObject\Copy')->copyFieldsetToTarget(
                                'downloadable_sales_copy_link', 'to_purchased', $links[$linkId], $linkPurchasedItem
                        );
                        $linkHash = strtr(base64_encode(microtime() . $linkPurchased->getId() . $orderItem->getId()
                                        . $product->getId()), '+/=', '-_,');
                        $numberOfDownloads = $links[$linkId]->getNumberOfDownloads() * $orderItem->getQtyOrdered();
                        $linkPurchasedItem->setLinkHash($linkHash)
                                ->setNumberOfDownloadsBought($numberOfDownloads)
                                ->setStatus(\Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING)
                                ->setCreatedAt($orderItem->getCreatedAt())
                                ->setUpdatedAt($orderItem->getUpdatedAt())
                                ->save();
                    }
                }
            }
        }
    }

    protected function saveLinkStatus($order) {
        /* @var $order \Magento\Sales\Model\Order */
        $status = '';
        $linkStatuses = array(
            'pending' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING,
            'expired' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED,
            'avail' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_AVAILABLE,
            'payment_pending' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING_PAYMENT,
            'payment_review' => \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW
        );

        $downloadableItemsStatuses = [];
        $orderItemStatusToEnable = $this->__helper->getStoreConfig(
                \Magento\Downloadable\Model\Link\Purchased\Item::XML_PATH_ORDER_ITEM_STATUS, $order->getStoreId()
        );

        if ($order->getState() == \Magento\Sales\Model\Order::STATE_HOLDED) {
            $status = $linkStatuses['pending'];
        } elseif ($order->isCanceled() || $order->getState() == \Magento\Sales\Model\Order::STATE_CLOSED || $order->getState() == \Magento\Sales\Model\Order::STATE_COMPLETE
        ) {
            $expiredStatuses = array(
                \Magento\Sales\Model\Order\Item::STATUS_CANCELED,
                \Magento\Sales\Model\Order\Item::STATUS_REFUNDED,
            );
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    if (in_array($item->getStatusId(), $expiredStatuses)) {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['expired'];
                    } else {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['avail'];
                    }
                }
            }
        } elseif ($order->getState() == \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT) {
            $status = $linkStatuses['payment_pending'];
        } elseif ($order->getState() == \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW) {
            $status = $linkStatuses['payment_review'];
        } else {
            $availableStatuses = array($orderItemStatusToEnable, \Magento\Sales\Model\Order\Item::STATUS_INVOICED);
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    if ($item->getStatusId() == \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED &&
                            $orderItemStatusToEnable == \Magento\Sales\Model\Order\Item::STATUS_PENDING &&
                            !in_array(\Magento\Sales\Model\Order\Item::STATUS_BACKORDERED, $availableStatuses, true)) {
                        $availableStatuses[] = \Magento\Sales\Model\Order\Item::STATUS_BACKORDERED;
                    }

                    if (in_array($item->getStatusId(), $availableStatuses)) {
                        $downloadableItemsStatuses[$item->getId()] = $linkStatuses['avail'];
                    }
                }
            }
        }
        if (!$downloadableItemsStatuses && $status) {
            foreach ($order->getAllItems() as $item) {
                if ($item->getProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE || $item->getRealProductType() == \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
                ) {
                    $downloadableItemsStatuses[$item->getId()] = $status;
                }
            }
        }

        if ($downloadableItemsStatuses) {
            $linkPurchased = $this->_objectManager->create('\Magento\Downloadable\Model\Link\Purchased\Item\Collection')
                    ->addFieldToFilter('order_item_id', array('in' => array_keys($downloadableItemsStatuses)));
            foreach ($linkPurchased as $link) {
                if ($link->getStatus() != $linkStatuses['expired'] && !empty($downloadableItemsStatuses[$link->getOrderItemId()])
                ) {
                    $link->setStatus($downloadableItemsStatuses[$link->getOrderItemId()])
                            ->save();
                }
            }
        }
    }

    /**
     * Retrieve entity attributes values
     *
     * @param Mage_Core_Model_Abstract $object
     * @param array $attributes
     * @return Mage_Sales_Model_Api_Resource
     */
    protected function _getAttributes($object, $type, array $attributes = null) {
        $result = [];

        if (!is_object($object)) {
            return $result;
        }

        foreach ($object->getData() as $attribute => $value) {
            if ($this->_isAllowedAttribute($attribute, $type, $attributes)) {
                $result[$attribute] = $value;
            }
        }

        if (isset($this->_attributesMap['global'])) {
            foreach ($this->_attributesMap['global'] as $alias => $attributeCode) {
                $result[$alias] = $object->getData($attributeCode);
            }
        }

        if (isset($this->_attributesMap[$type])) {
            foreach ($this->_attributesMap[$type] as $alias => $attributeCode) {
                $result[$alias] = $object->getData($attributeCode);
            }
        }

        return $result;
    }

    /**
     * Check is attribute allowed to usage
     *
     * @param Mage_Eav_Model_Entity_Attribute_Abstract $attribute
     * @param string $entityType
     * @param array $attributes
     * @return boolean
     */
    protected function _isAllowedAttribute($attributeCode, $type, array $attributes = null) {
        if (!empty($attributes) && !(in_array($attributeCode, $attributes))) {
            return false;
        }

        if (in_array($attributeCode, $this->_ignoredAttributeCodes['global'])) {
            return false;
        }

        if (isset($this->_ignoredAttributeCodes[$type]) && in_array($attributeCode, $this->_ignoredAttributeCodes[$type])) {
            return false;
        }

        return true;
    }

    protected function getOrderList($param, $pageno, $limit) {

        $yourCustomerId = $param['uid'];
        $field = 'customer_id';
        $collection = $this->_objectManager->create("Magento\Sales\Model\Order")->getCollection()
                        ->addAttributeToSelect('*')
                        ->addAttributeToFilter($field, $yourCustomerId)->setOrder('created_at', 'desc');
        $colcBkp = clone $collection;
        $prod_count = count($colcBkp);

        $collection = $collection->setPageSize($limit)->setCurPage($pageno)->load();

        $order_product['order'] = [];
        $j = 0;
        foreach ($collection as $order) {
            $order->getRealOrderId();
            $items = $order->getAllItems();
            //echo $itemcount=count($items);
            $status = $order->getStatus();
            if ($status == 'holded') {
                $status = 'On Hold';
            }

            $order_product['order'][$j]['order_id'] = $order->getRealOrderId();
            $order_product['order'][$j]['created_at'] = strtotime(date("Y-m-d H:i:s", strtotime($order->getCreatedAt())));

            $order_product['order'][$j]['status'] = $status;
            $order_product['order'][$j]['grand_total'] = number_format($order->getGrandTotal(), 2);
            
            $i = 0;
            foreach ($items as $itemId => $item) {
                $prod = $this->productFactory->load($item->getProductId());

                $order_product['order'][$j]['products'][$i]['id'] = $item->getProductId();
                $order_product['order'][$j]['products'][$i]['name'] = $item->getName();
                $order_product['order'][$j]['products'][$i]['qty'] = number_format($item->getQtyOrdered(),2);
                $order_product['order'][$j]['products'][$i]['unit_price'] = number_format($item->getPrice(), 2);
                $order_product['order'][$j]['products'][$i]['row_price'] = number_format($item->getRowTotalInclTax(), 2);
                
                $imgUrl = '';
                if ($prod->getImage() && (($prod->getImage()) != 'no_selection')) {
                    //$imgUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($prod->getImage());
                    if ($prod->getSmallImage()):
                        $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $prod->getSmallImage();
                    else:
                        $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $prod->getImage();
                    endif;
                }

                $order_product['order'][$j]['products'][$i]['image'] = $imgUrl;
                $order_product['order'][$j]['products'][$i]['price'] = number_format($item->getPrice(), 2);
                ++$i;
            }
            ++$j;
        }

        $more = count($collection);

        if ($prod_count <= ($limit * $pageno))
            $order_product['more'] = 0;
        else
            $order_product['more'] = 1;
        return $order_product;
    }

    protected function getOrderInfo($order, $result, $params) {

        if ($result['status'] == 'holded') {
            $result['status'] = 'On Hold';
        }


        $result['shipping_address'] = $this->_getAttributes($order->getShippingAddress(), 'order_address');
        $result['billing_address'] = $this->_getAttributes($order->getBillingAddress(), 'order_address');
        $result['items'] = [];

        foreach ($order->getAllItems() as $item) {
            /* if ($item->getGiftMessageId() > 0) {
              $item->setGiftMessage(
              Mage::getSingleton('giftmessage/message')->load($item->getGiftMessageId())->getMessage()
              );
              } */

            $result['items'][] = $this->_getAttributes($item, 'order_item');
        }

        $result['payment'] = $this->_getAttributes($order->getPayment(), 'order_payment');
        $result['status_history'] = [];

        foreach ($order->getAllStatusHistory() as $history) {
            $result['status_history'][] = $this->_getAttributes($history, 'order_status_history');
        }
        //echo "<pre>";
       // var_export($result); die;
        
        $data['order_id'] = $params['order_id'];
        $data['status'] = $result['status'];
        $data['shipping_description'] = $result['shipping_description'];
        $data['shipping_total'] = number_format($result['shipping_amount'], 2);
        $data['discount'] = number_format($result['discount_amount'], 2);
        $data['subtotal'] = number_format($result['subtotal'], 2);
        $data['tax'] = number_format($result['tax_amount'], 2);
        $data['grand_total'] = number_format($result['grand_total'], 2);
        $data['qty_ordered'] = number_format($result['total_qty_ordered'], 2);
        $data['created'] = strtotime(date("Y-m-d H:i:s", strtotime($result['created_at'])));
        $data['weight'] = $result['weight'];
        $data['is_virtual'] = $result['is_virtual'];
        
        
        $data['customer_info']['email'] = $result['customer_email'];
        $data['customer_info']['firstname'] = $result['customer_firstname'];
        $data['customer_info']['lastname'] = $result['customer_lastname'];
                
        if(!isset($result['is_virtual']) || $result['is_virtual']==0){
            $data['shipping_address']['firstname'] = $result['shipping_address']['firstname'];
            $data['shipping_address']['lastname'] = $result['shipping_address']['lastname'];
            $data['shipping_address']['street'] = $result['shipping_address']['street'];
            $data['shipping_address']['city'] = $result['shipping_address']['city'];
            $data['shipping_address']['state'] = $result['shipping_address']['region'];
            $data['shipping_address']['zipcode'] = $result['shipping_address']['postcode'];
            $data['shipping_address']['country_id'] = $result['shipping_address']['country_id'];
            $data['shipping_address']['telephone'] = $result['shipping_address']['telephone'];
            $countryname= '';
            try{
                 //$cntryobjb = Mage::getModel('directory/country')->loadByCode($result['shipping_address']['country_id']);
                 $cntryobjb = $this->_objectManager->get('Magento\Directory\Model\Country')->load($result['shipping_address']['country_id']);
                 $countryname =  $cntryobjb->getName();
            } catch (\Exception $e){
                $this->logger->debug($e);
            } 
            $data['shipping_address']['country_name'] = $countryname;
        } else {
            $data['shipping_address']['firstname'] = '';
            $data['shipping_address']['lastname'] =  '';
            $data['shipping_address']['street'] =  '';
            $data['shipping_address']['city'] =  '';
            $data['shipping_address']['state'] = '';
            $data['shipping_address']['zipcode'] = '';
            $data['shipping_address']['country_id'] =  '';
            $data['shipping_address']['telephone'] = '';
            $data['shipping_address']['country_name'] =  '';
        }
        
        
        $data['billing_address']['firstname'] = $result['billing_address']['firstname'];
        $data['billing_address']['lastname'] = $result['billing_address']['lastname'];
        $data['billing_address']['street'] = $result['billing_address']['street'];
        $data['billing_address']['city'] = $result['billing_address']['city'];
        $data['billing_address']['state'] = $result['billing_address']['region'];
        $data['billing_address']['zipcode'] = $result['billing_address']['postcode'];
        $data['billing_address']['country_id'] = $result['billing_address']['country_id'];
        $data['billing_address']['telephone'] = $result['billing_address']['telephone'];
        $countrynameb= '';
        try{
             //$cntryobja = Mage::getModel('directory/country')->loadByCode($result['billing_address']['country_id']);
             $cntryobjb = $this->_objectManager->get('Magento\Directory\Model\Country')->load($result['shipping_address']['country_id']);
             $countrynameb =  $cntryobja->getName();
        } catch (\Exception $e){
           $this->logger->debug($e);
        } 
        $data['billing_address']['country_name'] = $countrynameb;
        
        $data['items'] = [];
        $i = 0;
        foreach ($result['items'] as $item) {
            $data['items'][$i]['id'] = $item['product_id'];
            $data['items'][$i]['sku'] = $item['sku'];
            $data['items'][$i]['name'] = $item['name'];
            $data['items'][$i]['qty'] = number_format($item['qty_ordered']);
            $data['items'][$i]['unit_price'] = number_format($item['price'], 2);
            $data['items'][$i]['final_price'] = number_format($item['row_total_incl_tax'], 2);

            $product = $this->productFactory->load($item['product_id']);
            $imgUrl = '';
            if ($product->getImage() && (($product->getImage()) != 'no_selection')) {
                //$imgUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
                if ($product->getSmallImage()):
                    $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getSmallImage();
                else:
                    $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
                endif;
            }

            $data['items'][$i]['img_url'] = $imgUrl;
            ++$i;
        }
        
        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodTitle = $method->getTitle();

        $data['payment']['method_name'] = $methodTitle;
        return $data;
    }

    protected function getWishListCollection($customer) {
        $collection = $this->_objectManager->get('Magento\Wishlist\Model\ResourceModel\Item\Collection')
                ->addStoreFilter($this->getSharedStoreIds(false))
                ->setVisibilityFilter();
        $collection->setWebsiteId($customer->getWebsiteId());
        $collection->setCustomerGroupId($customer->getGroupId());
        return $collection;
    }

    protected function _getWishlistItems($customer_Id) {
        $subs = $this->checkPackageSubcription();
        if ($subs['active_package']) {
            $this->activePackage = $subs['active_package'];
        }

        $inWishListEnabled = $this->__helper->getStoreConfig("wishlist/general/active");

        $return = array('products' => []);
        $resultCode = 0;
        $resultText = "fail";
      
        if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {
            $inWishListEnabled = false;
        }

        if ($inWishListEnabled) {
            $customerId = $customer_Id;

            if (!empty($customerId)) {
                try {
                    $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($customerId);
                    if (!$customer->getId()) {
                        $return['msg'] = __('Invalid customer');
 						$return["result"] = $resultCode;
						$return["resultText"] = $resultText;

                        return ["return" => $return, "resultCode" => $resultCode, "resultText" => $resultText];
                        die;
                    }
                    $wishListInstance = $this->getWishListInstance($customer);
                    //$return['wish_list_id'] = $wishListInstance->getId();
                    $_items = $wishListInstance->getItemCollection();
                    foreach ($_items as $_item) {
                        $arry = [];
                        $arry = $_item->getData();
                        $_product = $_item->getProduct();

                        if ($_product->getStatus() != 1)
                            continue;

                        if (isset($arry['product']))
                            unset($arry['product']);

                        $arry['qty'] = number_format($arry['qty'], 2);
                        $arry['price'] = number_format($_product->getFinalPrice(), 2);

                        $arry['price_html'] = '';

                        if ($_product->getTypeId() == 'grouped') {
                            $minprice = '';
                            $oModel = $this->_objectManager->get("Magento\GroupedProduct\Model\Product\CatalogPrice");
                            $minprice = $oModel->getCatalogPrice($_product);
                            $arry['price'] = number_format($minprice, 2);
                        } else if ($_product->getTypeId() == 'bundle') {
                            $tierprice = [];
                            $oModel = $this->_objectManager->get("Magento\Bundle\Model\Product\Price");
                            $tierprice = $oModel->getTotalPrices($_product);
                            if (is_array($tierprice))
                                $arry['price_html'] = __('From %1 - Upto %2', $this->currencyHelper->currency($tierprice[0], true, false), $this->currencyHelper->currency($tierprice[1], true, false)); //number_format($tierprice,2);
                            else
                                $arry['price'] = number_format($this->currencyHelper->currency($tierprice, false, false), 2);
                        }

                        $imageUrl = '';
                        if ($_product->getImage() && (($_product->getImage()) != 'no_selection')) {
                            // $imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($_product->getImage());
                            $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getImage();
                        }
                        if (empty($imageUrl)) {
                            if ($_product->getThumbnail() && (($_product->getThumbnail()) != 'no_selection')) {
                                //$imageUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($_product->getThumbnail());
                                $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getThumbnail();
                            }
                        }
                        $arry['added_at'] = strtotime(date("Y-m-d H:i:s", strtotime($arry['added_at'])));
                        $arry['has_options'] = $_product->getData('has_options');
                        $arry['product_name'] = $_product->getName();
                        $arry['product_sku'] = $_product->getSku();
                        $arry['product_image'] = $imageUrl;
                        $arry['product_type'] = $_product->getTypeId();

                        $return['products'][] = $arry;
                        unset($arry);
                    }
                   
                    //~ $return['enabled'] = true;
                    //~ $return['hasItems'] = count($_items) > 0 ? true : false;
                    $resultCode = 1;
                    $resultText = "success";
                } catch (\Exception $ex) {

                    $this->logger->debug($ex->getMessage());
                }
            }
        }
        return ["return" => $return, "resultCode" => $resultCode, "resultText" => $resultText];
    }

    protected function _addProdToWishList($prod_id,$cust_id,$qty) {
        $subs = $this->checkPackageSubcription();
        if ($subs['active_package']) {
            $this->activePackage = $subs['active_package'];
        }

        $inWishListEnabled = $this->__helper->getStoreConfig("wishlist/general/active");
        $return = array('enabled' => false, 'canAdd' => false, 'error' => true, 'msg' => '');
        $resultCode = 0;
        $resultText = "fail";
        if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {
            $inWishListEnabled = false;
            $return['msg'] = __('We can\'t add the item to Wish List right now.');
        }
        if ($inWishListEnabled) {
			
		    $product_id = $prod_id;
            $customerId = $cust_id;
            $qty = $qty;

            $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($customerId);

            if ($customerId != '' and $product_id != '' and $qty != '') {
			
                $return['enabled'] = true;
                $return['canAdd'] = true;
                try {

                    if (!$customer->getId()) {
                        $return['msg'] = __('Invalid customer');
						$return["result"] = $resultCode;
						$return["resultText"] = $resultText;

                        return ["return" => $return, "resultCode" => $resultCode, "resultText" => $resultText];
                        die;
                    }

                    $product = $this->productFactory->load($product_id);
					
                    if (!$product->getId() || !$product->isVisibleInCatalog()) {
					
                        $return['msg'] = __('We can\'t specify a product.');

						$return["result"] = $resultCode;
						$return["resultText"] = $resultText;
                        return ["return" => $return, "resultCode" => $resultCode, "resultText" => $resultText];
                        die;
                    }
                    $wishlist = $this->getWishListInstance($customer);
                    $reqParam = array(
                        'product' => $product_id,
                        'related_product' => '',
                        'qty' => $qty,
                        'form_key' => $this->getRequest()->getParam('form_key', ''));
                    $buyRequest = new \Magento\Framework\DataObject($reqParam);

                    $result = $wishlist->addNewItem($product, $buyRequest);
                    if (is_string($result)) {
						
                        $return['msg'] = __('We can\'t add the item to Wish List right now.');
						$return["result"] = $resultCode;
						$return["resultText"] = $resultText;
                       
                        return ["return" => $return, "resultCode" => $resultCode, "resultText" => $resultText];
                        die;
                    }
                    $wishlist->save();

                    $this->_eventManager->dispatch(
                            'wishlist_add_product', ['wishlist' => $wishlist, 'product' => $product, 'item' => $result]
                    );

                    $return['enabled'] = true;
                    $return['canAdd'] = true;
                    $return['error'] = false;
                    $return['wishlist_item_id'] = $result->getWishlistItemId();
                    $message = __('%1 has been added to your wishlist.', $product->getName());
                    $return['msg'] = $message;
                    $resultCode = 1;
                    $resultText = "success";
                } catch (\Exception $ex) {
                    $this->logger->debug($ex->getMessage());
                    $return['msg'] = __('We can\'t add the item to Wish List right now.');
                }
            }
        }
        return ["return" => $return, "resultCode" => $resultCode, "resultText" => $resultText];
    }

    protected function _addCartItemToWishList() {
        /* $subs = $this->checkPackageSubcription();
          if ($subs['active_package']) {
          // $this->activePackage = $subs['active_package'];
          } */

        $inWishListEnabled = $this->__helper->getStoreConfig("wishlist/general/active");
        $return = array('enabled' => false, 'canAdd' => false, 'error' => true, 'msg' => '');
        $resultCode = 0;
        $resultText = "fail";
        /* if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {
          $inWishListEnabled = false;
          $return['msg'] = __('We can\'t add the item to Wish List right now.');
          } */
        $param = $this->getRequest()->getParams();
        if ($inWishListEnabled && !empty($param['quote_id']) && !empty($param['item_id']) && !empty($param['prod_id'])) {

            //$quoteItem = Mage::getModel('sales/quote_item')->load($param['item_id']);
            $quoteItem = $this->_objectManager->get('Magento\Quote\Model\Quote\Item')->load($param['item_id']);
            
            $productId = $quoteItem->getProductId();
            if ($param['prod_id'] == $productId && $param['quote_id'] == $quoteItem->getQuoteId()) {

                //$quoteItem->delete();

                $quote = $this->_objectManager->get('Magento\Quote\Model\Quote');
                $quote->setStoreId($quoteItem->getStoreId());
                $quote->load($quoteItem->getQuoteId());
                $quote->removeItem($quoteItem->getItemId());
                $quote->collectTotals()->save();
                $product_id = $this->getRequest()->getParam('prod_id');
                $customerId = $this->getRequest()->getParam('cust_id');
                $qty = 1;

                $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($customerId);
                if (!empty($customerId) and ! empty($product_id) and ! empty($qty)) {
                    $return['enabled'] = true;
                    $return['canAdd'] = true;
                    try {
                        if (!$customer->getId()) {
                            $return['msg'] = __('Invalid customer');
                            $finalreturn = [];
                            $finalreturn['response'] = $return['msg'];
                            $finalreturn['returnCode'] = [
                                "result" => $resultCode,
                                "resultText" => $resultText
                            ];
                            $this->getResponse()->setBody(json_encode($finalreturn));

                            return;
                        }

                        $product = $this->_objectManager->get("Magento\Catalog\Model\Product")->load($product_id);

                        if (!$product->getId() || !$product->isVisibleInCatalog()) {

                            $return['msg'] = __('Cannot specify product.');

                            $finalreturn = [];
                            $finalreturn['response'] = $return['msg'];
                            $finalreturn['returnCode'] = [
                                "result" => $resultCode,
                                "resultText" => $resultText
                            ];
                            $this->getResponse()->setBody(json_encode($finalreturn));
                            return;
                        }
                        $wishlist = $this->getWishListInstance($customer);
                        $reqParam = [
                            'product' => $product_id,
                            'related_product' => '',
                            'qty' => $qty];
                        $buyRequest = new \Magento\Framework\DataObject($reqParam);

                        $result = $wishlist->addNewItem($product, $buyRequest);
                        if (is_string($result)) {
                            $return['msg'] = __('Sorry!, Cant add Product to wishlist.');
                            $finalreturn = [];
                            $finalreturn['response'] = $return['msg'];
                            $finalreturn['returnCode'] = [
                                "result" => $resultCode,
                                "resultText" => $resultText
                            ];
                            $this->getResponse()->setBody(json_encode($finalreturn));
                            $this->logger->debug($result);
                        }
                        $wishlist->save();
                        $manager = $this->_objectManager->get('Magento\Framework\Event\ManagerInterface');
                        $manager->dispatch(
                                'wishlist_add_product', [
                            'wishlist' => $wishlist,
                            'product' => $product,
                            'item' => $result
                                ]
                        );
                        $return['enabled'] = true;
                        $return['canAdd'] = true;
                        $return['error'] = false;
                        $return['wishlist_item_id'] = $result->getWishlistItemId();
                        $message = __('%1$s has been added to your wishlist.', $product->getName());
                        $return['msg'] = $message;
                        $resultCode = 1;
                        $resultText = "success";
                    } catch (\Exception $ex) {

                        $return['msg'] = __('Sorry!, Cant add Product to wishlist.');
                    }
                } else {
                    $return['msg'] = __('Invalid datail');
                    $finalreturn = [];
                    $finalreturn['response'] = $return['msg'];
                    $finalreturn['returnCode'] = [
                        "result" => $resultCode,
                        "resultText" => $resultText
                    ];
                    $this->getResponse()->setBody(json_encode($finalreturn));

                    return;
                }
            } else {
                $return['msg'] = __('Invalid item id or quote id or product id.');
            }
        }
        return ["return" => $return, "resultCode" => $resultCode, "resultText" => $resultText];
    }

    protected function _removeWishListItem() {
        $inWishListEnabled = $this->__helper->getStoreConfig("wishlist/general/active");
        $return = array('enabled' => false, 'canRemove' => false, 'error' => true, 'msg' => '');
        $resultCode = 0;
        $resultText = "fail";
        if ($inWishListEnabled) {
			
			$request = $this->getRequest()->getContent();
            $param = json_decode($request, true);
			
            $item_id = $param['wishlist_item_id'];
            $customerId = $param['user_id'];
            //$customer = Mage::getModel('customer/customer')->load($customerId);
            $return['enabled'] = true;
            if (!empty($customerId) and ! empty($item_id)) {
                $return['canRemove'] = true;
                try {
                    $item = $this->_objectManager->get("Magento\Wishlist\Model\Item")->load($item_id);
                    $wishlist = $this->getWishListById($item->getWishlistId());

                    if ($wishlist->getCustomerId() == $customerId) {

                        $item->delete();
                        $wishlist->save();
                        $return['error'] = false;
                        $return['msg'] = __("Requested Item deleted from wishlist");

                        $resultCode = 1;
                        $resultText = "success";
                    } else {
                        $return['msg'] = __("Requested wishlist doesn't exist");
                    }
                } catch (\Exception $ex) {
                    $return['msg'] = __('An error occurred while deleting the item from wishlist.');
                }
            } else {
                $return['msg'] = __('Item or customer is invalid');
            }
        }

        $finalreturn = [];
        $finalreturn['data'] =[];
        $finalreturn['message'] = $return['msg'];
        $finalreturn['status'] = "success";
        $finalreturn['status_code'] = 200;
         array(
            "result" => $resultCode,
            "resultText" => $resultText
        );

        return $finalreturn;
    }

    protected function _addWItemToCart() {
        $inWishListEnabled = $this->__helper->getStoreConfig("wishlist/general/active");
        $return = array('enabled' => false, 'canAddToCart' => false, 'hasRequiredOpt' => false, 'error' => true, 'msg' => '');
        $resultCode = 0;
        $resultText = "fail";
        if ($inWishListEnabled) {
            $params = $this->getRequest()->getParams();
            $itemId = $this->getRequest()->getParam('item_id');
            $return['enabled'] = true;
            /* @var $item Mage_Wishlist_Model_Item */
            $collection = $this->_objectManager->get("Magento\Wishlist\Model\ResourceModel\Item\Collection");
            $collection->addFieldToFilter('wishlist_item_id', $itemId);
            $item = $collection->getFirstItem();

            $wishlist = $this->getWishListById($item->getWishlistId());
            $qty = $this->getRequest()->getParam('qty');
            if (is_array($qty)) {
                if (isset($qty[$itemId])) {
                    $qty = $qty[$itemId];
                } else {
                    $qty = 1;
                }
            }
            $qty = $this->_processLocalizedQty($qty);
            if ($qty) {
                $item->setQty($qty);
            }

            try {
                if (!$item->getId()) {
                    throw new \Exception(__('We can\'t add the item to the cart right now.'));
                }
                $cparams = $params;
                unset($cparams['salt']);
                unset($cparams['ccurrency']);
                unset($cparams['cstore']);
                unset($cparams['cust_id']);

                if (isset($params['quote_id']) and ! empty($params['quote_id'])) {
                    $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->load($params['quote_id']);
                    $this->cart->setQuote($quote);
                }

                $cparams['qty'] = $qty;
                $options = $this->_objectManager->get("Magento\Wishlist\Model\Item\Option")->getCollection()
                        ->addItemFilter(array($itemId));

                $item->setOptions($options->getOptionsByItem($itemId));
                $currentConf = [];
                if (empty($item->getOptionByCode('info_buyRequest')) || empty($options->getOptionsByItem($itemId))) {
                    $currentConf = [];
                } else {
                    $currentConf = $item->getBuyRequest();
                }

                $cparams['product'] = $item->getProductId();

                $buyRequest = $this->_objectManager->get('Magento\Catalog\Helper\Product')->addParamsToBuyRequest(
                        $cparams, array('current_config' => $currentConf)
                );

                $item->mergeBuyRequest($buyRequest);
                if ($item->addToCart($this->cart, true)) {
                    $this->cart->save()->getQuote()->collectTotals();
                    $wishlist->save();
                    if (!isset($params['quote_id']) or empty($params['quote_id'])) {
                        $quoteId = $this->cart->getQuote()->getId();
                        if (!empty($params['cust_id']) && $quoteId) {
                            $customerObj = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($params['cust_id']);
                            $quote = $this->_objectManager->get('Magento\Quote\Model\Quote')->loadActive($quoteId);
                            $newCustomerDataObject = $this->_objectManager->create("Magento\Customer\Api\Data\CustomerInterface");

                            $onbejctHelper = $this->_objectManager->create('Magento\Framework\Api\DataObjectHelper');
                            $onbejctHelper->populateWithArray(
                                    $newCustomerDataObject, $customerObj->getData(), '\Magento\Customer\Api\Data\CustomerInterface'
                            );

                            $quote->assignCustomer($newCustomerDataObject);
                            $quote->save();
                            //die;
                        }
                    }
                }

                $message = '';

                if (!$this->cart->getQuote()->getHasError()) {
                    $message = __(
                            'You added %1 to your shopping cart.', $this->escaper->escapeHtml($item->getProduct()->getName())
                    );
                } else {
                    throw new \Exception(__('We can\'t add the item to the cart right now.'));
                }


                // $this->registry->register('wishlist', $wishlist);
                $return['canAddToCart'] = true;
                $return['error'] = false;
                //$this->__checkoutSession->setQuoteId($this->cart->getQuote());
                $return['quote_id'] = $this->cart->getQuote()->getId();
                $return['quote_count'] = $this->cart->getSummaryQty();
                $return['msg'] = $message;
                $resultCode = 1;
                $resultText = "success";
            } catch (\Magento\Catalog\Model\Product\Exception $e) {
                $return['msg'] = __('This product(s) is out of stock.');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->logger->debug($e);
                $return['msg'] = $e->getMessage();
            } catch (\Exception $e) {
                $this->logger->debug($e);

                $return['msg'] = __('We can\'t add the item to the cart right now.');
            }
        } else {
            $return['msg'] = __('Sorry!, Wishlist is not enabled');
        }
        return ["return" => $return, "resultCode" => $resultCode, "resultText" => $resultText];
    }

    /**
     * Processes localized qty (entered by user at frontend) into internal php format
     *
     * @param string $qty
     * @return float|int|null
     */
    protected function _processLocalizedQty($qty) {

        $_localFilter = new \Zend_Filter_LocalizedToNormalized(
                array('locale' => $this->_storeManager->getStore()->getLocaleCode())
        );

        $qty = $_localFilter->filter((float) $qty);

        if ($qty < 0) {
            $qty = null;
        }

        return $qty;
    }

    protected function _updateWItemOptions() {
        $inWishListEnabled = $this->__helper->getStoreConfig("wishlist/general/active");
        $return = ['enabled' => false, 'canUpdate' => false, 'error' => true, 'msg' => ''];
        $resultCode = 0;
        $resultText = "fail";
        if ($inWishListEnabled) {
            $return['enabled'] = true;

            $productId = $this->getRequest()->getParam('prod_id');
            $product = $this->productFactory->load($productId);
            if (!$product->getId() || !$product->isVisibleInCatalog()) {
                $return['msg'] = __('Cannot specify product.');
            } else {
                try {
                    $id = $this->getRequest()->getParam('wishlist_item_id');
                    /* @var Mage_Wishlist_Model_Item */
                    $item = $this->_objectManager->get("Magento\Wishlist\Model\Item");
                    $item->load($id);
                    $wishlist = $this->getWishListById($item->getWishlistId());
                    if (!$wishlist) {
                        $return['msg'] = __('Cannot specify wishlist.');
                    } else {
                        $buyRequest = new \Magento\Framework\DataObject($this->getRequest()->getParams());

                        $wishlist->updateItem($id, $buyRequest)
                                ->save();

                        $this->_eventManager->dispatch('wishlist_update_item', [
                            'wishlist' => $wishlist, 'product' => $product, 'item' => $wishlist->getItem($id)
                        ]);
                        $return['error'] = false;
                        $return['canUpdate'] = true;
                        $return['msg'] = __('%1 has been updated in your wishlist.', $product->getName());

                        $resultCode = 1;
                        $resultText = "success";
                    }
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $return['msg'] = $e->getMessage();
                } catch (\Exception $e) {
                    $return['msg'] = __('An error occurred while updating wishlist.');
                    $this->logger->debug($e);
                }
            }
        } else {
            $return['msg'] = __('Sorry!, Wishlist is not enabled');
        }
        return ["return" => $return, "resultCode" => $resultCode, "resultText" => $resultText];
    }

    protected function _loadVisitorById($visitorId) {
        $vistiTorC = $this->_objectManager->create("Magento\Customer\Model\Visitor")->getCollection();
        $vistiTorC->addFieldToFilter('visitor_id', $visitorId);
        $vistiTor = $vistiTorC->getFirstItem();
        $this->session->setVisitorData($vistiTor->getData());
        return $vistiTor;
    }

    protected function __getRecentViewItms($yourCustomerId, $visitorID) {

        if (!empty($visitorID)) {

            //Mage::getModel('core/session')->setVisitorId($visitorID);
            $this->_loadVisitorById($visitorID);
        }

        $block = $this->_objectManager->create("Magento\Reports\Block\Product\Viewed");

        $block->setCustomerId($yourCustomerId);
        $productCollection = $block->getItemsCollection();

        if ($this->activePackage == self::Basic_Package) {

            $Option = $this->_productOptionCollection->addFieldToSelect('product_id');
            $Option->getSelect()->group('main_table.product_id');
            $entityIds = new \Zend_Db_Expr($Option->getSelect()->__toString());

            $productCollection->addAttributeToFilter('product_id', array('nin' => $entityIds));
        }
        if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {
            $productCollection->addAttributeToFilter('type_id', array('in' => array('simple')));
        } else if ($this->activePackage == self::Silver_Package) {
            $productCollection->addAttributeToFilter('type_id', array('in' => array('simple', 'configurable', 'virtual')));
        }

        $productCollection->addMinimalPrice()
                ->addFinalPrice();



        $productCollection->load();
        $return = [];
        $ct = 0;

        foreach ($productCollection as $indx => $_item) {
            try {

                $data = $_item->getData();
                $return[$ct]['name'] = $_item->getName();
                $return[$ct]['sku'] = $_item->getSku();
                $return[$ct]['product_id'] = $data['product_id'];
                $return[$ct]['type_id'] = $data['type_id'];
                $return[$ct]['has_options'] = $data['has_options'];

                $return[$ct]['final_price'] = number_format($this->currencyHelper->currency($_item->getFinalPrice(), false, false), 2);

                $return[$ct]['price'] = number_format($this->currencyHelper->currency($_item->getPrice(), false, false), 2);

                $return[$ct]['price_html'] = '';

                if ($_item->getTypeId() == 'grouped') {
                    $minprice = '';
                    $oModel = $this->_objectManager->get("Magento\GroupedProduct\Model\Product\CatalogPrice");
                    $minprice = $oModel->getCatalogPrice($_item);
                    $return[$ct]['final_price'] = number_format($minprice, 2);
                } else if ($_item->getTypeId() == 'bundle') {
                    $tierprice = [];
                    $oModel = $this->_objectManager->get("Magento\Bundle\Model\Product\Price");
                    $tierprice = $oModel->getTotalPrices($_item);
                    if (is_array($tierprice)) {
                        $return[$ct]['price_html'] = __('From %1 - Upto %2', $this->currencyHelper->currency($tierprice[0], true, false), $this->currencyHelper->currency($tierprice[1], true, false)); //number_format($tierprice,2);
                    } else {
                        $return[$ct]['final_price'] = number_format($this->currencyHelper->currency($tierprice, false, false), 2);

                        $_item->setFinalPrice($tierprice);
                    }
                }


                $return[$ct]['final_disc'] = "0";
                if (floatval($_item->getPrice()) > 0 && floatval($_item->getFinalPrice()) < floatval($_item->getPrice()))
                    $return[$ct]['final_disc'] = number_format(100 - (floatval($_item->getFinalPrice()) / floatval($_item->getPrice()) * 100), 2);



                $RatingOb = $this->_objectManager->get('Magento\Review\Model\Rating')->getEntitySummary($data['product_id']);
                $ratings = $RatingOb->getCount() > 0 ? ($RatingOb->getSum() / $RatingOb->getCount()) : false;
                if ($ratings == false) {
                    $ratings = 0;
                }

                $return[$ct]['rating'] = $ratings;
                $return[$ct]['in_stock'] = $_item->isSalable();
                $return[$ct]['created'] = $data['created_at'];

                $imageUrl = '';
                $_product = $_item;
                if ($_product->getImage() && (($_product->getImage()) != 'no_selection')) {
                    $imageUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getThumbnail();
                }

                $return[$ct]['image'] = $imageUrl;
                $ct++;
            } catch (\Exception $e) {
                
            }
        }
        return $return;
    }

    protected function _listProductReviews() {
         $request = $this->getRequest()->getContent();
	     $param = json_decode($request, true);
	    
        $productId = $param['prod_id'];
        $storeId = (isset($param['store_id']) and ! empty($param['store_id'])) ? $param['store_id'] : 6;

        $page_no = (isset($param['page_id']) and ! empty($param['page_id'])) ? $param['page_id'] : 1;

        $limit = (isset($param['limit']) and ! empty($param['limit'])) ? $param['limit'] : 20;

        $return = [];

        $reviewsCollection = $this->_objectManager->get('Magento\Review\Model\Review')->getCollection()
                ->addStoreFilter($storeId)
                ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->addEntityFilter('product', $productId)
                ->setDateOrder();
        $offset = ((int) $limit * ((int) $page_no - 1));
        $reviewsCollection->getSelect()->limit($limit, $offset);
        //$reviewsCollection->setCurPage($page_no);


        $reviewsCollection->addRateVotes();
        // var_dump($reviewsCollection->getSelect()->__toString()); //die; 
        $_items = $reviewsCollection->getItems();
        $return['total_reviews'] = count($_items);

        $RatingOb = $this->_objectManager->get('Magento\Review\Model\Rating')->getEntitySummary($productId);
        $ratings = $RatingOb->getCount() > 0 ? ($RatingOb->getSum() / $RatingOb->getCount()) : false;
        if ($ratings == false) {
            $ratings = 0;
        }
        $return['overall_rating'] = number_format($ratings, 2);

        $return['reviews'] = [];
        $ix = 0;

        foreach ($reviewsCollection as $indx => $_item) {
            $itData = $_item->getData();
            unset($itData['rating_votes']);
            $itData['created_at'] = date('M d, Y', strtotime($itData['created_at']));
            
            // review star rating according to price,qty,value
				$resource = $this->_objectManager->get('Magento\Framework\App\ResourceConnection'); 
				$connection = $resource->getConnection(); 
				$tableName = $resource->getTableName('rating_option_vote');
				$sql = "Select * FROM " . $tableName." where review_id = ".$_item->getReviewId(); 
				$result_query = $connection->fetchAll($sql);
				foreach($result_query as $ratingkey=>$ratingdata){
					if($ratingdata['rating_id'] == 3){
						$ratingdataarray['Price'] = $ratingdata['value'];
					}else if($ratingdata['rating_id'] == 2){
						$ratingdataarray['Value'] = $ratingdata['value'];
					}else if($ratingdata['rating_id'] == 1){
						$ratingdataarray['Quality'] = $ratingdata['value'];
					}
				}
				$itData['rating_star'] = $ratingdataarray;
            //ends here
            $return['reviews'][$ix] = $itData;

            $_votes = $_item->getRatingVotes();
            $return['reviews'][$ix]['has_votes'] = (count($_votes) > 0) ? 1 : 0;
            if (count($_votes)) {
                foreach ($_votes as $inx => $_vote) {
                    $_voteData = $_vote->getData();
                    $array = [];
                    $array['rating_code'] = htmlspecialchars($_voteData['rating_code']);
                    $array['rating_value'] = $_voteData['value'];
                    $array['percent'] = $_voteData['percent'];
                    $return['reviews'][$ix]['votes'][$array['rating_code']] = $array;
                }
                krsort($return['reviews'][$ix]['votes']);
                $return['reviews'][$ix]['votes'] = array_values($return['reviews'][$ix]['votes']);
            }
            $ix++;
        }
        return $return;
    }

    protected function _getReviewRatingCodes() {

        $storeId = 6;
        $ratingCollection = $this->_objectManager->get('Magento\Review\Model\Rating')
                ->getResourceCollection()
                ->addEntityFilter('product')
                ->setPositionOrder()
                ->addRatingPerStoreName($storeId)
                ->setStoreFilter($storeId)
                ->load()
                ->addOptionToItems();
        $return = [];

        $return['rating_codes'] = [];

        foreach ($ratingCollection as $_rating) {
            $array = $_rating->getData();
            unset($array['options']);
            foreach ($_rating->getOptions() as $_option) {
                $opData = $_option->getData();
                unset($opData['rating_id']);
                $array['options'][] = $opData;
            }
            $return['rating_codes'][] = $array;
        }
        $formkey = $this->_objectManager->get("Magento\Framework\View\Element\FormKey");
        $return['form_key'] = $formkey;
        return $return;
    }

    protected function _addProductReview() {
        $return = [];
        $resultCode = 0;
        $resultText = "fail";
        try {
            $subs = $this->checkPackageSubcription();
            if ($subs['active_package']) {
                $this->activePackage = $subs['active_package'];
            }
            if ($this->activePackage == self::Basic_Package || $this->activePackage == self::Basic_Exd_Package) {
                throw new \Exception(__('Unable to post the review.'));
            }
            $data = $this->getRequest()->getParams();
            $rating = $this->getRequest()->getParam('ratings', []);

            $data['detail'] = $this->getRequest()->getParam('review_field');
            $data['title'] = $this->getRequest()->getParam('summary_field');
            $data['nickname'] = $this->getRequest()->getParam('nickname_field');

            $productId = $data['prod_id'];
            $storeId = (isset($data['store_id']) and ! empty($data['store_id'])) ? $data['store_id'] : $this->_storeManager->getStore()->getId();
            $customrId = $this->getRequest()->getParam('cust_id');
            $product = $this->productFactory->load($productId);
            if (empty($customrId)) {
                $customrId = null;
            }
            $review = $this->_objectManager->get('Magento\Review\Model\Review')->setData($data);
            /* @var $review \Magento\Review\Model\Review */

            $validate = $review->validate();
            if ($validate === true) {
                try {
                    $review->setEntityId($review->getEntityIdByCode(\Magento\Review\Model\Review::ENTITY_PRODUCT_CODE))
                            ->setEntityPkValue($product->getId())
                            ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
                            ->setCustomerId($customrId)
                            ->setStoreId($storeId)
                            ->setStores(array($storeId))
                            ->save();

                    foreach ($rating as $ratingId => $optionId) {
                        $this->_objectManager->get('Magento\Review\Model\Rating')
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->setCustomerId($customrId)
                                ->addOptionVote($optionId, $product->getId());
                    }

                    $review->aggregate();
                    $resultCode = 1;
                    $resultText = "success";
                    $return = array('error' => false, 'msg' => __('Your review has been accepted for moderation.'));
                } catch (\Exception $e) {
                    $return = array('error' => true, 'msg' => __('Unable to post the review.'));
                }
            } else {
                if (is_array($validate)) {
                    $errors = [];
                    foreach ($validate as $errorMessage) {
                        $errors[] = $errorMessage;
                    }
                    $return = array('error' => true, 'msg' => implode(", ", $errors));
                } else {
                    $return = array('error' => true, 'msg' => __('Unable to post the review.'));
                }
            }
        } catch (\Exception $ex) {
            $return = array('error' => true, 'msg' => __('Unable to post the review.'));
            $this->logger->debug($ex->getMessage());
        }
        return ["return" => $return, "resultCode" => $resultCode, "resultText" => $resultText];
    }

    protected function _getMydownlodables() {
        $return = [];
        $result = 0;

        $param = $this->getRequest()->getParams();
        $limit = 4;
        $pageno = 1;

        if (!isset($param['page_no']) || empty($param['page_no']) || ($param['page_no'] == 0))
            $pageno = 1;
        else
            $pageno = $param['page_no'];

        try {
            $customerId = $this->getRequest()->getParam('cust_id');
            $purchased = $this->_objectManager->get('Magento\Downloadable\Model\ResourceModel\Link\Purchased\Collection')
                    ->addFieldToFilter('customer_id', $customerId)
                    ->addOrder('created_at', 'desc');
            //$this->setPurchased($purchased);

            $clonePurchaged = clone $purchased;

            $purc_count = count($clonePurchaged);

            $purchased = $purchased->setPageSize($limit)->setCurPage($pageno)->load();

            $purchasedIds = [];
            foreach ($purchased as $_item) {
                $purchasedIds[] = $_item->getId();
            }
            if (empty($purchasedIds)) {
                $purchasedIds = array(null);
            }
            $purchasedItems = $this->_objectManager->get('Magento\Downloadable\Model\ResourceModel\Link\Purchased\Item\Collection')
                    ->addFieldToFilter('purchased_id', array('in' => $purchasedIds))
                    ->addFieldToFilter('status', array(
                        'nin' => array(
                            \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING_PAYMENT,
                            \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW
                        )
                            )
                    )
                    ->setOrder('item_id', 'desc');
            $result = 1;
            if ($purchasedItems->count() > 0) {
                foreach ($purchasedItems as $item) {
                    $item->setPurchased($purchased->getItemById($item->getPurchasedId()));
                }

                $ic = 0;
                $block = $this->_objectManager->get('Magento\Downloadable\Block\Customer\Products\ListProducts');
                
                
                
                foreach ($purchasedItems as $_item) {
                    $product = $this->productFactory->load($_item->getProductId());
                    $imgUrl = '';
                    if ($product->getImage() && (($product->getImage()) != 'no_selection')) {
                        //$imgUrl = Mage::getModel('catalog/product_media_config')->getMediaUrl($product->getImage());
                        if ($product->getSmallImage()):
                            $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getSmallImage();
                        else:
                            $imgUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
                        endif;
                    } 
            
                    //$durl = $block->getUrl('downloadable/download/link', array('id' => $_item->getLinkHash(), '_secure' => true));
                    $return[$ic] = array(
                        'order_id' => $_item->getPurchased()->getOrderIncrementId(),
                        'order_date' => strtotime($_item->getPurchased()->getCreatedAt()),
                        'product_title' => $_item->getPurchased()->getProductName(),
                        'status' => __(ucfirst($_item->getStatus())),
                        'remaining' => $block->getRemainingDownloads($_item),
                        //'download_link' => $durl,
                        'link_title' => ucwords($block->escapeHtml($_item->getLinkTitle())),
                        'link_hash' => $_item->getLinkHash(),
                        'image' =>$imgUrl,
                        'sku' => $_item->getPurchased()->getProductSku()
                        
                    );

                    $ic++;
                }
            }
        } catch (\Exception $ex) {
            $result = 0;
            $this->logger->debug($ex);
        }

        $finalreturn['response']['items'] = $return;

        if ($purc_count <= ($limit * $pageno))
            $finalreturn['response']['more'] = 0;
        else
            $finalreturn['response']['more'] = 1;

        $finalreturn['returnCode'] = array(
            "result" => $result,
            "resultText" => $result == 1 ? "success" : "failure"
        );
        return $finalreturn;
    }

    protected function getCustomerAddresslist($custId) {
        $customerModel = $this->_objectManager->get('Magento\Customer\Model\Customer');
        $customer = $customerModel->load($custId);
        $defBid = $customer->getDefaultBilling();
        $defShip = $customer->getDefaultShipping();
        $addresses = [];
        foreach ($customer->getAddresses() as $address) {
            if ($address->getIsActive() == 1) {
                $addresses[] = array(
                    'addr_id' => $address->getId(),
                    'firstname' => $address->getFirstname(),
                    'lastname' => $address->getLastname(),
                    'city' => $address->getCity(),
                    'region' => $address->getRegion(),
                    'postcode' => $address->getPostcode(),
                    'country_id' => $address->getCountryId(),
                    'telephone' => $address->getTelephone(),
                    'region_id' => $address->getRegionId(),
                    'street' => $address->getStreet(),
                    'as_html' => $this->formatAddrHtml($address)
                );
            }
        }
        $quoteid = $this->getRequest()->getParam('quote_id', false);
        $quote_shipiing  = $quote_billing =  [
                                'quoteaddr_id'=>"",
                                'firstname'=>"",
                                'lastname'=>"",
                                'city' => "",
                                'region' => "",
                                'postcode' => "",
                                'country_id' => "",
                                'telephone' => "",
                                'region_id' => "",
                                'street' => "",
                                'is_active' => "",
                                'as_html' => ""
                        ];
        
        if($quoteid){
            $quote = $this->_objectManager->get("Magento\Quote\Model\Quote")->loadActive($quoteId);
            if($quote->getShippingAddress()){
                    $quoteshipp = $quote->getShippingAddress();
                    $quote_shipiing = [
                        'quoteaddr_id'=>$quoteshipp->getId(),
                        'firstname'=>$quoteshipp->getFirstname(),
                        'lastname'=>$quoteshipp->getLastname(),
                        'city' => $quoteshipp->getCity(),
                        'region' => $quoteshipp->getRegion(),
                        'postcode' => $quoteshipp->getPostcode(),
                        'country_id' => $quoteshipp->getCountryId(),
                        'telephone' => $quoteshipp->getTelephone(),
                        'region_id' => $quoteshipp->getRegionId(),
                        'street' => $quoteshipp->getStreet(),
                        'as_html' => trim($quoteshipp->format('html', "\n"))
                    ];
            } 
            if($quote->getBillingAddress()){
                $quoteBill = $quote->getShippingAddress();
                $quote_billing =  [
                        'quoteaddr_id'=>$quoteBill->getId(),
                        'firstname'=>$quoteBill->getFirstname(),
                        'lastname'=>$quoteBill->getLastname(),
                        'city' => $quoteBill->getCity(),
                        'region' => $quoteBill->getRegion(),
                        'postcode' => $quoteBill->getPostcode(),
                        'country_id' => $quoteBill->getCountryId(),
                        'telephone' => $quoteBill->getTelephone(),
                        'region_id' => $quoteBill->getRegionId(),
                        'street' => $quoteBill->getStreet(),
                        'as_html' => trim($quoteBill->format('html', "\n"))
                    ];
            }
        }
        
        $return['quote_shipiing'] = $quote_shipiing;
	$return['quote_billing'] = $quote_billing;
			
        $return['default_billing'] = $defBid;
        $return['default_shipping'] = $defShip;
        $return['addresses'] = $addresses;
        return $return;
    }

    protected function getCustAddressDetails($custId, $addrId) {
        $customerModel = $this->_objectManager->get('Magento\Customer\Model\Customer');
        $customer = $customerModel->load($custId);
        // $defBid = $customer->getDefaultBilling();
        //$defShip = $customer->getDefaultShipping();
        $addresses = [];
        foreach ($customer->getAddresses() as $address) {
			
            if ($address->getId() != $addrId) {
                continue;
            }
            
            if ($address->getIsActive() == 1) {
                $addresses = array(
                    'addr_id' => $address->getId(),
                    'firstname' => $address->getFirstname(),
                    'lastname' => $address->getLastname(),
                    'city' => $address->getCity(),
                    'email' => $customer->getEmail(),
                    'extramobile' => $customer->getExtramobile(),
                    'region' => $address->getRegion(),
                    'postcode' => $address->getPostcode(),
                    'country_id' => $address->getCountryId(),
                    'telephone' => $address->getTelephone(),
                    'region_id' => $address->getRegionId(),
                    'extra_phone' => $address->getExtraPhone(),
                    'street' => $address->getStreetFull(),
                    'fax' => $address->getFax(),
                    'company' => $address->getCompany() ? $address->getCompany() : '',
                    'as_html' => $this->formatAddrHtml($address)
                );
            }
        }


        if (empty($addresses))
            $addresses = new \stdClass();

        return $addresses;
    }

    public function formatAddrHtml($address) {
        $addr = [];
        $addr[] = strtoupper($address->getName());
        if ($address->getCompany())
            $addr[] = $address->getCompany();

        $addr[] = implode(' ', $address->getStreet()) . ' ,' . $address->getCity() . ' ,' . $address->getRegion() . ' ' . $address->getPostcode();
        if ($address->getData('country_id') && !$address->getData('country')) {
            $address->setData('country', $this->_objectManager->get('Magento\Directory\Model\Country')
                            ->load($address->getData('country_id'))->getIso2Code());
        }

        $addr[] = $address->getData('country');

        if ($address->getTelephone()) {
            $addr[] = ' T: ' . $address->getTelephone() . '' . ($address->getExtraPhone() ? ' ' . $address->getExtraPhone() : '');
        }
        return implode('<br/>', $addr);
    }

    protected function deleteCustAddress($custId, $addrId) {
        $customerModel = $this->_objectManager->get('Magento\Customer\Model\Customer');
        $customer = $customerModel->load($custId);
        $hasDeleted = 0;
        foreach ($customer->getAddresses() as $address) {
            if ($address->getId() != $addrId) {
                continue;
            }
            if ($address->getIsActive() == 1) {

                $address->delete();

                $hasDeleted = 1;
                break;
            }
        }
        if ($hasDeleted == 1) {
            $return = true;
        } else {
            $return = false;
        }
        return $return;
    }

    protected function updateCustAddress($custId, $addrId, $addressData) {
        unset($addressData['salt']);
        unset($addressData['cstore']);
        unset($addressData['ccurrency']);

        unset($addressData['cust_id']);
        unset($addressData['addr_id']);
        if (empty($addressData) or empty($custId)) {
            throw new \Exception('Empty request data');
        }
        $customerModel = $this->_objectManager->get('Magento\Customer\Model\Customer');
        $customer = $customerModel->load($custId);
            
        if ($customer->getId()) {
            if ($addrId) {

                $addressesCollection = $this->_objectManager->get('Magento\Customer\Model\Address')->getResourceCollection();
                // 
                /* for particular address */
                $addressesCollection->addFieldToFilter('entity_id', $addrId);
                $addressesCollection->addFieldToFilter('parent_id', $customer->getId());
                $addrExsits = $addressesCollection->getFirstItem();
                if (!$addrExsits->getId()) {
                    throw new \Exception("Invalid Request");
                }
                // var_dump($addrExsits->getId()); die;
                $addrId = $addrExsits->getId();
            }
            //telephone
            if(isset($addressData['zip']) and !isset($addressData['postcode'])){
                $addressData['postcode'] = $addressData['zip'];
            }
            if(isset($addressData['phone']) and !isset($addressData['telephone'])){
                $addressData['telephone'] = $addressData['phone'];
            }
            
            $address1 = $this->_objectManager->create('Magento\Customer\Model\Address');
            $address1->setData($addressData);
            
            $regionDataFactory= $this->_objectManager->create("Magento\Customer\Api\Data\RegionInterfaceFactory");
            $region = $regionDataFactory->create();
            
            $region->setRegion($address1->getRegion())
                ->setRegionCode($address1->getRegionCode())
                ->setRegionId($address1->getRegionId());
            
            $street = $address1->getStreet();
            
            $address1->unsData();
            unset($address1);
            
            $addressData[\Magento\Customer\Model\Data\Address::REGION] = $region;
            $addressData[\Magento\Customer\Model\Data\Address::STREET] = $street;
            
            $newCustomerDataObject = $this->_objectManager->create("Magento\Customer\Api\Data\AddressInterface");

            $onbejctHelper = $this->_objectManager->create('Magento\Framework\Api\DataObjectHelper');
            $onbejctHelper->populateWithArray(
                    $newCustomerDataObject, $addressData, '\Magento\Customer\Api\Data\AddressInterface'
            );
            
            $address = $this->_objectManager->create('Magento\Customer\Model\Address');
            
            if ($addrId)
                 $newCustomerDataObject->setId($addrId);
            
            $newCustomerDataObject->setCustomerId($customer->getId());
            
            $address->updateData($newCustomerDataObject);
           // var_dump($addressData); die;
            
            
            if ($address->validate()){
                $address->save();
            }
                
            if (!$addrId) {
                $customer->addAddress($address);
                $customer->save();
            }
        } else {
            throw new \Exception("Invalid Request");
        }
        return true;
    }

    protected function getCustomerNotification() {
        $custEmail = $this->getRequest()->getParam('email');
        $result = 0;
        $return = [];

        if ($custEmail) {
            $result = 1;
            $collection = $this->_objectManager->get('Softprodigy\Minimart\Model\Notification\History')->getCollection();
            $collection->addFieldToFilter('customer_email', $custEmail);
            $collection->addFieldToFilter('type_id', array('nin' => 'order'));
            $collection->getSelect()->order('id DESC');
            $collection->getSelect()->limit(10);

            $items = $collection->getItems();

            foreach ($items as $_item) {
                $dt = $_item->getData();
                unset($dt['id']);
                unset($dt['customer_email']);
                if ($dt['item_type'] == 'product') {
                    $product = $this->productFactory->load($dt['item_value']);
                    $dt['item_value'] = $dt['item_value'] . "#" . $product->getTypeId();
                } else if ($dt['item_type'] == 'page') {
                    $page = $this->_objectManager->get('Magento\Cms\Model\page')->load($dt['item_value'], 'identifier');
                    $dt['item_value'] = $this->_filterProvider->getPageFilter()->filter($page->getContent());
                }
                $return[] = $dt;
            }
        }
        return ["return" => $return, "resultCode" => $result, "resultText" => ($result == 1 ? "success" : "failure")];
    }

    protected function getDLinkUrlFormHash() {
        $param = $this->getRequest()->getParams();
        $result = 0;
        $return = '';
        try {

            $id = $id = $this->getRequest()->getParam('hash', 0);
            $customerId = $param['cust_id'];

            $linkPurchasedItem = $this->_objectManager->get('Magento\Downloadable\Model\Link\Purchased\Item')->load($id, 'link_hash');
            if (!$linkPurchasedItem->getId()) {
                throw new \Exception(__("Requested link does not exist."));
            }
            $downloadHelper = $this->_objectManager->get('Magento\Downloadable\Helper\Data');
            if (!$downloadHelper->getIsShareable($linkPurchasedItem)) {
                $linkPurchased = $this->_objectManager->get('Magento\Downloadable\Model\Link\Purchased')->load($linkPurchasedItem->getPurchasedId());
                if ($linkPurchased->getCustomerId() != $customerId) {
                    throw new \Exception(__("Requested link does not exist."));
                }
            }

            $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought() - $linkPurchasedItem->getNumberOfDownloadsUsed();

            $status = $linkPurchasedItem->getStatus();
            if ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_AVAILABLE && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)
            ) {
                $result = 1;
                $model = $this->_objectManager->get('Softprodigy\Minimart\Model\Downloadbles')->load($id, 'link_hash');
                $newKey = '';
                if ($model->getId()) {
                    $newKey = $model->getNewHash();
                    if (empty($newKey)) {
                        $newKey = md5($id);
                        $model->setData(
                                [
                                    'cust_id' => $customerId,
                                    'link_hash' => $id,
                                    'new_hash' => $newKey
                                ]
                        );
                        $model->save();
                    }
                } else {
                    $newKey = md5($id);
                    $model->setData(
                            [
                                'cust_id' => $customerId,
                                'link_hash' => $id,
                                'new_hash' => $newKey
                            ]
                    );
                    $model->save();
                }

                $return = $this->actionBuilder->getUrl('minimart/miniapi/downloadLink', array('id' => $newKey, 'unecp' => base64_encode($customerId)));
            } elseif ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED) {
                throw new \Exception(__('The link has expired.'));
            } elseif ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING || $status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW
            ) {
                throw new \Exception(__('The link is not available.'));
            } else {
                throw new \Exception(__('An error occurred while getting the requested content. Please contact the store owner.'));
            }
        } catch (\Exception $ex) {
            $return = $ex->getMessage();
        }
        return ["return" => $return, "resultCode" => $result, "resultText" => ($result == 1 ? "success" : "failure")];
    }

    protected function downloadLinkFromHash() {
        $newKey = $this->getRequest()->getParam('id', 0);
        $model = $this->_objectManager->get('Softprodigy\Minimart\Model\Downloadbles')->load($newKey, 'new_hash');
        $encoded = $this->getRequest()->getParam('unecp', 0);
        $decoded = '';

        if ($encoded) {
            $decoded = base64_decode($encoded);
        } else {
            $this->messageManager->addNotice(__('You are not authorized to download.'));
            $this->_getCustomerSession()->authenticate();
            $this->_getCustomerSession()->setBeforeAuthUrl($this->actionBuilder->getUrl('downloadable/customer/products/'), array('_secure' => true));
        }
        $downloadHelper = $this->_objectManager->get('Magento\Downloadable\Helper\Data');
        if ($model->getId() && $decoded) {
            $hashKey = $model->getLinkHash();
            $linkPurchasedItem = $this->_objectManager->get('Magento\Downloadable\Model\Link\Purchased\Item')->load($hashKey, 'link_hash');
            if (!$linkPurchasedItem->getId()) {
                $this->messageManager->addNotice(__("Requested link does not exist."));
                return false;
            }

            if (!$downloadHelper->getIsShareable($linkPurchasedItem)) {
                $customerId = $decoded; //$this->_getCustomerSession()->getCustomerId();
                if (!$customerId) {
                    $product = $this->productFactory->load($linkPurchasedItem->getProductId());
                    if ($product->getId()) {
                        $notice = __('Please log in to download your product or purchase <a href="%s">%s</a>.', $product->getProductUrl(), $product->getName());
                    } else {
                        $notice = __('Please log in to download your product.');
                    }
                    $this->messageManager->addNotice($notice);
                    $this->_getCustomerSession()->authenticate();
                    $this->_getCustomerSession()->setBeforeAuthUrl($this->actionBuilder->getUrl('downloadable/customer/products/'), array('_secure' => true)
                    );
                }
                $linkPurchased = $this->_objectManager->get('Magento\Downloadable\Model\Link\Purchased')->load($linkPurchasedItem->getPurchasedId());
                if ($linkPurchased->getCustomerId() != $customerId) {
                    $this->messageManager->addNotice(__("Requested link does not exist."));
                    return false;
                }
            }

            $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought() - $linkPurchasedItem->getNumberOfDownloadsUsed();

            $status = $linkPurchasedItem->getStatus();
            if ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_AVAILABLE && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)
            ) {
                $resource = '';
                $resourceType = '';
                if ($linkPurchasedItem->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_URL) {
                    $resource = $linkPurchasedItem->getLinkUrl();
                    $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_URL;
                } elseif ($linkPurchasedItem->getLinkType() == \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE) {
                    $linkObj =  $this->_objectManager->get('Magento\Downloadable\Model\Link');
                    $resource = $this->_objectManager->get('Magento\Downloadable\Helper\File')->getFilePath(
                           $linkObj->getBasePath(), $linkPurchasedItem->getLinkFile()
                    );
                    $resourceType = \Magento\Downloadable\Helper\Download::LINK_TYPE_FILE;
                }
                try {
                    $this->_processDownload($resource, $resourceType);
                    $linkPurchasedItem->setNumberOfDownloadsUsed($linkPurchasedItem->getNumberOfDownloadsUsed() + 1);

                    if ($linkPurchasedItem->getNumberOfDownloadsBought() != 0 && !($downloadsLeft - 1)) {
                        $linkPurchasedItem->setStatus(\Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED);
                    }
                    $linkPurchasedItem->save();
                    exit(0);
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('An error occurred while getting the requested content. Please contact the store owner.'));
                }
            } elseif ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_EXPIRED) {
                $this->messageManager->addNotice(__('The link has expired.'));
            } elseif ($status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PENDING || $status == \Magento\Downloadable\Model\Link\Purchased\Item::LINK_STATUS_PAYMENT_REVIEW) {
                $this->messageManager->addNotice(__('The link is not available.'));
            } else {
                $this->messageManager->addError(__('An error occurred while getting the requested content. Please contact the store owner.'));
            }
        }
        return true;
    }
    
    protected function _processDownload($resource, $resourceType) {
       // $helper = Mage::helper('downloadable/download');
        $helper= $this->_objectManager->get('Magento\Downloadable\Helper\Download');
        /* @var $helper Mage_Downloadable_Helper_Download */

        $helper->setResource($resource, $resourceType);

        $fileName = $helper->getFilename();
        $contentType = $helper->getContentType();

        $this->getResponse()
                ->setHttpResponseCode(200)
                ->setHeader('Pragma', 'public', true)
                ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
                ->setHeader('Content-type', $contentType, true);

        if ($fileSize = $helper->getFilesize()) {
            $this->getResponse()
                    ->setHeader('Content-Length', $fileSize);
        }

        if ($contentDisposition = $helper->getContentDisposition()) {
            $this->getResponse()
                    ->setHeader('Content-Disposition', $contentDisposition . '; filename=' . $fileName);
        }

        $this->getResponse()
                ->clearBody();
        $this->getResponse()
                ->sendHeaders();

        session_write_close();
        $helper->output();
    }
    
    protected function getStoreAndCurrency() {
        $result = 0;
        try {

            $defaultStoreId = $this->_storeManager
                    ->getWebsite(true)
                    ->getDefaultGroup()
                    ->getDefaultStoreId();

            $mainWebsiteId = $this->_storeManager->getWebsite(true)->getWebsiteId();
            //$allStores = Mage::app($mainWebsiteCode, 'website')->getStores();
            $storeCollection = $this->_objectManager->get('Magento\Store\Model\Store')->getCollection()
                    ->addFieldToFilter('website_id', $mainWebsiteId);
            $storeArr = [];
            foreach ($storeCollection as $ky => $val) {
                $storeArr[] = $val->getData();
            }

            $return['stores'] = $storeArr;
            $return['default_store_id'] = $defaultStoreId;

            $currenies = $this->getCurrencyList();
            $return['currency'] = $currenies;

            $result = 1;
        } catch (\Exception $ex) {
            $return = $ex->getMessage();
        }
        return ["return" => $return, "resultCode" => $result, "resultText" => ($result == 1 ? "success" : "failure")];
    }

    protected function getCurrencyList() {
        try {

            $currencies = [];
            $codes = $this->_storeManager->getStore()->getAvailableCurrencyCodes(true);
            if (is_array($codes) && count($codes) > 1) {
                $rates = $this->_objectManager->get('Magento\Directory\Model\Currency')->getCurrencyRates(
                        $this->_storeManager->getStore()->getBaseCurrency(), $codes
                );

                $currencyLocale = $this->_objectManager->create("Magento\Framework\Locale\Currency");
                $localResolver = $this->_objectManager->create("Magento\Framework\Locale\ResolverInterface");
                foreach ($codes as $code) {
                    if (isset($rates[$code])) {
                        $allCurrencies = (new CurrencyBundle())->get(
                                        $localResolver->getLocale()
                                )['Currencies'];

                        $currencies[] = array(
                            'code' => $code,
                            'label' => $code . " - " . ($allCurrencies[$code][1] ? : $code),
                            'symbol' => $currencyLocale->getCurrency($code)->getSymbol()
                        );
                    }
                }
            }
        } catch (\Exception $ex) {
            $currencies = [];
        }

        return $currencies;
    }

    public function resetMyPassword() {
        try {
            $param = $this->getRequest()->getParams();

            $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")
                    ->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())
                    ->loadByEmail($param['email']);
            if ($customer->getId()) {
                $newResetPasswordLinkToken = $this->_objectManager->get("Magento\User\Helper\Data")->generateResetPasswordLinkToken();
                $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                $customer->sendPasswordResetConfirmationEmail();
            }
            $jsonArray['response'] = __("Mail has been sent to given email-id.");
            $jsonArray['returnCode'] = ['result' => 1, 'resultText' => 'success'];
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = ['result' => 0, 'resultText' => 'fail'];
        }
        return $jsonArray;
    }

    public function iForgotPassword() {
        try {
            $request = $this->getRequest()->getContent();
            $params = json_decode($request, true);            
            $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")
                    ->setWebsiteId($this->_storeManager->getStore()->getWebsiteId())
                    ->loadByEmail($params['email']);

            if ($customer->getId()) {
                try {
                    /* $newPassword = $customer->generatePassword();
                      $customer->changePassword($newPassword, false);
                      $customer->sendPasswordReminderEmail(); */

                    $newResetPasswordLinkToken = $this->_objectManager->get("Magento\User\Helper\Data")->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    //$customer->sendPasswordResetConfirmationEmail();

                    $jsonArray['response'] = __("If an account matches the email address, you should receive an email with instruction to reset password.");
                    $jsonArray['returnCode'] = array('result' => 1, 'resultText' => 'success');
                } catch (\Exception $e) {
                    $jsonArray['response'] = $e->getMessage();
                    $jsonArray['returnCode'] = ['result' => 0, 'resultText' => 'fail'];
                }
            } else {
                $jsonArray['response'] = __("Email Address Not Found");
                $jsonArray['returnCode'] = ['result' => 1, 'resultText' => 'fail'];
            }
        } catch (\Exception $e) {
            $jsonArray['response'] = $e->getMessage();
            $jsonArray['returnCode'] = ['result' => 0, 'resultText' => 'fail'];
        }
        return $jsonArray;
    }

    protected function getAddressDataFromQuote($quote_address) {
        $columns_to_transfer = array(
            'firstname',
            'middlename',
            'lastname',
            'company',
            'street',
            'city',
            'region',
            'region_id',
            'postcode',
            'country_id',
            'telephone',
            'fax',
        );
        $array_to_return = [];
        foreach ($columns_to_transfer as $address_key) {
            $array_to_return[$address_key] = $quote_address->getData($address_key);
        }
        return $array_to_return;
    }

    protected function saveCustomerAddrs($quote, $customer) {
        if ($customer->getId()) {
            $quote_billing = $quote->getBillingAddress();
            $quote_shipping = $quote->getShippingAddress();

            if (empty($quote_billing) === false && $quote_billing->getCustomerAddressId() == null) {
                $customAddress = $this->_objectManager->get('Magento\Customer\Model\Address');
                $customAddress->setData($this->getAddressDataFromQuote($quote_billing));
                $customAddress->setCustomerId($customer->getId())
                        ->setIsDefaultBilling('1')
                        ->setSaveInAddressBook('1');
                try {
                    $customAddress->save();
                } catch (\Exception $ex) {
                    $this->logger->debug($ex->getMessage());
                }
            }
            if (empty($quote_shipping) === false && $quote_billing->getCustomerAddressId() == null) {
                $customAddressSh = $this->_objectManager->get('Magento\Customer\Model\Address');
                $customAddressSh->setData($this->getAddressDataFromQuote($quote_shipping));
                $customAddressSh->setCustomerId($customer->getId())
                        ->setIsDefaultShipping('1')
                        ->setSaveInAddressBook('1');

                try {
                    $customAddressSh->save();
                } catch (\Exception $ex) {
                    $this->logger->debug($ex->getMessage());
                }
            }
        }
    }

    //-----------reset Password --------------
    public function getHomeSections() {
        $storeId = $this->_storeManager->getStore()->getId();

        $collection = $this->_objectManager->get('Softprodigy\Minimart\Model\Homedesign')->getCollection();
        $collection->addFieldToFilter('status', 'active');
        $collection->addFieldToFilter('store_id', $storeId);
        $cats = [];
        $ix = 0;
        foreach ($collection->getItems() as $_item) {
            $category = $this->_objectManager->get('Magento\Catalog\Model\Category')->load($_item->getMainCat());
            $cats[$ix] = array(
                'primary_cat' => $_item->getMainCat(),
                'layout' => $_item->getLayoutCol(),
            );
            $title = $_item->getTitle();
            if (empty($title)) {
                $title = $category->getName();
            }
            $cats[$ix]['title'] = $title;

            $blockCats = unserialize($_item->getCatIds());
            // $i = 1;
            $returnBlocks = [];
            if ($blockCats) {
                $catColls = $this->_objectManager->get('Magento\Catalog\Model\Category')->getCollection();
                $catColls->addAttributeToSelect('*');
                $catColls->addAttributeToFilter('entity_id', array('in' => array_values($blockCats)));

                foreach ($blockCats as $_cat) {
                    $bncat = $catColls->getItemById($_cat);
                    $image1 = ($bncat->getThumbnail() ? $bncat->getThumbnail() : ($bncat->getImage() ? $bncat->getImage() : ''));
                    if (!empty($image1)) {
                        $image1 = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/category/' . $image1;
                    }
                    $returnBlocks[] = array('name' => $bncat->getName(), 'img' => $image1, 'id' => $bncat->getId());
                }
            }
            $cats[$ix]['blocks'] = $returnBlocks;
            $cats[$ix]['childs'] = [];
            $catresult[] = $this->getCategorytree($category->getId(), null, [], array(array('col' => 'image', 'type' => 'image')));
            $cats[$ix]['childs'] = $catresult;
            $ix++;
        }
        return $cats;
    }

    protected function isProductNew($product) {
        $newsFromDate = $product->getNewsFromDate();
        $newsToDate = $product->getNewsToDate();
        $localResolver = $this->_objectManager->create("Magento\Framework\Stdlib\DateTime\TimezoneInterface");
        if (!$newsFromDate && !$newsToDate) {
            return false;
        }
        return $localResolver->isScopeDateInInterval($product->getStore(), $newsFromDate, $newsToDate);
    }

    public function getMinimalPrice($product) {
        $minimal_price = '';
        try {

            $websitePRices = [];

            $currentWebsiteId = $this->_storeManager->getStore()->getWebsiteId();

            $priceCurrency = $this->_objectManager->create('Magento\Framework\Pricing\PriceCurrencyInterface');
            $tier_price = $product->getPriceInfo()->getPrice('tier_price')->getTierPriceList();
            if (count($tier_price) > 0) {
                foreach ($tier_price as $pirces) {
                    if (isset($pirces['website_price']) and ! empty($pirces['website_price'])) {
                        $websitePRices[(int) $pirces['website_id']][] = (float) $pirces['website_price'];
                    } else {
                        $websitePRices[(int) $pirces['website_id']][] = (float) $pirces['price']->getValue();
                    }
                }
                if (isset($websitePRices[(int) $currentWebsiteId])) {
                    return __('As low as') . " " . strip_tags($priceCurrency->convertAndFormat(min($websitePRices[(int) $currentWebsiteId])));
                } else {
                    return __('As low as') . " " . strip_tags($priceCurrency->convertAndFormat(min($websitePRices[0])));
                }
            }

            return $product->getPriceModel()->getTierPrices();
        } catch (\Exception $e) {
            $minimal_price = '';
        }
        return $minimal_price;
    }

    protected function exportCustomerAddress($customerAddressData) {

        $customerAddressDataWithRegion = [];
        $customerAddressDataWithRegion['region'] = [];
        $customerAddressDataWithRegion['region']['region'] = isset($customerAddressData['region']) ? $customerAddressData['region'] : '';

        if (isset($customerAddressData['region']) and is_numeric($customerAddressData['region'])) {
            $customerAddressData['region_id'] = $customerAddressData['region'];
        }

        if (isset($customerAddressData['region_code'])) {
            $customerAddressDataWithRegion['region']['region_code'] = $customerAddressData['region_code'];
        }
        if (isset($customerAddressData['region_id'])) {
            $customerAddressDataWithRegion['region']['region_id'] = $customerAddressData['region_id'];
        }
        $customerAddressData = array_merge($customerAddressData, $customerAddressDataWithRegion);

        if (isset($customerAddressData['street']) and ! is_array($customerAddressData['street'])) {
            $customerAddressData['street'] = array($customerAddressData['street']);
        }
        // var_dump($customerAddressData); die;
        $addressDataObject = $this->_objectManager->create("Magento\Customer\Api\Data\AddressInterfaceFactory")->create();
        $this->_objectManager->create("Magento\Framework\Api\DataObjectHelper")->populateWithArray(
                $addressDataObject, $customerAddressData, '\Magento\Customer\Api\Data\AddressInterface'
        );
        return $addressDataObject;
    }

    protected function _updateAddrPref() {
        $defShipping = [];
        $defBilling = [];
        $primShipId = $this->getRequest()->getParam('ship_id'); // shipping addr id
        $primBillId = $this->getRequest()->getParam('bill_id'); // billing addr id
        $quoteId = $this->getRequest()->getParam('quote_id');
        $custId = $this->getRequest()->getParam('cust_id');
        $customer = $this->_objectManager->get("Magento\Customer\Model\Customer")->load($custId);
        $return = false;
        if ($customer->getId()) {
            $quote = $this->_objectManager->get("Magento\Quote\Model\Quote")->loadActive($quoteId);
            if (!$quote->getId()) {
                throw new \Exception("Cart is not active please try again with new product.");
            }

            $newCustomerDataObject = $this->_objectManager->create("Magento\Customer\Api\Data\CustomerInterface");

            $onbejctHelper = $this->_objectManager->create('Magento\Framework\Api\DataObjectHelper');
            $onbejctHelper->populateWithArray(
                    $newCustomerDataObject, $customer->getData(), '\Magento\Customer\Api\Data\CustomerInterface'
            );


            $quote->assignCustomer($newCustomerDataObject)->setCustomerId($customer->getId())->save();

            $addrCollection = $this->_objectManager->get("Magento\Customer\Model\Address")->getCollection();
            $addrCollection->addAttributeToSelect('*');
            if (!empty($primShipId) and ! empty($primShipId)) {
                $addrCollection->addAttributeToFilter('entity_id', array('in' => array($primShipId, $primBillId)));
            } else if (empty($primShipId) and ! empty($primBillId)) {
                $addrCollection->addAttributeToFilter('entity_id', array('in' => array($primBillId)));
            } else if (!empty($primShipId) and empty($primBillId)) {
                $addrCollection->addAttributeToFilter('entity_id', array('in' => array($primShipId)));
            }
            $addrCollection->addAttributeToFilter('parent_id', $custId);
            $size = $addrCollection->getSize();

            if ($size >= 1 && $size <= 2) {
                foreach ($addrCollection as $_item) {

                    if (!empty($primBillId) and $primBillId == $_item->getId()):
                        $customer->setDefaultBilling($_item->getId());
                        $defBilling = $_item->getData();
                    endif;
                    if (!empty($primShipId) and $primShipId == $_item->getId()):
                        $defShipping = $_item->getData();
                        $customer->setDefaultShipping($_item->getId());
                    endif;
                }
                $customer->save();
            }

            if ($quoteId and ! empty($quoteId) and (int) $quoteId > 0) {
                $arrAddresses = [];
                if (!empty($primBillId)) {
                    $arrAddresses[] = array_merge(array(
                        "mode" => "billing"), $defBilling);
                }
                if (!empty($primShipId)) {
                    $arrAddresses[] = array_merge(array(
                        "mode" => "shipping"), $defShipping);
                }
                try {
                    $address = $this->_objectManager->create('Magento\Quote\Model\Quote\Address');
                    $customerAddressData = $this->_prepareCustomerAddressData($arrAddresses);
                    if (is_null($customerAddressData)) {
                        throw new \Exception(__('Customer Address data is empty'));
                    }
                    foreach ($customerAddressData as $addressItem) {
                        $quoteAddress = $this->_objectManager->create('Magento\Quote\Model\Quote\Address');
                        $addressMode = $addressItem['mode'];
                        unset($addressItem['mode']);

                        if (!empty($addressItem['entity_id'])) {
                            $customerAddress = $this->_getCustomerAddress($addressItem['entity_id']);

                            if ($customerAddress->getCustomerId() != $quote->getCustomerId()) {
                                throw new \Exception('address_not_belong_customer');
                            }
                            $customerAddress->explodeStreetAddress();
                            $cShippingAddr = $this->exportCustomerAddress($customerAddress->getData());
                            $address->importCustomerAddressData($cShippingAddr);
                        } else {
                            $address->setData($addressItem);
                        }

                        //$address->implodeStreetAddress();

                        if (($validateRes = $address->validate()) !== true) {
                            throw new \Exception('customer address invalid: ' . implode(PHP_EOL, $validateRes));
                        }

                        switch ($addressMode) {
                            case "billing":
                                $address->setEmail($quote->getCustomer()->getEmail());

                                if (!$quote->isVirtual()) {
                                    $usingCase = isset($addressItem['use_for_shipping']) ? (int) $addressItem['use_for_shipping'] : 0;
                                    switch ($usingCase) {
                                        case 0:
                                            $shippingAddress = $quote->getShippingAddress();
                                            $shippingAddress->setSameAsBilling(0);
                                            break;
                                        case 1:
                                            $billingAddress = clone $address;
                                            $billingAddress->unsAddressId()->unsAddressType();

                                            $shippingAddress = $quote->getShippingAddress();
                                            $shippingMethod = $shippingAddress->getShippingMethod();
                                            $shippingAddress->addData($billingAddress->getData())
                                                    ->setSameAsBilling(1)
                                                    ->setShippingMethod($shippingMethod)
                                                    ->setCollectShippingRates(true);
                                            break;
                                    }
                                }
                                $quote->setBillingAddress($address);
                                break;

                            case "shipping":
                                $address->setCollectShippingRates(true)
                                        ->setSameAsBilling(0);
                                $quote->setShippingAddress($address);
                                break;
                        }
                    }
                    try {
                        $quote->collectTotals()
                                ->save();
                    } catch (\Exception $e) {
                        throw new \Exception('address is not set');
                        $this->logger->debug($e->__toString());
                    }
                } catch (\Exception $e) {
                    $result = $e->getMessage();
                    $this->logger->debug($e->__toString());
                    throw new \Exception(str_replace("_", " ", $result));
                }
                $return = true;
            }
        }
        return $return;
    }

    /**
     * Get customer address by identifier
     *
     * @param   int $addressId
     * @return  Mage_Customer_Model_Address
     */
    protected function _getCustomerAddress($addressId) {
        $address = $this->_objectManager->get("Magento\Customer\Model\Address")->load((int) $addressId);
        if (is_null($address->getId())) {
            throw new \Exception('invalid address id');
        }

        $address->explodeStreetAddress();
        if ($address->getRegionId()) {
            $address->setRegion($address->getRegionId());
        }
        return $address;
    }

    private function _prepareCustomerAddressData($data) {
        if (!is_array($data) || !is_array($data[0])) {
            return null;
        }
        $attributesMap = [];
        $attributesMap['quote_address'] = ['address_id' => 'entity_id'];
        $dataAddresses = array();
        foreach ($data as $addressItem) {
            foreach ($attributesMap['quote_address'] as $attributeAlias => $attributeCode) {
                if (isset($addressItem[$attributeAlias])) {
                    $addressItem[$attributeCode] = $addressItem[$attributeAlias];
                    unset($addressItem[$attributeAlias]);
                }
            }
            $dataAddresses[] = $addressItem;
        }
        return $dataAddresses;
    }
    
    //login token functions
    public function getToken($email,$password){
			$baseurl =    $this->_storeManager->getStore()->getBaseUrl();
			$url = $baseurl.'rest/V1/integration/customer/token';
			$Data = array("username"=>$email,"password"=>$password);
            $jsonencode  = json_encode($Data);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS,$jsonencode);
			curl_setopt($ch, CURLOPT_POST, 1);
			$headers = array();
			$headers[] = "Content-Type: application/json";
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			$token = curl_exec($ch);
			if (curl_errno($ch)) {
				echo 'Error:' . curl_error($ch);
			}
			curl_close ($ch);
			
			return $token;
	}
	
	public function getCustomerSocialToken($email){
		$custModel = $this->_objectManager->get('Magento\Customer\Model\Customer');
		$cuCollection = $custModel->getCollection();
		$cuCollection->addAttributeToFilter('email', $email);
		$cuCollection->addAttributeToFilter('website_id', $this->_storeManager->getStore()->getWebsiteId());
		$cModel = $cuCollection->getFirstItem();
		$cid =  $cModel->getId();
		$TokenFactory = $this->_objectManager->get('\Magento\Integration\Model\Oauth\TokenFactory');
		$customerToken = $TokenFactory->create();
        $tokenKey = $customerToken->createCustomerToken($cid)->getToken();
		return $tokenKey;
	}
	
	public function getcustomerfromToken(){
		
		$custModel = $this->_objectManager->get("Magento\Customer\Model\Customer");
		$baseurl =    $this->_storeManager->getStore()->getBaseUrl();
		$httpRequestObject = new \Zend_Controller_Request_Http();
        $string = (string)$httpRequestObject->getHeader('Authorization');
        // curl to get data from token of customer
        $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $baseurl."rest/V1/customers/me");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		$headers = array();
		$headers[] = "Authorization:".$string;
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$result = curl_exec($ch);
		$resess = json_decode($result);
		if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close ($ch);
        $cust_id ='';
        if(isset($resess->id)){
			$cust_id =   $resess->id;  // logout customer if device id is not created
				return $cust_id;
			
			
		}else{
			return false;
		}
	}
	
	//~ public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException{
        //~ return null;
    //~ }

    //~ public function validateForCsrf(RequestInterface $request): ?bool{
        //~ return true;
    //~ }
	

}
