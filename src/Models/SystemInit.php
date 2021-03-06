<?php
/** @noinspection PhpUnused */
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
use Config\Services;
use Exception;
use Matriphe\ISO639\ISO639;
use function Sssm\Helpers\replace_kwd;

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
    
    private $languageDirs = [
        VENDORPATH . 'codeigniter4' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR . 'Language' . DIRECTORY_SEPARATOR ,
        VENDORPATH . 'codeigniter4' . DIRECTORY_SEPARATOR . 'translations' . DIRECTORY_SEPARATOR . 'Language' . DIRECTORY_SEPARATOR ,
        APPPATH . 'Language' . DIRECTORY_SEPARATOR ,
    ];
    
    private $extraLangName = [
        'ja'    => '日本語' ,
        'pt-BR' => 'Português brasileiro' ,
        'zh-TW' => '正體中文 (繁體)' ,
        'zh-CN' => '中文 (简体)' ,
    ];
    
    public $envBool = [
        'true'  => 'True' ,
        'false' => 'False' ,
    ];
    
    public $OK = "success";
    public $NG = "danger";
    
    public $lang;
    public $checkResult = [];
    
    public $exists_env = false;
    public $writable_env = false;
    
    public $baseUrl = '';
    public $indexPage = '';
    public $defaultLocale = 'ja';
    public $timeZoneList = '';
    public $localeList = '';
    
    public $db_DBDriver = 'MySQLi';
    public $db_hostname = 'localhost';
    public $db_port     = 3306;
    public $db_username = 'sssm';
    public $db_database = 'sssm';
    public $db_DBPrefix = 'sssm_';
    public $db_charset  = 'utf8mb4';
    public $db_DBCollat = 'utf8mb4_general_ci';
    
    public $validEnv = false;
    
    public $catchedException;
    
    private const envFileTemporaryTemplate =<<<_EOF_
# Install temporary Environment params build by sssm.
CI_ENVIRONMENT = development
sssm.sysname = '##sysname##'
sssm.module_ini_file = 'sssm_module.ini'
app.baseURL = '##baseURL##'
app.indexPage = '##indexPage##'
app.defaultLocale = '##defaultLocale##'
app.negotiateLocale = ##negotiateLocale##
@@supportedLocales@@
app.appTimezone = '##appTimezone##'
_EOF_;
    
    private const envFileTemplate =<<<_EOF_
#--------------------------------------------------------------------
# sssm Environment Configuration file
# Created by sssm. ##date_time##
#--------------------------------------------------------------------

#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = production

sssm.sysname = '##sssm.sysname##'
sssm.module_ini_file = 'sssm_module.ini'
sssm.login_id_validation = '##sssm.login_id_validation##'
sssm.login_pw_validation = '##sssm.login_pw_validation##'

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.baseURL = '##app.baseURL##'
app.indexPage = '##app.baseURL##'

app.defaultLocale = '##app.defaultLocale##'
app.negotiateLocale = ##app.negotiateLocale##
@@app.supportedLocales@@
app.appTimezone = '##app.appTimezone##'

app.sessionDriver = '##app.sessionDriver##'
app.sessionCookieName = '##app.sessionCookieName##'
app.sessionSavePath = '##app.sessionSavePath##'

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

database.default.DBDriver = ##database.default.DBDriver##
database.default.hostname = ##database.default.hostname##
database.default.database = ##database.default.database##
database.default.username = ##database.default.username##
@@database.default.port@@
@@database.default.password@@
@@database.default.Prefix@@
@@database.default.charset@@
@@database.default.DBCollat@@
_EOF_;
    
    
    
    public function __construct( ConnectionInterface &$db = null , ValidationInterface $validation = null ){
        parent::__construct( $db , $validation );
    
        if( $this->envFile != '' ){
            $this->checkEnvFiles = [
                $this->envFile ,
            ];
        }
    
        $this->checkResult['writable_success'] = 0;
        $this->checkResult['writable'] = [];
    
        $this->checkResult['checkEnvVarSet_success'] = 0;
        $this->checkResult['checkEnvVarSet'] = [];
        $this->checkResult['checkEnvVarList'] = [];
    
        $this->exists_env = file_exists( $this->envFile );
        $this->writable_env = is_writable( $this->envFile );
    
        if( !is_cli() ){
            $this->baseUrl = $_ENV['app.baseUrl'] ?? ( empty( $_SERVER['HTTPS'] ) ? 'http://' : 'https://' ) . $_SERVER['HTTP_HOST'] . dirname( $_SERVER['SCRIPT_NAME'] );
            $this->indexPage = basename( $_SERVER['SCRIPT_FILENAME'] );
        }
    
        $this->localeList = $this->getAllLocaleList();
        $this->timeZoneList = '"' . implode( '","' , timezone_identifiers_list() ) . '"';
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
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'Modules' . DIRECTORY_SEPARATOR ,
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'Module_info' . DIRECTORY_SEPARATOR ,
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'Menus' . DIRECTORY_SEPARATOR ,
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'Installed' . DIRECTORY_SEPARATOR ,
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'Skel' . DIRECTORY_SEPARATOR ,
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'Language' . DIRECTORY_SEPARATOR ,
                WRITEPATH . $_ENV['sssm.sysname'] . DIRECTORY_SEPARATOR . 'ProgressBar' . DIRECTORY_SEPARATOR ,
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
    
            $this->checkEnvVars();
    
            
        }catch( Exception $e ){
            throw $e;
        }
        return true;
    }
    
    /**
     * @throws Exception
     */
    private function checkEnvVars(){
        try{
            $validation = [];
            foreach( array_keys( $this->validationRulesEnv ) as $item ){
                if( isset( $_ENV[$item] ) ){
                    $this->checkResult['checkEnvVarSet'][$item] = $this->OK;
                    $this->checkResult['checkEnvVarList'][$item] = $_ENV[$item];
                    $this->checkResult['checkEnvVarSet_success']++;
                }else{
                    $this->checkResult['checkEnvVarList'][$item] = '';
                    $this->checkResult['checkEnvVarSet'][$item] = $this->NG;
                }
            }
    
            if( $this->checkResult['checkEnvVarSet_success'] == count( $this->validationRulesEnv ) ){
                $this->checkResult['checkEnvVarSet_success'] = true;
            }else{
                $this->checkResult['checkEnvVarSet_success'] = false;
            }
            
            $validation = Services::validation();
            //ドット区切りのNameは多次元配列に判断されてしまうので、エンティティに置換して判断する
            $data = [];
            foreach( $this->validationRulesEnv as $field => $rules ){
                $replaced_field = str_replace( '.' , '&#046;' , $field );
                $data[$replaced_field] = $_ENV[$field] ?? null;
                $validation->setRule( $replaced_field , null , $rules );
            }
            if( !$validation->run($data) ){
                throw new Exception( 'Validation Error' );
            }
            $this->checkResult['checkEnvVars']['result'] = $this->OK;
            $this->checkResult['checkEnvVars']['message'] = [];
        }catch( Exception $e ){
            $this->checkResult['checkEnvVars']['result'] = $this->NG;
            foreach( $validation->getErrors() as $field => $message ){
                $field = str_replace( '&#046;' , '.' , $field );
                $message = str_replace( '&#046;' , '.' , $message );
                $this->checkResult['checkEnvVars']['message'][$field] = $message;
            }
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
     * @param bool $actual
     * @throws Exception
     */
    public function saveEnvFile( $actual = false ){
        try{
            
            $_POST['baseURL'] = $_POST['baseURL'] ?? '';
            $_POST['indexPage'] = $_POST['indexPage'] ?? '';
    
            if( $_POST['baseURL'] == '' ){
                throw new Exception( "app.baseURL is required" );
            }
            
            if( $actual ){
                $contents = replace_kwd( self::envFileTemplate , $_ENV );
                $contents = replace_kwd(
                    $contents ,
                    [ 'app.supportedLocales' => $this->getArrayToEnvList( 'app.supportedLocales' , [] , 'app.supportedLocales' ) ] ,
                    false ,
                    "@@"
                );
                $contents = replace_kwd(
                    $contents ,
                    [ 'database.default.port' => $this->replaceEnvIfExists( 'database.default.port' , $_ENV['database.default.port'] ) ] ,
                    false ,
                    "@@"
                );
                $contents = replace_kwd(
                    $contents ,
                    [ 'database.default.password' => $this->replaceEnvIfExists( 'database.default.password' , $_ENV['database.default.password'] ) ] ,
                    false ,
                    "@@"
                );
                $contents = replace_kwd(
                    $contents ,
                    [ 'database.default.Prefix' => $this->replaceEnvIfExists( 'database.default.Prefix' , $_ENV['database.default.Prefix'] ) ] ,
                    false ,
                    "@@"
                );
                $contents = replace_kwd(
                    $contents ,
                    [ 'database.default.charset' => $this->replaceEnvIfExists( 'database.default.charset' , $_ENV['database.default.charset'] ) ] ,
                    false ,
                    "@@"
                );
                $contents = replace_kwd(
                    $contents ,
                    [ 'database.default.DBCollat' => $this->replaceEnvIfExists( 'database.default.DBCollat' , $_ENV['database.default.DBCollat'] ) ] ,
                    false ,
                    "@@"
                );
            }else{
                $contents = replace_kwd( self::envFileTemporaryTemplate , $_POST );
                $contents = replace_kwd(
                    $contents ,
                    [ 'supportedLocales' => $this->getArrayToEnvList( 'app.supportedLocales' , $_POST['supportedLocales'] ) ] ,
                    false ,
                    "@@"
                );
            }
            
            file_put_contents( ROOTPATH . '.env' , $contents );
    
            $this->checkResult['save_env_file'] = $this->OK;
            $this->checkResult['save_env_file_success'] = true;
        }catch( Exception $e ){
            $this->checkResult['save_env_file'] = $this->NG;
            $this->checkResult['save_env_file_success'] = false;
            $this->checkResult['save_env_file_message'] = $e->getMessage();
            throw $e;
        }
    }
    
    protected function getArrayToEnvList( $name , $data = [] , $type_env = '' ){
        $ret = '';
        
        if( $type_env !== '' ){
            $data = $this->convertEnvToArr( $type_env );
        }
        
        foreach( $data as $key => $value ){
            if( $value == '' ){
                continue;
            }
            $real_data[$value] = $key;
        }
        if( isset( $real_data ) ){
            ksort( $real_data , SORT_NATURAL );
            $real_data = array_values( $real_data );
            foreach( $real_data as $key => $value ){
                $ret .= "{$name}.{$key} = '{$value}'\n";
            }
        }
        return mb_substr( $ret , 0 , -1 );
    }
    
    protected function replaceEnvIfExists( $name , $var , $quote = '' ){
        if( $var != '' ){
            return "{$name} = {$quote}{$var}{$quote}";
        }
        return '' ;
    }
    
    
    protected function convertEnvToArr( $kwd ){
        $ret = [];
        try{
            if( !is_array( $_ENV ) ){
                throw new Exception();
            }
            
            $kwd_len = mb_strlen( $kwd );
            
            foreach( $_ENV as $key => $value ){
                if( mb_substr( $key , 0 , $kwd_len ) !== $kwd ){
                    continue;
                }
                $subj = mb_substr( $key , $kwd_len );
                $ret[$subj] = $value;
            }
            
        }catch( Exception $e ){
            $ret = [];
        }
        
        return $ret;
    }
    
    protected function getAllLocaleList(){
    
        $ret = [];
        $availableLanguage = $this->getTranslationDirList();
        
        $ISO639 = new ISO639();
        
        foreach( $availableLanguage as $language ){
            $ret[$language] = $ISO639->nativeByCode1( $language );
            if( isset( $this->extraLangName[$language] ) ){
                $ret[$language] = $this->extraLangName[$language];
            }
        }
        
        return $ret;
    }
    
    protected function getTranslationDirList(){
        $ret=[];
        foreach( $this->languageDirs as $dir ){
            if( is_dir( $dir ) ){
                $handle = opendir( $dir );
                while( false !== ( $readdir = readdir( $handle ) ) ){
                    if( $readdir != '.' && $readdir != '..' && !is_file( $dir . $readdir ) ){
                        $ret[] = $readdir;
                    }
                }
                closedir( $handle );
            }
        }
        return $ret;
    }
    
    
}