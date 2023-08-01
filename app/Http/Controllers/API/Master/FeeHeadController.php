<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\FmFeeHead;
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

class FeeHeadController extends Controller
{
    private $_mFeeHeads;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mFeeHeads = new FmFeeHead();
    }

    //Active All
    public function activeAll(Request $req)
    {
        try {
            $getData = $this->_mFeeHeads->active();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Active Records", $getData, "M_API_3.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_3.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function countFeeHead(Request $req)
    {
        try {
            $rowCount = $this->_mFeeHeads->countActive();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Total Fee Heads", $rowCount, "M_API_3.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_3.2", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Commented On- 28-07-2023 
     * | Commented By- Lakshmi Kumari
     * | Description - if need to show all records
     */
    // public function retrieveAll(Request $req)
    // {
    //     try {
    //         $getData = $this->_mFeeHeads->retrieve();
    //         $perPage = $req->perPage ? $req->perPage : 10;
    //         $paginater = $getData->paginate($perPage);
    //         $list = [
    //             "current_page" => $paginater->currentPage(),
    //             "perPage" => $perPage,
    //             "last_page" => $paginater->lastPage(),
    //             "data" => $paginater->items(),
    //             "total" => $paginater->total()
    //         ];
    //         $queryTime = collect(DB::getQueryLog())->sum("time");
    //         return responseMsgsT(true, "View All Records", $list, "M_API_3.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "M_API_3.2", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }


}
