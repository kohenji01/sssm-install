<?php
/**
 * =============================================================================================
 *  Project: sssm-core
 *  File: Routes.php
 *  Date: 2020/05/21 19:19
 *  Author: Shoji Ogura <kohenji@sarahsytems.com>
 *  Copyright (c) 2020. Shoji Ogura
 *  This software is released under the MIT License, see LICENSE.txt.
 * =============================================================================================
 */

$routes->group('', ['namespace' => 'Sssm\Install\Controllers'], function($routes) {
    $routes->get('install', 'Install::index');
    $routes->get('install/(:any)', 'Install::$1');
});
