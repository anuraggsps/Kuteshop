<?php
namespace Softprodigy\Minimart\Model\ResourceModel;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Notification
 *
 * @author mannu
 */
class Deviceinfo extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb {
     /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('minimart_user_device_token', 'id');
    }
}
