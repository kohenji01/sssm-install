<?php
/**
 * =============================================================================================
 *  Project: sssm
 *  File: Install.php
 *  Date: 2020/05/20 14:18
 *  Author: Shoji Ogura <kohenji@sarahsytems.com>
 *  Copyright (c) 2020. SarahSystems lpc.
 *  This software is released under the MIT License, see LICENSE.txt.
 * =============================================================================================
 */

namespace Sssm\Install\Controllers;

use Exception;
use Sssm\Base\Controllers\UserBaseController;
use Sssm\Install\Models\SystemInit;

class Install extends UserBaseController{
    
    public $data=[];
    
    public function __construct(){
        try{
            if( file_exists( WRITEPATH . 'sssm_was_installed' ) ){
                throw new Exception( 'sssm installer is already executed. If you want to run installer again, You should erase ' . WRITEPATH . 'sssm_was_installed file and reload this page.' );
            }
            
            if( !file_exists( ROOTPATH . '.env' ) ){
                $url = ( empty( $_SERVER['HTTPS'] ) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
                file_put_contents( ROOTPATH . '.env' , <<<__EOF__
# Environment params build by sssm.
CI_ENVIRONMENT = development
sssm.sysname = 'sssm'
app.baseURL = '{$url}'
app.indexPage='index.php'
__EOF__
                );
                header( "Location: {$url}Install" );
            }
            
        }catch( Exception $e ){
            die( $e->getMessage() );
        }
        $this->data['site_url'] = site_url();
    }
    
    public function index(){
        $this->smarty->assign( 'DATA' , $this->data );
        return $this->view( __METHOD__ );
    }

    public function checkenv(){
        $install = new SystemInit();
        $install->run();
        $this->data['checkResult'] = $install->checkResult;
        $this->smarty->assign( 'DATA' , $this->data );
        return $this->view( __METHOD__ );
        
    }
    
    public function execute(){
    
    }
}