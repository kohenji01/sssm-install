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

use Config\Database;
use Sssm\Base\API\SssmApiBase;
use Sssm\Base\Config\SssmLang;
use Exception;

class DBInit extends SystemInit{
    
    use SssmApiBase;
    
    protected $DBDriver;
    protected $hostname;
    protected $port;
    protected $database;
    protected $username;
    protected $password;
    protected $DBPrefix;
    protected $charset;
    protected $DBCollat;
    
    public $apiEnable = true;
    public $apiExecutable = [
        'connectionTest' ,
        'userTest' ,
        'progressTestExec',
    ];
    public $apiBackGroundExecutable = [
        'progressTestExec',
    ];
    public $accessFromAPI = false;
    
    public $apiOutputType = 'json';
    
    private $check_params = [
        'db_DBDriver',
        'db_hostname',
        'db_port',
        'db_database',
        'db_username',
        'db_password',
        'db_DBPrefix',
        'db_charset',
        'db_DBCollat',
    ];
    
    /** @noinspection PhpUnused */
    /**
     * @param array $params
     * @return mixed
     */
    public function userTest( $params = [] ){
        return $this->connectionTest( $params , true );
    }
    
    /**
     * @param array $params
     * @param bool $user_test
     * @return mixed
     */
    public function connectionTest( $params = [] , $user_test = false ){
        try{
            
            if( $this->accessFromAPI ){
                $params = $_POST;
            }
            
            $conf = [
                'hostname' => $params['db_hostname'] ,
                'username' => $params['db_username'] ,
                'DBDriver' => $params['db_DBDriver'] ,
            ];
            
            if( isset( $params['db_password'] ) && $params['db_password'] != "" ){
                $conf['password'] = $params['db_password'];
            }
            
            switch( strtolower( $this->DBDriver ) ){
                case 'mysqli':
                case 'mysql':
                    $conf['charset']  = $params['db_charset'];
                    $conf['DBCollat'] = $params['db_DBCollat'];
                    break;
            }
            
            if( !$user_test ){
                $forge = Database::Forge( $conf );
                $forge->createDatabase( $params['db_database'] , true );
                $conf['database'] = $params['db_database'];
                $forge = Database::Forge( $conf );
            }else{
                $forge = Database::Forge( $conf );
            }
            $forge->getConnection();
            $this->checkResult['db_connection_test']['result'] = true;
        }catch( Exception $e ){
            $this->checkResult['db_connection_test']['result'] = false;
            $this->checkResult['db_connection_test']['message'] = $e->getMessage();
        }
        
        return $this->checkResult['db_connection_test']['result'];
    }
    
    
    /** @noinspection PhpUnused */
    /**
     * @param array $params
     * @throws Exception
     */
    protected function setParam( $params = [] ){
        try{
            $this->hostname = $params['db_hostname'] ?? $_ENV['database.default.hostname'] ?? NULL;
            $this->database = $params['db_database'] ?? $_ENV['database.default.database'] ?? NULL;
            $this->username = $params['db_username'] ?? $_ENV['database.default.username'] ?? NULL;
            $this->password = $params['db_password'] ?? $_ENV['database.default.password'] ?? NULL;
            $this->DBDriver = $params['db_DBDriver'] ?? $_ENV['database.default.DBDriver'] ?? NULL;
            $this->charset  = $params['db_charset']  ?? $_ENV['database.default.charset']  ?? NULL;
            $this->DBCollat = $params['db_DBCollat'] ?? $_ENV['database.default.DBCollat'] ?? NULL;
            $this->DBPrefix = $params['db_DBPrefix'] ?? $_ENV['database.default.DBPrefix'] ?? NULL;
        
            foreach( $this->check_params as $item ){
                if( $this->$item === '' ){
                    $error = new SssmLang();
                    throw new Exception( $error->systemErrorMessage[$error::INSTALLER_INVALID_PARAMS] , $error::INSTALLER_INVALID_PARAMS );
                }
            }
        }catch( Exception $e ){
            throw $e;
        }
    }
    
    /** @noinspection PhpUnused */
    /**
     * @return bool
     * @throws Exception
     */
    public function createDB(){
        try{
            $conf = [
                'hostname' => $this->hostname ,
                'username' => $this->username ,
                'password' => $this->password ,
                'DBDriver' => $this->DBDriver ,
            ];
            
            switch( strtolower( $this->DBDriver ) ){
                case 'mysqli':
                case 'mysql':
                    $conf['charset']  = $this->charset;
                    $conf['DBCollat'] = $this->DBCollat;
                    break;
            }
        
            $forge = Database::Forge( $conf );
            $forge->createDatabase( $this->database );
        }catch( Exception $e ){
            throw $e;
        }
        return true;
    }
    
    /** @noinspection PhpUnused */
    /**
     * @return bool
     * @throws Exception
     */
    public function dropDB(){
        try{
            $forge = Database::Forge();
            $forge->dropDatabase( $this->database );
        }catch( Exception $e ){
            throw $e;
        }
        return true;
    }
    
    
    
}