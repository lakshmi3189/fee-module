<?php

namespace App\Http\Controllers\API\FeeStructure;

use App\Http\Controllers\Controller;
use App\Models\FeeStructure\FeeCollection;
use App\Models\Payment\Payment;
use App\Models\Student\Student;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Haruncpi\LaravelIdGenerator\IdGenerator;


class FeeCollectionController extends Controller
{
    /**
     * | Created On-27-06-2023 
     * | Created By- Umesh Kumar 
     * | Code Status : Open 
     */

    private $_mFeeCollections;
    private $_mPayment;

    public function __construct()
    {
        $this->_mFeeCollections = new FeeCollection();
        $this->_mPayment = new Payment();
    }
    // Add records
    public function store(Request $req)
    {
        // $validator = Validator::make($req->all(), [
        //     'admissionNo'=>"required|string",
        //     'monthName' => 'required|string',
        //     "totalFee"=>"required|numeric",
        //     'grandTotal' => 'required|numeric'           
        //  ]);
        $validator = Validator::make($req->all(), [
            'admissionNo' => 'required',
            'paymentModeId' => 'required|numeric',
            'grandTotal' => 'required|numeric',
            'paymentDate' => 'required|string',
            'isPaid' => 'required|numeric',
            'feeCollection' => 'required|array',
            'feeCollection.*.admissionNo' => 'required',
            'feeCollection.*.monthName' => 'required',
            'feeCollection.*.totalFee' => 'required|numeric',
            'feeCollection.*.isPaid' => 'required|numeric',
            'feeCollection.*.paymentDate' => 'required|string',
            'feeCollection.*.paymentModeId' => 'required|numeric',
            'feeCollection.*.grandTotal' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));

            $generateReceipt = IdGenerator::generate([
                'table' => 'fee_collections',
                'field' => 'receipt_no',
                'length' => 20,
                'prefix' => 'Receipt/' . $fy . '/',
                // 'prefix' => date('Y').'/',
                'reset_on_prefix_change' => true
            ]);

            $mStudents = Student::where('admission_no', $req->admissionNo)
                ->where('status', 1)
                ->first();
            if (collect($mStudents)->isEmpty())
                throw new Exception('Admission no is not existing');
            $studentId  = $mStudents->id;
            $studentFY  = $mStudents->academic_year;
            // $isExists = $this->_mFeeCollections->readFeeCollectionGroup($req, $studentId, $studentFY);
            // if (collect($isExists)->isNotEmpty())
            //     throw new Exception("Fee Collection Already Existing");
            $data = array();
            if ($req['feeCollection'] != "") {
                foreach ($req['feeCollection'] as $ob) {
                    // print_var($ob);
                    // die;
                    // $isGroupExists = $this->_mFeeCollections->readAllFeeCollectionGroup($ob, $studentId, $studentFY);
                    // if (collect($isGroupExists)->isNotEmpty())
                    //     throw new Exception("Fee Collection Already Existing");

                    $isExists = $this->_mFeeCollections->readAllFeeCollectionGroup($ob, $studentId, $studentFY);
                    if (collect($isExists)->isNotEmpty())
                        throw new Exception("Fee Collection Already Existing");

                    $metaReqs = [
                        'student_id' => $studentId,
                        'month_name' => $ob['monthName'],
                        'total_fee' => $ob['totalFee'],
                        'grand_total' => $ob['grandTotal'],
                        'payment_mode_id' => $ob['paymentModeId'],
                        'is_paid' => $ob['isPaid'],
                        'payment_date' => $ob['paymentDate'],
                        'academic_year' => $studentFY,
                        'receipt_no' => $generateReceipt,
                        'ip_address' => getClientIpAddress(),
                        'school_id' => authUser()->school_id,
                        'created_by' => authUser()->id,
                        'version_no' => 0
                    ];
                    $metaReqs = array_merge($metaReqs, [
                        'json_logs' => trim(json_encode($metaReqs), ",")
                    ]);
                    $this->_mFeeCollections->store($metaReqs);


                    // $metaReqs1 = [
                    //     'student_id' => $studentId,
                    //     'fee_collection_id' => '0',
                    //     'payment_mode_id' => $ob['paymentModeId'],
                    //     'is_paid' => $ob['isPaid'],
                    //     'payment_date' => $ob['paymentDate'],
                    //     'bank_approved' => '0',
                    //     'academic_year' => $studentFY,
                    //     'ip_address' => getClientIpAddress(),
                    //     'school_id' => authUser()->school_id,
                    //     'created_by' => authUser()->id,
                    //     'version_no' => 0
                    // ];
                    // $metaReqs1 = array_merge($metaReqs1, [
                    //     'json_logs' => trim(json_encode($metaReqs1), ",")
                    // ]);
                    // $this->_mPayment->store($metaReqs1);

                    // $mFeeCollection = new FeeCollection;
                    // $mFeeCollection->student_id = $studentId;
                    // $mFeeCollection->month_name =  $ob['monthName'];
                    // $mFeeCollection->total_fee = $ob['totalFee'];
                    // $mFeeCollection->grand_total = $ob['grandTotal'];
                    // // $mFeeCollection->isPaid = $ob['isPaid'];
                    // // $mFeeCollection->paymentDate = $ob['paymentDate'];
                    // $mFeeCollection->academic_year = $studentFY;
                    // $mFeeCollection->school_id = authUser()->school_id;
                    // $mFeeCollection->created_by = authUser()->id;
                    // $mFeeCollection->ip_address = getClientIpAddress();
                    // $mFeeCollection->save();
                    // // dd($mFeeCollection);
                    // $data[] = $mFeeCollection;
                }
                // die;
            }
            //Store in Payment Table
            $metaReqs = [
                'student_id' => $studentId,
                'payment_mode_id' => $req->paymentModeId,
                'is_paid' => $req->isPaid,
                'payment_date' => $req->paymentDate,
                'sub_total' => $req->grandTotal,
                'academic_year' => $fy,
                'ip_address' => getClientIpAddress(),
                'school_id' => authUser()->school_id,
                'created_by' => authUser()->id,
                'version_no' => 0
            ];
            $metaReqs = array_merge($metaReqs, [
                'json_logs' => trim(json_encode($metaReqs), ",")
            ]);
            // return $metaReqs; die; 
            $data = ['receiptNo' => $generateReceipt];
            $this->_mPayment->store($metaReqs);
            return responseMsgs(true, "Successfully Saved", $data, "", "API_15.1", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_15.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // Edit records
    public function edit(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'numeric',
            'admissionNo' => "required|string",
            'monthName' => 'required|string',
            "totalFee" => "required|numeric",
            'grandTotal' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));
            $mStudents = Student::where('admission_no', $req->admissionNo)
                ->where('status', 1)
                ->first();
            if (collect($mStudents)->isEmpty())
                throw new Exception('Admission no is not existing');
            $studentId  = $mStudents->id;
            $isExists = $this->_mFeeCollections->readFeeCollectionGroup($req, $studentId, $fy);
            if ($isExists && $isExists->where('id', '!=', $req->id)->isNotEmpty())
                throw new Exception("Fee Collection Already existing");
            $getData = $this->_mFeeCollections::findOrFail($req->id);
            $metaReqs = [
                'student_id' => $studentId,
                'month_name' => $req->monthName,
                'total_fee' => $req->totalFee,
                'grand_total' => $req->grandTotal,
                'academic_year' => $fy,
                'version_no' => $getData->version_no + 1,
                'updated_at' => Carbon::now()
            ];
            $metaReqs = array_merge($metaReqs, [
                'json_logs' => trim($getData->json_logs . "," . json_encode($metaReqs), ",")
            ]);
            if (isset($req->status)) {              // In Case of Deactivation or Activation 
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs = array_merge($metaReqs, [
                    'status' => $status
                ]);
            }
            $editData = $this->_mFeeCollections::findOrFail($req->id);
            $editData->update($metaReqs);
            return responseMsgs(true, "Successfully Updated", [$metaReqs], "", "API_15.2", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_15.2", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Get Discont Group By Id
     */
    public function show(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $show = $this->_mFeeCollections->getGroupById($req->id);
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            return responseMsgs(true, "", $show, "", "API_15.3", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_15.3", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //View All
    public function retrieveAll(Request $req)
    {
        try {
            $getData = $this->_mFeeCollections->retrieve();
            return responseMsgs(true, "", $getData, "", "API_15.4", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_15.4", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Delete
    public function delete(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'status' => 'required|in:active,deactive'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            if (isset($req->status)) {                  // In Case of Deactivation or Activation 
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs =  [
                    'status' => $status
                ];
            }
            $delete = $this->_mFeeCollections::findOrFail($req->id);
            //  if ($teachingTitle->status == 0)
            //      throw new Exception("Records Already Deleted");
            $delete->update($metaReqs);
            return responseMsgs(true, "Deleted Successfully", [], "", "API_15.5", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_15.5", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Active All
    public function activeAll(Request $req)
    {
        try {
            $data = $this->_mFeeCollections->active();
            return responseMsgs(true, "", $data, "", "API_15.6", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_15.6", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //view by name
    public function search(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'search' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $search = $this->_mFeeCollections->searchByName($req->search);
            if (collect($search)->isEmpty())
                throw new Exception("Data Not Found");
            return responseMsgs(true, "", $search, "", "API_15.7", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_15.7", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // search fees by admission no
    public function searchFeesByAdmNo(Request $req)
    {
        //Description: Get records by id
        $validator = Validator::make($req->all(), [
            'admissionNo' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $mStudents = Student::where('admission_no', $req->admissionNo)
                ->where('status', 1)
                ->first();
            if (collect($mStudents)->isEmpty())
                throw new Exception('Admission no is not existing');
            $studentId  = $mStudents->id;

            $msg = '';
            $data = $this->_mFeeCollections::select('id', 'student_id', 'month_name', 'is_paid')
                ->where([['student_id', '=', $studentId], ['status', '=', '1']])->get();
            if ($data != "") {
                $msg = "Fee Already Existing";
                $data1 = $data;
            } else {
                $msg = "Fees Not Found";
                $data1 = ['admission_no' => $req->admissionNo, 'message' => 'Admission No. not found', 'value' => 'false'];
            }
            return responseMsgs(true, $msg, $data1, "API_15.8", "", "146ms", "post", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_15.8", "", "", "post", $req->deviceId ?? "");
        }
    }

    /**
     * | show fees by receipt no
     */
    public function showReceipt(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'receiptNo' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $show = $this->_mFeeCollections->getGroupByReceipt($req);
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            return responseMsgs(true, "", $show, "", "API_15.9", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_15.9", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | show fees by receipt no
     */
    public function showReceiptTest(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'receiptNo' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $show = $this->_mFeeCollections->getGroupByReceiptTest($req);
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            return responseMsgs(true, "", $show, "", "API_15.9", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_15.9", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
