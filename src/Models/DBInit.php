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
use Sssm\Base\Config\SssmLang;
use Exception;

class DBInit extends SystemInit{
    
    protected $hostname;
    protected $database;
    protected $username;
    protected $password;
    protected $DBDriver;
    protected $charset;
    protected $DBCollat;
    
    private $check_params = [
        'hostname',
        'database',
        'username',
        'password',
        'DBDriver',
    ];

    public function __construct( $params = [] ){
        parent::__construct();
        
        try{
            $this->hostname = $params['hostname'] ?? $_ENV['database.default.hostname'] ?? '';
            $this->database = $params['database'] ?? $_ENV['database.default.database'] ?? '';
            $this->username = $params['username'] ?? $_ENV['database.default.username'] ?? '';
            $this->password = $params['password'] ?? $_ENV['database.default.password'] ?? '';
            $this->DBDriver = $params['DBDriver'] ?? $_ENV['database.default.DBDriver'] ?? '';
            $this->charset  = $params['charset']  ?? $_ENV['database.default.charset']  ?? '';
            $this->DBCollat = $params['DBCollat'] ?? $_ENV['database.default.DBCollat'] ?? '';
    
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