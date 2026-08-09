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
$routes->get('/health', 'Home::health');
$routes->get('/dashboard', 'Dash::index', ['filter' => 'session']);
$routes->get('/dashboard/graph', 'Graph::index', ['filter' => 'session']);
$routes->get('/dashboard/search', 'Search::index', ['filter' => 'session']);
$routes->post('/dashboard/rescan', 'Home::rescan', ['filter' => 'session']);
$routes->post('/dashboard/rescan/all', 'Home::rescanAll', ['filter' => 'session']);
$routes->get('/dashboard/rescan/progress', 'Home::progress', ['filter' => 'session']);
$routes->post('/dashboard/device/link', 'Dash::linkDevice', ['filter' => 'session']);
$routes->get('/dashboard/analyse', 'Analyse::index', ['filter' => 'session']);
$routes->post('/dashboard/analyse/rule', 'Analyse::saveRule', ['filter' => 'session']);
$routes->get('/dashboard/errors/test/(:num)', 'Debug::error/$1');
$routes->get('/dashboard/transactions', 'Transactions::index', ['filter' => 'session']);
$routes->get('/dashboard/transactions/export', 'Transactions::export', ['filter' => 'session']);
$routes->get('/dashboard/history', 'History::index', ['filter' => 'session']);
$routes->get('/dashboard/info', 'Info::index', ['filter' => 'session']);
$routes->post('/dashboard/info/generate-token', 'Info::generateToken', ['filter' => 'session']);
$routes->post('/dashboard/info/revoke-token', 'Info::revokeToken', ['filter' => 'session']);

// Settings
$routes->get('/dashboard/settings', 'Settings::index', ['filter' => 'session']);
$routes->get('/dashboard/settings/profile', 'Settings::profile', ['filter' => 'session']);
$routes->post('/dashboard/settings/profile/save', 'Settings::saveProfile', ['filter' => 'session']);
$routes->get('/dashboard/settings/notifications', 'Settings::notifications', ['filter' => 'session']);
$routes->post('/dashboard/settings/notifications/save', 'Settings::saveNotifications', ['filter' => 'session']);
$routes->get('/dashboard/settings/preferences', 'Settings::preferences', ['filter' => 'session']);
$routes->post('/dashboard/settings/preferences/save', 'Settings::savePreferences', ['filter' => 'session']);
$routes->get('/dashboard/settings/security', 'Settings::security', ['filter' => 'session']);
$routes->post('/dashboard/settings/security/revoke-device', 'Settings::revokeDevice', ['filter' => 'session']);
$routes->get('/dashboard/settings/data', 'Settings::data', ['filter' => 'session']);
$routes->post('/dashboard/settings/data/purge', 'Settings::purgeData', ['filter' => 'session']);
$routes->post('/dashboard/settings/data/delete-upload', 'Settings::deleteUpload', ['filter' => 'session']);
$routes->get('/dashboard/settings/export/csv', 'Settings::exportCsv', ['filter' => 'session']);
$routes->get('/dashboard/settings/export/json', 'Settings::exportJson', ['filter' => 'session']);
$routes->get('/dashboard/settings/data/export-settings', 'Settings::exportSettings', ['filter' => 'session']);
$routes->post('/dashboard/settings/data/import-settings', 'Settings::importSettings', ['filter' => 'session']);
$routes->get('/dashboard/settings/data/export-rules', 'Settings::exportCategoryRules', ['filter' => 'session']);
$routes->post('/dashboard/settings/data/import-rules', 'Settings::importCategoryRules', ['filter' => 'session']);

// Tags
$routes->get('/dashboard/settings/tags', 'Settings::tags', ['filter' => 'session']);
$routes->post('/dashboard/settings/tags/save', 'Settings::saveTag', ['filter' => 'session']);
$routes->post('/dashboard/settings/tags/delete', 'Settings::deleteTag', ['filter' => 'session']);

// Notes (AJAX)
$routes->post('/dashboard/settings/notes/save', 'Settings::saveNote', ['filter' => 'session']);
$routes->get('/dashboard/settings/notes/get/(:num)', 'Settings::getNote/$1', ['filter' => 'session']);

// Spending Goals
$routes->get('/dashboard/settings/goals', 'Settings::goals', ['filter' => 'session']);
$routes->post('/dashboard/settings/goals/save', 'Settings::saveGoal', ['filter' => 'session']);
$routes->post('/dashboard/settings/goals/delete', 'Settings::deleteGoal', ['filter' => 'session']);

// Recurring Transactions
$routes->get('/dashboard/settings/recurring', 'Settings::recurring', ['filter' => 'session']);
$routes->post('/dashboard/settings/recurring/save', 'Settings::saveRecurring', ['filter' => 'session']);
$routes->post('/dashboard/settings/recurring/delete', 'Settings::deleteRecurring', ['filter' => 'session']);

// Report Scheduling
$routes->get('/dashboard/settings/report-schedule', 'Settings::reportSchedule', ['filter' => 'session']);
$routes->post('/dashboard/settings/report-schedule/save', 'Settings::saveReportSchedule', ['filter' => 'session']);

// Analytics & Insights
$routes->get('/dashboard/reports', 'Reports::index', ['filter' => 'session']);
$routes->get('/dashboard/reports/print', 'Reports::printView', ['filter' => 'session']);
$routes->get('/dashboard/budget', 'Budget::index', ['filter' => 'session']);
$routes->match(['get', 'post'], '/dashboard/budget/save', 'Budget::save', ['filter' => 'session']);
$routes->match(['get', 'post'], '/dashboard/budget/delete', 'Budget::delete', ['filter' => 'session']);

$routes->get('/home', 'Home::index');
$routes->get('/android-app', 'Home::androidApp');
$routes->get('/ml-backend', 'Home::mlBackend');
$routes->get('/setup', 'Home::setup');
$routes->get('/faq', 'Home::faq');

$routes->group('auth', function ($routes) {
    $routes->add('login', 'Auths::login');
    $routes->add('register', 'Auths::register');
    $routes->add('search', 'Auths::user_info');
    $routes->add('logout', 'Auths::user_logout');
});

$routes->group('auth', function ($routes) {
    $routes->add('auth_login', 'UserAuth::user_login');
    $routes->add('auth_register', 'UserAuth::user_register');
    $routes->add('verify_token', 'UserAuth::verify_token');
});

$routes->add('/process/upload', 'Upload::upload');
$routes->add('/process/device', 'Upload::device_print');
$routes->add('/process/get/my_uploads', 'Upload::upload_listing');
$routes->add('/process/get/my_uploads_count', 'Upload::loot_uploaded_count');
$routes->add('/process/get/my_uploads_category_count', 'Upload::loot_uploaded_category_count');
    $routes->add('/process/get/my_uploads_graph', 'Upload::loot_uploaded_graph');
    $routes->add('/process/set/delete_loot_by_uuid', 'Upload::loot_delete_by_uuid');
    $routes->add('/process/get/user_info', 'Auths::user_info');

    $routes->add('/process/get/my_summary', 'Upload::upload_summary');
$routes->add('/process/get/my_summary_calculations', 'Upload::upload_summary_calculation');
    $routes->add('/process/get/list_all_sms_in_category', 'Upload::list_all_sms_in_category');
    $routes->add('/process/get/my_financial_overview', 'Upload::financial_overview');
    $routes->add('/process/get/my_transactions_by_category', 'Upload::transactions_by_category');
    $routes->add('/process/get/my_sender_profiles', 'Upload::sender_profiles');

$routes->add('/api/settings/profile', 'Api::profile');
$routes->add('/api/settings/profile/update', 'Api::updateProfile');
$routes->add('/api/settings/preferences', 'Api::preferences');
$routes->add('/api/settings/preferences/save', 'Api::savePreferences');
$routes->add('/api/transactions/notes/get/(:num)', 'Api::noteGet/$1');
$routes->add('/api/transactions/notes/save', 'Api::noteSave');
$routes->add('/api/process/scan', 'Api::scanTrigger');
$routes->add('/api/process/progress', 'Api::scanProgress');
$routes->add('/api/export/(:any)', 'Api::export/$1');

$routes->add('/process/test', 'Testar::random');
$routes->add('/process/test_data', 'Upload::prepare_dataset');
$routes->add('/process/verify_token', 'UserAuth::verify_token');
$routes->add('/process/delete_account', 'UserAuth::delete_account');
$routes->add('/process/delete_data', 'UserAuth::delete_data');

// Admin routes
$routes->group('admin', ['filter' => ['session', 'admin']], function ($routes) {
    $routes->get('/', 'Admin\Overview::index');
    $routes->get('', 'Admin\Overview::index');

    // ML Backend
    $routes->get('ml', 'Admin\Ml::index');
    $routes->get('ml/models', 'Admin\Ml::models');
    $routes->get('ml/config', 'Admin\Ml::config');
    $routes->get('ml/test', 'Admin\Ml::test');
    $routes->post('ml/config/save', 'Admin\Ml::saveConfig');
    $routes->post('ml/models/activate', 'Admin\Ml::activateModel');
    $routes->post('ml/test/run', 'Admin\Ml::runTest');

    // Maintenance
    $routes->get('settings/maintenance', 'Admin\Maintenance::index');
    $routes->post('settings/maintenance/toggle', 'Admin\Maintenance::toggle');
    $routes->post('settings/maintenance/schedule', 'Admin\Maintenance::schedule');

    // Database info
    $routes->get('db-info', 'Admin\DbInfo::index');

    // Cron jobs
    $routes->get('crons', 'Admin\Crons::index');
    $routes->post('crons/save', 'Admin\Crons::save');
    $routes->post('crons/delete', 'Admin\Crons::delete');
    $routes->post('crons/toggle', 'Admin\Crons::toggle');
    $routes->post('crons/run', 'Admin\Crons::run');
    $routes->post('crons/output', 'Admin\Crons::output');

    // User management
    $routes->get('users', 'Admin\Users::index');
    $routes->post('users/toggle', 'Admin\Users::toggle');
    $routes->post('users/change-group', 'Admin\Users::changeGroup');
    $routes->post('users/delete', 'Admin\Users::delete');

    // Email notifications
    $routes->get('notifications', 'Admin\Notifications::index');
    $routes->post('notifications/save-config', 'Admin\Notifications::saveConfig');
    $routes->post('notifications/send-test-email', 'Admin\Notifications::sendTestEmail');
    $routes->post('notifications/save-triggers', 'Admin\Notifications::saveTriggers');
});

// Explicit Shield Authentication Routes
$routes->group('', ['namespace' => '\CodeIgniter\Shield\Controllers'], static function ($routes) {
    // Login/out
    $routes->get('login', 'LoginController::loginView');
    $routes->post('login', 'LoginController::loginAction');
    $routes->get('logout', 'LoginController::logoutAction');
    
    // Registration
    $routes->get('register', 'RegisterController::registerView');
    $routes->post('register', 'RegisterController::registerAction');
    
    // Auth Actions (2FA, Email Activation)
    $routes->get('auth/a/show', 'ActionController::show');
    $routes->post('auth/a/handle', 'ActionController::handle');
    $routes->post('auth/a/verify', 'ActionController::verify');
    
    // Forgot Password / Magic Link
    $routes->get('magic-link', 'MagicLinkController::loginView');
    $routes->post('magic-link', 'MagicLinkController::loginAction');
    $routes->get('magic-link/verify', 'MagicLinkController::verify');
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
