<?php

namespace App\Http\Controllers\API\Report;

use App\Http\Controllers\Controller;
use App\Models\Report\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;
use App\Models\FeeStructure\FeeCollection;
use App\Models\Student\Student;
use Exception;
use DB;

/**
 * | Created On-31-07-2023 
 * | Created By- Lakshmi Kumari
 * | Code Status : Open 
 */
class ReportController extends Controller
{
    private $_mReports;
    private $_mFeeCollections;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mReports = new Report();
        $this->_mFeeCollections = new FeeCollection();
    }



    public function showFyClassMonthReport(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'fyId' => 'required|numeric',
            'classId' => 'required|numeric',
            'monthId' => 'numeric|nullable'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $feeCollection = FeeCollection::where('fy_id', $req->fyId)
                ->where('class_id', $req->classId)
                ->orWhere('month_id', $req->monthId)
                ->get();
            if ($feeCollection->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Data Not Found',
                    // 'data' => []
                ]);
            }
            $studentId = $feeCollection->first()->student_id;
            $student = Student::select('admission_no', 'full_name', 'class_name', 'section_name', 'roll_no')->where('id', $studentId)->first();


            $monthWiseData = $feeCollection->groupBy('month_name')->map(function ($monthData) {
                $totalFees = $monthData->sum('fee_amount');
                $totalRecFees = $monthData->sum('received_amount');
                $totalDueFees = $monthData->sum('due_amount');
                $paymentDate = Carbon::parse($monthData->first()->payment_date)->format('d-m-y');
                $monthPaid = $monthData->first()->month_name;
                $isPaid = $monthData->first()->is_paid;
                $id = $monthData->first()->id;
                $details = $monthData->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'feeHeadName' => $item->fee_head_name,
                        'amount' => $item->fee_amount,
                        'receivedAmount' => $item->received_amount,
                        'dueAmount' => $item->due_amount,
                    ];
                });
                return [
                    // 'id' => $id,
                    'monthName' => $monthData->first()->month_name,
                    'totalFees' => $totalFees,
                    'receivedAmount' => $totalRecFees,
                    'dueAmount' => $totalDueFees,
                    'paymentDate' => $paymentDate,
                    'monthPaid' => $monthPaid,
                    'isPaid' => $isPaid,
                    'details' => $details->toArray(),
                ];
            });
            $result["stdDetails"] = $student;
            $result["feeDetails"] = $monthWiseData->values()->toArray();
            return responseMsgs(true, "View All Records", $result, "", "API_7.1", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_7.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
