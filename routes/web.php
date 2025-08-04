<?php

use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\ImportController;
use App\Http\Controllers\Admin\PublishersController;
use App\Http\Controllers\Admin\SlskeyGroupsController;
use App\Http\Controllers\Admin\SlskeyHistoryController;
use App\Http\Controllers\Admin\SwitchGroupsController;
use App\Http\Controllers\Admin\LogJobController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Main\ActivationController;
use App\Http\Controllers\Main\ReportingController;
use App\Http\Controllers\Main\UsersController;
use App\Http\Controllers\Patrons\ReactivationTokenController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Landing and Login
Route::get('/login', [AuthController::class, 'index'])
    ->name('login');
Route::get('/login/eduid', [AuthController::class, 'loginEduID'])
    ->name('login_eduid');

// Forward from old PURA backend to new SLSKey when logged in in old PURA
Route::get('/migration', [AuthController::class, 'migration'])
    ->name('migration');

// No Roles Error Page
Route::get('/noroles', [AuthController::class, 'noroles'])
    ->name('noroles');

// Reactivation Token Routes
Route::get('/reactivate/{token}', [ReactivationTokenController::class, 'reactivate'])
    ->name('token.reactivate');
Route::get('/reactivate/{token}/renew', [ReactivationTokenController::class, 'renew'])
    ->name('token.renew');

// Publisher Participate Info Page
Route::get('/participate', [AuthController::class, 'participate'])
    ->name('participate');
Route::get('/publishers', [AuthController::class, 'participate'])
    ->name('publishers');

// Authenticated Routes
Route::middleware([
    //config('jetstream.auth_session'),
    'auth.check',
])->group(function () {
    // Logout
    Route::get('logout/user', [AuthController::class, 'logoutUsernamePassword'])
        ->name('logout.user');

    // Start / Preview
    Route::get('/', [ActivationController::class, 'start'])
        ->name('activation.start');
    Route::get('activation/{identifier}', [ActivationController::class, 'preview'])
        ->name('activation.preview');

    // Admin
    Route::middleware([
        'auth.permission_check',
    ])->group(function () {
        // Activate / Deactivate
        Route::post('activation/{primary_id}', [ActivationController::class, 'activate'])
            ->name('activation.activate');
        Route::delete('activation/{primary_id}', [ActivationController::class, 'deactivate'])
            ->name('activation.deactivate');
        // Block / Unblock
        Route::post('activation/{primary_id}/block', [ActivationController::class, 'block'])
            ->name('activation.block');
        Route::delete('activation/{primary_id}/block', [ActivationController::class, 'unblock'])
            ->name('activation.unblock');
        // Expiration Disabled / Enabled
        Route::post('activation/{primary_id}/expiration', [ActivationController::class, 'disableExpiration'])
            ->name('activation.expiration.disable');
        Route::delete('activation/{primary_id}/expiration', [ActivationController::class, 'enableExpiration'])
            ->name('activation.expiration.enable');
        // Member of Educational Institution
        Route::post('activation/{primary_id}/member_educational_institution', [ActivationController::class, 'changeMemberEducationalInstitution'])
            ->name('activation.member');
    });

    // User Management
    Route::get('/users', [UsersController::class, 'index'])
        ->name('users.index');
    Route::get('/users/data', [UsersController::class, 'getData'])
        ->name('users.data');
    Route::get('/users/export', [UsersController::class, 'exportList'])
        ->name('users.export');
    Route::get('/users/{identifier}', [UsersController::class, 'show'])
        ->name('users.show');
    Route::get('/users/alma/{identifier}', [UsersController::class, 'getAndUpdateAlmaUserInfos'])
        ->name('users.alma');
    // Switch Status of an Activation
    Route::get('/users/switch/{primary_id}/{slskey_code}', [UsersController::class, 'getSwitchStatus'])
        ->name('users.switchStatus');

    // Reporting
    Route::get('/reporting', [ReportingController::class, 'index'])
        ->name('reporting.index');
    Route::get('/reporting/data', [ReportingController::class, 'getData'])
        ->name('reporting.data');
    Route::get('/reporting/export', [ReportingController::class, 'export'])
        ->name('reporting.export');
    Route::get('reporting/{identifier}', [ReportingController::class, 'showReportSettings'])
        ->name('reporting.show');
    Route::post('reporting/{identifier}', [ReportingController::class, 'addReportingEmail'])
        ->name('reporting.addEmail');
    Route::delete('reporting/{identifier}/{emailId}', [ReportingController::class, 'removeReportingEmail'])
        ->name('reporting.removeEmail');

    // Admin
    Route::middleware([
        'role:slskeyadmin',
    ])->group(function () {
        // History
        Route::get('/admin/history', [SlskeyHistoryController::class, 'index'])
            ->name('admin.history.index');

        // SLSKey Groups
        Route::get('/admin/groups', [SlskeyGroupsController::class, 'index'])
            ->name('admin.groups.index');
        Route::get('/admin/groups/create', [SlskeyGroupsController::class, 'create'])
            ->name('admin.groups.create');
        Route::post('/admin/groups', [SlskeyGroupsController::class, 'store'])
            ->name('admin.groups.store');
        Route::get('/admin/groups/{slskey_code}', [SlskeyGroupsController::class, 'show'])
            ->name('admin.groups.show');
        Route::put('/admin/groups/{slskeyGroup}', [SlskeyGroupsController::class, 'update'])
            ->name('admin.groups.update');
        Route::delete('/admin/groups/{slskeyGroup}', [SlskeyGroupsController::class, 'destroy'])
            ->name('admin.groups.destroy');

        // Switch Groups
        Route::get('/admin/switchgroups', [SwitchGroupsController::class, 'index'])
            ->name('admin.switchgroups.index');
        Route::get('/admin/switchgroups/create', [SwitchGroupsController::class, 'create'])
            ->name('admin.switchgroups.create');
        Route::post('/admin/switchgroups', [SwitchGroupsController::class, 'store'])
            ->name('admin.switchgroups.store');
        Route::get('/admin/switchgroups/{switchGroup}', [SwitchGroupsController::class, 'show'])
            ->name('admin.switchgroups.show');
        Route::put('/admin/switchgroups/{switchGroup}', [SwitchGroupsController::class, 'update'])
            ->name('admin.switchgroups.update');
        Route::get('/admin/switchgroups/publishers/download', [SwitchGroupsController::class, 'downloadPublishers'])
            ->name('admin.switchgroups.publishers.download');

        // Publishers
        Route::get('/admin/publishers', [PublishersController::class, 'index'])
            ->name('admin.publishers.index');
        Route::get('/admin/publishers/create', [PublishersController::class, 'create'])
            ->name('admin.publishers.create');
        Route::post('/admin/publishers', [PublishersController::class, 'store'])
            ->name('admin.publishers.store');
        Route::get('/admin/publishers/{publisher}', [PublishersController::class, 'show'])
            ->name('admin.publishers.show');
        Route::put('/admin/publishers/{publisher}', [PublishersController::class, 'update'])
            ->name('admin.publishers.update');
        Route::delete('/admin/publishers/{publisher}', [PublishersController::class, 'destroy'])
            ->name('admin.publishers.destroy');

        // Admin users
        Route::get('/admin/users', [AdminUsersController::class, 'index'])
            ->name('admin.users.index');
        Route::get('/admin/users/create', [AdminUsersController::class, 'create'])
            ->name('admin.users.create');
        Route::post('/admin/users', [AdminUsersController::class, 'store'])
            ->name('admin.users.store');
        Route::get('/admin/users/{user_identifier}', [AdminUsersController::class, 'show'])
            ->name('admin.users.show');
        Route::put('/admin/users/{user_identifier}', [AdminUsersController::class, 'update'])
            ->name('admin.users.update');
        Route::delete('/admin/users/{user_identifier}', [AdminUsersController::class, 'destroy'])
            ->name('admin.users.destroy');
        Route::get('/admin/users/findeduid/{user_identifier}', [AdminUsersController::class, 'findEduIdPrimaryIdentifier'])
            ->name('admin.users.findeduid');

        // Mass Import
        Route::get('admin/import', [ImportController::class, 'index'])
            ->name('admin.import.index');
        Route::post('admin/import/preview', [ImportController::class, 'preview'])
            ->name('admin.import.preview');
        Route::post('admin/import/store', [ImportController::class, 'store'])
            ->name('admin.import.store');
        Route::post('admin/import/stop', [ImportController::class, 'cancelImport'])
            ->name('admin.import.stop');

        // API Logs
        Route::get('/admin/logjob', [LogJobController::class, 'index'])
            ->name('admin.logjob.index');
    });

    // Localization
    Route::put('/lang/{locale}', function ($locale) {
        if (!in_array($locale, ['de', 'fr', 'it', 'en'])) {
            abort(400);
        }
        $cookie = Cookie::forever('language', $locale);
        App::setLocale($locale);

        return Response::json(['message' => 'Language set successfully'], 200)->cookie($cookie);
    })->name('changeLocale');
});
