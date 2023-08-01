<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\MsClass;
use Exception;
use Illuminate\Http\Request;
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

class ClassMasterController extends Controller
{
    private $_mClassMasters;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mClassMasters = new MsClass();
    }

    //Active All
    public function activeAll(Request $req)
    {
        try {
            $getData = $this->_mClassMasters->active();
            if (collect($getData)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Active Records", $getData, "M_API_1.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_1.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function countClass(Request $req)
    {
        try {
            $rowCount = $this->_mClassMasters->countActive();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Total Classes", $rowCount, "M_API_1.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_1.2", responseTime(), "POST", $req->deviceId ?? "");
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
    //         $getData = $this->_mClassMasters->retrieve();
    //         $perPage = $req->perPage ? $req->perPage : 10;
    //         $paginater = $getData->paginate($perPage);
    //         // if ($paginater == "")
    //         //     throw new Exception("Data Not Found");
    //         $list = [
    //             "current_page" => $paginater->currentPage(),
    //             "perPage" => $perPage,
    //             "last_page" => $paginater->lastPage(),
    //             "data" => $paginater->items(),
    //             "total" => $paginater->total()
    //         ];
    //         $queryTime = collect(DB::getQueryLog())->sum("time");
    //         return responseMsgsT(true, "View All Records", $list, "M_API_1.3", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "M_API_1.3", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }
}
