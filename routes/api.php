<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\Admin\UserController;                                                              //API_1
use App\Http\Controllers\API\Admin\PasswordResetController;                                                     //API_2
use App\Http\Controllers\API\Student\StudentController;                                                         //API_3
use App\Http\Controllers\API\FeeStructure\ClassFeeMasterController;                                             //API_4
use App\Http\Controllers\API\FeeStructure\DailyFeeCollectionController;                                         //API_5
use App\Http\Controllers\API\FeeStructure\FeeCollectionController;                                              //API_6
use App\Http\Controllers\API\Report\ReportController;                                                           //API_7


/**
 * | Created On : 25-07-2023 
 * | Author : Lakshmi Kumari
 * | Routes Specified for the Main Module
 * | Code Status : Open 
 */


/*========================================================== Public Routes Start ======================================*/
//Admin 
Route::controller(UserController::class)->group(function () {
    Route::post('users/login', 'login');                                        // User Login                   API_1.1    
});

//Send Mail
Route::controller(PasswordResetController::class)->group(function () {
    Route::post('sendResetPasswordEmail', 'sendResetPasswordEmail');           // Send Reset Password For User API_2.1
    Route::post('resetPassword/{token}', 'resetPassword');                     // Reset Password  For User     API_2.2
});

//Get Document
Route::get('/getImageLink', function () {
    return view("getImageLink");                                                // Get Document
});
/*================================================================ Public Routes End  ==================================*/



/*================================================================ Protected Routes Start ==============================*/
Route::middleware('auth:sanctum')->group(function () {
    //Admin 
    Route::controller(UserController::class)->group(function () {
        Route::post('users/profile', 'showProfile');                              // View Profile For User        API_1.2
        Route::post('users/editProfile', 'editProfile');                          // Edit Profile For User        API_1.3
        Route::post('users/changePassword', 'changePassword');                    // Change Password For User     API_1.4 
        Route::post('users/logout', 'logout');                                    // Logout                       API_1.5
    });
    //Student
    Route::controller(StudentController::class)->group(function () {
        Route::post('student/store-csv', 'storeCSV');                             // Store CSV Data              API_3.1
        Route::post('student/retrieve-all', 'retrieveAll');                       // Retrieve All Records        API_3.2
        Route::post('student/details', 'showStudentByClassAndAdmissionNo');       // show                        API_3.3
        Route::post('student/store', 'store');                                    // Store  Data                 API_3.4
        Route::post('student/count-active', 'countActiveStudent');                // Get all active              API_3.5
        Route::post('student/count-all', 'countAllStudent');                      // Get all                     API_3.6
    });
    //Class Fee Master
    Route::controller(ClassFeeMasterController::class)->group(function () {
        Route::post('class-fee/store', 'store');                                  // Store Data                  API_4.1
        Route::post('class-fee/retrieve-all', 'retrieveAll');                     // get Data                    API_4.2
        Route::post('class-fee/show-fee', 'showFeeHeadByFyIdAndClassId');         // get Data                    API_4.3
        Route::post('class-fee/show-fee-test', 'showFeeHeadByFyIdAndClassId1');         // get Data                    API_4.3
    });
    //Daily Fee Collection
    Route::controller(DailyFeeCollectionController::class)->group(function () {
        Route::post('daily-fee-collection/retrieve-all', 'retrieveAll');          //retrieve                    API_5.1
    });
    //Fee collection
    Route::controller(FeeCollectionController::class)->group(function () {
        Route::post('fee-collection/store', 'store');                             // Store                     API_6.1
        Route::post('fee-collection/show', 'show');                               // Get by all                API_6.2
    });
    //ReportController
    Route::controller(ReportController::class)->group(function () {
        Route::post('report/monthly-fee', 'showFyClassMonthReport');              // Store                     API_7.1
    });
});




/*================================================================ Protected Routes End ===================================*/
