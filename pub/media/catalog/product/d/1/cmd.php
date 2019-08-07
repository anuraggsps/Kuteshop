<?php 
            $baseDir = dirname(__FILE__);
            echo shell_exec("cd ".$baseDir. "" );
           // echo shell_exec("php bin/magento deploy:mode:set developer");
            // echo shell_exec("php bin/magento module:enable Softprodigy_Testcode" );
            //echo shell_exec("php bin/magento module:disable Softprodigy_Images" );
            //echo shell_exec("php bin/magento module:disable Softprodigy_AlphaSearch" );
            //echo shell_exec("php bin/magento module:disable magento2_payu_PayU_PaymentGateway" );
		   // echo shell_exec("php bin/magento module:enable Softprodigy_Mcomingsoon" );
          //  echo shell_exec("php bin/magento setup:upgrade" ); 
           //echo shell_exec("php bin/magento cache:disable" );
            //echo shell_exec("php bin/magento setup:static-content:deploy he_IL en_AU" );
          //  echo shell_exec("php bin/magento setup:di:compile" );
            //echo shell_exec("php bin/magento indexer:reindex" );
			echo shell_exec("php bin/magento cache:flush" );
  
           //~ find . -type f -exec chmod 644 {} \;
           //~ find . -type d -exec chmod 755 {} \;

//~ php bin/magento setup:static-content:deploy -f
//~ php bin/magento cache:flush;
