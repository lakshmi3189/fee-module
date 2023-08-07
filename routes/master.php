<?php

use App\Http\Controllers\API\Master\ClassMasterController;                                  //M_API_1
use App\Http\Controllers\API\Master\FeeHeadTypeController;                                  //M_API_2
use App\Http\Controllers\API\Master\FeeHeadController;                                      //M_API_3
use App\Http\Controllers\API\Master\SectionController;                                      //M_API_4
use App\Http\Controllers\API\Master\CategoryController;                                     //M_API_5
use App\Http\Controllers\API\Master\MonthController;                                        //M_API_6                      
use App\Http\Controllers\API\Master\FinancialYearController;                                //M_API_7

/**
 * | Created On-24-07-2023 
 * | Author - Umesh Kumar
 * | Code Status : Close
 */

/**
 * | Updated On- 28-07-2023 
 * | Author - Lakshmi Kumari
 * | Code Status : Open
 */

// ========================================Private Routes Start=======================================
Route::middleware('auth:sanctum')->group(function () {
    Route::controller(ClassMasterController::class)->group(function () {
        Route::post('class/active-all', 'activeAll');                       // Get all active   M_API_1.1
        Route::post('class/count-all', 'countClass');                       // Get all count    M_API_1.2
        Route::post('class/retrieve-all', 'retrieveAll');                   // Get all          M_API_1.3 
        Route::post('class/delete', 'delete');                              // Delete           M_API_1.4 
        Route::post('class/add', 'store');                                  // Add              M_API_1.5
        Route::post('class/search', 'search');                              // search           M_API_1.6
    });

    Route::controller(FeeHeadTypeController::class)->group(function () {
        Route::post('feehead-type/active-all', 'activeAll');               // Get all active    M_API_2.1            
        // Route::post('feehead-type/retrieve-all', 'retrieveAll');        // Get all           M_API_2.2       
    });

    Route::controller(FeeHeadController::class)->group(function () {
        Route::post('feehead/active-all', 'activeAll');                    // Get all  active   M_API_3.1 
        Route::post('feehead/count-all', 'countFeeHead');                  // Get all count     M_API_3.2
        Route::post('feehead/retrieve-all', 'retrieveAll');                // Get all           M_API_3.3 
        Route::post('feehead/delete', 'delete');                           // Delete            M_API_3.4 
        Route::post('feehead/add', 'store');                               // Add               M_API_3.5
        Route::post('feehead/search', 'search');                           // search            M_API_3.6
    });

    Route::controller(SectionController::class)->group(function () {
        Route::post('section/active-all', 'activeAll');                    // Get all active    M_API_4.1
        Route::post('section/count-all', 'countSection');                  // Get all count     M_API_4.2
        Route::post('section/retrieve-all', 'retrieveAll');                // Get all           M_API_4.3
    });

    Route::controller(CategoryController::class)->group(function () {
        Route::post('category/active-all', 'activeAll');                   // Get all active    M_API_5.1
    });

    Route::controller(MonthController::class)->group(function () {
        Route::post('month/active-all', 'activeAll');                      // Get all active    M_API_6.1        
    });

    Route::controller(FinancialYearController::class)->group(function () {
        Route::post('financial-year/active-all', 'activeAll');             // Get all active    M_API_7.1
        Route::post('financial-year/retrieve-all', 'retrieveAll');                // Get all    M_API_7.2 
        Route::post('financial-year/delete', 'delete');                           // Delete     M_API_7.3 
        Route::post('financial-year/add', 'store');                               // Add        M_API_7.4
        Route::post('financial-year/search', 'search');                           // search     M_API_7.5
    });
});

// ========================================Private Routes End==========================================






























// Route::controller(FeeDemandController::class)->group(function() {
//     Route::post('generate-student-demand','generateDemand');
//     // Route::post('/fee-demand/addDemand','addDemand');                           //Add  
//     // Route::post('/fee-demand/readDemand','readDemand');                           //Add  
    
// });