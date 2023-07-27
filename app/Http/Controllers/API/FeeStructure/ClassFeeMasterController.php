<?php

namespace App\Http\Controllers\API\FeeStructure;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeeStructure\FmClassFeeMaster;
use App\Models\Master\FmFeeHead;
use Exception;
use Illuminate\Support\Facades\Validator;
use DB;

/**
 * | Created On-26-Jul-2023 
 * | Created By- Lakshmi Kumari
 * | Class Fee Master Crud Operations
 */

class ClassFeeMasterController extends Controller
{
    private $_mClassFeeMasters;
    private $_mFeeHeads;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mClassFeeMasters = new FmClassFeeMaster();
        $this->_mFeeHeads = new FmFeeHead();
    }

    // Add records
    public function store(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'datas' => 'required|array',
            'datas.*.fyId' => 'required|numeric',
            'datas.*.classId' => 'required|numeric',
            'datas.*.feeHeadTypeId' => 'required|numeric',
            'datas.*.feeHeadId' => 'required|numeric',
            'datas.*.feeAmount' => 'required|numeric',
            'datas.*.description' => 'required|string',
            'datas.*.isMothChecked' => 'numeric',
            'datas.*.monthId' => 'required|numeric',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $data = array();
            if ($req['datas'] != "") {
                foreach ($req['datas'] as $ob) {
                    $isGroupExists = $this->_mClassFeeMasters->readClassFeeMastersGroup($ob);
                    if (collect($isGroupExists)->isNotEmpty())
                        throw new Exception("Fee Head Already Existing");
                    $classFeeMaster = new FmClassFeeMaster;
                    $classFeeMaster->fy_id = $ob['fyId'];
                    $classFeeMaster->class_id = $ob['classId'];
                    $classFeeMaster->fee_head_type_id = $ob['feeHeadTypeId'];
                    $classFeeMaster->fee_head_id = $ob['feeHeadId'];
                    $classFeeMaster->fee_amount = $ob['feeAmount'];
                    $classFeeMaster->month_id = $ob['monthId'];
                    $classFeeMaster->description = $ob['description'];
                    $classFeeMaster->created_by = authUser()->id;
                    $classFeeMaster->ip_address = getClientIpAddress();
                    $classFeeMaster->save();
                    // dd($classFeeMaster);
                    $data[] = $classFeeMaster;
                }
            }
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Records Added Successfully", $data, "API_4.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_4.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //View All
    public function retrieveAll(Request $req)
    {
        try {
            $getData = $this->_mClassFeeMasters->retrieve();
            $perPage = $req->perPage ? $req->perPage : 10;
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
            return responseMsgsT(true, "View All Records", $list, "API_4.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_4.2", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //show data by id
    public function showFeeHeadByFyIdAndClassId(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId'        => 'required',
            'classId'     => 'required'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $show = $this->_mClassFeeMasters->getFeeHeadByFyIdAndClassId($req);
            // print_var($show);
            // die;
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $show, "API_4.3", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_4.3", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
