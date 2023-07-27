<?php

namespace App\Http\Controllers\API\FeeStructure;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure\FmDailyFeeCollection;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DailyFeeCollectionController extends Controller
{
    /**
     * | Created On-26-07-2023 
     * | Created By- Umesh Kumar
     * | Code Status - Open
     */

    private $_mDailyFeeCollections;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mDailyFeeCollections = new FmDailyFeeCollection();
    }

    // Add records
    public function store(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'dailyFeeColl' => 'required|array',
            'dailyFeeColl.*.fyId' => 'required|numeric',
            'dailyFeeColl.*.classId' => 'required|numeric',
            'dailyFeeColl.*.admissionNo' => 'required|string',
            'dailyFeeColl.*.feeName' => 'required|string',
            'dailyFeeColl.*.feeAmount' => 'required|numeric',
            'dailyFeeColl.*.description' => 'required|string',
            'dailyFeeColl.*.isFeeReceived' => 'required|numeric',
            'dailyFeeColl.*.receivedAmount' => 'required|numeric',
            'dailyFeeColl.*.dueAmount' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $data = array();
            if ($req['dailyFeeColl'] != "") {
                foreach ($req['dailyFeeColl'] as $ob) {
                    $isGroupExists = $this->_mDailyFeeCollections->readDailyFeeCollGroup($ob);
                    if (collect($isGroupExists)->isNotEmpty())
                        throw new Exception("Daily Fee Collection Already Existing");
                    $fmDailyFeeCollection = new FmDailyFeeCollection;
                    $fmDailyFeeCollection->fy_id = $ob['fyId'];
                    $fmDailyFeeCollection->class_id = $ob['classId'];
                    $fmDailyFeeCollection->fee_head_type_id = $ob['feeHeadTypeId'];
                    $fmDailyFeeCollection->fee_head_id = $ob['feeHeadId'];
                    $fmDailyFeeCollection->fee_amount = $ob['feeAmount'];
                    $fmDailyFeeCollection->month_id = $ob['monthId'];
                    $fmDailyFeeCollection->description = $ob['description'];
                    $fmDailyFeeCollection->created_by = authUser()->id;
                    $fmDailyFeeCollection->ip_address = getClientIpAddress();
                    $fmDailyFeeCollection->save();
                    // dd($fmDailyFeeCollection);
                    $data[] = $fmDailyFeeCollection;
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
            $getData = $this->_mDailyFeeCollections->retrieve($req);
            $perPage = $req->perPage ? $req->perPage : 10;
            $paginater = $getData->paginate($perPage);
            // if (isEmpty($paginater))
            //     throw new Exception("Data Not Found");
            $list = [
                "current_page" => $paginater->currentPage(),
                "perPage" => $perPage,
                "last_page" => $paginater->lastPage(),
                "data" => $paginater->items(),
                "total" => $paginater->total()
            ];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Records", $list, "API_4.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_4.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
