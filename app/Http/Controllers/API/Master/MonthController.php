<?php

namespace App\Http\Controllers\api\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\MsMonth;
use DB;
use Illuminate\Http\Request;
use Exception;

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

class MonthController extends Controller
{
    //global variable
    private $_mMonths;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mMonths = new MsMonth();
    }
    //Active All
    public function activeAll(Request $req)
    {
        try {
            $getData = $this->_mMonths->active();
            if (collect($getData)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Active Records", $getData, "M_API_6.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_6.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
