<?php

namespace App\Http\Controllers\API\FeeStructure;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FeeStructure\FmClassFeeMaster;
use App\Models\Master\FmFeeHead;
use App\Models\Master\MsFinancialYear;
use App\Models\Master\FmFeeHeadType;
use App\Models\Master\MsCategory;
use App\Models\Master\MsClass;
use App\Models\Master\MsMonth;
use App\Models\Master\MsSection;
use Exception;
use Illuminate\Support\Facades\Validator;
use DB;

use function PHPUnit\Framework\isEmpty;

/**
 * | Created On-26-Jul-2023 
 * | Created By- Lakshmi Kumari
 * | Class Fee Master Crud Operations 
 */

class ClassFeeMasterController extends Controller
{
    private $_mClassFeeMasters;
    private $_mFeeHeads;
    private $_mMsFinancialYear;
    private $_mFmFeeHeadType;
    private $_mMsCategory;
    private $_mMsClass;
    private $_mMsMonth;
    private $_mMsSection;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mClassFeeMasters = new FmClassFeeMaster();
        $this->_mFeeHeads = new FmFeeHead();
        $this->_mMsFinancialYear = new MsFinancialYear();
        $this->_mFmFeeHeadType = new FmFeeHeadType();
        $this->_mMsCategory = new MsCategory();
        $this->_mMsClass = new MsClass();
        $this->_mMsMonth = new MsMonth();
        $this->_mMsSection = new MsSection();
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
            // 'datas.*.description' => 'string',
            'datas.*.isMothChecked' => 'numeric',
            'datas.*.monthId' => 'required|numeric',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            DB::beginTransaction();
            $data = array();
            if ($req['datas'] != "") {
                foreach ($req['datas'] as $ob) {
                    //check data valid data
                    $fy = $this->_mMsFinancialYear::where('id', $ob['fyId'])->exists();
                    if (!$fy)
                        throw new Exception("Invalid Financial Year");

                    $fhd = $this->_mFeeHeads::where('id', $ob['feeHeadId'])->exists();
                    if (!$fhd)
                        throw new Exception("Invalid Fee Head");

                    $fht = $this->_mFmFeeHeadType::where('id', $ob['feeHeadTypeId'])->exists();
                    if (!$fht)
                        throw new Exception("Invalid Fee Head Type");

                    $cls = $this->_mMsClass::where('id', $ob['classId'])->exists();
                    if (!$cls)
                        throw new Exception("Invalid Class");

                    $mon = $this->_mMsMonth::where('id', $ob['monthId'])->exists();
                    if (!$mon)
                        throw new Exception("Invalid Month");

                    //check data existing or not
                    $isGroupExists = $this->_mClassFeeMasters->readClassFeeMastersGroup($ob);
                    if (collect($isGroupExists)->isNotEmpty())
                        throw new Exception("Fee Head Already Existing");
                    //DB::rollback();
                    //add data
                    if ($ob['monthId'] != 0 && $ob['feeAmount'] != 0) {
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
                        $classFeeMaster->json_logs = trim($classFeeMaster->json_logs . "," . json_encode($classFeeMaster), ",");
                        $classFeeMaster->save();
                    }
                }
            }
            DB::commit();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Records Added Successfully", [], "API_4.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollback();
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
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $show, "API_4.3", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_4.3", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //show data by id 
    public function showFeeHeadByFyIdAndClassId1(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId'        => 'required',
            'classId'     => 'required'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $show = $this->_mClassFeeMasters->getFeeHeadByFyIdAndClassId1($req);
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $show, "API_4.3", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_4.3", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
