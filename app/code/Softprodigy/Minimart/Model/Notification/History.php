<?php
namespace Softprodigy\Minimart\Model\Notification;
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
class History extends \Magento\Framework\Model\AbstractModel {
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Softprodigy\Minimart\Model\ResourceModel\Notification\History');
    }
}