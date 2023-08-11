<?php

namespace App\Http\Controllers\API\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
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

    public function retrieveAll(Request $req)
    {
        try {
            $getData = $this->_mClassMasters->retrieve();
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
            return responseMsgsT(true, "View All Records", $list, "M_API_1.3", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_1.3", responseTime(), "POST", $req->deviceId ?? "");
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
            $getData = $this->_mClassMasters::where('id', $req->id)->first();
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
            return responseMsgsT(true, "Changes Done Successfully", $req->status, "M_API_1.4", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_1.4", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // Add records
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'className' => 'required|string',  //regex:/^[a-zA-z]+$/',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $isGroupExists = $this->_mClassMasters::where('class_name', Str::upper($req->className))->get();
            if (collect($isGroupExists)->isNotEmpty())
                throw new Exception("Head Name Already Existing");
            $metaReqs = [
                'class_name' => Str::upper($req->className),
                'created_by' => authUser()->id,
                'ip_address' => getClientIpAddress(),
                'version_no' => 0
            ];
            $metaReqs = array_merge($metaReqs, [
                'json_logs' => trim(json_encode($metaReqs), ",")
            ]);
            $this->_mClassMasters->store($metaReqs);
            $data = ['Class Name' => $req->className];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Records Added Successfully", $data, "M_API_1.5", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_1.5", responseTime(), "POST", $req->deviceId ?? "");
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
            $getData = $this->_mClassMasters->searchByName($req);
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
            return responseMsgsT(true, "View Searched Records", $list, "M_API_1.6", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "M_API_1.6", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
