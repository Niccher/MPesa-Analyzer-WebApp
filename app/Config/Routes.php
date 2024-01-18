<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
//$routes->get('/', 'Auths::index');
$routes->get('/', 'Home::index');
$routes->get('/home', 'Home::index');

$routes->group('auth', function ($routes) {
    $routes->add('login', 'Auths::login');
    $routes->add('register', 'Auths::register');
    $routes->add('search', 'Auths::user_info');
    $routes->add('logout', 'Auths::user_logout');
});

$routes->group('auth', function ($routes) {
    $routes->add('auth_login', 'UserAuth::user_login');
    $routes->add('auth_register', 'UserAuth::user_register');
});

$routes->add('/process/upload', 'Upload::upload');
$routes->add('/process/device', 'Upload::device_print');
$routes->add('/process/get/my_uploads', 'Upload::upload_listing');
$routes->add('/process/get/my_uploads_count', 'Upload::loot_uploaded_count');
$routes->add('/process/get/my_uploads_category_count', 'Upload::loot_uploaded_category_count');

$routes->add('/process/get/my_summary', 'Upload::upload_summary');
$routes->add('/process/get/my_summary_calculations', 'Upload::upload_summary_calculation');
$routes->add('/process/get/list_all_sms_in_category', 'Upload::list_all_sms_in_category');

$routes->add('/process/test', 'Testar::random');

//service('auth')->routes($routes);##Disable Shield

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
