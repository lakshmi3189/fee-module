<?php

namespace App\Http\Controllers\api\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Master\MsSection;
use DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

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

class SectionController extends Controller
{
    private $_mSections;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mSections = new MsSection();
    }
    //Active All
    public function activeAll(Request $req)
    {
        try {
            $getData = $this->_mSections->active();
            if (collect($getData)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Active Records", $getData, "M_API_4.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_4.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function countSection(Request $req)
    {
        try {
            $rowCount = $this->_mSections->countActive();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Total Sections", $rowCount, "M_API_4.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_4.2", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Commented On- 28-07-2023 
     * | Commented By- Lakshmi Kumari
     * | Description - if need to show all records
     */
    //View All
    // public function retrieveAll(Request $req)
    // {
    //     try {
    //         $getData = $this->_mSections->retrieve();
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
    //         return responseMsgsT(true, "View All Records", $list, "M_API_4.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "M_API_4.2", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }
}
