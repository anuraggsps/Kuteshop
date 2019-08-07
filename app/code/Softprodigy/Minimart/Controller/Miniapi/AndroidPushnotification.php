<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of AndroidPushnotification
 *
 * @author root
 */
class AndroidPushnotification extends \Softprodigy\Minimart\Controller\AbstractAction {
    
    public function execute() {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/ogb_apple_noti.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        
        //define( 'API_ACCESS_KEY', 'AIzaSyDoDdIFfihpFlfD7PNBVgsE2EP-MyZabmY' );
        $params = $this->getRequest()->getParams();
        $message = urldecode($params['msg']);
        $order_id = isset($params['order_id']) ? urldecode($params['order_id']) : '';
        $isOffers = isset($params['is_offer']) ? urldecode($params['is_offer']) : false;
        $quote_id = isset($params['quote_id']) ? urldecode($params['quote_id']) : '';
        $email = urldecode($params['email']);
        $item_type = isset($params['item_type']) ? urldecode($params['item_type']) : '';
        $item_value = isset($params['item_value']) ? urldecode($params['item_value']) : '';

        $type_id = 'general';
        if ($order_id and ! empty($order_id)) {
            $type_id = 'order';
        } else if ($isOffers == true && !empty($item_type) && !empty($item_value)) {
            $type_id = 'offer';
        } else if (!empty($quote_id)) {
            $type_id = 'abandoned_cart';
        }

        $tokeninfo = $this->_objectManager->get('Softprodigy\Minimart\Model\Deviceinfo')->load($email, 'customer_email');

        //var_dump($params); die;
        if (isset($params['dtype']) and ! empty($params['dtype']) and strtolower($params['dtype']) == 'iphone') {
            try {
                $apmode = (int) $this->__helper->getStoreConfig('minimart/minimart_registration/notification_mode');
                $pushMode = 1;

                require_once('ApnsPHP/Autoload.php');

                if ($apmode == 1) {
                    $pushMode = \ApnsPHP_Abstract::ENVIRONMENT_SANDBOX;
                } else {
                    $pushMode = \ApnsPHP_Abstract::ENVIRONMENT_PRODUCTION;
                }

                $push = new \ApnsPHP_Push($pushMode, 'OnGoBuyo.pem');

                // do the actual push notification
                $push->connect();
                try {
                    $messageObj = new \ApnsPHP_Message($params['deviceId']);
                    $messageObj->setCustomIdentifier("Message-Badge-3");
                    $messageObj->setBadge((int) $tokeninfo->getBadgeCount() + 1);
                    $messageObj->setText($message);
                    // Play the default sound
                    $messageObj->setSound();
                    // Set a custom property
                    $messageObj->setCustomProperty('acme2', array('order-id' => $order_id,
                        'quote_id' => $quote_id,
                        'offers' => $isOffers,
                        'email' => $email,
                        'item_type' => $item_type,
                        'item_value' => $item_value,
                        'type_id' => $type_id));

                    $messageObj->setExpiry(30);
                } catch (\Exception $e) {
                    $logger->info($e->getMessage());
                }
                try {
                    $push->add($messageObj);
                } catch (\Exception $e) {
                    $logger->info($e->getMessage());
                }
                try {
                    $push->send();
                } catch (\Exception $e) {
                    $logger->info($e->getMessage());
                }
                try {
                    $push->disconnect();
                } catch (\Exception $e) {
                    $logger->info($e->getMessage());
                }

                $aErrorQueue = $push->getErrors();

                if (empty($aErrorQueue)) {
                    $model =  $this->_objectManager->get('Softprodigy\Minimart\Model\Notification\History');
                   
                    $model->setData(array(
                        'type_id' => $type_id,
                        'customer_email' => $email,
                        'msg' => $message,
                        'order_id' => $order_id,
                        'quote_id' => $quote_id,
                        'is_offer' => $isOffers == true ? '1' : '0',
                        'item_type' => $item_type,
                        'item_value' => $item_value,
                        'created_at' => date('Y-m-d H:i:s')
                    ));
                    $model->save();
                    $model->unsetData();

                    $tid = $tokeninfo->getId();
                    $tokeninfo->setBadgeCount((int) $tokeninfo->getBadgeCount() + 1);
                    $tokeninfo->setId($tid);
                    $tokeninfo->save();

                    echo json_encode(array('success' => 1));
                    die;
                } else {
                    
                    $logger->info(serialize($aErrorQueue));
                    echo json_encode($aErrorQueue);
                    die;
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
                die;
            }
        } else {

            $deviceId[] = $params['deviceId'];

            $apiKey = $this->__helper->getStoreConfig("minimart/minimart_registration/gcm_api_key");
            define('API_ACCESS_KEY', $apiKey);
            // define('API_ACCESS_KEY', 'AIzaSyDoDdIFfihpFlfD7PNBVgsE2EP-MyZabmY');
            $registrationIds = $deviceId;

            $msg = array(
                'message' => $message,
                'title' => 'This is a title. title',
                'subtitle' => 'This is a subtitle. subtitle',
                'tickerText' => 'Ticker text here...Ticker text here...Ticker text here',
                'vibrate' => 1,
                'sound' => 1,
                'order-id' => $order_id,
                'quote_id' => $quote_id,
                'offers' => $isOffers,
                'email' => $email,
                'item_type' => $item_type,
                'item_value' => $item_value,
                'type_id' => $type_id
            );

            if ($msg['item_type'] == 'product') {
                $product = $this->productFactory->load($msg['item_value']);
                $msg['item_value'] = $msg['item_value'] . "#" . $product->getTypeId();
            }

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
            //var_dump($headers);
            //var_dump($fields); 
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://android.googleapis.com/gcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
            $result = curl_exec($ch);
            curl_close($ch);
            try {
                $respjson = json_decode($result, true);
                 
                $logger->info(print_r($result, true));
                
                $logger->info(print_r($result, true)); 
                if ($respjson and ! empty($respjson)) {
                    if ($respjson['success'] == 1) {
                        $model =  $this->_objectManager->get('Softprodigy\Minimart\Model\Notification\History');
                        $model->setData(array(
                            'type_id' => $type_id,
                            'customer_email' => $email,
                            'msg' => $message,
                            'order_id' => $order_id,
                            'quote_id' => $quote_id,
                            'is_offer' => $isOffers == true ? '1' : '0',
                            'item_type' => $item_type,
                            'item_value' => $item_value,
                            'created_at' => date('Y-m-d H:i:s')
                        ));
                        $model->save();
                        $model->unsetData();

                        $tid = $tokeninfo->getId();
                        $tokeninfo->setBadgeCount((int) $tokeninfo->getBadgeCount() + 1);
                        $tokeninfo->setId($tid);
                        $tokeninfo->save();
                    }
                }
            } catch (\Exception $ex) {
                $logger->info($ex->getMessage());
            }
            //echo '<pre>';
            echo $result;
            die;
        }
    }

}
