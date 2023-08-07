<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\FmFeeHeadType;
use Exception;
use DB;

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

class FeeHeadTypeController extends Controller
{
    private $_mFeeHeadTypes;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mFeeHeadTypes = new FmFeeHeadType();
    }

    //Active All
    public function activeAll(Request $req)
    {
        try {
            $getData = $this->_mFeeHeadTypes->active();
            if (collect($getData)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Active Records", $getData, "M_API_2.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_2.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
