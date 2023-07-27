<?php

namespace App\Http\Controllers\API\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\Employee;
use App\Models\Admin\User;
use Illuminate\Support\Facades\DB;
use Validator;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/*=================================================== Employee =========================================================
Created By : Lakshmi kumari 
Created On : 20-Apr-2023 
Code Status : Open 
*/

class EmployeeController extends Controller
{
    /**
     *  @OA\Post(
     *  path="/add_employee",
     *  tags={"Employee"},
     *  summary="Add Employee",
     *  operationId="addEmployee",     
     *  @OA\Parameter(name="emp_no",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="salutation_id",in="query",required=true,@OA\Schema(type="string",example="1")),
     *  @OA\Parameter(name="salutation_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="first_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="middle_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="last_name",in="query",required=true,@OA\Schema(type="string",example="")),  
     *  @OA\Parameter(name="email",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="mobile",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="dob",in="query",required=true,@OA\Schema(type="string",example="")),  
     *  @OA\Parameter(name="doj",in="query",required=true,@OA\Schema(type="string",example="")), 
     *  @OA\Parameter(name="aadhar_no",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="disability",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="gender_id",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="gender_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="category_id",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="category_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="blood_group_id",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="blood_group_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="department_id",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="department_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="employee_type_id",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="employee_type_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="teaching_title_id",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="teaching_title_name",in="query",required=true,@OA\Schema(type="string",example="")),       
     *  @OA\Parameter(name="marital_status_id",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="marital_status_name",in="query",required=true,@OA\Schema(type="string",example="")),
     * 
     *  @OA\RequestBody(required=false,@OA\MediaType(mediaType="multipart/form-data",
     *  @OA\Schema(@OA\Property(property="upload_image",description="upload image",type="file",format="binary")))),   
     *     
     *  @OA\Parameter(name="p_address1",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_address2",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_locality",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_landmark",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_country_id",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_country_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_state_id",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_state_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_district_id",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_district_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_pincode",in="query",required=false,@OA\Schema(type="numeric",example="")),
     *  @OA\Parameter(name="c_address1",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_address2",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_locality",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_landmark",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_country_id",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_country_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_state_id",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_state_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_district_id",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_district_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_pincode",in="query",required=false,@OA\Schema(type="numeric",example="")),
     *  @OA\Parameter(name="fathers_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathers_qualification_id",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathers_qualification_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathers_occupation_id",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathers_occupation_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="mothers_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="mothers_qualification_id",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="mothers_qualification_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="mothers_occupation_id",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="mothers_occupation_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="bank_id",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="bank_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="account_no",in="query",required=false,@OA\Schema(type="numeric",example=" ")),
     *  @OA\Parameter(name="account_type",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="ifsc_code",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="branch_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="nominee_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="nominee_relation_id",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="nominee_relation_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="pan_no",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="epf_no",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="uan_no",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="esi_no",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="nps_no",in="query",required=false,@OA\Schema(type="string",example=" ")),
     * 
     * 
     *    
     *  @OA\Response(response=201,description="Success",@OA\MediaType(mediaType="application/json",)),
     *  @OA\Response(response=401,description="Unauthenticated"),
     *  @OA\Response(response=400,description="Bad Request"),
     *  @OA\Response(response=404,description="not found"),     
     *)
    **/


    public function addEmployee(Request $req){
        //Description: add employee details 
        try {
            $data = array();
            $validator = Validator::make($req->all(), [
            'salutation_id'                 => 'required|string',
            'salutation_name'               => 'required|string',
            'first_name'                    => 'required|string|max:20',
            'middle_name'                   => 'string|max:20',
            'last_name'                     => 'required|string|max:20',            
            'email'                         => 'string|email|max:255',
            'mobile'                        => 'required|numeric|digits:10|regex:/[0-9]/',
            'dob'                           => 'required|date',
            'doj'                           => 'required|date',
            'aadhar_no'                     => 'required|numeric|digits:12',
            'disability'                    => 'required|string|max:10',
            'gender_id'                     => 'required|integer',
            'gender_name'                   => 'required|string|max:20',            
            'category_id'                   => 'required|integer',
            'category_name'                 => 'required|string|max:100',
            'blood_group_id'                => 'required|integer',
            'blood_group_name'              => 'required|string|max:10',
            'department_id'                 => 'required|integer',
            'department_name'               => 'required|string|max:50',
            'employee_type_id'              => 'required|integer',
            'employee_type_name'            => 'required|string|max:50',
            'teaching_title_id'             => 'required|integer',
            'teaching_title_name'           => 'required|string|max:50',
            'marital_status_id'             => 'required|integer',
            'marital_status_name'           => 'required|string|max:50'
            // 'upload_image'                  => 'string|upload_image|mimes:jpg,png,jpeg|max:255',
            // 'p_address1'                    => 'string|max:255',
            // 'p_address2'                    => 'string|max:255',
            // 'p_locality'                    => 'string|max:255',
            // 'p_landmark'                    => 'string|max:255',
            // 'p_country'                     => 'string|max:50',
            // 'p_state'                       => 'string|max:50',
            // 'p_district'                    => 'string|max:50',
            // 'p_pincode'                     => 'numeric|digits:6',
            // 'c_address1'                    => 'string|max:255',
            // 'c_address2'                    => 'string|max:255',
            // 'c_locality'                    => 'string|max:255',
            // 'c_landmark'                    => 'string|max:255',
            // 'c_country'                     => 'string|max:50',
            // 'c_state'                       => 'string|max:50',
            // 'c_district'                    => 'string|max:50',
            // 'c_pincode'                     => 'numeric|digits:6',
            // 'fathers_name'                  => 'string|max:50',
            // 'fathers_qualification_name'    => 'string|max:50',
            // 'fathers_occupation_name'       => 'string|max:50',
            // // 'fathers_annual_income'         => 'string|max:10',
            // 'mothers_name'                  => 'string|max:50',
            // 'mothers_qualification_name'    => 'string|max:50',
            // 'mothers_occupation_name'       => 'string|max:50',
            // // 'mothers_annual_income'         => 'string|max:10',
            // 'bank_name'                     => 'string|max:50',
            // 'account_no'                    => 'numeric',
            // 'account_type'                  => 'string|max:20',
            // 'ifsc_code'                     => 'string|max:20',
            // 'branch_name'                   => 'string|max:50',
            // 'nominee_name'                  => 'string|max:50',
            // 'nominee_relation_name'         => 'string|max:255',
            // 'pan_no'                        => 'string|max:50',
            // 'epf_no'                        => 'string|max:50',
            // 'uan_no'                        => 'string|max:50',
            // 'esi_no'                        => 'string|max:50',
            // 'nps_no'                        => 'string|max:50'
            ]); 
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'error' => $errors
                ], 400);
            }
            if ($validator->passes()) {
                // DB::beginTransaction(); 
                $mObject = new Employee();
                $data = $mObject->insertData($req);
                $mDeviceId = $req->deviceId ?? ""; 
                // $addUser = $this->addUser($req);
                // DB::commit();
                return responseMsgs(true, "Records added successfully", $data, "API_ID_130","", "186ms", "post", $mDeviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_130","", "", "post", $mDeviceId);
            // DB::rollBack();
        } 
    }

    
    //Description: created a new function and called it in just above function to store some records in auth table
    // public function addUser($req){
    //     $pass = Str::random(10);            
    //     $mObjectU = new User();
    //     $insert = [
    //       $mObjectU->name        = $req['first_name'],
    //       $mObjectU->email       = $req['email'],          
    //       $mObjectU->password    = Hash::make($pass),
    //       $mObjectU->c_password  = $pass
    //     ];
    //     $mObjectU->save($insert);  
    // }

    /**
     * @OA\Get(
     *    path="/view_employee",
     *    operationId="viewEmployee",
     *    tags={"Employee"},
     *    summary="View Employee",
     *    description="View Employee",           
     *     @OA\Response(
     *          response=200, description="Success",
     *          @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="200"),
     *             @OA\Property(property="data",type="object")
     *          )
     *       )
     *  )
    */ 
    public function viewEmployee(){
        //Description: Get all records 
        try {
            $data = Employee::list(); 
            $mDeviceId = $req->deviceId ?? "";
            return responseMsgs(true, "View all records", $data, "API_ID_131","", "186ms", "get", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_131","", "", "get", $mDeviceId);
        }
    }

    /**
     * @OA\Post(
     * path="/search_employee_by_id",
     * tags={"Employee"},
     * summary="Search Employee",
     * operationId="searchEmpByEmpId",
     * @OA\Parameter(name="emp_no",in="query",required=true,@OA\Schema(type="string",example="20230001")),
     * @OA\Response(response=200, description="Success",@OA\JsonContent(
     *    @OA\Property(property="status", type="integer", example=""),
     *    @OA\Property(property="data",type="object")
     *  )))
    **/
    public function searchEmpByEmpId(Request $req){ 
        //Description: Get records by id
        try {
            $data = array();
            $validator = Validator::make($req->all(), [
                'emp_no'=>'required|string'
            ]);   
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'error' => $errors
                ], 400);
            }
            if ($validator->passes()) {
                $searchEmpId = new Employee();
                $data1  = $searchEmpId->searchEmpId($req);
                $msg = $data1['message'];
                $data = $data1;
                // $data = $data1['emp_id'];
                $mDeviceId = $req->deviceId ?? "";
                return responseMsgs(true, $msg, $data, "API_ID_132","", "146ms", "post", $mDeviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_132","", "", "post", $mDeviceId);
        }     
    }
    
    /**
     * @OA\Post(
     * path="/view_employee_by_id",
     * tags={"Employee"},
     * summary="View Employee By ID",
     * operationId="viewEmployeeById",
     * @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer",example="1")),
     * @OA\Response(response=200, description="Success",@OA\JsonContent(
     *    @OA\Property(property="status", type="integer", example=""),
     *    @OA\Property(property="data",type="object")
     *  )))
    **/
    public function viewEmployeeById(Request $req){ 
        //Description: Get records by id
        try {
            $data = array();
            $validator = Validator::make($req->all(), [
                'id' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'error' => $errors
                ], 400);
            }
            if ($validator->passes()) {  
                $listbyId = new Employee();
                $data  = $listbyId->listById($req);
                $mDeviceId = $req->deviceId ?? "";
                // $msg = $data1['message'];
                // $data = $data1;
                return responseMsgs(true, "View all records", $data, "API_ID_132","", "146ms", "post", $mDeviceId);
            }
        } catch (Exception $e) {
            return responseErrMsg(true, "Records not found");
            // return responseMsgs(false, $e->getMessage(), $data, "API_ID_132","", "", "post", $mDeviceId);
        }     
    }
    
    // /**
    //  * @OA\Post(
    //  * path="/edit_employee",
    //  * tags={"Employee"},
    //  * summary="Edit Employee",
    //  * operationId="editEmployee",
    //  * @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer",example="")),
    //  * @OA\Parameter(name="salutation_id",in="query",required=true,@OA\Schema(type="string",example="")),
    //  * @OA\Parameter(name="salutation_name",in="query",required=true,@OA\Schema(type="string",example="")),
    //  * @OA\Parameter(name="first_name",in="query",required=true,@OA\Schema(type="string",example="")),
    //  * @OA\Parameter(name="middle_name",in="query",required=true,@OA\Schema(type="string",example="")),
    //  * @OA\Parameter(name="last_name",in="query",required=true,@OA\Schema(type="string",example="")),
    //  * @OA\Parameter(name="email",in="query",required=false,@OA\Schema(type="string",example="")),
    //  * @OA\Parameter(name="mobile",in="query",required=true,@OA\Schema(type="string",example="")),
    //  * @OA\Parameter(name="dob",in="query",required=true,@OA\Schema(type="string",example="")),
    //  * @OA\Parameter(name="doj",in="query",required=true,@OA\Schema(type="string",example="")),
    //  * @OA\Parameter(name="aadhar_no",in="query",required=true,@OA\Schema(type="string",example="")),
    //  * @OA\Parameter(name="disability",in="query",required=true,@OA\Schema(type="string",example="")),
    //  *
    //  * @OA\Response(response=200, description="Success",@OA\JsonContent(
    //  *    @OA\Property(property="status", type="integer", example=""),
    //  *    @OA\Property(property="data",type="object")
    //  *  )))
    // **/

    /**
     *  @OA\Post(
     *  path="/edit_employee",
     *  tags={"Employee"},
     *  summary="Edit Employee",
     *  operationId="editEmployee",     
     *  @OA\Parameter(name="id",in="query",required=true,@OA\Schema(type="integer",example="1")),
     *  @OA\Parameter(name="salutation_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="salutation_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="first_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="middle_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="last_name",in="query",required=true,@OA\Schema(type="string",example="")),  
     *  @OA\Parameter(name="email",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="mobile",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="dob",in="query",required=true,@OA\Schema(type="date",example="")),  
     *  @OA\Parameter(name="doj",in="query",required=true,@OA\Schema(type="date",example="")), 
     *  @OA\Parameter(name="aadhar_no",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="disability",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="gender_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="gender_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="category_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="category_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="blood_group_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="blood_group_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="department_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="department_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="employee_type_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="employee_type_name",in="query",required=true,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="teaching_title_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="teaching_title_name",in="query",required=true,@OA\Schema(type="string",example="")),       
     *  @OA\Parameter(name="marital_status_id",in="query",required=true,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="marital_status_name",in="query",required=true,@OA\Schema(type="string",example="")),
     * 
     *  @OA\RequestBody(required=false,@OA\MediaType(mediaType="multipart/form-data",
     *  @OA\Schema(@OA\Property(property="upload_image",description="upload image",type="file",format="binary")))),   
     *     
     *  @OA\Parameter(name="p_address1",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_address2",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_locality",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_landmark",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_country_id",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_country_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_state_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="p_state_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_district_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="p_district_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="p_pincode",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="c_address1",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_address2",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_locality",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_landmark",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_country_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="c_country_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_state_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="c_state_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_district_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="c_district_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="c_pincode",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="fathers_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathers_qualification_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="fathers_qualification_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathers_occupation_id",in="query",required=false,@OA\Schema(type="integer",example="")),
     *  @OA\Parameter(name="fathers_occupation_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="fathers_annual_income",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="mothers_name",in="query",required=false,@OA\Schema(type="string",example="")),
     *  @OA\Parameter(name="mothers_qualification_id",in="query",required=false,@OA\Schema(type="integer",example=" ")),
     *  @OA\Parameter(name="mothers_qualification_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="mothers_occupation_id",in="query",required=false,@OA\Schema(type="integer",example=" ")),
     *  @OA\Parameter(name="mothers_occupation_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="mothers_annual_income",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="bank_id",in="query",required=false,@OA\Schema(type="integer",example=" ")),
     *  @OA\Parameter(name="bank_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="account_no",in="query",required=false,@OA\Schema(type="numeric",example=" ")),
     *  @OA\Parameter(name="account_type",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="ifsc_code",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="branch_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="nominee_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="nominee_relation_id",in="query",required=false,@OA\Schema(type="integer",example=" ")),
     *  @OA\Parameter(name="nominee_relation_name",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="pan_no",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="epf_no",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="uan_no",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="esi_no",in="query",required=false,@OA\Schema(type="string",example=" ")),
     *  @OA\Parameter(name="nps_no",in="query",required=false,@OA\Schema(type="string",example=" ")),
     * 
     * 
     *    
     *  @OA\Response(response=201,description="Success",@OA\MediaType(mediaType="application/json",)),
     *  @OA\Response(response=401,description="Unauthenticated"),
     *  @OA\Response(response=400,description="Bad Request"),
     *  @OA\Response(response=404,description="not found"),     
     *)
    **/
    public function editEmployee(Request $req){
        //Description: edit records of a particular id 
        try {
            $data = array();
            //condition for allow edit basic details

            //

            //condition to not allow edit basic details
            $validator = Validator::make($req->all(), [
            'salutation_id'                 => 'required|string',
            'salutation_name'               => 'required|string',
            'first_name'                    => 'required|string|max:20',
            // 'middle_name'                   => 'string|max:20',
            'last_name'                     => 'required|string|max:20',            
            'email'                         => 'string|email|max:255',
            'mobile'                        => 'required|numeric|digits:10|regex:/[0-9]/',
            'dob'                           => 'required|date',
            'doj'                           => 'required|date',
            'aadhar_no'                     => 'required|numeric|digits:12',
            'disability'                    => 'required|string|max:10',
            'gender_id'                     => 'required|integer',
            'gender_name'                   => 'required|string|max:20',            
            'category_id'                   => 'required|integer',
            'category_name'                 => 'required|string|max:100',
            'blood_group_id'                => 'required|integer',
            'blood_group_name'              => 'required|string|max:10',
            'department_id'                 => 'required|integer',
            'department_name'               => 'required|string|max:50',
            'employee_type_id'              => 'required|integer',
            'employee_type_name'            => 'required|string|max:50',
            'teaching_title_id'             => 'required|integer',
            'teaching_title_name'           => 'required|string|max:50',
            'marital_status_id'             => 'required|integer',
            'marital_status_name'           => 'required|string|max:50',
            // 'upload_image'                  => 'string|upload_image|mimes:jpg,png,jpeg|max:255',
            // 'p_address1'                    => 'string|max:255',
            // 'p_address2'                    => 'string|max:255',
            // 'p_locality'                    => 'string|max:255',
            // 'p_landmark'                    => 'string|max:255',
            // 'p_country'                     => 'string|max:50',
            // 'p_state'                       => 'string|max:50',
            // 'p_district'                    => 'string|max:50',
            // 'p_pincode'                     => 'numeric|digits:6',
            // 'c_address1'                    => 'string|max:255',
            // 'c_address2'                    => 'string|max:255',
            // 'c_locality'                    => 'string|max:255',
            // 'c_landmark'                    => 'string|max:255',
            // 'c_country'                     => 'string|max:50',
            // 'c_state'                       => 'string|max:50',
            // 'c_district'                    => 'string|max:50',
            // 'c_pincode'                     => 'numeric|digits:6',
            // 'fathers_name'                  => 'string|max:50',
            // 'fathers_qualification_name'    => 'string|max:50',
            // 'fathers_occupation_name'       => 'string|max:50',
            // 'fathers_annual_income'         => 'string|max:10',
            // 'mothers_name'                  => 'string|max:50',
            // 'mothers_qualification_name'    => 'string|max:50',
            // 'mothers_occupation_name'       => 'string|max:50',
            // 'mothers_annual_income'         => 'string|max:10',
            // 'bank_name'                     => 'string|max:50',
            // 'account_no'                    => 'numeric',
            // 'account_type'                  => 'string|max:20',
            // 'ifsc_code'                     => 'string|max:20',
            // 'branch_name'                   => 'string|max:50',
            // 'nominee_name'                  => 'string|max:50',
            // 'nominee_relation_name'         => 'string|max:255',
            // 'pan_no'                        => 'string|max:50',
            // 'epf_no'                        => 'string|max:50',
            // 'uan_no'                        => 'string|max:50',
            // 'esi_no'                        => 'string|max:50',
            // 'nps_no'                        => 'string|max:50'
            ]); 
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'error' => $errors
                ], 400);
            }
            if ($validator->passes()) {
                $mObject = new Employee();
                $data = $mObject->updateData($req);
                $mDeviceId = $req->deviceId ?? "";
                return responseMsgs(true, "Records updated successfully", $data, "API_ID_133","", "213ms", "post", $mDeviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_133","", "", "post", $mDeviceId);
        }            
    }
    
    /**
     * @OA\Post(
     * path="/delete_employee",
     * operationId="deleteEmployeeById",
     * tags={"Employee"},
     * summary="Delete Employee",
     * description="Delete Employee",
     * @OA\RequestBody(required=true,@OA\JsonContent(required={"id"},
     * @OA\Property(property="id", type="string", format="string", example="1"),),),
     * @OA\Response(response=200, description="Success",
     * @OA\JsonContent(
     * @OA\Property(property="status", type="integer", example=""),
     *    @OA\Property(property="data",type="object")
     * )))
    **/
    public function deleteEmployeeById(Request $req){
        //Description: delete record of a particular id
        try {
            $data = array();
            $validator = Validator::make($req->all(), [
                'id' => 'required|numeric'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'error' => $errors
                ], 400);
            }
            if ($validator->passes()) {
                $mObject = new Employee();
                $data = $mObject->deleteData($req);
                $mDeviceId = $req->deviceId ?? "";
                return responseMsgs(true, "Records deleted successfully", $data, "API_ID_134","", "173ms", "post", $mDeviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_134","", "", "post", $mDeviceId);
        } 
    }    
     
    //for truncate
    public function deleteAllEmployee(){
        // //Description: delete all records 
        // try {
        //     $mObject = new Employee();
        //     $data = $mObject->truncateData();
        //     $mDeviceId = $req->deviceId ?? "";
        //     return responseMsgs(true, "All records has been deleted successfully", $data, "API_ID_135","", "175ms", "delete", $mDeviceId);
        // } catch (Exception $e) {
        //     return responseMsgs(false, $e->getMessage(), $data, "API_ID_135","", "", "delete", $mDeviceId);
        // }    
    }

    
    


}
