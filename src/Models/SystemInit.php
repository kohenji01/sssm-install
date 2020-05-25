<?php
/**
 * =============================================================================================
 *  Project: sssm
 *  File: Installer.php
 *  Date: 2020/05/20 10:23
 *  Author: Shoji Ogura <kohenji@sarahsytems.com>
 *  Copyright (c) 2020. SarahSystems lpc.
 *  This software is released under the MIT License, see LICENSE.txt.
 * =============================================================================================
 */

namespace Sssm\Install\Models;

use CodeIgniter\Database\ConnectionInterface;
use CodeIgniter\Model;
use CodeIgniter\Validation\ValidationInterface;
use Exception;

class SystemInit extends Model{
    
    private $checkDirectories = [];
    
    private $checkFiles = [
        ROOTPATH . '.env' ,
    ];
    
    public $OK = "success";
    public $NG = "danger";
    
    public $lang;
    public $checkResult = [];
    
    public function __construct( ConnectionInterface &$db = null , ValidationInterface $validation = null ){
        parent::__construct( $db , $validation );
    
        $this->checkDirectories = [
            WRITEPATH ,
            WRITEPATH . $_ENV['sssm.sysname'] ,
            WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'module_info' . DIRECTORY_SEPARATOR,
            WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'menus' . DIRECTORY_SEPARATOR,
            WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'installed' . DIRECTORY_SEPARATOR,
            WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'skel' . DIRECTORY_SEPARATOR,
            '/home/kohenji/hogehoge',
        ];
        
        $this->checkResult['writable_success'] = 0;
        $this->checkResult['writable'] = [];
    
    }
    
    public function run(){
        try{
            $this->checkDirectories();
            $this->checkFiles();
            
            if( $this->checkResult['writable_success'] == count( $this->checkDirectories ) + count( $this->checkFiles ) ){
                $this->checkResult['writable_success'] = true;
            }else{
                $this->checkResult['writable_success'] = false;
            }
        }catch( Exception $e ){
            throw $e;
        }
        return true;
    }
    
    private function checkFiles(){
        try{
            foreach( $this->checkFiles as $file ){
                if( file_exists( $file ) && is_writable( $file ) ){
                    $this->checkResult['writable'][$file] = $this->OK;
                    $this->checkResult['writable_success']++;
                }else{
                    if( !file_exists( $file ) && is_writable( dirname( $file ) ) ){
                        $this->checkResult['writable'][$file] = $this->OK;
                        $this->checkResult['writable_success']++;
                    }else{
                        $this->checkResult['writable'][$file] = $this->NG;
                    }
                }
            }
        }catch( Exception $e ){
            throw $e;
        }
    }
    
    private function checkDirectories(){
        try{
            foreach( $this->checkDirectories as $directory ){
                if( is_dir( $directory ) ){
                    if( is_writable( $directory ) ){
                        $this->checkResult['writable'][$directory] = $this->OK;
                        $this->checkResult['writable_success']++;
                    }else{
                        $this->checkResult['writable'][$directory] = $this->NG;
                    }
                    continue;
                }
                try{
                    mkdir( $directory );
                    $this->checkResult['writable'][$directory] = $this->OK;
                    $this->checkResult['writable_success']++;
                }catch( Exception $e ){
                    $this->checkResult['writable'][$directory] = $this->NG;
                }
            }
        }catch( Exception $e ){
            throw $e;
        }
        return true;
    }
    
}