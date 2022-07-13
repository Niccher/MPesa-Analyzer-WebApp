<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (is_file(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

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
//$routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Auths::index');
$routes->get('/home', 'Home::index');

$routes->group('auth', ['namespace' => 'IonAuth\Controllers'], function ($routes) {
    $routes->get('auth/login', 'Auth::login');
    $routes->get('auth/logout', 'Auth::logout');
    $routes->add('auth/forgot_password', 'Auth::forgot_password');
    $routes->get('auth/', 'Auth::index');
    $routes->add('auth/create_user', 'Auth::create_user');
    $routes->add('auth/edit_user/(:num)', 'Auth::edit_user/$1');
    $routes->add('auth/create_group', 'Auth::create_group');
    $routes->get('auth/activate/(:num)', 'Auth::activate/$1');
    $routes->get('auth/activate/(:num)/(:hash)', 'Auth::activate/$1/$2');
    $routes->add('auth/deactivate/(:num)', 'Auth::deactivate/$1');
    $routes->get('auth/reset_password/(:hash)', 'Auth::reset_password/$1');
    $routes->post('auth/reset_password/(:hash)', 'Auth::reset_password/$1');
});

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
