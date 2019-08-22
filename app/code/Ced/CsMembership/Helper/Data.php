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
  * @package   Ced_CsMembership
  * @author    CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license      http://cedcommerce.com/license-agreement.txt
  */
namespace Ced\CsMembership\Helper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\Message\ManagerInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{

    protected $_transaction;
    private $messageManager;
    protected $_transportBuilder;
 
    public function __construct(\Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Session $customerSession,
        \Magento\Framework\DB\Transaction $transaction,
        ManagerInterface $messageManager,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
    
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_transaction = $transaction;
        $this->messageManager = $messageManager;
        $this->_transportBuilder = $transportBuilder;
        parent::__construct($context);
    }
    
    
   
    
    public function getCategoriesCost($category_ids)
    {
        $categories=array_unique(explode(',',$category_ids));
        $perCategoryPrice = $this->scopeConfig->getValue('ced_csmarketplace/membership_form_fields/category_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $specificCategoryPrice = $this->scopeConfig->getValue('ced_csmarketplace/membership_form_fields/category_prices', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $specificCategoryPrice = unserialize($specificCategoryPrice);
        $Price = 0;
        $groupCategory = $this->scopeConfig->getValue('ced_csmarketplace/vproducts/category_mode', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $groupAllowedCategory = $this->scopeConfig->getValue('ced_csmarketplace/vproducts/category', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if($groupCategory == '0')
        {
          return $Price;
        }
        else
        {
          $freeCategories = array_unique(explode(',',$groupAllowedCategory));
        }
        $specific=array();
        if(!empty($specificCategoryPrice))
        {
          foreach($specificCategoryPrice as $key => $category) {
            if($key=='__empty')
              continue;  
            if(in_array($category['category'],$categories)){
              array_push($specific,$category['category']);
              $Price = $Price + $category['price'];
            }
          }
        }
        
        foreach ($categories as $category) {
          if(!in_array($category,$specific) && !in_array($category, $freeCategories)){
            $Price = $Price + $perCategoryPrice;
          }
        }
        return $Price;
    }

    public function getProductCost($product_limit)
    {
        $perproductCost = $this->scopeConfig->getValue('ced_csmarketplace/membership_form_fields/product_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $product_limit * $perproductCost;
    }

    public function getDurationCost($duration)
    {
        $durationCost = 0;
        $durationCosts = $this->scopeConfig->getValue('ced_csmarketplace/membership_form_fields/duration', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $durationCosts = unserialize($durationCosts);

        foreach ($durationCosts as $key => $value) {
          if($key=='__empty')
            continue;
          if(trim($value['duration'])==trim($duration)){
            $durationCost=$value['price'];
          }
        }
        return $durationCost;
    }

    public function getVendorEmail()
    {
        $options = ['' => '-- '.__('All Vendor Subscription').' --'];
        $collection = $this->_objectManager->create('Ced\CsMembership\Model\Subscription')->getCollection();
        foreach ($collection as $key => $value) {
          $options[$value->getVendorId()] = $value->getCustomerEmail();
        }
        return $options; 
    }

    public function getMemberships()
    {
        $option = ['' => '-- '.__('All Membership Users').' --'];
        $collection = $this->_objectManager->create('Ced\CsMembership\Model\Subscription')->getCollection();
        foreach ($collection as $key => $value) {
          $option[$value->getSubscriptionId()] = __($value->getName());
        }
        return $option; 
    }

    public function getSubscribedUsers($packageId)
    {
        $collection = $this->_objectManager->create('Ced\CsMembership\Model\Subscription')
                                           ->getCollection()
                                           ->addFieldToFilter('subscription_id',$packageId)
                                           ->addFieldToSelect('vendor_id')
                                           ->getData();
        return $collection; 
    }

    public function getBasePrice()
    {
      return $this->scopeConfig->getValue('ced_csmarketplace/membership_form_fields/base_membership_fee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
  *@param $membershipId
        *@param $selectedVendors
  *@return bool
  */
  public function assignMembership($membershipId,$selectedVendors)
    {
        if(count($selectedVendors)>0)
        {
            $membershipModel = $this->_objectManager->create('Ced\CsMembership\Model\Membership');
            $subscription = $membershipModel->load($membershipId)->getData();
            $subscriptionId = $subscription['id'];
            $p_duration = $subscription['duration'];
            $name = $subscription['name'] ;
            $category = $subscription['category_ids'] ;
            $limit = $subscription['product_limit'];
            $price = $subscription['price'] ;
            $specialPrice = $subscription['special_price'];
            $currentTimestamp = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->timestamp(time()); 
            $date = date('Y-m-d',$currentTimestamp); 
            $duration = "+ $p_duration months";
            $newDate  = date('Y-m-d', strtotime($duration, strtotime($date)));
            foreach ($selectedVendors as $Vendor)
            {
                $existing = $this->getExistingSubcription($Vendor);
                if(in_array($membershipId,$existing))
                {
                  continue;
                }
                $VendorDetails = $this->_objectManager->create('Ced\CsMarketplace\Model\Vendor')->load($Vendor);
                $customer_email = $VendorDetails->getEmail();
                $model = $this->_objectManager->create('Ced\CsMembership\Model\Subscription');
                $model->setData('vendor_id',$Vendor);
                $model->setData('subscription_id',$subscriptionId);
                $model->setData('store',$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore(null)->getStoreId());
                $model->setData('website_id',$subscription['website_id']);
                $model->setData('status',\Ced\CsMembership\Model\Status::STATUS_RUNNING);
                $model->setData('order_id','by admin');
                $model->setData('start_date',$date);
                $model->setData('end_date',$newDate);
                $model->setData('customer_email',$customer_email);
                $model->setData('payment_name','by admin');
                $model->setData('transaction_id','by admin');
                $model->setData('name',$name);
                $model->setData('duration',$p_duration);
                $model->setData('category_ids',$category);
                $model->setData('product_limit', $limit);
                $model->setData('price',$price);
                $model->setData('special_price',$specialPrice);
                try{
                  $model->save();
                  }catch(Exception $e){
                }
          }
      return true;            
      } 
  }

  public function getExistingSubcription($vendorId)
  {
      $status="running";
      $collection = $this->_objectManager->create('Ced\CsMembership\Model\Subscription')
                           ->getCollection()
                           ->addFieldToFilter('vendor_id',$vendorId)
                           ->addFieldToFilter('status',$status)
                           ->addFieldToSelect('subscription_id')
                           ->getData();
       return $collection;                    
  }

  public function getMembershipPlans()
    {
        $data = $this->_objectManager->create('Ced\CsMembership\Model\Membership')
                                     ->getCollection()
                                     ->addFieldToFilter('status',\Ced\CsMembership\Model\Status::STATUS_ENABLED)
                                     //->addFieldToFilter('group_type',trim(strtolower($groupCode)))
                                     ;                      
        return $data;    
    }

  public function getProductIdByMembershipId($id)
  {
    $ProductId = $this->_objectManager->create('Ced\CsMembership\Model\Membership')->load($id)->getProductId();
    if($ProductId){
      return $ProductId;
    }
  }

  /**
  *@param $order
  */
  public function setSubscription($order)
      {
        if($this->_customerSession->getVendorId())
        {
            $id = $order->getId();
            $increment_id = $order->getIncrementId();
            $orderDetails = $this->_objectManager->create('Magento\Sales\Model\Order')->load($id);
            $items = $order->getAllItems();
            foreach($items as $item):
              $productId = $item->getProductId();
            endforeach;
            $vendorId = $this->_customerSession->getVendorId();
            if ($this->_customerSession->isLoggedIn()) {
                $customer = $this->_customerSession->getCustomer();
                $customer_email = $customer->getEmail();
            }   
            $model = $this->_objectManager->create('Ced\CsMembership\Model\Subscription');
            $subscription = $this->_objectManager->create('Ced\CsMembership\Helper\Data')->getSubscriptionByProduct($productId);
            $subscriptionId = $subscription['id'];
            if($subscriptionId){
              $payment_method_title = $order->getPayment()->getMethodInstance()->getTitle();
              $transactionId = $order->getPayment()->getTransactionId();
              $p_duration = $subscription['duration'];
              $name = $subscription['name'] ;
              $category = $subscription['category_ids'] ;
              $limit = $subscription['product_limit'];
              $price = $subscription['price'] ;
              $specialPrice = $subscription['special_price'];
              $currentTimestamp = $this->_objectManager->get('Magento\Framework\Stdlib\DateTime\DateTime')->timestamp(time());
              $date = date('Y-m-d',$currentTimestamp); 
              $duration="+ $p_duration months";
              $newDate  = date('Y-m-d', strtotime($duration, strtotime($date)));
              $model->setData('vendor_id',$vendorId);
              $model->setData('subscription_id',$subscriptionId);
              $model->setData('store',$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore(null)->getId());
              $model->setData('status',\Ced\CsMembership\Model\Status::STATUS_RUNNING);
              $model->setData('order_id',$increment_id);
              $model->setData('start_date',$date);
              $model->setData('end_date',$newDate);
              $model->setData('customer_email',$customer_email);
              $model->setData('payment_name',$payment_method_title);
              $model->setData('transaction_id',$transactionId);
              $model->setData('name',$name);
              $model->setData('duration',$p_duration);
              $model->setData('category_ids',$category);
              $model->setData('product_limit', $limit);
              $model->setData('price',$price);
              $model->setData('special_price',$specialPrice);
              $model->setData('website_id',$subscription['website_id']);
              try{
                  $model->save();
                  $qtyModel = $this->_objectManager->create('Ced\CsMembership\Model\Membership')->load($subscriptionId);
                  $prvqty = $qtyModel->getQty();
                  $newqty = $prvqty - 1;
                  $qtyModel->setQty($newqty);
                  $qtyModel->save();
                  $items = $order->getAllItems();
            

                  $this->sendsubscriptionMail($increment_id,$customer_email,$name,$p_duration,$category, $limit,$newDate);
                 try {
                    if(!$order->canInvoice())
                    {
                        throw new \Magento\Framework\Exception\LocalizedException(__('Cannot create invoice.'));
                    }
                    //$items = array();
                   // $items[$item->getQuoteItemId()] = 1;
                    $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($order);
                    if (!$invoice->getTotalQty()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('Cannot create an invoice without products.'));
                    }
                  $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                  $invoice->register();
                  $transactionSave = $this->_transaction
                  ->addObject($invoice)
                  ->addObject($invoice->getOrder());
                  $transactionSave->save();
                  }
                  catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                  }
                  $this->messageManager->addSuccess('You have subscribed this package successfully');
                }catch(\Exception $e){
                    $this->messageManager->addError(__($e->getMessage()));
                }
              } 
    }
  }

  /**
    *@param $productId
    * @return $data;
    */
    public function getSubscriptionByProduct($productId){
        $data = $this->_objectManager->get('Ced\CsMembership\Model\Membership')
                                     ->getCollection()
                                     ->addFieldToFilter('product_id',$productId)
                                     //->addFieldToFilter('group_type',trim(strtolower($groupCode)))
                                     ->getData(); 
        if(!empty($data))
          return $data[0];
    }

    public function getAllowedCategory()
    {
       $vendorId = $this->_customerSession->getVendorId(); 
       $subcriptionCollection = $this->_objectManager->create('Ced\CsMembership\Model\Subscription')
                                                     ->getCollection()
                                                     ->addFieldToFilter('vendor_id',$vendorId)
                                                     ->addFieldToFilter('store',$this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore(null)->getStoreId());
       $categories=array();
       foreach ($subcriptionCollection as $key => $subcription) {
          if($subcription->getStatus() == \Ced\CsMembership\Model\Status::STATUS_RUNNING){
             $categoryIds = $subcription->getCategoryIds();
             $categoryJson = explode(',',$categoryIds);
             $categories = array_merge($categoryJson,$categories);
          }
      }
      return array_unique($categories);
    }


    public function getLimit($storeId)
    {

          $websiteId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getWebsiteId();
         $vendorId = $this->_customerSession->getVendorId(); 
         $productLimit = 0;
         $subcriptionCollection = $this->_objectManager->create('Ced\CsMembership\Model\Subscription')
                                                       ->getCollection()
                                                       ->addFieldToFilter('vendor_id',$vendorId)
                                                       ->addFieldToFilter('website_id',$websiteId);
         foreach ($subcriptionCollection as $key => $subcription) {
        
          if($subcription->getStatus() == \Ced\CsMembership\Model\Status::STATUS_RUNNING){
            $productLimit = $productLimit + $subcription->getProductLimit();
          }
        }
        return $productLimit;
    }

    public function setExpireMethod()
    {
        $perproductCost = $this->scopeConfig->getValue('ced_csmarketplace/membership_form_fields/product_price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $product_limit * $perproductCost;
    }

    /**
     *@param $IncrementId
     *@param $customer_email
     *@param $name
     *@param $p_duration
     *@param $category
     *@param $limit
     *@param $newDate   
     *@return bool
     */
  public function sendsubscriptionMail($IncrementId, $customer_email, $name, $p_duration, $category, $limit, $newDate)
    {
        $vendor = $this->_objectManager->create('Ced\CsMarketplace\Model\Vendor')->load($this->_customerSession->getVendorId());
        if($vendor->getId() && $vendor->getName())
           $customerName = $vendor->getName();
        else 
           $customerName = $vendor->getPublicName();
       /* if($vendor->getId() && $vendor->getData('company_logo'))
           $vendorlogoUrl = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$vendor->getData('company_logo');
        else 
           $vendorlogoUrl=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$vendor->getData('profile_picture');*/
        $emailTemplate = 'subscription_order_email_template';
        $senderName = $this->scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $emailTemplateVariables = array();
        $emailTemplateVariables['name'] = $customerName;
        $emailTemplateVariables['email'] = $customer_email;
        $emailTemplateVariables['orderid'] = $IncrementId;
       // $emailTemplateVariables['logo'] = $vendorlogoUrl;
        $emailTemplateVariables['package'] = $name;
        $emailTemplateVariables['duration'] = $p_duration.' Month(s)';
        $category = explode(',',$category);
        $cathtml = '';
        foreach ($category as $cat) {
          $model=$this->_objectManager->create('Magento\Catalog\Model\Category')->load($cat);
          if($model->getLevel() == '0' || $model->getLevel() == '1')
              continue;
          $cathtml = $cathtml . $model->getName() . '<br>';
        }
        $emailTemplateVariables['category'] = $cathtml;
        $emailTemplateVariables['limit'] = $limit;
        $emailTemplateVariables['expire'] =$newDate;
        $this->sendMail($emailTemplate, $storeId =1, $emailTemplateVariables, $senderEmail, $customer_email);
    }

        /**
         *@param $subcription
         *@return bool
         */  
    public function sendExpireMail($subscription)
    {
        $vendorId = $subcription->getVendorId();
        $customer_email = $subcription->getCustomerEmail();
        $vendor = $this->_objectManager->create('Ced\CsMarketplace\Model\Vendor')->load($vendorId);
        if($vendor->getId() && $vendor->getName())
           $customerName = $vendor->getName();
        else 
           $customerName = $vendor->getPublicName();
        /*if($vendor->getId() && $vendor->getData('company_logo'))
           $vendorlogoUrl=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$vendor->getData('company_logo');
        else 
           $vendorlogoUrl=Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$vendor->getData('profile_picture');*/
        $emailTemplate = 'expiration_order_email_template';
        $senderName = $this->scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $senderEmail = $this->scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $emailTemplateVariables = array();
        $emailTemplateVariables['name'] = $customerName;
       // $emailTemplateVariables['logo'] = $vendorlogoUrl;
        $emailTemplateVariables['package'] = $subcription->getName();
        $this->sendMail($emailTemplate, $storeId =1, $emailTemplateVariables, $senderEmail, $customer_email);
    }

    public function sendMail($emailTemplate, $storeId =1, $emailTemplateVariables, $senderEmail, $reciever_email)
    {
        try{
          $sender = [
                      'name' => $emailTemplateVariables['name'],
                      'email' => $reciever_email,
                      ];
          $transport = $this->_transportBuilder->setTemplateIdentifier($emailTemplate)
              ->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID])
              ->setTemplateVars($emailTemplateVariables)
              ->setFrom($sender)
              ->addTo($reciever_email)
              ->getTransport();
          $transport->sendMessage();

      }
      catch(\Exception $e)
      {
        print_r($e->getMessage());
      }
    }


    
}

