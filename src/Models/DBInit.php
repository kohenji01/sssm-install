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
use CodeIgniter\Validation\ValidationInterface;
use Config\Database;
use Sssm\Base\API\SssmApiBaseTrait;
use Exception;
use function Sssm\Helpers\replace_kwd;

class DBInit extends SystemInit{
    
    use SssmApiBaseTrait;
    
    protected $DBDriver;
    protected $hostname;
    protected $port;
    protected $database;
    protected $username;
    protected $password;
    protected $DBPrefix;
    protected $charset;
    protected $DBCollat;
    
    private $forge_conf =[];
    
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
    
    private $initialTables = [
        'sessions' => [
            'comment' => 'セッション' ,
        ] ,
        'modules' => [
            'comment' => 'モジュール' ,
        ],
    ];

    private $initialSchema = [
        'sessions' => [
            'id' => [
                'type'              => 'VARCHAR' ,
                'constraint'        => 128 ,
                'null'              => false ,
                'comment'           => 'ID' ,
            ] ,
            'ip_address' => [
                'type'              => 'VARCHAR' ,
                'constraint'        => 45 ,
                'null'              => false ,
                'comment'           => 'IPアドレス' ,
            ] ,
            'timestamp' => [
                'type'              => 'INT' ,
                'constraint'        => 10 ,
                'unsigned'          => true ,
                'null'              => false ,
                'default'           => 0 ,
                'comment'           => 'タイムスタンプ' ,
            ] ,
            'data' => [
                'type'              => 'BLOB' ,
                'null'              => false ,
                'comment'           => 'データ' ,
            ] ,
        ],
        'modules' => [
            'id' => [
                'type'              => 'INT' ,
                'unsigned'          => true ,
                'auto_increment'    => true ,
                'null'              => false ,
                'comment'           => 'モジュールID' ,
            ] ,
            'name' => [
                'type'              => 'VARCHAR' ,
                'constraint'        => 64 ,
                'null'              => false ,
                'comment'           => 'モジュール名' ,
            ] ,
            'namespace' => [
                'type'              => 'VARCHAR' ,
                'constraint'        => 255 ,
                'null'              => false ,
                'comment'           => '名前空間' ,
            ] ,
            'path' => [
                'type'              => 'VARCHAR' ,
                'constraint'        => 255 ,
                'null'              => false ,
                'comment'           => 'パス' ,
            ] ,
        ] ,
    ];
    
    private $initialPrimaryKey = [
        'sessions' => [
            'id' ,
        ],
        'modules' => [
            'id' ,
        ],
    ];
    
    private $initialKey = [
        'sessions' => [
            'timestamp' ,
        ],
        'modules' => [
            'namespace' ,
            'path' ,
        ],
    ];
    
    private $initialForeignKey = [];
    
    private $initialUniqueKey = [];
    
    public $sessionDriver = 'CodeIgniter\Session\Handlers\DatabaseHandler';
    public $sessionCookieName = 'sssm_session';
    public $sessionSavePath = 'sessions';
    
    private const databaseEnvTemplate = <<<_EOL_

database.default.DBDriver = '##db_DBDriver##'
database.default.hostname = '##db_hostname##'
database.default.database = '##db_database##'
database.default.username = '##db_username##'
@@db_port@@
@@db_password@@
@@db_DBPrefix@@
@@db_charset@@
@@db_DBCollat@@
_EOL_;
    
    private const sessionEnvTemplate = <<<_EOL_

app.sessionDriver = '##sessionDriver##'
app.sessionCookieName = '##sessionCookieName##'
app.sessionSavePath = '##sessionSavePath##'
_EOL_;

    
    protected $table = 'dummy';
    
    public function __construct( ConnectionInterface &$db = null , ValidationInterface $validation = null ){
        parent::__construct( $db , $validation );
        $this->setApiExecutable( [
            'connectionTest' ,
            'userTest' ,
            'test' ,
        ] );
    }
    
    /**
     * @throws Exception
     */
    public function createTables(){
        try{
            $this->setForgeConf();
            $forge = Database::Forge( $this->getForgeConf( true ) );
            foreach( $this->initialTables as $table => $attr ){
                if( isset( $this->initialSchema[$table] ) ){
                    
                    $forge->addField( $this->initialSchema[$table] );
                    if( isset( $this->initialKey[$table] ) && count( $this->initialKey[$table] ) > 0 ){
                        $forge->addKey( $this->initialKey[$table] );
                    }
                    if( isset( $this->initialPrimaryKey[$table] ) && count( $this->initialPrimaryKey[$table] ) > 0  ){
                        $forge->addPrimaryKey( $this->initialPrimaryKey[$table] );
                    }
                    if( isset( $this->initialForeignKey[$table] ) && count( $this->initialForeignKey[$table] ) > 0 ){
                        $forge->addForeignKey( $this->initialForeignKey[$table] );
                    }
                    if( isset( $this->initialUniqueKey[$table] ) && count( $this->initialUniqueKey[$table] ) > 0 ){
                        $forge->addUniqueKey( $this->initialUniqueKey[$table] );
                    }
                    $forge->createTable( $table , true , $attr );
                }
            }
        }catch( Exception $e ){
            throw $e;
        }
    }
    
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

            $this->setForgeConf( $params );
            $conf = $this->getForgeConf();

            file_put_contents( WRITEPATH . __FUNCTION__ , print_r( $conf , true ) );
            
            if( !$user_test ){
                $forge = Database::Forge( $conf );
                $forge->createDatabase( $params['db_database'] , true );
                $conf['database'] = $params['db_database'];
            }
    
            $forge = Database::Forge( $conf );
    
    
            $forge->getConnection();
            $this->checkResult['db_connection_test']['result'] = true;
        }catch( Exception $e ){
            $this->checkResult['db_connection_test']['result'] = false;
            $this->checkResult['db_connection_test']['message'] = $e->getMessage();
        }
        
        return $this->checkResult['db_connection_test']['result'];
    }
    

    private function setForgeConf( $params = [] ){
        $this->hostname = $params['db_hostname'] ?? $_ENV['database.default.hostname'] ?? NULL;
        $this->database = $params['db_database'] ?? $_ENV['database.default.database'] ?? NULL;
        $this->username = $params['db_username'] ?? $_ENV['database.default.username'] ?? NULL;
        $this->password = $params['db_password'] ?? $_ENV['database.default.password'] ?? NULL;
        $this->DBDriver = $params['db_DBDriver'] ?? $_ENV['database.default.DBDriver'] ?? NULL;
        $this->charset  = $params['db_charset']  ?? $_ENV['database.default.charset']  ?? NULL;
        $this->port     = $params['db_port']     ?? $_ENV['database.default.port']     ?? NULL;
        $this->DBCollat = $params['db_DBCollat'] ?? $_ENV['database.default.DBCollat'] ?? NULL;
        $this->DBPrefix = $params['db_DBPrefix'] ?? $_ENV['database.default.DBPrefix'] ?? NULL;
    }

    /** @noinspection PhpUnused */
    /**
     * @param bool $database
     * @return array
     */
    protected function getForgeConf( $database = false ){
        $conf = [
            'hostname' => $this->hostname ,
            'username' => $this->username ,
            'DBDriver' => $this->DBDriver ,
        ];
    
        if( $this->password != "" ){
            $conf['password'] = $this->password;
        }
    
        if( $this->port != "" ){
            $conf['port'] = $this->port;
        }
    
        if( $this->DBPrefix != "" ){
            $conf['DBPrefix'] = $this->DBPrefix;
        }
    
        if( $database ){
            $conf['database'] = $this->database;
        }
    
        switch( strtolower( $this->DBDriver ) ){
            case 'mysqli':
            case 'mysql':
                $conf['charset']  = $this->charset;
                $conf['DBCollat'] = $this->DBCollat;
                break;
        }
        
        return $conf;
    }
    
    /** @noinspection PhpUnused */
    /**
     * @return bool
     * @throws Exception
     */
    public function createDB(){
        try{
            $forge = Database::Forge( $this->getForgeConf() );
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
            $forge = Database::Forge( $this->getForgeConf( true ) );
            $forge->dropDatabase( $this->database );
        }catch( Exception $e ){
            throw $e;
        }
        return true;
    }
    
    
    /**
     * @throws Exception
     */
    public function saveDbInfo(){
        try{
            
            $contents = replace_kwd( self::databaseEnvTemplate , $_POST );

            $contents = replace_kwd(
                $contents ,
                [ 'db_port' => $this->replaceEnvIfExists( 'database.default.port' , $_POST['db_port'] ) ] ,
                false ,
                "@@"
            );
            $contents = replace_kwd(
                $contents ,
                [ 'db_password' => $this->replaceEnvIfExists( 'database.default.password' , $_POST['db_password'] , "'" ) ] ,
                false ,
                "@@"
            );
            $contents = replace_kwd(
                $contents ,
                [ 'db_DBPrefix' => $this->replaceEnvIfExists( 'database.default.DBPrefix' , $_POST['db_DBPrefix'] , "'" ) ] ,
                false ,
                "@@"
            );
            $contents = replace_kwd(
                $contents ,
                [ 'db_charset' => $this->replaceEnvIfExists( 'database.default.charset' , $_POST['db_charset'] , "'" ) ] ,
                false ,
                "@@"
            );
            $contents = replace_kwd(
                $contents ,
                [ 'db_DBCollat' => $this->replaceEnvIfExists( 'database.default.DBCollat' , $_POST['db_DBCollat'] , "'" ) ] ,
                false ,
                "@@"
            );
    
            file_put_contents( ROOTPATH . '.env' , $contents , FILE_APPEND );
    
            $this->checkResult['save_db_info'] = $this->OK;
            $this->checkResult['save_db_info_success'] = true;
        }catch( Exception $e ){
            $this->checkResult['save_db_info'] = $this->NG;
            $this->checkResult['save_db_info_success'] = false;
            $this->checkResult['save_db_info_message'] = $e->getMessage();
            throw $e;
        }
        return true;
    }
    
    /**
     * @throws Exception
     */
    public function saveSessionInfo(){
        try{
        
            $param = $_POST;
            $param['sessionDriver'] = $this->sessionDriver;
            $param['sessionSavePath'] = $this->sessionSavePath;
            $param['sessionCookieName'] = $this->sessionCookieName;
        
            $contents = replace_kwd( self::sessionEnvTemplate , $param );
        
            file_put_contents( ROOTPATH . '.env' , $contents , FILE_APPEND );
        
            $this->checkResult['save_session_info'] = $this->OK;
            $this->checkResult['save_session_info_success'] = true;
        }catch( Exception $e ){
            $this->checkResult['save_session_info'] = $this->NG;
            $this->checkResult['save_session_info_success'] = false;
            $this->checkResult['save_session_info_message'] = $e->getMessage();
            throw $e;
        }
        
    }
    
    public function test(){
        return "ok";
    }
    
}