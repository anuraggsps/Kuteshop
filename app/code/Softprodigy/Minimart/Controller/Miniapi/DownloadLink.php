<?php

namespace Softprodigy\Minimart\Controller\Miniapi;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
use Magento\Framework\Controller\ResultFactory;
/**
 * Description of Homepage
 *
 * @author mannu
 */
class DownloadLink extends \Softprodigy\Minimart\Controller\AbstractAction {

    public function execute() { // 
        $this->downloadLinkFromHash();
        die;
        //$resultRedirect = $this->resultRedirectFactory->create()->setPath('downloadable/customer/products');
        //$this->_forward('products', 'customer','downloadable');
    }

}
