<?php


/**
 * | Created On-24-07-2023 
 * | Author - Umesh Kumar
 * | Code Status : Open
 */

use App\Http\Controllers\API\Master\ClassMasterController;                                  //M_API_1
use App\Http\Controllers\API\Master\FeeHeadTypeController;                                  //M_API_2
use App\Http\Controllers\API\Master\FeeHeadController;                                      //M_API_3
use App\Http\Controllers\API\Master\SectionController;                                      //M_API_4
use App\Http\Controllers\API\Master\CategoryController;                                     //M_API_5
use App\Http\Controllers\API\Master\MonthController;                                        //M_API_6                      
use App\Http\Controllers\API\Master\FinancialYearController;                                //M_API_7


// ==============================================Public Routes Start===========================================================

// ==================== 24/07/2023 ================

Route::controller(ClassMasterController::class)->group(function () {
    Route::post('class/active-all', 'activeAll');                                         // Get all     M_API_1.1
});

Route::controller(FeeHeadTypeController::class)->group(function () {
    Route::post('feehead-type/active-all', 'activeAll');                        // Get all     M_API_2.1            
    Route::post('feehead-type/retrieve-all', 'retrieveAll');                    // Get all     M_API_2.2       
});

Route::controller(FeeHeadController::class)->group(function () {
    Route::post('feehead/active-all', 'activeAll');                             // Get all      M_API_3.1              
    Route::post('feehead/retrieve-all', 'retrieveAll');                         // Get all      M_API_3.2         
});

Route::controller(SectionController::class)->group(function () {
    Route::post('section/active-all', 'activeAll');                                       // Get all       M_API_4.1
});

Route::controller(CategoryController::class)->group(function () {
    Route::post('category/active-all', 'activeAll');                             // Get all      M_API_5.1              
    Route::post('category/retrieve-all', 'retrieveAll');                         // Get all      M_API_5.2          
});

Route::controller(MonthController::class)->group(function () {
    Route::post('month/active-all', 'activeAll');                               // Get all      M_API_6.1              
    Route::post('month/retrieve-all', 'retrieveAll');                           // Get all      M_API_6.2         
});

Route::controller(FinancialYearController::class)->group(function () {
    Route::post('financial-year/active-all', 'activeAll');                      // Get all      M_API_7.1
});




// ==============================================Public Routes End=============================================================


    

   






























// Route::controller(FeeDemandController::class)->group(function() {
//     Route::post('generate-student-demand','generateDemand');
//     // Route::post('/fee-demand/addDemand','addDemand');                           //Add  
//     // Route::post('/fee-demand/readDemand','readDemand');                           //Add  
    
// });