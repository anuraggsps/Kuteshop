<?php

class Softprodigy_Minimart_Model_Observer {

    const Basic_Package = 'Basic';
    const Silver_Package = 'Silver';
    const Gold_Package = 'Gold';
    const Basic_Exd_Package = 'Extended';

    public function sendNotification(Varien_Event_Observer $observer) {
        try {
            /* @var Mage_Sales_Model_Order $order */
            $order = $observer->getOrder();
            $stateProcessing = $order::STATE_PROCESSING;
            // Only trigger when an order enters processing state.
            $email = $order->getCustomerEmail();
            if ($order->getState() == $stateProcessing || $order->getState() == 'complete' || $order->getState() == 'canceled') {

                $model = Mage::getModel('minimart/deviceinfo')->load($email, 'customer_email');
                /*
                  $user_token = Mage::getSingleton('core/resource')->getTableName('user_token');
                  $query = "select * from {$user_token} where customer_email = '" . $email . "'";
                  $data = Mage::getSingleton('core/resource')->getConnection('core_read')->query($query);
                 */
                $data = $model->getData();
                //var_dump($data); die;
                if ($order->getState() == $stateProcessing)
                    $msg = 'Your order with order id -' . $order->getIncrementId() . ' is in ' . $order->getState() . ' state.';
                if ($order->getState() == 'complete')
                    $msg = 'Your order with order id -' . $order->getIncrementId() . ' has been completed.';
                if ($order->getState() == 'canceled')
                    $msg = 'Your order with order id -' . $order->getIncrementId() . ' has been canceled.';

                $msg = urlencode($msg);
                $order_id = urlencode($order->getIncrementId());
                $hashSalt = Mage::getStoreConfig('minimart/minimart_registration/ogb_api_key');
                if ($data['token']) {
                    $target_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . "minimart/miniapi/AndroidPushnotification?salt=" . $hashSalt . "&deviceId=" . $data['token'] . "&dtype=" . $data['type'] . "&msg=$msg&order_id=$order_id&email=" . urlencode($email);
                    $ch = curl_init($target_url);

                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

                    curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

                    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                    curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

                    $response_data = curl_exec($ch);

                    // echo '<pre>';
                    //  print_r($response_data);
                    //  die; 
                }
            }
        } catch (Exception $e) {
            //echo $e->getMessage();
            //exit;
        }
    }

    public function sendAbondendCartEmail() {
        if ($this->canAbondonedCartNoti()) {
            $salesQuote = Mage::getModel('sales/quote')->getCollection();
            //$salesQuote->addFieldToFilter("updated_at",array("gteq"=>$expire_stamp));
            $salesQuote->addFieldToFilter("is_active", 1);
            $salesQuote->getSelect()->where('updated_at+ INTERVAL 23 HOUR < NOW()');
            // var_dump($salesQuote->getSelect()->__toString()); die;
            /* Sender Name */
            $senderName = Mage::getStoreConfig('trans_email/ident_sales/name');
            /* Sender Email */
            $senderEmail = Mage::getStoreConfig('trans_email/ident_sales/email');
            $ccode = Mage::getStoreConfig('minimart/abcart_setting/ccode');
            foreach ($salesQuote as $quote) {
                try {
                    $customerEmailId = $quote->getcustomer_email();
                    $customerFullName = $quote->getCustomerFirstname() . ' ' . $quote->getCustomerLastname();

                    $emailTemplate = Mage::getModel('core/email_template')
                            ->loadDefault('minimart_abcart_template');

                    // it depends on the template variables
                    $emailTemplateVariables = array();
                    $emailTemplateVariables['quote'] = $quote;
                    $emailTemplateVariables['store'] = Mage::app()->getStore();
                    $emailTemplateVariables['coupon_code'] = $ccode;
                    //  var_dump($emailTemplateVariables);
                    //var_dump($emailTemplate->getProcessedTemplate($emailTemplateVariables));
                    //die;
                    $emailTemplate->setSenderName($senderName);
                    $emailTemplate->setSenderEmail($senderEmail);
                    $emailTemplate->setType('html');
                    $emailTemplate->setTemplateSubject('Please complete your order today');
                    $emailTemplate->send($customerEmailId, $customerFullName, $emailTemplateVariables);

                    //send notifcation
                    try {
                        $hashSalt = Mage::getStoreConfig('minimart/minimart_registration/ogb_api_key');
                        $model = Mage::getModel('minimart/deviceinfo')->load($quote->getcustomer_email(), 'customer_email');
                        if ($model->getId()) {
                            $data = $model->getData();
                            $param = array(
                                'deviceId' => $data['token'],
                                'msg' => urlencode($this->__('Hurry! Your Cart is waiting for you. You have some items in your cart.')),
                                'quote_id' => $quote->getId()
                            );
                            $target_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . "minimart/miniapi/AndroidPushnotification?salt=" . $hashSalt . "&deviceId=" . $param['deviceId'] . "&dtype=" . $data['type'] . "&msg=" . $param['msg'] . "&quote_id=" . $param['quote_id'] . "&email=" . urlencode($quote->getcustomer_email());
                            $ch = curl_init($target_url);

                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                            curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

                            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);

                            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

                            $response_data = curl_exec($ch);
                        }
                    } catch (Exception $ex) {
                        Mage::logException($ex);
                    }
                } catch (Exception $ex) {
                    Mage::logException($ex);
                }
                //            $quote->setUpdatedAt(date("Y-m-d H:i:s", strtotime("+24 HOURS")));
                //            $quote->save();
                //var_dump($quote->getId()); die;
            }
        }
    }

    /**
     * Cron method to send mass notifcation to customer
     */
    public function sendMassNotifications() {
        if ($this->canMssNotifcationEnabled()) {
            $collection = Mage::getModel('minimart/notification')->getCollection();
            $collection->addFieldToFilter('status', '0');
            $collection->getSelect()->order('id DESC');
            $collection->getSelect()->limit(20);
            $hashSalt = Mage::getStoreConfig('minimart/minimart_registration/ogb_api_key');
            foreach ($collection->getItems() as $_item) {
                $model = Mage::getModel('minimart/deviceinfo')->load($_item->getCustomerEmail(), 'customer_email');

                $data = $model->getData();
                $param = array(
                    'deviceId' => $data['token'],
                    'msg' => urlencode($_item->getMsg()),
                    'is_offer' => true
                );
                $target_url = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . "minimart/miniapi/AndroidPushnotification?salt=" . $hashSalt . "&deviceId=" . $param['deviceId'] . "&dtype=" . $data['type'] . "&msg=" . $param['msg'] . "&order_id=&is_offer=" . $param['is_offer'] . "&email=" . urlencode($_item->getCustomerEmail()) . "&item_type=" . urlencode($_item->getOfferItemType()) . "&item_value=" . urlencode($_item->getOfferItemValue());
                $ch = curl_init($target_url);
                
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);

                curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);

                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
                curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

                $response_data = curl_exec($ch);
                curl_close($ch);
                $respjson = json_decode($response_data, true);

                if ($respjson and ! empty($respjson)) {
                    if (isset($respjson['success']) and $respjson['success'] == 1) {
                        $_item->setSentAt(date('y-m-d h:i:s'));
                        $_item->setStatus('1');
                        $_item->save();
                    }
                }
                //$this->__callToNotification($param);
            }
        }
    }

    private function __callToNotification($params) {
        try {
            $deviceId[] = $params['deviceId'];
            $message = urldecode($params['msg']);
            $apiKey = Mage::getStoreConfig("minimart/minimart_registration/gcm_api_key");
            define('API_ACCESS_KEY', $apiKey);
            //define('API_ACCESS_KEY', 'AIzaSyDeGYRjBtuv93ZqplsA9gcLRKWfFdYJvVU');

            $registrationIds = $deviceId;

            $msg = array
                (
                'message' => $message,
                'title' => 'This is a title. title',
                'subtitle' => 'This is a subtitle. subtitle',
                'tickerText' => 'Ticker text here...Ticker text here...Ticker text here',
                'vibrate' => 1,
                'sound' => 1,
            );

            $fields = array
                (
                'registration_ids' => $registrationIds,
                'data' => $msg
            );

            $headers = array
                (
                'Authorization: key=' . API_ACCESS_KEY,
                'Content-Type: application/json'
            );
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);
            Mage::log($result);
            //var_dump($result); die;
        } catch (Exception $ex) {
            Mage::logException($ex);
        }
    }

    public function checkPkgSubsData() {
        $hashKey = Mage::getStoreConfig('minimart/minimart_registration/ogb_api_key');
        $store_url = $_SERVER['SERVER_NAME']; //Mage::getStoreConfig('minimart/minimart_registration/ogb_api_invoice_id');

        $fields = array
            (
            'key' => $hashKey,
            'store_url' => $store_url,
        );
        //var_dump($fields);
        $requestUrl = 'https://www.ongobuyo.com/ogb_connection.php';
        //$headers = array('Content-Type: application/json');
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $requestUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function canMssNotifcationEnabled() {

        $result = $this->checkPkgSubsData();
        $subs = json_decode($result, true);

        if ($subs['package_name'] == self::Basic_Package || $subs['package_name'] == self::Basic_Exd_Package) {
            //return false;
        }
        return true;
    }

    public function canAbondonedCartNoti() {
        $result = $this->checkPkgSubsData();
        $subs = json_decode($result, true);
        if ($subs['package_name'] == self::Gold_Package) {
            return true;
        }
        return false;
    }

    public function checkPackageSubcription() {


        $result = $this->checkPkgSubsData();
        $resp = json_decode($result, true);
        if ($resp) {
            $mailSubject = "";
            $preSubject = "Hurry! OnGoBuYo subscription %s.";
            $preBodyBefore = "OnGoBuYo subscription will be end in %s.";
            $mailBodyBefore = "";
            $subcriptionClosed = $resp['sub_expire'];
            $remaingInterval = (float) $resp['interval'];
            //$remaingInterval = $remaingDays * 24;
            $sendmail = false;
            if ($subcriptionClosed == false) {
                switch (true) {
                    case $remaingInterval <= 720 || $remaingInterval > 715:
                        $time = (int) $remaingInterval / 24;
                        $mailSubject = $this->__($preSubject, "is going to end soon");
                        $mailBodyBefore = $this->__($preBodyBefore, $time . " days");
                        $sendmail = true;
                        break;
                    case $remaingInterval <= 360 || $remaingInterval > 355:
                        $time = (int) $remaingInterval / 24;
                        $mailSubject = $this->__($preSubject, "is going to end in " . $time . " days");
                        $mailBodyBefore = $this->__($preBodyBefore, $time . " days");
                        $sendmail = true;
                        break;
                    case $remaingInterval <= 48 || $remaingInterval > 45:
                        $time = (int) $remaingInterval;
                        $mailSubject = $this->__($preSubject, "is going to end in " . $time . " hours");
                        $mailBodyBefore = $this->__($preBodyBefore, $time . " hours");
                        $sendmail = true;
                        break;
                    case $remaingInterval <= 24 || $remaingInterval > 20:
                        $time = (int) $remaingInterval;
                        $mailSubject = $this->__($preSubject, "is going to end in " . $time . " hours");
                        $mailBodyBefore = $this->__($preBodyBefore, $time . " hours");
                        $sendmail = true;
                        break;
                    case $remaingInterval <= 6 || $remaingInterval > 4:
                        $time = (int) $remaingInterval;
                        $mailSubject = $this->__($preSubject, "is going to end in " . $time . " hours");
                        $mailBodyBefore = $this->__($preBodyBefore, $time . " hours");
                        $sendmail = true;
                        break;
                    case $remaingInterval <= 2:
                        $time = (int) $remaingInterval * 60;
                        $mailSubject = $this->__($preSubject, "is going to end in " . $time . " minutes");
                        $mailBodyBefore = $this->__($preBodyBefore, $time . " minutes");
                        $sendmail = true;
                        break;
                }
            } else if ($subcriptionClosed == true) {

                if ($remaingInterval > 48) {
                    $rem = $remaingInterval % 48;
                    if ($rem < 5) {
                        $time = $remaingInterval < 24 ? $remaingInterval . " hour(s)" : ($remaingInterval / 24) . " day(s).";
                        $mailSubject = $this->__($preSubject, "has been expired since " . $time);
                        $mailBodyBefore = "OnGoBuYo subscription has been expired. Please re-new as soon as possible.";
                        $sendmail = true;
                    }
                }
            }
            if ($sendmail == true) {
                $this->sendSubscriptionEmail($mailSubject, $mailBodyBefore);
            }
        }
        return;
    }

    private function sendSubscriptionEmail($mailSubject, $mailBodyBefore) {
        try {
            $toemail = Mage::getStoreConfig('trans_email/ident_general/email'); //fetch sender email Admin
            $toname = Mage::getStoreConfig('trans_email/ident_general/name'); //fetch sender name Admin
            $emailTemplate = Mage::getModel('core/email_template')
                    ->loadDefault('minimart_subsc_template');

            // it depends on the template variables
            $emailTemplateVariables = array();
            $emailTemplateVariables['mail_subject'] = $mailSubject;
            $emailTemplateVariables['mail_body_before'] = $mailBodyBefore;
            $emailTemplateVariables['support_mail'] = $mailBodyBefore;
            $emailTemplateVariables['base_url'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            //  var_dump($emailTemplateVariables);
            //var_dump($emailTemplate->getProcessedTemplate($emailTemplateVariables));
            //die;
            $emailTemplate->setSenderName('OnGobuYo');
            $emailTemplate->setSenderEmail('support@ongobuyo.com');
            $emailTemplate->setType('html');
            $emailTemplate->setTemplateSubject($mailSubject);
            $emailTemplate->send($toemail, $toname, $emailTemplateVariables);
        } catch (Exception $ex) {
            Mage::log($ex, null, 'mimimart-subs.log');
        }
    }

    public function onConfigSave($observer) {
        try {

            $hashKey = Mage::getStoreConfig('minimart/minimart_registration/ogb_api_key');

            $from_email = Mage::getStoreConfig('trans_email/ident_general/email'); //fetch sender email Admin
            $from_name = Mage::getStoreConfig('trans_email/ident_general/name'); //fetch sender name Admin

            $fields = array
                (
                'store_url' => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB),
                'store_email' => $from_email,
                'store_name' => $from_name,
                'status' => $hashKey ? 1 : 0
            );
            //var_dump($fields);
            $requestUrl = 'https://www.ongobuyo.com/ogb_inst_log.php';
            $headers = array('Content-Type: application/json');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $requestUrl);
            curl_setopt($ch, CURLOPT_POST, true);

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
            $result = curl_exec($ch);
            curl_close($ch);
            //var_dump($result);
        } catch (Exception $ex) {
            Mage::log($ex->getTraceAsString());
        }

        return $this;
    }
    
    public function customerDeleteAfter($observer){
        $customer = $observer->getEvent()->getCustomer();
        $custEmail = $customer->getEmail();
        $model = Mage::getModel('minimart/deviceinfo')->load($custEmail, 'customer_email');
        if($model->getId()){
            $model->delete();
        }
        return $this;
    }
}
