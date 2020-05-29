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

use CodeIgniter\Model;
use Exception;

class SystemInit extends Model{
    
    private $checkDirectories = [];
    
    private $checkFiles = [];
    
    private $envFile = ROOTPATH . '.env';
    
    private $checkEnvFiles = [];
    
    private $validationRulesEnv = [
        'sssm.sysname'  => 'required|alpha_numeric|min_length[3]' ,
        'app.baseURL'   => 'required|valid_url' ,
        'app.indexPage' => 'if_exist' ,
    ];
    
    
    public $OK = "success";
    public $NG = "danger";
    
    public $lang;
    public $checkResult = [];
    
    public $exists_env = false;
    public $writable_env = false;
    
    public $baseUrl = '';
    public $indexPage = '';
    
    public $validEnv = false;
    
    public function __construct( ConnectionInterface &$db = null , ValidationInterface $validation = null ){
        parent::__construct( $db , $validation );
    
        $this->checkEnvFiles = [
            $this->envFile ,
        ];
    
        $this->checkResult['writable_success'] = 0;
        $this->checkResult['writable'] = [];
    
        $this->exists_env = file_exists( $this->envFile );
        $this->writable_env = is_writable( $this->envFile );
    
        $this->baseUrl = $_ENV['app.baseUrl'] ?? ( empty( $_SERVER['HTTPS'] ) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['SCRIPT_NAME'] );
        $this->indexPage = basename( $_SERVER['SCRIPT_FILENAME'] );

    }
    
    /**
     * 書込権限チェックディレクトリの初期化
     * @throws Exception
     */
    private function setCheckDirectories(){
        try{
            $this->checkDirectories = [
                WRITEPATH ,
                WRITEPATH . $_ENV['sssm.sysname'] ,
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'module_info' . DIRECTORY_SEPARATOR,
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'menus' . DIRECTORY_SEPARATOR,
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'installed' . DIRECTORY_SEPARATOR,
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'skel' . DIRECTORY_SEPARATOR,
            ];
        }catch( Exception $e ){
            throw $e;
        }
    }
    
    /**
     * ファイルチェックの実行
     * @return bool
     * @throws Exception
     */
    public function runCheckWritable(){
        try{
    
            $this->setCheckDirectories();
            $this->checkDirectories( $this->checkDirectories );
            $this->checkFiles( $this->checkFiles );
            
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
    
    /**
     * 環境ファイルチェックの実行
     * @return bool
     * @throws Exception
     */
    public function runCheckEnv(){
        try{
            $this->checkFiles( $this->checkEnvFiles );
            
            if( $this->checkResult['writable_success'] == count( $this->checkEnvFiles ) ){
                $this->checkResult['writable_success'] = true;
            }else{
                $this->checkResult['writable_success'] = false;
            }
        }catch( Exception $e ){
            throw $e;
        }
        return true;
    }
    
    public function runEnvValidation(){
        $this->checkEnvVars();
    }
    
    private function checkEnvVars(){
        try{
            foreach( array_keys( $this->validationRulesEnv ) as $item ){
                if( !isset( $_ENV[$item] ) ){
                    throw new Exception( ".env var {$item} is not defined." );
                }
            }
    
            $validation = \Config\Services::validation();
//            $validation->setRules( $this->validationRulesEnv );
            foreach( $this->validationRulesEnv as $field => $rules ){
                $replaced_field = str_replace( '.' , '-' , $field );
                $data[$replaced_field] = $_ENV[$field];
                $validation->setRule( $replaced_field , null , $rules );
            }
            if( !$validation->run(  ) ){
                echo "<pre>" . print_r( $validation->getErrors() , true ) .print_r( $_ENV , true ) . "</pre>";
            }
            
        }catch( Exception $e ){
            die( $e->getMessage() );
            throw $e;
        }
    }
    
    /**
     * ファイル群の書込権限チェック
     * @param array $list
     * @throws Exception
     */
    private function checkFiles( $list = [] ){
        try{
            foreach( $list as $file ){
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
    
    /**
     * ディレクトリ群の書込権限チェック
     * @param array $list
     * @return bool
     * @throws Exception
     */
    private function checkDirectories( $list = [] ){
        try{
            foreach( $list as $directory ){
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
    
    /**
     * .envファイルの書込
     * @throws Exception
     */
    public function save_env_file(){
        try{
        
            $_POST['baseURL'] = $_POST['baseURL'] ?? '';
            $_POST['indexPage'] = $_POST['indexPage'] ?? '';
    
            if( $_POST['baseURL'] == '' ){
                throw new Exception( "baseURL is required" );
            }
    
            file_put_contents( ROOTPATH . '.env' , <<<__EOF__
# Install temporary Environment params build by sssm.
CI_ENVIRONMENT = development
sssm.sysname = 'sssm'
app.baseURL = '{$_POST['baseURL']}'
app.indexPage='{$_POST['indexPage']}'
__EOF__
            );
    
            $this->checkResult['save_env_file'] = $this->OK;
            $this->checkResult['save_env_file_success'] = true;
        }catch( Exception $e ){
            $this->checkResult['save_env_file'] = $this->NG;
            $this->checkResult['save_env_file_success'] = false;
            $this->checkResult['save_env_file_message'] = $e->getMessage();
            throw $e;
        }
    }
    
}