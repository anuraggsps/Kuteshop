<?php
namespace Softprodigy\Minimart\Model\ResourceModel\Homedesign;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
/**
 * Description of Collection
 *
 * @author mannu
 */
class Collection extends AbstractCollection {
    
    protected function _construct()
    {
        $this->_init('Softprodigy\Minimart\Model\Homedesign', 'Softprodigy\Minimart\Model\ResourceModel\Homedesign');
    }
    
}
