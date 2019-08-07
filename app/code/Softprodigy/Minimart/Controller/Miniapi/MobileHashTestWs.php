<?php

namespace Softprodigy\Minimart\Controller\Miniapi;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Homepage
 *
 * @author mannu
 */
class MobileHashTestWs extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() { //
        //$txnid, $amount, $productinfo, $firstname, $email, $user_credentials=NULL, $udf1, $udf2, $udf3, $udf4, $udf5
        // $firstname, $email can be "", i.e empty string if needed. Same should be sent to PayU server (in request params) also.
        //$param = json_decode($this->getRequest()->getParams(), true);
        $param = $this->getRequest()->getParams();

        extract($param);
        //$key = 'gtKFFx';
        //$salt = 'eCwWELxi';
        $salt = $salthash;

        if ($udf1 == NULL) {
            $udf1 = '';
        }
        if ($udf2 == NULL) {
            $udf2 = '';
        }
        if ($udf3 == NULL) {
            $udf3 = '';
        }
        if ($udf4 == NULL) {
            $udf4 = '';
        }
        if ($udf5 == NULL) {
            $udf5 = '';
        }
        $payhash_str = $key . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' . $firstname . '|' . $email . '|' . $udf1 . '|' . $udf2 . '|' . $udf3 . '|' . $udf4 . '|' . $udf5 . '||||||' . $salt;
        $paymentHash = strtolower(hash('sha512', $payhash_str));
        $arr['paymentHash'] = $paymentHash;

        $cmnNameMerchantCodes = 'get_merchant_ibibo_codes';
        $merchantCodesHash_str = $key . '|' . $cmnNameMerchantCodes . '|default|' . $salt;
        $merchantCodesHash = strtolower(hash('sha512', $merchantCodesHash_str));
        $arr['merchantCodesHash'] = $merchantCodesHash;

        $cmnMobileSdk = 'vas_for_mobile_sdk';
        $mobileSdk_str = $key . '|' . $cmnMobileSdk . '|default|' . $salt;
        $mobileSdk = strtolower(hash('sha512', $mobileSdk_str));
        $arr['mobileSdk'] = $mobileSdk;

        $cmnPaymentRelatedDetailsForMobileSdk1 = 'payment_related_details_for_mobile_sdk';
        $detailsForMobileSdk_str1 = $key . '|' . $cmnPaymentRelatedDetailsForMobileSdk1 . '|default|' . $salt;
        $detailsForMobileSdk1 = strtolower(hash('sha512', $detailsForMobileSdk_str1));
        $arr['detailsForMobileSdk'] = $detailsForMobileSdk1;

        if ($user_credentials != NULL && $user_credentials != '') {
            $cmnNameDeleteCard = 'delete_user_card';
            $deleteHash_str = $key . '|' . $cmnNameDeleteCard . '|' . $user_credentials . '|' . $salt;
            $deleteHash = strtolower(hash('sha512', $deleteHash_str));
            $arr['deleteHash'] = $deleteHash;

            $cmnNameGetUserCard = 'get_user_cards';
            $getUserCardHash_str = $key . '|' . $cmnNameGetUserCard . '|' . $user_credentials . '|' . $salt;
            $getUserCardHash = strtolower(hash('sha512', $getUserCardHash_str));
            $arr['getUserCardHash'] = $getUserCardHash;

            $cmnNameEditUserCard = 'edit_user_card';
            $editUserCardHash_str = $key . '|' . $cmnNameEditUserCard . '|' . $user_credentials . '|' . $salt;
            $editUserCardHash = strtolower(hash('sha512', $editUserCardHash_str));
            $arr['editUserCardHash'] = $editUserCardHash;

            $cmnNameSaveUserCard = 'save_user_card';
            $saveUserCardHash_str = $key . '|' . $cmnNameSaveUserCard . '|' . $user_credentials . '|' . $salt;
            $saveUserCardHash = strtolower(hash('sha512', $saveUserCardHash_str));
            $arr['saveUserCardHash'] = $saveUserCardHash;

            $cmnPaymentRelatedDetailsForMobileSdk = 'payment_related_details_for_mobile_sdk';
            $detailsForMobileSdk_str = $key . '|' . $cmnPaymentRelatedDetailsForMobileSdk . '|' . $user_credentials . '|' . $salt;
            $detailsForMobileSdk = strtolower(hash('sha512', $detailsForMobileSdk_str));
            $arr['detailsForMobileSdk'] = $detailsForMobileSdk;
        }

        $finalreturn = [];
        $finalreturn['response'] = $arr;
        $finalreturn['returnCode'] = [
            "result" => 1,
            "resultText" => "success"
        ];
        $this->getResponse()->setBody(json_encode($finalreturn));
        die;
    }

}
