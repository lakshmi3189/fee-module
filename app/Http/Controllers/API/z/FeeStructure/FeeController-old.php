<?php

namespace App\Http\Controllers\API\FeeStructure;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use App\Models\FeeStructure\FeeHeadType;
use Exception;
use Illuminate\Support\Str;

/*=================================================== Employee API =========================================================
Created By : Lakshmi kumari 
Created On : 06-May-2023 
Code Status : Open 
*/
class FeeController extends Controller
{  
    private $_mFeeHeadTypes;

    public function __construct()
    {
        $this->_mFeeHeadTypes = new FeeHeadType();
    }
    /**
     * | Created On-23-05-2023 
     * | Created On- Lakshmi Kumari
     * | Fee Head Type Crud Operations
     */

    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'feeHeadType'=>'required|string',
            'isAnnual'=>'required|integer',
            'isOptional' => 'required|integer',
            'isLateFineApplicable'=>'required|integer',
            'academicYear' => 'required|string',
            'deviceId' => 'string'
        ]);   
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'error' => $errors
            ], 422);
        }
        try {
            $ip = getClientIpAddress();
            $createdBy = 'Admin';
            $schoolId = 'DAV_Ranchi_834001';
            $metaReqs=[
                'fee_head_type' => Str::ucFirst($req->feeHeadType),
                'is_annual' => $req->isAnnual,
                'is_optional' => $req->isOptional,
                'is_latefee_applicable' => $req->isLateFineApplicable,
                'academic_year' => $req->academicYear,
                'school_id' => $schoolId,
                'created_by' => $createdBy,
                'ip_address' => $ip
            ]; 
            $this->_mFeeHeadTypes->store($metaReqs);
            return responseMsgs(true, "Successfully Saved", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
     }

    //================================ Fee Head Type API Start ========================================================
    /**
     *  @OA\Post(
     *  path="/add_feehead_type",
     *  tags={"Fee Head Type"},
     *  summary="Add Fee Head Type",
     *  operationId="addFeeHeadType",
     *  @OA\Parameter(name="feeHeadType",in="query",required=true,@OA\Schema(type="string",example="Building Fee")),     
     *  @OA\Parameter(name="isAnnual",in="query",required=true,@OA\Schema(type="integer",example="1")),  
     *  @OA\Parameter(name="isOptional",in="query",required=true,@OA\Schema(type="integer",example="0")),
     *  @OA\Parameter(name="isLateFineApplicable",in="query",required=true,@OA\Schema(type="integer",example="0")),
     *  @OA\Parameter(name="academicYear",in="query",required=true,@OA\Schema(type="string",example="2023-2024")),     
     *  @OA\Response(response=201,description="Success",@OA\MediaType(mediaType="application/json",)),
     *  @OA\Response(response=401,description="Unauthenticated"),
     *  @OA\Response(response=400,description="Bad Request"),
     *  @OA\Response(response=404,description="not found"),     
     *)
    **/
    public function postFeeHeadType(Request $req){
        //Description: store master records
        $validator = Validator::make($req->all(), [
            'feeHeadType'=>'required|string',
            'isAnnual'=>'required|integer',
            'isOptional' => 'required|integer',
            'isLateFineApplicable'=>'required|integer',
            'academicYear' => 'required|string',
            'deviceId' => 'string'
        ]);   
        if ($validator->fails()) {
            $errors = $validator->errors();
            return response()->json([
                'error' => $errors
            ], 422);
        }
        try 
        {  
            $ip = getClientIpAddress();
            $createdBy = 'Admin';
            $schoolId = 'DAV_Ranchi_834001';
            $mDeviceId = $req->deviceId ?? ""; 
            $getResponseTime = responseTime();
            $mFeeHeadType=new FeeHeadType();

            $metaReqs=[
                'fee_head_type' => Str::ucFirst($req->feeHeadType),
                'is_annual' => $req->isAnnual,
                'is_optional' => $req->isOptional,
                'is_latefee_applicable' => $req->isLateFineApplicable,
                'academic_year' => $req->academicYear,
                'school_id' => $schoolId,
                'created_by' => $createdBy,
                'ip_address' => $ip
            ];            
             $checkExist = FeeHeadType::where([['fee_head_type','=',$req->feeHeadType],['is_deleted','=','0']])->count(); 
            // dd($checkExist);
            $checkDeleted = FeeHeadType::where([['fee_head_type','=',$req->feeHeadType],['is_deleted','=','1']])->count();
            // print_r($checkDeleted); die; 
            if($checkExist > 0){
            throw new Exception("Fee head type name is already existing!");
            } 
            if($checkDeleted >= 0){
                $mFeeHeadType->store($metaReqs);
            }
            return responseMsgs(true, "Records added successfully", "", "API_ID_235","", $getResponseTime, "post", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "API_ID_235","", "", "post", $mDeviceId);
        } 
    }

    // /**
    //  * @OA\post(
    //  * path="/view_feehead_type",
    //  * operationId="viewFeeHeadType",
    //  *  tags={"Fee Head Type"},
    //  * summary="View Fee Head Type",
    //  * description="View Fee Head Type",           
    //  * @OA\Response(response=200, description="Success",
    //  * @OA\JsonContent(@OA\Property(property="status", type="string", example="200"),
    //  *  @OA\Property(property="data",type="object"))))
    // */
    public function readFeeHeadType(Request $req){
        //Description : Get all records
        try {
            // $data = FeeHeadType::list(); 
            // $mDeviceId = $req->deviceId ?? "";
            // $getResponseTime = responseTime();  
            $getResponseTime = responseTime();
            $mFeeHeadType=new FeeHeadType();

            return responseMsgs(true, "View all records", $data, "API_ID_236","", $getResponseTime, "get", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_236","", "", "get", $mDeviceId);
        }
    }
    
    // /**
    //  * @OA\Post(
    //  * path="/view_feehead_type_byId",
    //  * tags={"Fee Head Type"},
    //  * summary="Edit Fee Head Type",
    //  * operationId="viewFeeHeadTypeById",
    //  * @OA\Parameter(name="Id",in="query",required=true,@OA\Schema(type="integer",example="1")),
    //  * @OA\Response(response=200, description="Success",@OA\JsonContent(
    //  *    @OA\Property(property="status", type="integer", example=""),
    //  *    @OA\Property(property="data",type="object")
    //  *  )))
    // **/
    public function getFeeHeadTypeById(Request $req){ 
        //Description: Get records by id
        try {
            $listbyId = new FeeHeadType();
            $data  = $listbyId->listById($req);
            $mDeviceId = $req->deviceId ?? "";
            $getResponseTime = responseTime();  
            return responseMsgs(true, "View all records", $data, "API_ID_237","", $getResponseTime, "post", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_237","", "", "post", $mDeviceId);
        }     
    }

    // /**
    //  * @OA\Post(
    //  * path="/edit_feehead_type",
    //  * tags={"Fee Head"},
    //  * summary="Edit Fee Head",
    //  * operationId="editFeeHeadType",
    //  * @OA\Parameter(name="Id",in="query",required=true,@OA\Schema(type="integer",example="1")),
    //  * @OA\Parameter(name="feeHeadType",in="query",required=true,@OA\Schema(type="string",example="Building Fee")),     
    //  * @OA\Parameter(name="isAnnual",in="query",required=true,@OA\Schema(type="integer",example="1")),  
    //  * @OA\Parameter(name="isOptional",in="query",required=true,@OA\Schema(type="integer",example="0")),
    //  * @OA\Parameter(name="isLateFineApplicable",in="query",required=true,@OA\Schema(type="integer",example="0")),
    //  * @OA\Parameter(name="academicYear",in="query",required=true,@OA\Schema(type="string",example="2023-2024")),
    //  * 
    //  * @OA\Response(response=200, description="Success",@OA\JsonContent(
    //  *    @OA\Property(property="status", type="integer", example=""),
    //  *    @OA\Property(property="data",type="object")
    //  *  )))
    // **/
    public function editFeeHeadType(Request $req){
        //Description: edit records of a particular id 
        try {
            $mObject = new FeeHeadType();
            $data = $mObject->updateData($req);
            $mDeviceId = $req->deviceId ?? "";
            return responseMsgs(true, "Records updated successfully", $data, "API_ID_49","", "222ms", "post", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_49","", "", "post", $mDeviceId);
        } 

    }

    // /**
    //  * @OA\Post(
    //  * path="/delete_feehead_type",
    //  * operationId="deleteFeeHeadType",
    //  * tags={"Fee Head"},
    //  * summary="Delete Fee Head",
    //  * description="Delete Fee Head",
    //  * @OA\RequestBody(required=true,@OA\JsonContent(required={"Id"},
    //  * @OA\Property(property="Id", type="integer", example="1"),),),
    //  * @OA\Response(response=200, description="Success",
    //  * @OA\JsonContent(
    //  * @OA\Property(property="status", type="integer", example=""),
    //  *    @OA\Property(property="data",type="object")
    //  * )))
    // **/
    public function deleteFeeHeadType(Request $req){
        //Description: delete record of a particular id
        try {
            $mObject = new FeeHeadType();
            $data = $mObject->deleteData($req);
            $mDeviceId = $req->deviceId ?? "";
            return responseMsgs(true, "Records deleted successfully", $data, "API_ID_50","", "194ms", "post", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_50","", "", "post", $mDeviceId);
        } 
    }  
    // ======================================= Fee Head Type API End =====================================================



    //Fee Head API Start
    /**
     *  @OA\Post(
     *  path="/add_fee_head",
     *  tags={"Fee Head"},
     *  summary="Add Fee",
     *  operationId="addFeeHead",
     *  @OA\Parameter(name="feeHeadName",in="query",required=true,@OA\Schema(type="string",example="Admission Fee")),     
     *  @OA\Parameter(name="feeCode",in="query",required=true,@OA\Schema(type="integer",example="123")),  
     *  @OA\Parameter(name="feeDescription",in="query",required=true,@OA\Schema(type="string",example="Admission Fee")),
     *  @OA\Parameter(name="academicYear",in="query",required=true,@OA\Schema(type="string",example="2023-2024")),     
     *  @OA\Response(response=201,description="Success",@OA\MediaType(mediaType="application/json",)),
     *  @OA\Response(response=401,description="Unauthenticated"),
     *  @OA\Response(response=400,description="Bad Request"),
     *  @OA\Response(response=404,description="not found"),     
     *)
    **/
    public function addFeeHead(Request $req){
        //Description: store master records
        try 
        {            
            $data = array();
            $validator = Validator::make($req->all(), [
                'feeHeadName'=>'required|string',
                'feeCode'=>'required|numeric',
                'feeDescription' => 'required|string',
                'academicYear'=>'required|string'
            ]);   
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'error' => $errors
                ], 400);
            }
            if ($validator->passes()) {
                $mObject = new FeeHead();
                $data = $mObject->insertData($req);
                $mDeviceId = $req->deviceId ?? "";         
                return responseMsgs(true, "Records added successfully", $data, "API_ID_46","", "198ms", "post", $mDeviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_46","", "", "post", $mDeviceId);
        } 
    }

    /**
     * @OA\Get(
     * path="/view_fee_head",
     * operationId="viewFeeHead",
     * tags={"Fee Head"},
     * summary="View Fee Head",
     * description="View Fee Head",           
     * @OA\Response(response=200, description="Success",
     * @OA\JsonContent(@OA\Property(property="status", type="string", example="200"),
     *  @OA\Property(property="data",type="object"))))
    */
    public function viewFeeHead(Request $req){
        //Description : Get all records
        try {
            $data = FeeHead::list(); 
            $mDeviceId = $req->deviceId ?? "";
            return responseMsgs(true, "View all records", $data, "API_ID_47","", "171ms", "get", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_47","", "", "get", $mDeviceId);
        }
    }

    public function viewFeeHeadById(Request $req){ 
        //Description: Get records by id
        try {
            $listbyId = new FeeHead();
            $data  = $listbyId->listById($req);
            $mDeviceId = $req->deviceId ?? "";
            return responseMsgs(true, "View all records", $data, "API_ID_48","", "331ms", "post", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_48","", "", "post", $mDeviceId);
        }     
    }

    /**
     * @OA\Post(
     * path="/edit_fee_head",
     * tags={"Fee Head"},
     * summary="Edit Fee Head",
     * operationId="editFeeHead",
     *   @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="string",example="1")),
     *  @OA\Parameter(name="feeHeadName",in="query",required=true,@OA\Schema(type="string",example="Sports Fee")),     
     *  @OA\Parameter(name="feeCode",in="query",required=true,@OA\Schema(type="integer",example="124")),  
     *  @OA\Parameter(name="feeDescription",in="query",required=true,@OA\Schema(type="string",example="Sports Fee")),
     *  @OA\Parameter(name="academicYear",in="query",required=true,@OA\Schema(type="string",example="2023-2024")),
     * @OA\Response(response=200, description="Success",@OA\JsonContent(
     *    @OA\Property(property="status", type="integer", example=""),
     *    @OA\Property(property="data",type="object")
     *  )))
    **/
    public function editFeeHead(Request $req){
        //Description: edit records of a particular id 
        try {
            $mObject = new FeeHead();
            $data = $mObject->updateData($req);
            $mDeviceId = $req->deviceId ?? "";
            return responseMsgs(true, "Records updated successfully", $data, "API_ID_49","", "222ms", "post", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_49","", "", "post", $mDeviceId);
        } 

    }

    /**
     * @OA\Post(
     * path="/delete_fee_head",
     * operationId="deleteFeeHeadById",
     * tags={"Fee Head"},
     * summary="Delete Fee Head",
     * description="Delete Fee Head",
     * @OA\RequestBody(required=true,@OA\JsonContent(required={"id"},
     * @OA\Property(property="id", type="string", format="string", example="1"),),),
     * @OA\Response(response=200, description="Success",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="integer", example=""),
     *    @OA\Property(property="data",type="object")
     * )))
    **/
    public function deleteFeeHeadById(Request $req){
        //Description: delete record of a particular id
        try {
            $mObject = new FeeHead();
            $data = $mObject->deleteData($req);
            $mDeviceId = $req->deviceId ?? "";
            return responseMsgs(true, "Records deleted successfully", $data, "API_ID_50","", "194ms", "post", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_50","", "", "post", $mDeviceId);
        } 
    }

    public function deleteAllFeeHead(Request $req){
        //Description: delete all records 
        // try {
        //     $mObject = new FeeHead();
        //     $data = $mObject->truncateData();
        //     $mDeviceId = $req->deviceId ?? "";
        //     return responseMsgs(true, "All records has been deleted successfully", $data, "API_ID_51","", "194ms", "delete", $mDeviceId);
        // } catch (Exception $e) {
        //     return responseMsgs(false, $e->getMessage(), $data, "API_ID_51","", "", "delete", $mDeviceId);
        // }    
    }
    // //Fee Head API End

    // /**
    //  *  @OA\Post(
    //  *  path="/add_fee_master",
    //  *  tags={"Fee Structure"},
    //  *  summary="Add Fee Master",
    //  *  operationId="addFeeMaster",
    //  *  @OA\Parameter(name="school_id",in="query",required=true,@OA\Schema(type="integer",example="")),     
    //  *  @OA\Parameter(name="class_id",in="query",required=true,@OA\Schema(type="integer",example="")),     
    //  *  @OA\Parameter(name="fee_head_id",in="query",required=true,@OA\Schema(type="integer",example="")),     
    //  *  @OA\Parameter(name="fee_head_amount",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //  *  @OA\Parameter(name="academic_year",in="query",required=true,@OA\Schema(type="string",example="")),     
    //  *  @OA\Response(response=201,description="Success",@OA\MediaType(mediaType="application/json",)),
    //  *  @OA\Response(response=401,description="Unauthenticated"),
    //  *  @OA\Response(response=400,description="Bad Request"),
    //  *  @OA\Response(response=404,description="not found"),     
    //  *)
    // **/
    // public function addFeeMaster(Request $req){
    //     //Description: store master records
    //     $data = array();
    //     $validator = Validator::make($req->all(),[
    //     'school_id'=>'required|numeric',
    //     'class_id' => 'required|numeric',
    //     'fee_head_id' => 'required|numeric',
    //     'fee_head_amount'=>'required|numeric',
    //     'academic_year'=>'required|string'
    //     ]);

    //     try {
    //         $mObject = new FeeMaster();
    //         $data = $mObject->insertFeeMasterData($req);
    //         $mDeviceId = $req->deviceId ?? "";         
    //         return responseMsgs(true, "Records added successfully", $data, "API_ID_52","", "1135ms", "post", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_52","", "", "post", $mDeviceId);
    //     } 
    // }

    // /**
    //  * @OA\Get(
    //  * path="/view_fee_master",
    //  * operationId="viewFeeMaster",
    //  * tags={"Fee Structure"},
    //  * summary="View Fee Master",
    //  * description="View Fee Master",           
    //  * @OA\Response(response=200, description="Success",
    //  * @OA\JsonContent(@OA\Property(property="status", type="string", example="200"),
    //  *  @OA\Property(property="data",type="object"))))
    // */
    // public function viewFeeMaster(Request $req){
    //     //Description : Get all records
    //     try {
    //         $data = FeeMaster::list(); 
    //         $mDeviceId = $req->deviceId ?? "";
    //         return responseMsgs(true, "View all records", $data, "API_ID_53","", "475ms", "get", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_53","", "", "get", $mDeviceId);
    //     }
    // }

    // public function viewFeeMasterById(Request $req){ 
    //     //Description: Get records by id
    //     try {
    //         $listbyId = new FeeMaster();
    //         $data  = $listbyId->listById($req);
    //         $mDeviceId = $req->deviceId ?? "";
    //         return responseMsgs(true, "View all records", $data, "API_ID_54","", "331ms", "post", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_54","", "", "post", $mDeviceId);
    //     }     
    // }

    // /**
    //  * @OA\Post(
    //  * path="/edit_fee_master",
    //  * tags={"Fee Structure"},
    //  * summary="Edit Fee Master",
    //  * operationId="editFeeMaster",
    //  * @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer",example="1")),
    //  * @OA\Parameter(name="school_id",in="query",required=true,@OA\Schema(type="integer",example="")),     
    //  * @OA\Parameter(name="class_id",in="query",required=true,@OA\Schema(type="integer",example="")),     
    //  * @OA\Parameter(name="fee_head_id",in="query",required=true,@OA\Schema(type="integer",example="")),     
    //  * @OA\Parameter(name="fee_head_amount",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //  * @OA\Parameter(name="academic_year",in="query",required=true,@OA\Schema(type="string",example="")),  
    //  * @OA\Response(response=200, description="Success",@OA\JsonContent(
    //  * @OA\Property(property="status", type="integer", example=""),
    //  * @OA\Property(property="data",type="object")
    //  *  )))
    // **/
    // public function editFeeMaster(Request $req){
    //     //Description: edit records of a particular id 
    //     try {
    //         $mObject = new FeeMaster();
    //         $data = $mObject->updateFeeMasterData($req);
    //         $mDeviceId = $req->deviceId ?? "";
    //         return responseMsgs(true, "Records updated successfully", $data, "API_ID_55","", "394ms", "post", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_55","", "", "post", $mDeviceId);
    //     } 

    // }

    // /**
    //  * @OA\Post(
    //  * path="/delete_fee_master",
    //  * operationId="deleteFeeMasterById",
    //  * tags={"Fee Structure"},
    //  * summary="Delete Fee Master",
    //  * description="Delete Fee Master",
    //  * @OA\RequestBody(required=true,@OA\JsonContent(required={"id"},
    //  * @OA\Property(property="id", type="string", format="string", example="1"),),),
    //  * @OA\Response(response=200, description="Success",
    //  * @OA\JsonContent(
    //  * @OA\Property(property="status", type="integer", example=""),
    //  *    @OA\Property(property="data",type="object")
    //  * )))
    // **/
    // public function deleteFeeMasterById(Request $req){
    //     //Description: delete record of a particular id
    //     try {
    //         $mObject = new FeeMaster();
    //         $data = $mObject->deleteFeeMasterData($req);
    //         $mDeviceId = $req->deviceId ?? "";
    //         return responseMsgs(true, "Records deleted successfully", $data, "API_ID_56","", "342ms", "post", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_56","", "", "post", $mDeviceId);
    //     } 
    // }

    // public function deleteAllFeeMaster(Request $req){
    //     //Description: delete all records 
    //     try {
    //         $mObject = new FeeMaster();
    //         $data = $mObject->truncateData();
    //         $mDeviceId = $req->deviceId ?? "";
    //         return responseMsgs(true, "All records has been deleted successfully", $data, "API_ID_57","", "381ms", "delete", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_57","", "", "delete", $mDeviceId);
    //     }    
    // }

    //School Scholarship API Starts
	// /**
    //   * @OA\Post(
    //   *   path="/add_scholarship",
    //   *   tags={"Fee Structure"},
    //   *   summary="Add School Scholarship",
    //   *   operationId="addScholarship",
    //   *   @OA\Parameter(name="school_id",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //   *   @OA\Parameter(name="class_id",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //   *   @OA\Parameter(name="fee_head_id",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //   *   @OA\Parameter(name="discount_amount",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //   *   @OA\Parameter(name="academic_year",in="query",required=true,@OA\Schema(type="string",example="")),       
    //   *   @OA\Response(response=201,description="Success",@OA\MediaType(mediaType="application/json",)),
    //   *   @OA\Response(response=401,description="Unauthenticated"),
    //   *   @OA\Response(response=400,description="Bad Request"),
    //   *   @OA\Response(response=404,description="not found"),   
    //   *)
    // **/
    // public function addScholarship(Request $req){
    //     //Description: store master records
    //     $data = array();
    //     $validator = Validator::make($req->all(),[
    //     'school_id'=>'required|numeric',
    //     'class_id'=>'required|numeric',
    //     'fee_head_id'=>'required|numeric',
    //     'discount_amount'=>'required|numeric',
    //     'academic_year'=>'required|string',
    //     ]);
    //     try {
    //         $mObject = new SchoolScholarship();
    //         $data = $mObject->insertScholarshipData($req);
    //         $mDeviceId = $req->deviceId ?? "";         
    //         return responseMsgs(true, "Records added successfully", $data, "API_ID_28","", "754ms", "post", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_28","", "", "post", $mDeviceId);
    //     } 
    // }

    // /**
    //  * @OA\Get(
    //  *  path="/view_scholarship",
    //  *  operationId="viewScholarship",
    //  *  tags={"Fee Structure"},
    //  *  summary="View School Scholarship",
    //  *  description="View School Scholarship",       
    //  *  @OA\Response(response=200, description="Success",
    //  *  @OA\JsonContent(@OA\Property(property="status", type="string", example="200"),
    //  *  @OA\Property(property="data",type="object"))))
    // **/
    // public function viewScholarship(Request $req){
    //     //Description : Get all records
    //     try {
    //         $data = SchoolScholarship::list(); 
    //         $mDeviceId = $req->deviceId ?? "";
    //         return responseMsgs(true, "View all records", $data, "API_ID_29","", "875ms", "get", $mDeviceId);
    //     } catch (\Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_29","", "", "get", $mDeviceId);
    //     }
    // }

    // public function viewScholarshipById(Request $req){ 
    //     //Description: Get records by id
    //     try {
    //         $listbyId = new SchoolScholarship();
    //         $data  = $listbyId->listById($req);
    //         $mDeviceId = $req->deviceId ?? "";
    //         return responseMsgs(true, "View all records", $data, "API_ID_30","", "446ms", "post", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_30","", "", "post", $mDeviceId);
    //     }     
    // }

    // /**
    //  * @OA\Post(
    //  * path="/edit_scholarship",
    //  * tags={"Fee Structure"},
    //  * summary="Edit School Scholarship",
    //  * operationId="editScholarship",
    //  * @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //  * @OA\Parameter(name="school_id",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //  * @OA\Parameter(name="class_id",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //  * @OA\Parameter(name="fee_head_id",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //  * @OA\Parameter(name="discount_amount",in="query",required=true,@OA\Schema(type="integer",example="")),       
    //  * @OA\Parameter(name="academic_year",in="query",required=true,@OA\Schema(type="string",example="")),
    //  * @OA\Response(response=200, description="Success",@OA\JsonContent(
    //  * @OA\Property(property="status", type="integer", example=""),
    //  * @OA\Property(property="data",type="object")
    //  *  )))
    // **/
    // public function editScholarship(Request $req){
    //     //Description: edit records of a particular id 
    //     try {
    //         $mObject = new SchoolScholarship();
    //         $data = $mObject->updateScholarshipData($req);
    //         $mDeviceId = $req->deviceId ?? "";
    //         return responseMsgs(true, "Records updated successfully", $data, "API_ID_31","", "366ms", "post", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_31","", "", "post", $mDeviceId);
    //     }
    // }

    // /**
    //  * @OA\Post(
    //  * path="/delete_scholarship_by_id",
    //  * operationId="deleteScholarshipById",
    //  * tags={"Fee Structure"},
    //  * summary="Delete School Scholarship",
    //  * description="Delete School Scholarship",
    //  * @OA\RequestBody(required=true,@OA\JsonContent(required={"id"},
    //  * @OA\Property(property="id", type="string", format="string", example="1"),),),
    //  * @OA\Response(response=200, description="Success",
    //  * @OA\JsonContent(
    //  * @OA\Property(property="status", type="integer", example=""),
    //  *    @OA\Property(property="data",type="object")
    //  * )))
    // **/
    // public function deleteScholarshipById(Request $req){
    //     //Description: delete record of a particular id
    //     try {
    //         $mObject = new SchoolScholarship();
    //         $data = $mObject->deleteScholarshipData($req);
    //         $mDeviceId = $req->deviceId ?? "";
    //         return responseMsgs(true, "Records deleted successfully", $data, "API_ID_32","", "403ms", "post", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_32","", "", "post", $mDeviceId);
    //     } 
    // }

    // public function deleteAllScholarship(Request $req){
    //     //Description: delete all records 
    //     try {
    //         $mObject = new SchoolScholarship();
    //         $data = $mObject->truncateData();
    //         $mDeviceId = $req->deviceId ?? "";
    //         return responseMsgs(true, "All records has been deleted successfully", $data, "API_ID_33","", "433ms", "delete", $mDeviceId);
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), $data, "API_ID_33","", "", "delete", $mDeviceId);
    //     }    
    // }    
}
