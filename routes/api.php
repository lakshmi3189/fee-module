<?php

use Illuminate\Support\Facades\Route;

/**
 * | Created On : 25-July-2023 
 * | Author : Lakshmi Kumari
 * | Routes Specified for the Main Module
 * | Code Status : Open 
 */

//laxmi: api-start
use App\Http\Controllers\API\Admin\UserController;                                                              //API_1
use App\Http\Controllers\API\Admin\PasswordResetController;                                                     //API_2
use App\Http\Controllers\API\Student\StudentController;                                                         //API_3
use App\Http\Controllers\API\FeeStructure\ClassFeeMasterController;                                             //API_4
use App\Http\Controllers\API\FeeStructure\DailyFeeCollectionController;                                         //API_5
// api-end



/*================================================================ Public Routes Start ======================================*/

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

/*================================================================ Public Routes End  ======================================*/

/*================================================================ Protected Routes Start ===================================*/
Route::middleware('auth:sanctum')->group(function () {
    //Admin 
    Route::controller(UserController::class)->group(function () {
        Route::post('users/profile', 'showProfile');                                 // View Profile For User        API_1.2
        Route::post('users/editProfile', 'editProfile');                             // Edit Profile For User        API_1.3
        Route::post('users/changePassword', 'changePassword');                       // Change Password For User     API_1.4 
        Route::post('users/logout', 'logout');                                       // Logout                       API_1.5
    });

    Route::controller(StudentController::class)->group(function () {
        Route::post('student/store-csv', 'storeCSV');                                // Store CSV Data              API_3.1
        Route::post('student/retrieve-all', 'retrieveAll');                          // Retrieve All Records        API_3.2
        Route::post('student/details', 'showStudentByClassAndAdmissionNo');          // show                        API_3.3
    });

    Route::controller(ClassFeeMasterController::class)->group(function () {
        Route::post('class-fee/store', 'store');                                     // Store Data                  API_4.1
        Route::post('class-fee/retrieve-all', 'retrieveAll');                        // get Data                    API_4.2
        Route::post('class-fee/show-fee', 'showFeeHeadByFyIdAndClassId');            // get Data                    API_4.3
    });

    Route::controller(DailyFeeCollectionController::class)->group(function () {
        Route::post('daily-fee-collection/retrieve-all', 'retrieveAll');              //retrieve                    API_5.1
    });



    // Route::controller(FeeCollectionController::class)->group(function () {
    //     Route::post('fee-collection/crud/store', 'store');                      // Store                        API_15.1
    //     Route::post('fee-collection/crud/edit', 'edit');                        // Edit                         API_15.2
    //     Route::post('fee-collection/crud/show', 'show');                        // Get by Id                    API_15.3
    //     Route::post('fee-collection/crud/retrieve-all', 'retrieveAll');         // Get all records              API_15.4
    //     Route::post('fee-collection/crud/delete', 'delete');                    // delete                       API_15.5
    //     Route::post('fee-collection/crud/active-all', 'activeAll');             // Active All Records           API_15.6
    //     Route::post('fee-collection/crud/search', 'search');                    // Search                       API_15.7
    //     Route::post('fee-collection/fees', 'searchFeesByAdmNo');                // Search bt adm no             API_15.8
    //     Route::post('fee-collection/receipt', 'showReceipt');                   // show receipt                 API_15.9
    //     Route::post('fee-collection/receipt1', 'showReceiptTest');                   // show receipt                 API_15.9
    // });

    // Route::controller(PaymentController::class)->group(function () {
    //     Route::post('payment/crud/store', 'store');                             // Store                        API_16.1
    //     Route::post('payment/crud/edit', 'edit');                               // Edit                         API_16.2
    //     Route::post('payment/crud/show', 'show');                               // Get by Id                    API_16.3
    //     Route::post('payment/crud/retrieve-all', 'retrieveAll');                // Get all records              API_16.4
    //     Route::post('payment/crud/delete', 'delete');                           // delete                       API_16.5
    //     Route::post('payment/crud/active-all', 'activeAll');                    // Active All Records           API_16.6
    //     Route::post('payment/crud/search', 'search');                           // Search                       API_16.7
    // });
});





// Route::middleware('auth:sanctum')->group(function () {
//     //For aadrika
//     Route::controller(AuthController::class)->group(function () {
//         Route::post('users/change-password', 'changePassword');                 // Auth change password         API_14.2    
//         Route::post('users/logout', 'logout');                                  // Auth Logout                  API_14.3   
//         Route::post('users/view-profile', 'show');                              // Auth Profile                 API_14.4   
//     });

//     //For Schools 
//     Route::controller(SchoolMasterController::class)->group(function () {
//         Route::post('school-masters/view-profile', 'show');                     // User Name Existing           API_5.4
//         Route::post('school-masters/change-password', 'changePassword');        // School Register              API_5.6
//         Route::post('school-masters/update-profile', 'edit');                   // School Update Profile        API_5.7
//         Route::post('school-masters/logout', 'logout');                         // School Logout                API_5.8
//         Route::post('school-masters/retrieve-all', 'retrieveAll');              // School retrieve              API_5.9
//         Route::post('school-masters/active-all', 'activeAll');                  // School active                API_5.10
//         Route::post('school-masters/delete', 'delete');                         // delete                       API_5.11
//         Route::post('school-masters/update-role', 'updateRole');                // update role                  API_5.12
//     });
// });


/*================================================================ Protected Routes End ===================================*/
