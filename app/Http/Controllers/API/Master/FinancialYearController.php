<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\MsFinancialYear;
use DB;
use Exception;

/**
 * | Created On- 24-07-2023 
 * | Created By- Umesh Kumar
 * | Code Status- Open
 */

class FinancialYearController extends Controller
{

    private $_mFinancialYears;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mFinancialYears = new MsFinancialYear();
    }

    //Active All
    public function activeAll(Request $req)
    {
        try {
            $getData = $this->_mFinancialYears->active();
            if (collect($getData)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Active Records", $getData, "M_API_2.6", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_2.6", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
