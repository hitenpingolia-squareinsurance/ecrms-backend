<?php

use App\Http\Controllers\AuthController;

use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\FilterController;
use App\Http\Controllers\RightsController;
use App\Http\Controllers\SidebarController;
use App\Http\Controllers\employee_master\BranchController;
use App\Http\Controllers\employee_master\EmployeeMasterController;
use App\Http\Controllers\employee_master\RegionalOfficeController;
use App\Http\Controllers\employee_master\ServiceLocationController;
use App\Http\Controllers\employee_master\VerticalController;
use App\Http\Controllers\employee_master\ZoneController;
use App\Http\Controllers\business_master\ProductController;
use App\Http\Controllers\business_master\BankController;
use App\Http\Controllers\business_master\BrokerController;
use App\Http\Controllers\business_master\CpaController;
use App\Http\Controllers\business_master\InsurerController;
use App\Http\Controllers\business_master\PinCodeController;
use App\Http\Controllers\business_master\RtoController;
use App\Http\Controllers\business_master\StateController;

use App\Http\Controllers\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::get('qr', 'generate_qr');
    Route::post('forgot-password', 'forgotpassword');
});


Route::controller(Test::class)->group(function () {
    Route::post('test', 'index');
});
 


Route::group([
    'middleware' => 'auth:sanctum',
    'prefix' => 'v1'    
], function () {
    //Your protected routes go here!

    Route::get('resendOtp', [AuthController::class, 'resendOtp']);
    Route::post('verifyOtp_and_update_password', [AuthController::class, 'verifyOtp_and_update_password']);

    Route::get('filter', [FilterController::class, 'index']);

    Route::controller(SidebarController::class)->group( function () {
        Route::get('sidebar', 'index');
        Route::post('add_item', 'add_item');
        Route::post('records', 'records');
        Route::get('getparentMenus', 'getparentMenus');
        Route::get('removeItem', 'removeItem');
    });

    //v1/employee/list
    Route::controller(EmployeeController::class)->prefix('employee')->group(function () {
        Route::post('list', 'index');
        Route::get('details', 'details'); 
    });

    Route::controller(RightsController::class)->prefix('rights')->group(function () {
        Route::get('', 'index');
        Route::post('assign', 'assign'); 
    });

    Route::controller(ProductController::class)->prefix('product')->group(function () {
        Route::get('filters', 'filters');
        Route::get('getProducts', 'getProducts');
        Route::post('getPolicyType', 'getPolicyType');
        Route::post('getPlanType', 'getPlanType');
        Route::post('add', 'index');
        Route::post('list', 'list'); 
        Route::get('removeItem', 'removeItem');
    });

    //Employee Master Routes Start

     // VerticalController
     Route::controller(VerticalController::class)->prefix('vertical')->group(function () {
        Route::post('save', 'saveVertical');
        Route::post('list', 'index');
        Route::get('removeVertical', 'removeItem');
        Route::get('updateStatus', 'updateStatus');
        Route::post('update', 'update');
        Route::post('export', 'export');
    });

    // ZoneController
    Route::controller(ZoneController::class)->prefix('zone')->group(function () {
        Route::post('list', 'index');

    });
    // EmployeeMasterController
    Route::controller(EmployeeMasterController::class)->prefix('employee-master')->group(function () {
        Route::post('save', 'save');
        Route::post('list', 'index');
        Route::get('remove', 'remove');
        Route::get('updateStatus', 'updateStatus');
        Route::post('update', 'update');
        Route::post('export', 'export');
    });

    // BranchController
    Route::controller(BranchController::class)->prefix('branch')->group(function () {
        Route::post('save', 'saveBranch');
        Route::post('list', 'index');
        Route::get('removeBranch', 'removeBranch');
        Route::get('updateStatus', 'updateStatus');
        Route::get('zones', 'getZones');
        Route::get('regional-offices', 'getRegionalOffices');
        Route::get('all-data', 'getAllData');
        Route::post('export', 'export');
    });

    // RegionalOfficeController
    Route::controller(RegionalOfficeController::class)->prefix('regionalOffice')->group(function () {
        Route::post('save', 'saveRegionalOffice');
        Route::post('list', 'index');
        Route::get('removeregionalOffice', 'removeregionalOffice');
        Route::get('updateStatus', 'updateStatus');
        Route::post('update', 'update');
        Route::get('zones', 'getZones');
        Route::post('export', 'export');
    });

    // ServiceLocationController
    Route::controller(ServiceLocationController::class)->prefix('servicelocation')->group(function () {
        Route::get('all-data', 'getAllData');
        Route::post('list', 'index');
        Route::get('removeservicelocation', 'removeServiceLocation');
        Route::get('updateStatus', 'updateStatus');
        Route::get('zones', 'getZones');
        Route::post('regional-offices', 'getRegionalOfficesByZone');
        Route::post('branches', 'getBranchesByRegionalOffice');
        Route::post('save', 'saveServiceLocation');
        Route::post('export', 'export');
    });

    //Employee Master Routes End

    // Business master Route Start

    // BankController
    Route::controller(BankController::class)->prefix('bank')->group(function () {
        Route::post('save', 'saveBank');
        Route::post('list', 'index');
        Route::get('updateStatus', 'updateStatus');
        Route::get('removebank', 'removeBank');
        Route::post('export', 'export');
    });

    // StateController
    Route::controller(StateController::class)->prefix('state')->group(function () {
        Route::post('save', 'saveState');
        Route::post('list', 'index');
        Route::get('updateStatus', 'updateStatus');
        Route::get('removebank', 'removeBank');
        Route::post('export', 'export');
        Route::get('zones', 'getZones');
    });

    // BrokerController
    Route::controller(BrokerController::class)->prefix('broker')->group(function () {
        Route::post('save', 'saveBroker');
        Route::post('list', 'index');
        Route::get('updateStatus', 'updateStatus');
        Route::get('removebroker', 'removeBroker');
        Route::post('export', 'export');
    });

    // PincodeController
    Route::controller(PinCodeController::class)->prefix('pincode')->group(function () {
        Route::post('save', 'save');
        Route::post('list', 'index');
        Route::get('updateStatus', 'updateStatus');
        Route::get('remove', 'removePincode');
        Route::post('export', 'export');
        Route::get('states', 'getStates');
        Route::get('getDistricts/{stateId}', 'getDistricts');
        Route::get('getCities/{districtId}', 'getCities');
        Route::get('getAreas/{cityId}', 'getAreas');
        Route::get('get-all/{id}', 'show');
    });

    // RtoController
    Route::controller(RtoController::class)->prefix('rto')->group(function () {
        Route::post('save', 'saveRto');
        Route::post('list', 'index');
        Route::get('updateStatus', 'updateStatus');
        Route::get('removerto', 'removeRto');
        Route::post('export', 'export');
        Route::get('states', 'getStates');
    });

    // CpaController
    Route::controller(CpaController::class)->prefix('cpa')->group(function () {
        Route::post('save', 'saveCpa');
        Route::post('list', 'index');
        Route::get('updateStatus', 'updateStatus');
        Route::get('removecpa', 'removeCpa');
        Route::post('export', 'export');
        Route::get('companys', 'getCompany');
    });

    //ProductController
    Route::controller(ProductController::class)->prefix('product')->group(function () {
        Route::get('filters', 'filters');
        Route::get('getProducts', 'getProducts');
        Route::post('getPolicyType', 'getPolicyType');
        Route::post('getPlanType', 'getPlanType');
        Route::post('add', 'index');
        Route::post('list', 'list');
        Route::get('removeItem', 'removeItem');
    });

    // InsurerController
    Route::controller(InsurerController::class)->prefix('insurer')->group(function () {
        Route::post('save', 'saveInsurer');
        Route::post('list', 'index');
        Route::get('updateStatus', 'updateStatus');
        Route::get('removeinsurer', 'removeInInsurer');
        Route::post('export', 'export');
        Route::get('get-all/{id}', 'show');
    });

     // Business master Route End
    
   
    
});
 