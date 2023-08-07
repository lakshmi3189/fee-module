<?php

namespace App\Http\Controllers\API\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Report\Report;
use App\Models\FeeStructure\FeeCollection;
use Exception;
use DB;

/**commented due to change in new version code */
// use App\Models\Student\Student;
// use Illuminate\Support\Carbon;


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

    // version-2: written all login inside the model
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

    public function getFeeCollectionJson()
    {
        // Fetch the data from the database
        $data = FeeCollection::all();

        // Initialize an empty array to store the formatted data
        $formattedData = [];

        // Group the data by 'fy_name', 'class_name', and 'admission_no'
        $groupedData = $data->groupBy(['fy_name', 'class_name', 'admission_no']);

        foreach ($groupedData as $key => $collection) {
            // Extract the financial year, class, and admission number from the key
            list($fyName, $className, $admissionNo) = explode('_', $key);

            // Initialize an empty array to store the fee details for each month
            $feeDetails = [];

            foreach ($collection as $record) {
                $feeDetails[] = [
                    'id' => $record->id,
                    'feeHeadName' => $record->fee_head_name,
                    'amount' => $record->fee_amount,
                    'receivedAmount' => $record->received_amount,
                    'dueAmount' => $record->due_amount,
                ];
            }

            // Add the formatted data for each admission number to the main array
            $formattedData[] = [
                'fyName' => $fyName,
                'class' => $className,
                'admission_no' => $admissionNo,
                'feeHistory' => [
                    [
                        'monthName' => 'January', // You can dynamically get the month name based on month_id if needed.
                        'feeDtl' => $feeDetails,
                    ],
                    [
                        'monthName' => 'February', // You can dynamically get the month name based on month_id if needed.
                        'feeDtl' => $feeDetails,
                    ],
                    // Add other months here as needed...
                ],
            ];
        }

        // Convert the formatted data to JSON and return it
        return response()->json($formattedData);
    }


    // version-1: written all login in this controller
    // public function showFyClassMonthReport_old(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'fyId' => 'required|numeric',
    //         'classId' => 'required|numeric',
    //         'monthId' => 'numeric|nullable'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);

    //     try {
    //         $query = FeeCollection::where('fy_id', $req->fyId)
    //             ->where('class_id', $req->classId);

    //         // If monthId is provided and not null, add the where clause for month_id
    //         if ($req->monthId) {
    //             $query->where('month_id', $req->monthId);
    //         }
    //         $feeCollection = $query->get();
    //         $monthNames = $feeCollection->pluck('month_name')->unique();
    //         if (collect($feeCollection)->isEmpty())
    //             throw new Exception("Data Not Found");

    //         $studentIds = $feeCollection->pluck('student_id')->unique();
    //         $students = Student::select('id', 'admission_no', 'full_name')->whereIn('id', $studentIds)->get();
    //         if (collect($students)->isEmpty())
    //             throw new Exception("No Students Found with Fee Collection for the Given Criteria");

    //         $finaldata = $monthNames->map(function ($monthName) use ($feeCollection, $students) {
    //             return $students->map(function ($student, $key) use ($feeCollection, $monthName) {
    //                 $studentFee = $feeCollection->where("student_id", $student->id)->where('month_name', $monthName);
    //                 $className = ($studentFee->values())[0]["class_name"] ?? null;
    //                 $fyName = ($studentFee->values())[0]["fy_name"] ?? null;
    //                 $feehead = $studentFee->pluck("fee_head_name")->unique();

    //                 $feeDetails = $feehead->map(function ($headName, $key) use ($studentFee) {
    //                     $fee = $studentFee->where('fee_head_name', $headName);
    //                     return [
    //                         'feeHeadName' => $headName,
    //                         'amount' => $fee->sum("fee_amount"),
    //                         'receivedAmount' => $fee->sum("received_amount"),
    //                         'dueAmount' => $fee->sum("due_amount"),
    //                     ];
    //                 })->values();
    //                 return [
    //                     'fyName' => $fyName,
    //                     'class' => $className,
    //                     'monthName' => $monthName,
    //                     'studentDtl' => $student,
    //                     'feeDtl' => $feeDetails,
    //                 ];
    //             });
    //         });
    //         $result = $finaldata->values()->toArray();
    //         $queryTime = collect(DB::getQueryLog())->sum("time");
    //         return responseMsgsT(true, "View All Records", $result, "API_7.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_7.1", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }

    /*******************************************************************************************************************************
     * dummy code start
     *******************************************************************************************************************************/

    // public function showFyClassMonthReport1(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'fyId' => 'required|numeric',
    //         'classId' => 'required|numeric',
    //         'monthId' => 'numeric|nullable'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);

    //     try {
    //         Db::enableQueryLog();
    //         $query = FeeCollection::where('fy_id', $req->fyId)
    //             ->where('class_id', $req->classId);

    //         // If monthId is provided and not null, add the where clause for month_id
    //         if ($req->monthId) {
    //             $query->where('month_id', $req->monthId);
    //         }
    //         $feeCollection = $query->get();
    //         if (collect($feeCollection)->isEmpty())
    //             throw new Exception("Data Not Found");

    //         $studentIds = $feeCollection->pluck('student_id')->unique();
    //         $students = Student::select('id', 'admission_no', 'class_id', "full_name")->whereIn('id', $studentIds)->get();
    //         if (collect($students)->isEmpty())
    //             throw new Exception("No Students Found with Fee Collection for the Given Criteria");

    //         $monthWiseData = $feeCollection->groupBy(['month_name', 'class_id'])->map(function ($monthData, $key) use ($students) {
    //             $monthClassArray = explode('-', $key);
    //             $monthName = $monthClassArray[0];
    //             $newdata = collect();
    //             if (!$monthData->isEmpty()) {
    //                 $newdata = collect(array_values($monthData->toArray())[0] ?? []);
    //             }
    //             $monthData = $newdata;

    //             $classId = $monthData[0]["class_name"] ?? null; // Set classId to null if not available
    //             $stdId = $monthData[0]["student_id"] ?? null;
    //             $studentDtl = ($students->filter(function ($val) use ($stdId) {
    //                 return $val->id == $stdId;
    //             }));
    //             $feeDetails = $students->map(function ($student) use ($monthData) {
    //                 $details = $monthData->where('student_id', $student->id)->map(function ($item) {
    //                     return [
    //                         'feeHeadName' => $item["fee_head_name"],
    //                         'amount' => $item["fee_amount"],
    //                         'receivedAmount' => $item["received_amount"],
    //                         'dueAmount' => $item["due_amount"],
    //                     ];
    //                 })->values();
    //                 return [
    //                     'details' => $details,
    //                     "studentDtl" => $student
    //                 ];
    //             })->values();
    //             $studentDtl = $feeDetails->pluck("studentDtl");
    //             return [
    //                 'monthName' => $monthName,
    //                 'class' => $classId,
    //                 'feeDetails' => $feeDetails,
    //                 "studentDtl" => $studentDtl,
    //                 "stdId" => $stdId,
    //             ];
    //         });
    //         $result["data"] = $monthWiseData->values()->toArray();
    //         return responseMsgs(true, "View All Records", $result, "", "API_6.2", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_6.2", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }


    // public function showFyClassMonthReportBsckup(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'fyId' => 'required|numeric',
    //         'classId' => 'required|numeric',
    //         'monthId' => 'numeric|nullable'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);

    //     try {
    //         // $feeCollection = FeeCollection::where('fy_id', $req->fyId)
    //         //     ->where('class_id', $req->classId)
    //         //     ->OrWhere('month_id', $req->monthId)
    //         //     ->get();

    //         $query = FeeCollection::where('fy_id', $req->fyId)
    //             ->where('class_id', $req->classId);

    //         // If monthId is provided and not null, add the where clause for month_id
    //         if ($req->monthId !== null) {
    //             $query->where('month_id', $req->monthId);
    //         }
    //         $feeCollection = $query->get();
    //         if (collect($feeCollection)->isEmpty())
    //             throw new Exception("Data Not Found");

    //         $studentIds = $feeCollection->pluck('student_id')->unique();
    //         $students = Student::select('id', 'admission_no', 'class_id')->whereIn('id', $studentIds)->get();
    //         if (collect($students)->isEmpty())
    //             throw new Exception("No Students Found with Fee Collection for the Given Criteria");

    //         $monthWiseData = $feeCollection->groupBy(['month_name', 'class_id'])->map(function ($monthData, $key) use ($students) {
    //             $monthClassArray = explode('-', $key);
    //             $monthName = $monthClassArray[0];
    //             $classId = $monthClassArray[1] ?? null; // Set classId to null if not available

    //             $feeDetails = $students->map(function ($student) use ($monthData) {
    //                 $details = $monthData->where('student_id', $student->id)->map(function ($item) {
    //                     return [
    //                         'feeHeadName' => $item->fee_head_name,
    //                         'amount' => $item->fee_amount,
    //                         'receivedAmount' => $item->received_amount,
    //                         'dueAmount' => $item->due_amount,
    //                     ];
    //                 })->values();

    //                 return [
    //                     'admission' => $student->admission_no,
    //                     'details' => $details,
    //                 ];
    //             })->values();

    //             return [
    //                 'monthName' => $monthName,
    //                 'class' => $classId,
    //                 'feeDetails' => $feeDetails,
    //             ];
    //         });
    //         $result["data"] = $monthWiseData->values()->toArray();
    //         return responseMsgs(true, "View All Records", $result, "", "API_6.2", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_6.2", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }

    // public function showFyClassMonthReport2(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'fyId' => 'required|numeric',
    //         'classId' => 'required|numeric',
    //         'monthId' => 'numeric|nullable'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);

    //     try {
    //         $feeCollection = FeeCollection::where('fy_id', $req->fyId)
    //             ->where('class_id', $req->classId)
    //             ->orWhere('month_id', $req->monthId)
    //             ->get();

    //         if ($feeCollection->isEmpty()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Data Not Found',
    //             ]);
    //         }

    //         $studentId = $feeCollection->pluck('student_id')->unique();
    //         $students = Student::select('id', 'admission_no')->whereIn('id', $studentId)->get();

    //         if ($students->isEmpty()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'No Students Found with Fee Collection for the Given Criteria',
    //             ]);
    //         }

    //         $monthWiseData = $feeCollection->groupBy(['month_name', 'class_id'])->map(function ($monthData, $key) use ($students) {
    //             $monthClassArray = explode('-', $key);
    //             $monthName = $monthClassArray[0];
    //             $classId = $monthClassArray[1] ?? null; // Set classId to null if not available

    //             $feeDetails = $students->map(function ($student) use ($monthData) {
    //                 $details = $monthData->where('student_id', $student->id)->map(function ($item) {
    //                     return [
    //                         'feeHeadName' => $item->fee_head_name,
    //                         'amount' => $item->fee_amount,
    //                         'receivedAmount' => $item->received_amount,
    //                         'dueAmount' => $item->due_amount,
    //                     ];
    //                 })->values();

    //                 return [
    //                     'admission' => $student->admission_no,
    //                     'details' => $details,
    //                 ];
    //             })->values();

    //             return [
    //                 'monthName' => $monthName,
    //                 'class' => $classId,
    //                 'feeDetails' => $feeDetails,
    //             ];
    //         });

    //         $result["status"] = true;
    //         $result["message"] = "View All Records";
    //         $result["meta-data"] = [
    //             'apiId' => "API_7.1",
    //             'responsetime' => responseTime(),
    //             'epoch' => now()->format('Y-m-d H:i:s'),
    //             'action' => "POST",
    //             'deviceId' => $req->deviceId ?? ""
    //         ];
    //         $result["data"] = $monthWiseData->values()->toArray();

    //         return response()->json($result);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_7.1", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }


    // public function showFyClassMonthReport22(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'fyId' => 'required|numeric',
    //         'classId' => 'required|numeric',
    //         'monthId' => 'numeric|nullable'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);

    //     try {
    //         $feeCollection = FeeCollection::where('fy_id', $req->fyId)
    //             ->where('class_id', $req->classId)
    //             ->orWhere('month_id', $req->monthId)
    //             ->get();

    //         if ($feeCollection->isEmpty()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Data Not Found',
    //             ]);
    //         }

    //         $studentId = $feeCollection->pluck('student_id')->unique();
    //         $students = Student::select('id', 'admission_no')->whereIn('id', $studentId)->get();

    //         $monthWiseData = $feeCollection->groupBy(['month_name', 'class_id'])->map(function ($monthData, $key) use ($students) {
    //             $monthClassArray = explode('-', $key);
    //             $monthName = $monthClassArray[0];
    //             $classId = $monthClassArray[1] ?? null; // Set classId to null if not available

    //             $feeDetails = $students->map(function ($student) use ($monthData) {
    //                 $details = $monthData->where('student_id', $student->id)->map(function ($item) {
    //                     return [
    //                         'feeHeadName' => $item->fee_head_name,
    //                         'amount' => $item->fee_amount,
    //                         'receivedAmount' => $item->received_amount,
    //                         'dueAmount' => $item->due_amount,
    //                     ];
    //                 })->values();

    //                 return [
    //                     'admission' => $student->admission_no,
    //                     'details' => $details,
    //                 ];
    //             })->values();

    //             return [
    //                 'monthName' => $monthName,
    //                 'class' => $classId,
    //                 'feeDetails' => $feeDetails,
    //             ];
    //         });

    //         $result["status"] = true;
    //         $result["message"] = "View All Records";
    //         $result["meta-data"] = [
    //             'apiId' => "API_7.1",
    //             'responsetime' => responseTime(),
    //             'epoch' => now()->format('Y-m-d H:i:s'),
    //             'action' => "POST",
    //             'deviceId' => $req->deviceId ?? ""
    //         ];
    //         $result["data"] = $monthWiseData->values()->toArray();

    //         return response()->json($result);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_7.1", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }


    // public function showFyClassMonthReportdemo(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'fyId' => 'required|numeric',
    //         'classId' => 'required|numeric',
    //         'monthId' => 'numeric|nullable'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);

    //     try {
    //         $feeCollection = FeeCollection::where('fy_id', $req->fyId)
    //             ->where('class_id', $req->classId)
    //             ->orWhere('month_id', $req->monthId)
    //             ->get();

    //         if ($feeCollection->isEmpty()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Data Not Found',
    //             ]);
    //         }

    //         $studentId = $feeCollection->pluck('student_id')->unique();
    //         $students = Student::select('id', 'admission_no')->whereIn('id', $studentId)->get();

    //         $monthWiseData = $feeCollection->groupBy(['month_name', 'class_id'])->map(function ($monthData, $key) use ($students) {
    //             $monthClassArray = explode('-', $key);
    //             $monthName = $monthClassArray[0];
    //             $classId = $monthClassArray[1] ?? null; // Set classId to null if not available

    //             $feeDetails = $students->map(function ($student) use ($monthData) {
    //                 $details = $monthData->where('student_id', $student->id)->map(function ($item) {
    //                     return [
    //                         'feeHeadName' => $item->fee_head_name,
    //                         'amount' => $item->fee_amount,
    //                         'receivedAmount' => $item->received_amount,
    //                         'dueAmount' => $item->due_amount,
    //                     ];
    //                 })->values();

    //                 return [
    //                     'admission' => $student->admission_no,
    //                     'details' => $details,
    //                 ];
    //             })->values();

    //             return [
    //                 'monthName' => $monthName,
    //                 'class' => $classId,
    //                 'feeDetails' => $feeDetails,
    //             ];
    //         });

    //         $result["status"] = true;
    //         $result["message"] = "View All Records";
    //         $result["meta-data"] = [
    //             'apiId' => "API_7.1",
    //             'responsetime' => responseTime(),
    //             'epoch' => now()->format('Y-m-d H:i:s'),
    //             'action' => "POST",
    //             'deviceId' => $req->deviceId ?? ""
    //         ];
    //         $result["data"] = $monthWiseData->values()->toArray();

    //         return response()->json($result);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_7.1", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }



    // public function showFyClassMonthReport1(Request $req)
    // {

    //     $validator = Validator::make($req->all(), [
    //         'fyId' => 'required|numeric',
    //         'classId' => 'required|numeric',
    //         'monthId' => 'numeric|nullable'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);
    //     try {
    //         $feeCollection = FeeCollection::where('fy_id', $req->fyId)
    //             ->where('class_id', $req->classId)
    //             ->orWhere('month_id', $req->monthId)
    //             ->get();
    //         if ($feeCollection->isEmpty()) {
    //             return response()->json([
    //                 'status' => false,
    //                 'message' => 'Data Not Found',
    //                 // 'data' => []
    //             ]);
    //         }
    //         $studentId = $feeCollection->first()->student_id;
    //         $student = Student::select('admission_no', 'full_name', 'class_name', 'section_name', 'roll_no')->where('id', $studentId)->first();


    //         $monthWiseData = $feeCollection->groupBy('month_name')->map(function ($monthData) {
    //             $totalFees = $monthData->sum('fee_amount');
    //             $totalRecFees = $monthData->sum('received_amount');
    //             $totalDueFees = $monthData->sum('due_amount');
    //             $paymentDate = Carbon::parse($monthData->first()->payment_date)->format('d-m-y');
    //             $monthPaid = $monthData->first()->month_name;
    //             $isPaid = $monthData->first()->is_paid;
    //             $id = $monthData->first()->id;
    //             $details = $monthData->map(function ($item) {
    //                 return [
    //                     'id' => $item->id,
    //                     'feeHeadName' => $item->fee_head_name,
    //                     'amount' => $item->fee_amount,
    //                     'receivedAmount' => $item->received_amount,
    //                     'dueAmount' => $item->due_amount,
    //                 ];
    //             });
    //             return [
    //                 // 'id' => $id,
    //                 'monthName' => $monthData->first()->month_name,
    //                 'totalFees' => $totalFees,
    //                 'receivedAmount' => $totalRecFees,
    //                 'dueAmount' => $totalDueFees,
    //                 'paymentDate' => $paymentDate,
    //                 'monthPaid' => $monthPaid,
    //                 'isPaid' => $isPaid,
    //                 'details' => $details->toArray(),
    //             ];
    //         });
    //         $result["stdDetails"] = $student;
    //         $result["feeDetails"] = $monthWiseData->values()->toArray();
    //         return responseMsgs(true, "View All Records", $result, "", "API_7.1", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_7.1", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }
    /********************************************************************************************************************************
     * dummy code end
     *******************************************************************************************************************************/
}
