<?php

namespace App\Http\Controllers\API\FeeStructure;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure\FeeCollection;
// use App\Models\Payment\Payment;
use App\Models\Student\Student;
use App\Models\Master\MsFinancialYear;
use App\Models\Master\FmFeeHead;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use App\Models\Master\ReceiptCounter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;


// use Haruncpi\LaravelIdGenerator\IdGenerator;
use DB;

/**
 * | Created On-26-07-2023 
 * | Created By- Lakshmi Kumari
 * | Code Status : Open 
 */
class FeeCollectionController extends Controller
{

    private $_mFeeCollections;
    // private $_mPayment;
    private $_mReceiptCounters;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mFeeCollections = new FeeCollection();
        // $this->_mPayment = new Payment();
        $this->_mReceiptCounters = new ReceiptCounter();
    }
    // Add records
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId' => 'required|numeric',
            // 'fyName' => 'required|string',
            'classId' => 'required|numeric',
            'className' => 'required|string',
            'admissionNo' => 'required|string',
            'paymentMode' => 'required|string',
            'discount' => 'numeric|nullable',
            'discountDescription' => 'string|nullable',
            'paymentDate' => 'required|string',
            'feeCollection' => 'required|array',
            'feeCollection.*.monthId' => 'required|numeric',
            'feeCollection.*.monthName' => 'required|string',
            'feeCollection.*.feeHeadId' => 'required|numeric',
            'feeCollection.*.feeHeadName' => 'string|nullable',
            'feeCollection.*.amount' => 'required|numeric',
            'feeCollection.*.receivedAmount' => 'required|numeric',
            'feeCollection.*.dueAmount' => 'required|numeric',
            'feeCollection.*.description' => 'string|nullable'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            DB::beginTransaction();
            $result = array();
            $receipt = $this->_mReceiptCounters->generateReceiptNumber();

            $fyObj = MsFinancialYear::where('id', $req->fyId)->firstOrFail();
            $fyName = $fyObj->financial_year;

            $mStudents = Student::whereRaw('LOWER(admission_no) LIKE ?', [strtolower("%$req->admissionNo%")])
                // where('admission_no', $req->admissionNo) 
                ->where('status', 1)
                ->first();
            if (collect($mStudents)->isEmpty())
                throw new Exception('Admission no is not existing');
            $studentId  = $mStudents->id;

            foreach ($req['feeCollection'] as $feeData) {
                $isGroupExists = $this->_mFeeCollections->readFeeCollectionGroup($feeData, $req);
                if (collect($isGroupExists)->isNotEmpty())
                    throw new Exception("Fee Already Existing");

                $feeCollection = new FeeCollection();
                $feeCollection->student_id = $studentId;
                $feeCollection->fy_id = $req['fyId'];
                $feeCollection->fy_name = $fyName;
                $feeCollection->month_id = $feeData['monthId'];
                $feeCollection->month_name = $feeData['monthName'];
                $feeCollection->student_id = $studentId;
                $feeCollection->admission_no = Str::title($req['admissionNo']);
                $feeCollection->class_id = $req['classId'];
                $feeCollection->class_name = $req['className'];
                $feeCollection->discount = $req['discount'];
                $feeCollection->discount_description = $req['discountDescription'];
                $feeCollection->payment_mode = $req['paymentMode'];
                $feeCollection->payment_date = $req['paymentDate'];
                $feeCollection->fee_head_id = $feeData['feeHeadId'];
                $feeCollection->fee_head_name = $feeData['feeHeadName'];
                $feeCollection->fee_amount = $feeData['amount'];
                $feeCollection->received_amount = $feeData['receivedAmount'];
                $feeCollection->due_amount = $feeData['dueAmount'];
                $feeCollection->receipt_no = $receipt;
                $feeCollection->description = '';
                $feeCollection->description = $feeData['description'];
                $feeCollection->created_by = authUser()->id;
                $feeCollection->ip_address = getClientIpAddress();
                $feeCollection->json_logs = trim($feeCollection->json_logs . "," . json_encode($feeCollection), ",");
                // print_var($feeCollection);
                // die;
                $feeCollection->save();
            }
            DB::commit();
            $result["receiptNo"] = $receipt;
            return responseMsgs(true, "Successfully Saved", $result, "", "API_6.1", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollback();
            return responseMsgs(false, $e->getMessage(), [], "", "API_6.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //show data by receipt no 
    public function showByReceiptNo(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'receiptNo' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $show = $this->_mFeeCollections->getReceiptNoExist($req);
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");

            $receiptNo = $req->input('receiptNo');

            // Fetch the feeCollection data based on the provided receipt number
            $feeCollection = FeeCollection::where('receipt_no', $receiptNo)->get();
            // $studentDetails = Student::where('id', $feeCollection->student_id)->first();
            // Check if data is found and return the response accordingly
            if ($feeCollection->isEmpty()) {
                return response()->json([
                    'status' => false,
                    'message' => 'No data found for the provided receipt number.',
                    'data' => []
                ]);
            }
            $studentId = $feeCollection->first()->student_id;
            $student = Student::select('admission_no', 'full_name', 'class_name', 'section_name', 'roll_no')->where('id', $studentId)->first();

            // Group the feeCollection data by "month_name"
            $monthWiseData = $feeCollection->groupBy('month_name')->map(function ($monthData) {
                $totalFees = $monthData->sum('fee_amount');
                $totalRecFees = $monthData->sum('received_amount');
                $discount = $monthData->first()->discount;
                $netPay = ($totalRecFees - (($totalRecFees * $discount) / 100));
                $totalDueFees = $monthData->sum('due_amount');
                $paymentDate = Carbon::parse($monthData->first()->payment_date)->format('d-m-y');
                $monthPaid = $monthData->first()->month_name;
                $isPaid = $monthData->first()->is_paid;
                $isMonth = $monthData->first()->is_month;
                $details = $monthData->map(function ($item) {
                    return [
                        'feeHeadName' => $item->fee_head_name,
                        'amount' => $item->fee_amount,
                        'receivedAmount' => $item->received_amount,
                        'dueAmount' => $item->due_amount,
                    ];
                });
                return [
                    'monthName' => $monthData->first()->month_name,
                    'totalFees' => $totalFees,
                    'receivedAmount' => $totalRecFees,
                    'dueAmount' => $totalDueFees,
                    'paymentDate' => $paymentDate,
                    'discount' => $discount,
                    'netPay' => $netPay,
                    'monthPaid' => $monthPaid,
                    'isPaid' => $isPaid,
                    'isMonthPresent' => $isMonth,
                    'details' => $details->toArray(),
                ];
            });
            $result["stdDetails"] = $student;
            $result["feeDetails"] = $monthWiseData->values()->toArray();
            return responseMsgs(true, "View All Records", $result, "", "API_6.2", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_6.2", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //show data by receipt no - report
    //fyId, classId, monthId, receiptNo
    public function generateByFyClassMonthAndReceiptNo(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId' => 'required|numeric',
            'classId' => 'required|numeric',
            'monthId' => 'required|numeric',
            'admissionNo' => 'required|string'
            // 'receiptNo' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            // $validatedData = $req->validate([
            //     'fyId' => 'required|numeric',
            //     'classId' => 'required|numeric',
            //     'monthId' => 'required|numeric',
            //     // 'receiptNo' => 'required|string',
            //     'admissionNo' => 'required|string'
            // ]);
            // return $validatedData['fyId'];
            // die;
            // return $hashedData = Hash::make($validatedData['fyId'] . $validatedData['classId'] . $validatedData['monthId'] . $validatedData['receiptNo']);

            // $show = $this->_mFeeCollections->getReceiptNoExist($req);
            // if (collect($show)->isEmpty())
            //     throw new Exception("Receipt No Not Found");

            // $receiptNo = $req->input('receiptNo');


            $mStudents = Student::whereRaw('LOWER(admission_no) LIKE ?', [strtolower("%$req->admissionNo%")])
                ->where('status', 1)
                ->first();
            if (collect($mStudents)->isEmpty())
                throw new Exception('Admission no is not existing');
            $studentId  = $mStudents->id;

            // Fetch the feeCollection data based on the provided receipt number
            $feeCollection = FeeCollection::where('fy_id', $req->fyId)
                ->where('class_id', $req->classId)
                ->where('month_id', $req->monthId)
                // ->where('receipt_no', $req->receiptNo)
                ->where('student_id', $studentId)
                ->get();

            // Check if data is found and return the response accordingly            
            if ($feeCollection->isEmpty())
                throw new Exception('Fee Details Not Found');
            //  {
            //     return response()->json([
            //         'status' => false,
            //         'message' => 'Data Not Found',
            //         'data' => []
            //     ]);
            // }
            //$studentId = $feeCollection->first()->student_id;
            $student = Student::select('admission_no', 'full_name', 'class_name', 'section_name', 'roll_no')
                ->where('id', $studentId)
                ->where('status', 1)
                ->first();

            // Group the feeCollection data by "month_name"
            $monthWiseData = $feeCollection->groupBy('month_name')->map(function ($monthData) {
                $totalFees = $monthData->sum('fee_amount');
                $totalRecFees = $monthData->sum('received_amount');
                $discount = $monthData->first()->discount;
                $netPay = ($totalRecFees - (($totalRecFees * $discount) / 100));
                $words = getIndianCurrency($netPay);
                $totalDueFees = $monthData->sum('due_amount');
                $paymentDate = Carbon::parse($monthData->first()->payment_date)->format('d-m-y');
                $monthPaid = $monthData->first()->month_name;
                $isPaid = $monthData->first()->is_paid;
                $details = $monthData->map(function ($item) {
                    return [
                        'feeHeadName' => $item->fee_head_name,
                        'amount' => $item->fee_amount,
                        'receivedAmount' => $item->received_amount,
                        'dueAmount' => $item->due_amount,
                    ];
                });
                return [
                    'monthName' => $monthData->first()->month_name,
                    'receiptNo' => $monthData->first()->receipt_no,
                    'totalFees' => $totalFees,
                    'receivedAmount' => $totalRecFees,
                    'dueAmount' => $totalDueFees,
                    'paymentDate' => $paymentDate,
                    'discount' => $discount,
                    'netPay' => $netPay,
                    'inWords' => $words,
                    'monthPaid' => $monthPaid,
                    'isPaid' => $isPaid,
                    'details' => $details->toArray(),
                ];
            });
            $result["stdDetails"] = $student;
            $result["feeDetails"] = $monthWiseData->values()->toArray();
            return responseMsgs(true, "View All Records", $result, "", "API_6.", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_6.", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function convertAmountToWords($amount)
    {
        $ones = array(
            1 => "One", 2 => "Two", 3 => "Three", 4 => "Four", 5 => "Five",
            6 => "Six", 7 => "Seven", 8 => "Eight", 9 => "Nine", 10 => "Ten",
            11 => "Eleven", 12 => "Twelve", 13 => "Thirteen", 14 => "Fourteen", 15 => "Fifteen",
            16 => "Sixteen", 17 => "Seventeen", 18 => "Eighteen", 19 => "Nineteen"
        );
        $tens = array(
            2 => "Twenty", 3 => "Thirty", 4 => "Forty", 5 => "Fifty",
            6 => "Sixty", 7 => "Seventy", 8 => "Eighty", 9 => "Ninety"
        );

        if ($amount < 20) {
            return $ones[$amount];
        } elseif ($amount < 100) {
            return $tens[floor($amount / 10)] . " " . $ones[$amount % 10];
        } elseif ($amount < 1000) {
            return $ones[floor($amount / 100)] . " Hundred " . $this->convertAmountToWords($amount % 100);
        } elseif ($amount < 1000000) {
            return $this->convertAmountToWords(floor($amount / 1000)) . " Thousand " . $this->convertAmountToWords($amount % 1000);
        } else {
            return "Out of Range";
        }
    }

    public function updateDue(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'feeCollId' => 'required|numeric',
            'receivedAmount' => 'required|numeric',
            'dueAmount' => 'numeric|nullable'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            DB::beginTransaction();
            $dt =   Carbon::now();
            $getData = $this->_mFeeCollections::findOrFail($req->feeCollId);
            $totalFee = $getData->fee_amount;
            $recFee = $getData->received_amount + $req->receivedAmount;
            $dueFee = $totalFee - $recFee;

            if ($recFee > $totalFee)
                throw new Exception("Fee amount can not be greater than total fee");

            $metaReqs = [
                'id' => $req->feeCollId,
                'received_amount' => $recFee,
                'due_amount' => $dueFee,
                'version_no' => $getData->version_no + 1,
                'updated_at' => Carbon::now()
            ];
            $metaReqs = array_merge($metaReqs, [
                'json_logs' => trim($getData->json_logs . ", { last-update : " . $dt . "} ," . json_encode($metaReqs), ",")
            ]);
            $getData->update($metaReqs);
            $data = [
                'monthName' => $getData->month_name,
                'admissionNo' => $getData->admission_no,
                'className' => $getData->class_name,
                'className' => $getData->class_name,
                'feeHeadName' => $getData->fee_head_name,
                'totalFeeAmount' => $totalFee,
                'receivedAmount' => $recFee,
                'dueAmount' => $dueFee
            ];
            DB::commit();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Updated Successfully", $data, "API_6.3", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            DB::rollback();
            return responseMsgs(false, $e->getMessage(), [], "", "API_6.3", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Get Records By Id
     */
    // public function showById(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'id' => 'required|numeric'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);
    //     try {
    //         $show = $this->_mFeeCollections->getGroupById($req);
    //         if (collect($show)->isEmpty())
    //             throw new Exception("Data Not Found");
    //         return responseMsgs(true, "", $show, "", "API_15.3", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_15.3", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }





    //for future refrence

    // // Edit records
    // public function edit(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'id' => 'numeric',
    //         'admissionNo' => "required|string",
    //         'monthName' => 'required|string',
    //         "totalFee" => "required|numeric",
    //         'grandTotal' => 'required|numeric'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);
    //     try {
    //         $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));
    //         $mStudents = Student::where('admission_no', $req->admissionNo)
    //             ->where('status', 1)
    //             ->first();
    //         if (collect($mStudents)->isEmpty())
    //             throw new Exception('Admission no is not existing');
    //         $studentId  = $mStudents->id;
    //         $isExists = $this->_mFeeCollections->readFeeCollectionGroup($req, $studentId, $fy);
    //         if ($isExists && $isExists->where('id', '!=', $req->id)->isNotEmpty())
    //             throw new Exception("Fee Collection Already existing");
    //         $getData = $this->_mFeeCollections::findOrFail($req->id);
    //         $metaReqs = [
    //             'student_id' => $studentId,
    //             'month_name' => $req->monthName,
    //             'total_fee' => $req->totalFee,
    //             'grand_total' => $req->grandTotal,
    //             'academic_year' => $fy,
    //             'version_no' => $getData->version_no + 1,
    //             'updated_at' => Carbon::now()
    //         ];
    //         $metaReqs = array_merge($metaReqs, [
    //             'json_logs' => trim($getData->json_logs . "," . json_encode($metaReqs), ",")
    //         ]);
    //         if (isset($req->status)) {              // In Case of Deactivation or Activation 
    //             $status = $req->status == 'deactive' ? 0 : 1;
    //             $metaReqs = array_merge($metaReqs, [
    //                 'status' => $status
    //             ]);
    //         }
    //         $editData = $this->_mFeeCollections::findOrFail($req->id);
    //         $editData->update($metaReqs);
    //         return responseMsgs(true, "Successfully Updated", [$metaReqs], "", "API_15.2", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_15.2", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }

    // /**
    //  * | Get Discont Group By Id
    //  */
    // public function show1(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'receiptNo' => 'required|string'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);
    //     try {
    //         $show = $this->_mFeeCollections->getGroupByReceiptNo($req);
    //         if (collect($show)->isEmpty())
    //             throw new Exception("Data Not Found");
    //         return responseMsgs(true, "", $show, "", "API_15.3", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_15.3", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }



    // //View All
    // public function retrieveAll(Request $req)
    // {
    //     try {
    //         $getData = $this->_mFeeCollections->retrieve();
    //         return responseMsgs(true, "", $getData, "", "API_15.4", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_15.4", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }

    // //Delete
    // public function delete(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'status' => 'required|in:active,deactive'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);
    //     try {
    //         if (isset($req->status)) {                  // In Case of Deactivation or Activation 
    //             $status = $req->status == 'deactive' ? 0 : 1;
    //             $metaReqs =  [
    //                 'status' => $status
    //             ];
    //         }
    //         $delete = $this->_mFeeCollections::findOrFail($req->id);
    //         //  if ($teachingTitle->status == 0)
    //         //      throw new Exception("Records Already Deleted");
    //         $delete->update($metaReqs);
    //         return responseMsgs(true, "Deleted Successfully", [], "", "API_15.5", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_15.5", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }

    // //Active All
    // public function activeAll(Request $req)
    // {
    //     try {
    //         $data = $this->_mFeeCollections->active();
    //         return responseMsgs(true, "", $data, "", "API_15.6", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_15.6", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }

    // //view by name
    // public function search(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'search' => 'required|string'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);
    //     try {
    //         $search = $this->_mFeeCollections->searchByName($req->search);
    //         if (collect($search)->isEmpty())
    //             throw new Exception("Data Not Found");
    //         return responseMsgs(true, "", $search, "", "API_15.7", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_15.7", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }

    // // search fees by admission no
    // public function searchFeesByAdmNo(Request $req)
    // {
    //     //Description: Get records by id
    //     $validator = Validator::make($req->all(), [
    //         'admissionNo' => 'required|string'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);
    //     try {
    //         $mStudents = Student::where('admission_no', $req->admissionNo)
    //             ->where('status', 1)
    //             ->first();
    //         if (collect($mStudents)->isEmpty())
    //             throw new Exception('Admission no is not existing');
    //         $studentId  = $mStudents->id;

    //         $msg = '';
    //         $data = $this->_mFeeCollections::select('id', 'student_id', 'month_name', 'is_paid')
    //             ->where([['student_id', '=', $studentId], ['status', '=', '1']])->get();
    //         if ($data != "") {
    //             $msg = "Fee Already Existing";
    //             $data1 = $data;
    //         } else {
    //             $msg = "Fees Not Found";
    //             $data1 = ['admission_no' => $req->admissionNo, 'message' => 'Admission No. not found', 'value' => 'false'];
    //         }
    //         return responseMsgs(true, $msg, $data1, "API_15.8", "", "146ms", "post", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "API_15.8", "", "", "post", $req->deviceId ?? "");
    //     }
    // }

    // /**
    //  * | show fees by receipt no
    //  */
    // public function showReceipt(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'receiptNo' => 'required|string'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);
    //     try {
    //         $show = $this->_mFeeCollections->getGroupByReceipt($req);
    //         if (collect($show)->isEmpty())
    //             throw new Exception("Data Not Found");
    //         return responseMsgs(true, "", $show, "", "API_15.9", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_15.9", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }

    // /**
    //  * | show fees by receipt no
    //  */
    // public function showReceiptTest(Request $req)
    // {
    //     $validator = Validator::make($req->all(), [
    //         'receiptNo' => 'required|string'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);
    //     try {
    //         $show = $this->_mFeeCollections->getGroupByReceiptTest($req);
    //         if (collect($show)->isEmpty())
    //             throw new Exception("Data Not Found");
    //         return responseMsgs(true, "", $show, "", "API_15.9", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API_15.9", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }
}
