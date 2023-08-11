<?php

namespace App\Http\Controllers\API\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Report\Report;
use App\Models\FeeStructure\FeeCollection;
use App\Models\FeeStructure\FmClassFeeMaster;
use App\Models\Student\Student;
use App\Models\Master\MsClass;
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
    private $_mStudents;
    private $_mFeeCollections;
    private $_mFmClassFeeMasters;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mReports = new Report();
        $this->_mStudents = new Student();
        $this->_mFeeCollections = new FeeCollection();
        $this->_mFmClassFeeMasters = new FmClassFeeMaster();
    }

    // show class and month wise due fee report
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
            $result = $this->_mReports->fyClassMonthWiseFeeReport($req);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Records", $result, "API_7.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_7.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // show fy wise and month wise total fees showing in dashboard
    public function showByFyAndMonthWise(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $result = $this->_mReports->getFyAndMonthByReport($req);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Fee Details", $result, "API_7.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_7.2", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // show fy wise total fees : comparision
    public function feeComparision(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            // get class wise actual fees
            $classAndFyWiseFeeCounts = $this->_mFmClassFeeMasters::select('class_id', DB::raw('SUM(fee_amount) as fee_amount'))
                ->where('status', 1)
                ->where('fy_id', $req->fyId)
                ->groupBy('class_id', 'fy_id', 'status')
                ->orderBy('class_id')
                ->get();
            // 'perMonthFee' => number_format($perMonth, 2),
            foreach ($classAndFyWiseFeeCounts as $fee) {
                $perMonth = $fee['fee_amount'] / 12;
                $getClsFeeData[] = [
                    'classId' => $fee['class_id'],
                    'totalFee' => $fee['fee_amount'],
                    'perMonthFee' => round($perMonth, 2),
                ];
            }
            //get fee collection
            $classAndFyWiseFeeCollCounts = $this->_mFeeCollections::select(
                'class_id',
                DB::raw('SUM(fee_amount) as fee_amount'),
                DB::raw('SUM(received_amount) as received_amount'),
                DB::raw('SUM(due_amount) as due_amount'),
                DB::raw("student_id")
            )
                ->where('status', 1)
                ->where('fy_id', $req->fyId)
                ->groupBy('class_id', 'fy_id', 'status', "student_id")
                ->orderBy('class_id')
                ->get();

            foreach ($classAndFyWiseFeeCollCounts as $feeColl) {
                $getFeeCollData[] = [
                    'classId' => $feeColl['class_id'],
                    'totalFee' => $feeColl['fee_amount'],
                    "studentId" => $feeColl['student_id'],
                    'totalReceive' => $feeColl['received_amount'],
                    'totalDue' => $feeColl['due_amount']
                ];
            }
            //get total student
            $classAndFyWiseStudentCounts = $this->_mStudents::select('class_id', 'month_id', DB::raw("count(*) as total_students, string_agg(cast(id as text),',') as ids"))
                ->where('status', 1)
                ->where('fy_id', $req->fyId)
                ->groupBy('class_id', 'fy_id', 'status', 'month_id')
                ->orderBy('class_id')
                ->get();
            $actualTotal = 0;
            $receivedAmount = 0;
            $diffrence = 0;
            foreach ($classAndFyWiseStudentCounts as $std) {
                $totalMonth = ((12 - $std['month_id']) + 1);
                $fee = (collect($getClsFeeData)->where("classId", $std['class_id']));
                $collection = (collect($getFeeCollData)->where("classId", $std['class_id'])->whereIN("studentId", $std['ids']));
                $recevingAmt =  $collection->sum("totalReceive");
                // $totalDue =  $collection->sum("totalDue");
                $actuleDemand =  $totalMonth * $fee->sum("perMonthFee") * $std['total_students'];
                $getStdData[] = [
                    'classId' => $std['class_id'],
                    'monthId' =>  $std['month_id'],
                    'totalMonth' => $totalMonth,
                    "perMonthFee" => $fee->sum("perMonthFee"),
                    'totalStudent' => $std->total_students,
                    "actuleDemand" => $actuleDemand,
                    "totalReceive" => $recevingAmt,
                    "totalDue" => $actuleDemand - $recevingAmt,
                    "totalFee" => $fee->sum("totalFee"),
                ];
                $actualTotal = round($actualTotal + $actuleDemand, 2);
                $receivedAmount = round($receivedAmount + $recevingAmt, 2);
                $diffrence = $actualTotal - $receivedAmount;
            }
            $result = ["actualAmount" => $actualTotal, "receiveAmount" => $receivedAmount, "diff" => $diffrence, "details" => $getStdData];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Details", $result, "API_7.3", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_7.3", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // class wise demand fee reporting;
    public function classWiseDemandReport(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $totalsAndFees = FmClassFeeMaster::where('status', 1)
                ->where('fy_id', $req->fyId)
                ->groupBy('class_id', 'fy_id')
                ->selectRaw('class_id, fy_id, SUM(fee_amount) as total_fees')
                ->orderBy('class_id')
                ->get();

            $activeStudentsByClass = Student::where('status', 1)
                ->where('fy_id', $req->fyId)
                ->groupBy('class_id', 'fy_id')
                ->selectRaw('class_id, fy_id, COUNT(*) as active_students_count')
                ->get();

            $inactiveStudentsByClass = Student::where('status', 0)
                ->where('fy_id', $req->fyId)
                ->groupBy('class_id', 'fy_id')
                ->selectRaw('class_id, fy_id, COUNT(*) as inactive_students_count')
                ->get();

            $finalResult = [];
            $getDetl = [];

            $totalActiveFee = 0;
            $totalDeactiveFee = 0;
            $classWiseTotalFee = 0;

            foreach ($totalsAndFees as $totalAndFee) {
                $classId = $totalAndFee->class_id;

                $getcls = MsClass::where('id', $classId)->first();
                $className = $getcls->class_name;

                $fyId = $totalAndFee->fy_id;

                $activeCount = $activeStudentsByClass
                    ->where('class_id', $classId)
                    ->where('fy_id', $fyId)
                    ->first()->active_students_count ?? 0;

                $inactiveCount = $inactiveStudentsByClass
                    ->where('class_id', $classId)
                    ->where('fy_id', $fyId)
                    ->first()->inactive_students_count ?? 0;

                $totalFees = $totalAndFee->total_fees;

                $result = [
                    'class_id' => $className,
                    'fy_id' => $fyId,
                    'activeStudentsCounts' => $activeCount,
                    'inactiveStudentsCounts' => $inactiveCount,
                    'classWisePerStudentFees' => $totalFees,
                    'totalFeesFromActiveStudents' => $totalFees * $activeCount,
                    'totalFeesFromDeactiveStudents' => $totalFees * $inactiveCount,
                ];
                $classWiseTotalFee += $totalFees;
                $totalActiveFee += ($totalFees * $activeCount);
                $totalDeactiveFee += ($totalFees * $inactiveCount);
                $actualAmount = $totalActiveFee + $totalDeactiveFee;
                $finalResult[] = $result;
            }
            $getDetl = [
                "classWisePerStudentFees" => $classWiseTotalFee,
                "activeStudentTotalFees" => $totalActiveFee,
                "deactiveStudentTotalFees" => $totalDeactiveFee,
                "demandAmounts" => $actualAmount
            ];
            $result =  [
                "commonDetails" => $getDetl,
                "classWiseDetails" => $finalResult
            ];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Report", $result, "API_7.4", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_7.4", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // class wise demand fee reporting;
    public function classWiseReceiveFeeReport(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $totalsFeeColl = FeeCollection::where('status', 1)
                ->where('fy_id', $req->fyId)
                ->groupBy('class_id', 'fy_id')
                ->selectRaw('class_id, fy_id, SUM(fee_amount) as fee_amount, SUM(received_amount) as received_amount, 
                SUM(due_amount) as due_amount')
                ->orderBy('class_id')
                ->get();

            $std = DB::table('fee_collections')->distinct('student_id')->count();
            $finalResult = [];
            $totalReceive = 0;
            $totalDues = 0;
            foreach ($totalsFeeColl as $totalsFeeColl) {
                $classId = $totalsFeeColl->class_id;
                $fyId = $totalsFeeColl->fy_id;
                $rec = $totalsFeeColl->received_amount;
                $due = $totalsFeeColl->due_amount;
                $result = [
                    'class_id' => $classId,
                    'fy_id' => $fyId,
                    // 'activeStudentsCounts' => $std,
                    'feeAmount' => $totalsFeeColl->fee_amount,
                    'receivedAmount' => $totalsFeeColl->received_amount,
                    'dueAmount' => $totalsFeeColl->due_amount,
                ];
                $finalResult[] = $result;
                $totalReceive += $rec;
                $totalDues += $due;
            }
            $result1 =  [
                "details" => $finalResult,
                "totalReceivedAmount" => $totalReceive,
                "totalDue" => $totalDues
            ];

            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Report", $result1, "API_7.5", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_7.5", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
