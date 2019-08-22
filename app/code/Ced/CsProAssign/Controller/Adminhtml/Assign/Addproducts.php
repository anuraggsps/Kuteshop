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
  * @category  Ced
  * @package   Ced_CsProAssign
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license   http://cedcommerce.com/license-agreement.txt
  */

namespace Ced\CsProAssign\Controller\Adminhtml\Assign;
 
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
 
class Addproducts extends \Magento\Backend\App\Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;
    protected $modelFactory;
    protected $_scopeConfig;
    protected $_storeManager;
    protected $_registry=null;
 
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
        $this->_registry=$registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
    }
 
    /**
     * Index action
     *
     * @return void
     */
    public function execute()
    {   
    	
        $enable=$this->_scopeConfig->getValue(
            'ced_csmarketplace/general/csproassignactivation',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
       
        if($enable) {
            $vendor_id = $this->getRequest()->getParam('vendor_id');
            $product_ids = $this->getRequest()->getParam('product_ids');
            try {
                $vproductsModel=$this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts');
                $product_ids=explode(',', $product_ids);

                if(count($product_ids)) {
                    /**
               * Product limit validation
               **/
                    $vendorGroup=$this->_objectManager->create('Ced\CsMarketplace\Model\Vendor')->load($vendor_id)->getGroup();
                    $curProductCount=count($vproductsModel->getVendorProductIds($vendor_id));
                    $afterAdd=$curProductCount+count($product_ids);
                    $vendorLimit=$this->_scopeConfig->getValue($vendorGroup.'/ced_vproducts/general/limit');
                    if(!$vendorLimit) {
                        $vendorLimit=$this->_scopeConfig->getValue('ced_vproducts/general/limit');
                    }
                    $availLimit=$vendorLimit-$curProductCount;
                    $result='';
                    if($afterAdd > $vendorLimit) {
                        if($availLimit) {
                            $result=__('You can assign only') .$availLimit. __('products to vendor'); 
                        }
                        else {
                            $result=__('Vendors Product limit has Exceeded'); 
                        }
                        $this->messageManager->addError($result);
                        $this->getResponse()->setBody($result);
                        return;
                    }
                    foreach ($product_ids as $product_id) {
                        $allreadyvendorsproduct=$vproductsModel->getVendorProductIds($vendor_id);
                      
                        if(count($allreadyvendorsproduct)!=0) {
                        	if(in_array(trim($product_id), $allreadyvendorsproduct)) {
                                continue;
                            }
                        }   
                       
                    $product=$this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
                   
             // SIMPLE PRODUTC ASSIGNMENT ----------------------------------------------------------------

                        $websiteIds=array();
                        $websiteIds='';
                        if($this->_registry->registry('ced_csmarketplace_current_website')!='') {
                            $websiteIds=$this->_registry->registry('ced_csmarketplace_current_website');
                        }
                        else {
                            $websiteIds=implode(",", $product->getWebsiteIds());
                        }

                        $productId=$product->getId(); 
                        if($product->getSpecialPrice()) {
                            $specialPrice=$product->getSpecialPrice();
                        }else{
                            $specialPrice='0';
                        }
                        $is_in_stock= $this->_objectManager->get('Magento\CatalogInventory\Model\Stock\Item')->getIsInStock();
                        $quantity=$product->getQty();           
                        $type_id=$product->getTypeId();
                        $vproductModel2 = $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')->addData($product->getData());
                        $vproductModel2->setQty($quantity)
                            ->setIsInStock($is_in_stock)
                            ->setPrice($product->getPrice())
                            ->setSpecialPrice($specialPrice)
                            ->setCheckStatus('1')
                            ->setProductId($product->getId())
                            ->setVendorId($vendor_id)
                            ->setType($type_id)
                            ->setWebsiteId($websiteIds)
                            ->setStatus('1')
                            ->save();  
          //SIMPLE PRODUCT ASSIGNMENT END-------------------------------------------------------------

           //CONFIGURABLE PRODUCT ASSIGNMENT ---------------------------------------------------
            if(($product->getTypeId()=='configurable'))
                       {
                        
                        $config = $product->getTypeInstance(true);
                        $childproduct_config = $config->getUsedProducts($product);
                       
                        foreach ($childproduct_config as $value) {
                        $config_product=$this->_objectManager->create('Magento\Catalog\Model\Product')->load(
                        $value->getId());
                        $websiteIds=array();
                        $websiteIds='';
                        if($this->_registry->registry('ced_csmarketplace_current_website')!='') {
                            $websiteIds=$this->_registry->registry('ced_csmarketplace_current_website');
                        }
                        else {
                            $websiteIds=implode(",", $config_product->getWebsiteIds());
                        }

                        $productId=$config_product->getId(); 
                        if($config_product->getSpecialPrice()) {
                            $specialPrice=$config_product->getSpecialPrice();
                        }else{
                            $specialPrice='0';
                        }
                        $is_in_stock= $this->_objectManager->get('Magento\CatalogInventory\Model\Stock\Item')->getIsInStock();
                        $quantity=$config_product->getQty();           
                        $type_id=$config_product->getTypeId();
                        $vproductModel2 = $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')->addData($config_product->getData());
                             $vproductModel2->setQty($quantity)
                            ->setIsInStock($is_in_stock)
                            ->setPrice($config_product->getPrice())
                            ->setSpecialPrice($specialPrice)
                            ->setCheckStatus('1')
                            ->setProductId($config_product->getId())
                            ->setVendorId($vendor_id)
                            ->setType($type_id)
                            ->setWebsiteId($websiteIds)
                            ->setStatus('1')
                            ->save();
                            }
                          
                       }
       // CONFIGURABLE ASSIGNMENT DONE ------------------------------------------

       // GROUPED ASSIGNMENT DONE -----------------------------------------------
               if(($product->getTypeId()=='grouped'))
                       {
                        
                        $config = $product->getTypeInstance(true);
                        $childproduct_grouped = $config->getUsedProducts($product);
                        foreach ($childproduct_grouped as $value) {
                        $group_product=$this->_objectManager->create('Magento\Catalog\Model\Product')->load(
                        $value->getId());
                        $websiteIds=array();
                        $websiteIds='';
                        if($this->_registry->registry('ced_csmarketplace_current_website')!='') {
                            $websiteIds=$this->_registry->registry('ced_csmarketplace_current_website');
                        }
                        else {
                            $websiteIds=implode(",", $group_product->getWebsiteIds());
                        }

                        $productId=$group_product->getId(); 
                        if($group_product->getSpecialPrice()) {
                            $specialPrice=$group_product->getSpecialPrice();
                        }else{
                            $specialPrice='0';
                        }
                        $is_in_stock= $this->_objectManager->get('Magento\CatalogInventory\Model\Stock\Item')->getIsInStock();
                        $quantity=$group_product->getQty();           
                        $type_id=$group_product->getTypeId();
                        $vproductModel2 = $this->_objectManager->create('Ced\CsMarketplace\Model\Vproducts')->addData($group_product->getData());
                             $vproductModel2->setQty($quantity)
                            ->setIsInStock($is_in_stock)
                            ->setPrice($group_product->getPrice())
                            ->setSpecialPrice($specialPrice)
                            ->setCheckStatus('1')
                            ->setProductId($group_product->getId())
                            ->setVendorId($vendor_id)
                            ->setType($type_id)
                            ->setWebsiteId($websiteIds)
                            ->setStatus('1')
                            ->save();
                            }
                          
                       }
       // GROUPED ASSIGNMENT END--------------------------------                                                  
                        $result='success';             
                    } 
                    $this->messageManager->addSuccess(__('Product(s) Assigned Successfully.'));
                    $this->getResponse()->setBody($result);
                }else{die("fv");
                    $result='noproduct';
                    $this->messageManager->addError(__('Please select product to assign.'));
                    $this->getResponse()->setBody($result);
                }
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addError(__($result));
                $this->getResponse()->setBody($result);
            } catch (InputException $e) {
                 $this->messageManager->addError(__($result));
                $this->getResponse()->setBody($result);
            }
        }else{
            $this->messageManager->addError(__('You can not assign products.'));
            $this->getResponse()->setBody($result);
        }
        
    }
}