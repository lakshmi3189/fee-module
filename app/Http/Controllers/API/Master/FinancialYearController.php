<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Http\Request;
use App\Models\Master\MsFinancialYear;
use DB;
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
            return responseMsgsT(true, "View All Active Records", $getData, "M_API_7.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_7.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function retrieveAll(Request $req)
    {
        try {
            $getData = $this->_mFinancialYears->retrieve();
            $perPage = $req->perPage ? $req->perPage : 5;
            $paginater = $getData->paginate($perPage);
            // if ($paginater == "")
            //     throw new Exception("Data Not Found");
            $list = [
                "current_page" => $paginater->currentPage(),
                "perPage" => $perPage,
                "last_page" => $paginater->lastPage(),
                "data" => $paginater->items(),
                "total" => $paginater->total()
            ];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Records", $list, "M_API_7.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_7.2", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Activate / Deactivate
    public function delete(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric',
            'status' => 'required|in:active,deactive'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $getData = $this->_mFinancialYears::where('id', $req->id)->first();
            if (!$getData)
                throw new Exception("Data Not Found");

            if (isset($req->status)) {                  // In Case of Deactivation or Activation 
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs =  [
                    'status' => $status,
                    'version_no' => $getData->version_no + 1,
                    'updated_at' => Carbon::now(),
                    'ip_address' => getClientIpAddress()
                ];
                $metaReqs = array_merge($metaReqs, [
                    'json_logs' => trim($getData->json_logs . "," . json_encode($metaReqs), ",")
                ]);
            }
            $getData->update($metaReqs);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Changes Done Successfully", $req->status, "M_API_7.3", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_7.3", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // Add records
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'financialYear' => 'required|string',  //regex:/^[a-zA-z]+$/',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $isGroupExists = $this->_mFinancialYears::where('financial_year', $req->financialYear)->get();
            if (collect($isGroupExists)->isNotEmpty())
                throw new Exception("Financial Year Already Existing");
            $metaReqs = [
                'financial_year' => Str::title($req->financialYear),
                'created_by' => authUser()->id,
                'ip_address' => getClientIpAddress(),
                'version_no' => 0
            ];
            $metaReqs = array_merge($metaReqs, [
                'json_logs' => trim(json_encode($metaReqs), ",")
            ]);
            $this->_mFinancialYears->store($metaReqs);
            $data = ['Head Name' => $req->feeHead];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Records Added Successfully", $data, "M_API_7.4", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_7.4", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //search by name
    public function search(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'search' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $getData = $this->_mFinancialYears->searchByName($req);
            $perPage = $req->perPage ? $req->perPage : 5;
            $paginater = $getData->paginate($perPage);
            // if ($paginater == "")
            //     throw new Exception("Data Not Found");
            $list = [
                "current_page" => $paginater->currentPage(),
                "perPage" => $perPage,
                "last_page" => $paginater->lastPage(),
                "data" => $paginater->items(),
                "total" => $paginater->total()
            ];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Searched Records", $list, "M_API_7.5", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_7.5", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
