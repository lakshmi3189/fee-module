<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\MsCategory;
use DB;
use Exception;
use Illuminate\Http\Request;

/**
 * | Created On- 24-07-2023 
 * | Created By- Umesh Kumar
 * | Code Status- Close
 */

/**
 * | Updated On- 28-07-2023 
 * | Author - Lakshmi Kumari
 * | Code Status : Open
 */

class CategoryController extends Controller
{
    private $_mCategories;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mCategories = new MsCategory();
    }

    //Active All
    public function activeAll(Request $req)
    {
        try {
            $getData = $this->_mCategories->active();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Active Records", $getData, "M_API_5.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_5.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
