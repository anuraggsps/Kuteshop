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
 * @package     Ced_CsMembership
 * @author      CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright   Copyright CedCommerce (http://cedcommerce.com/)
 * @license      http://cedcommerce.com/license-agreement.txt
 */

namespace Ced\CsMembership\Observer; 
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Message\ManagerInterface;
Class CreateVirtual implements ObserverInterface
{
	
	protected $_objectManager;
	protected $_quoteFactory;
	protected $_advanceFactory;
	protected $_object;
    protected $_coreRegistry = null;
    protected $frontController;
    protected $request;
    private $messageManager;
	
	public function __construct(		
			\Magento\Framework\DataObject $object,
			\Magento\Framework\ObjectManagerInterface $objectManager,
			\Magento\Quote\Model\QuoteFactory $quoteFactory,
            \Magento\Framework\Registry $registry,
            \Magento\Framework\App\FrontControllerInterface $frontController,
            \Magento\Framework\App\Request\Http $request,
            ManagerInterface $messageManager
           
	)
    {
    	$this->_object = $object;
        $this->_objectManager = $objectManager;
        $this->_quoteFactory = $quoteFactory;
        $this->_coreRegistry = $registry;
        $this->frontController = $frontController;
        $this->request = $request;
        $this->messageManager = $messageManager;
    }
	
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

     	$postdata=$observer->getEvent()->getPostdata();
        $form_data = $this->request->getPost();
        $mode = $observer->getEvent()->getMode();
        switch ($mode) {
            case 'edit':
                $editid = $observer->getEvent()->getEditid();
                $product_id = $this->_objectManager->create('Ced\CsMembership\Model\Membership')->load($editid)->getProductId();
                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id);
                $product->setStockData(array(
                            'manage_stock'=>0,
                            'is_in_stock' => 1,
                            'qty'=>$postdata['qty'],
                            'min_sale_qty'=>1,
                            'max_sale_qty'=>1))
                        ->setName($postdata['name']) 
                        ->setShortDescription('Description')
                        ->setDescription('Description')  
                        ->setPrice($postdata['price'])
                        ->setTaxClassId(2)    // Taxable Goods by default
                        ->setWeight(10);
                        if($postdata['special_price'] > 0)
                            $product->setSpecialPrice($postdata['special_price']);
                        if($_FILES['image']['name']) 
                        {
                            $product->addImageToMediaGallery($postdata['image'], array('image', 'small_image', 'thumbnail'), false, false);
                        }
                        if(isset($form_data['image']['delete']))
                        {
                            $productRepository = $this->_objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
                            $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                            foreach ($existingMediaGalleryEntries as $key => $entry) {
                                unset($existingMediaGalleryEntries[$key]);
                            }
                            $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                            $productRepository->save($product);
                        }
                        
                           
                        try{
                            $product->save();
                            $result = $observer->getEvent()->getResult();
                            $result->setResult($product->getId());
                        }catch(\Exception $e){
                            $this->messageManager->addError(__($e->getMessage()));
                        }
                break;
            case 'new':
                try{
                    $result = $observer->getEvent()->getResult();
                    $sku = 'membership'.rand();
                    $product = $this->_objectManager->create('Magento\Catalog\Model\Product');
                    $product
                        ->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL)
                        ->setAttributeSetId($this->_objectManager->create('Magento\Catalog\Model\Product')->getDefaultAttributeSetId())
                        ->setSku($sku) 
                        ->setWebsiteIds(array(1)) 
                        ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
                        ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
                        ->setStockData(array(
                                    'manage_stock' => 0,
                                    'is_in_stock' => 1,
                                    'qty' => $postdata['qty'],
                                    'min_sale_qty' => 1,
                                    'max_sale_qty' => 1
                                        ))
                        ->setName($postdata['name']) 
                        ->setShortDescription('Description')
                        ->setDescription('Description')  
                        ->setPrice($postdata['price'])
                        ->setTaxClassId(2)    // Taxable Goods by default
                        ->setWeight(10);
                    if($postdata['special_price'] > 0)
                         $product->setSpecialPrice($postdata['special_price']);            
                    if($_FILES['image']['name']) 
                    {
                        $product->addImageToMediaGallery($postdata['image'], array('image', 'small_image', 'thumbnail'), false, false);
                    }
                    if(isset($form_data['image']['delete']))
                    {
                        $productRepository = $this->_objectManager->create('Magento\Catalog\Api\ProductRepositoryInterface');
                        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
                        foreach ($existingMediaGalleryEntries as $key => $entry) {
                            unset($existingMediaGalleryEntries[$key]);
                        }
                        $product->setMediaGalleryEntries($existingMediaGalleryEntries);
                        $productRepository->save($product);
                    }
                    try{
                        $product->save();
                        $product_id = $this->_objectManager->create('Magento\Catalog\Model\Product')->getIdBySku($sku);
                        $result->setResult($product_id);
                    }catch(\Exception $e){
                        $this->messageManager->addError(__($e->getMessage()));
                    }
                }
                catch(\Exception $e){
                        $this->messageManager->addError(__($e->getMessage()));
                }
                break;
        }
        return;
        
           
    }	
    

}
