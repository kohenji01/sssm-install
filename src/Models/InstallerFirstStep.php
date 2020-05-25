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

use Exception;

class InstallerFirstStep {
    
    private const envFileTemplate =<<<_EOF_
#--------------------------------------------------------------------
# sssm Environment Configuration file
#--------------------------------------------------------------------

#--------------------------------------------------------------------
# ENVIRONMENT
#--------------------------------------------------------------------

CI_ENVIRONMENT = production

sssm.sysname = '##sssm.sysname##'
sssm.login_id_validation = '##sssm.login_id_validation##'
sssm.login_pw_validation = '##sssm.login_pw_validation##'

#--------------------------------------------------------------------
# APP
#--------------------------------------------------------------------

app.baseURL = '##app.baseURL##'
app.indexPage = '##app.baseURL##'

app.sessionDriver = '##app.sessionDriver##'
app.sessionCookieName = 'sssm_session'
app.sessionSavePath = 'z_sssm_sessions'

#--------------------------------------------------------------------
# DATABASE
#--------------------------------------------------------------------

database.default.hostname = ##database.default.hostname##
database.default.database = ##database.default.database##
database.default.username = ##database.default.username##
database.default.password = ##database.default.password##
database.default.DBDriver = ##database.default.DBDriver##
_EOF_;

    
    private const envFileFirstTemplate =<<<_EOF_
# Environment params build by sssm.
CI_ENVIRONMENT = development
app.baseURL = '##app.baseURL##'
app.indexPage = '##app.baseURL##'

_EOF_;

    public $defaultBaseUrl = '';
    
    public function __construct(){
        $this->defaultBaseUrl = $_ENV['app.baseURL'] ?? ( "http" . ( $_SERVER['HTTPS'] ? 's' : '' ) . "://" . $_SERVER['SERVER_NAME'] . "/" );
    }
    
    public function run(){
    
    }
    
}