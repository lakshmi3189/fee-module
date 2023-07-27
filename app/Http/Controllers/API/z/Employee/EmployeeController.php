<?php

namespace App\Http\Controllers\API\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Employee\Employee;
use App\Models\Employee\EmployeeEducation;
use App\Models\Employee\EmployeeExperience;
use App\Models\Employee\EmployeeFamily;
use DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Models\Admin\User;
use Illuminate\Support\Facades\Hash;
use Exception;
use Validator;

/*
Created By : Lakshmi kumari 
Created On : 20-Apr-2023 
Code Status : Open 
*/

class EmployeeController extends Controller
{
    private $_mEmployees;
    private $_mEmployeeEducation;
    private $_mEmployeeExperience;
    private $_mEmployeeFamily;

    public function __construct()
    {
        $this->_mEmployees = new Employee();
        $this->_mEmployeeEducation = new EmployeeEducation();
        $this->_mEmployeeExperience = new EmployeeExperience();
        $this->_mEmployeeFamily = new EmployeeFamily();
    }

    /**
     * | Search emp by emp no
     */

    public function search(Request $req)
    {
        //validation 
        $validator = Validator::make($req->all(), [
            'empNo' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            // $data1 = array();
            $msg = '';
            $data = Employee::select('id')
                ->where([['emp_no', '=', $req->empNo], ['status', '=', '1']])->first();
            if ($data != "") {
                $msg = "Employee ID Already Existing";
                $data1 = $data;
            } else {
                $msg = "Employee Not Found";
                $data1 = ['emp_no' => $req->empNo, 'message' => 'Employee No. not found', 'value' => 'false'];
            }
            return responseMsgs(true, $msg, $data1, "API_ID_132", "", "146ms", "post",  $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_132", "", "", "post", $req->deviceId ?? "");
        }
    }

    public function search1(Request $req)
    {
        //validation
        $validator = Validator::make($req->all(), [
            'empNo' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            $searchEmpId = new Employee();
            $data1  = $searchEmpId->searchEmpId($req);
            $msg = $data1['message'];
            $data = $data1;
            return responseMsgs(true, $msg, $data, "API_ID_132", "", "146ms", "post",  $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_132", "", "", "post", $req->deviceId ?? "");
        }
    }

    /**
     * |add bulk data using csv
     */
    public function storeCSV(Request $req)
    {
        //validation
        $validator = Validator::make($req->all(), [
            'uploadCSV' => 'mimes:csv'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $file = $req->file('uploadCSV');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            // Valid File Extensions
            $valid_extension = array("csv");

            // 2MB in Bytes
            $maxFileSize = 2097152;

            // Check file extension
            if (in_array(strtolower($extension), $valid_extension)) {
                // Check file size
                if ($fileSize <= $maxFileSize) {
                    // File upload location
                    $location = 'uploads';
                    // Upload file
                    $file->move($location, $filename);
                    // Import CSV to Database
                    $filepath = public_path($location . "/" . $filename);

                    // Reading file
                    $file = fopen($filepath, "r");
                    $importData_arr = array();
                    $i = 0;
                    while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                        $num = count($filedata);
                        // Skip first row (Remove below comment if you want to skip the first row)
                        if ($i == 0) {
                            $i++;
                            continue;
                        }
                        for ($c = 0; $c < $num; $c++) {
                            $importData_arr[$i][] = $filedata[$c];
                        }
                        $i++;
                    }
                    fclose($file);
                    // Insert to MySQL database
                    foreach ($importData_arr as $importData) {
                        // dd($importData);
                        $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));
                        $insertData = array(
                            "emp_no" => $importData[0],
                            "salutation_name" => $importData[1],
                            "first_name" => $importData[2],
                            "middle_name" => $importData[3],
                            "last_name" => $importData[4],
                            "email" => $importData[5],
                            "mobile" => $importData[6],
                            "dob" => $importData[7],
                            "doj" => $importData[8],
                            "aadhar_no" => $importData[9],
                            "disability" => $importData[10],
                            "gender_name" => $importData[11],
                            "category_name" => $importData[12],
                            "blood_group_name" => $importData[13],
                            "department_name" => $importData[14],
                            "employee_type_name" => $importData[15],
                            "teaching_title_name" => $importData[16],
                            "marital_status_name" => $importData[17],
                            "upload_image" => $importData[18],
                            "academic_year" => $fy,
                            "school_id" => authUser()->school_id,
                            "created_by" => authUser()->id,
                            "ip_address" => getClientIpAddress(),
                            "department_id" => 0,
                            "employee_type_id" => 0,
                            "teaching_title_id" => 0
                        );
                        // dd($insertData);
                        Employee::csv($insertData);
                    }
                }
            }
            return responseMsgs(true, "Successfully Uploaded", $insertData, "", "1.1", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Add data
     */
    public function store(Request $req)
    {

        //validation
        $validator = Validator::make($req->all(), [
            'empNo' => 'required|string',
            'salutation' => 'required|string',
            'firstName' => 'required|string',
            'lastName' => 'required|string',
            'gender' => 'required|string',
            'category' => 'required|string',
            'dob' => 'required|string',
            'doj' => 'required|string',
            'mobile' => 'required|numeric',
            'bloodGroup' => 'required|string',
            'department' => 'required|numeric',
            'employeeType' => 'required|numeric',
            'maritalStatus'  => 'required|string',
            'teachingTitle' => 'required|numeric',
            'disability' => 'required|string',
            'aadharNo' => 'required|numeric|digits:12',
            // 'email' => 'string|email',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            // DB::beginTransaction();
            //variable
            $result = array();
            $data = array();
            $file_name = '';
            //check if employee no existing or not 
            $isExists = $this->_mEmployees->readEmployeeGroup($req);
            if (collect($isExists)->isNotEmpty())
                throw new Exception("Employee ID Already Existing");

            $isExists = $this->_mEmployees->readEmployeeAadharGroup($req);
            if (collect($isExists)->isNotEmpty())
                throw new Exception("Aadhar No Already Existing");

            //get financial year
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));

            if ($req->uploadImage != "") {
                $uploadImage = $req->uploadImage;
                $get_file_name = $req->aadharNo . '-' . $uploadImage->getClientOriginalName();
                $path = public_path('school/employees/');
                $file_name = 'school/employees/' . $req->aadharNo . '-' . $uploadImage->getClientOriginalName();
                $req->file('uploadImage')->move($path, $get_file_name);
            }

            $this->_mEmployees->emp_no = $req->empNo;
            $this->_mEmployees->salutation_name = $req->salutation;
            $this->_mEmployees->first_name = $req->firstName;
            $this->_mEmployees->middle_name = $req->middleName;
            $this->_mEmployees->last_name = $req->lastName;
            $this->_mEmployees->email = $req->email;
            $this->_mEmployees->mobile = $req->mobile;
            $this->_mEmployees->dob = $req->dob;
            $this->_mEmployees->doj = $req->doj;
            $this->_mEmployees->aadhar_no = $req->aadharNo;
            $this->_mEmployees->disability = $req->disability;
            $this->_mEmployees->gender_name = $req->gender;
            $this->_mEmployees->category_name = $req->category;
            $this->_mEmployees->blood_group_name = $req->bloodGroup;
            $this->_mEmployees->department_id = $req->department;
            $this->_mEmployees->employee_type_id = $req->employeeType;
            $this->_mEmployees->teaching_title_id = $req->teachingTitle;
            $this->_mEmployees->marital_status_name = $req->maritalStatus;
            $this->_mEmployees->upload_image = $file_name;
            $this->_mEmployees->p_address1 = $req->pAddress1;
            $this->_mEmployees->p_address2 = $req->pAddress2;
            $this->_mEmployees->p_locality = $req->pLocality;
            $this->_mEmployees->p_landmark = $req->pLandmark;
            $this->_mEmployees->p_country_id = $req->pCountry;
            $this->_mEmployees->p_state_id = $req->pState;
            $this->_mEmployees->p_district_id = $req->pDistrict;
            $this->_mEmployees->p_pincode = $req->pPincode;
            $this->_mEmployees->c_address1 = $req->cAddress1;
            $this->_mEmployees->c_address2 = $req->cAddress2;
            $this->_mEmployees->c_locality = $req->cLocality;
            $this->_mEmployees->c_landmark = $req->cLandmark;
            $this->_mEmployees->c_country_id = $req->cCountry;
            $this->_mEmployees->c_state_id = $req->cState;
            $this->_mEmployees->c_district_id = $req->cDistrict;
            $this->_mEmployees->c_pincode = $req->cPincode;
            $this->_mEmployees->fathers_name = $req->fathersName;
            $this->_mEmployees->fathers_qualification_name = $req->fathersQualification;
            $this->_mEmployees->fathers_occupation_name = $req->fathersOccupation;
            $this->_mEmployees->mothers_name = $req->mothersName;
            $this->_mEmployees->mothers_qualification_name = $req->mothersQualification;
            $this->_mEmployees->mothers_occupation_name = $req->mothersOccupation;
            $this->_mEmployees->bank_id = $req->bank;
            $this->_mEmployees->account_no = $req->accountNo;
            $this->_mEmployees->account_type = $req->accountType;
            $this->_mEmployees->ifsc_code = $req->ifscCode;
            $this->_mEmployees->branch_name = $req->branchName;
            $this->_mEmployees->nominee_name = $req->nomineeName;
            $this->_mEmployees->nominee_relation_name = $req->nomineeRelation;
            $this->_mEmployees->pan_no = $req->panNo;
            $this->_mEmployees->epf_no = $req->epfNo;
            $this->_mEmployees->uan_no = $req->uanNo;
            $this->_mEmployees->esi_no = $req->esiNo;
            $this->_mEmployees->nps_no = $req->npsNo;
            $this->_mEmployees->academic_year = $fy;
            $this->_mEmployees->school_id = authUser()->school_id;
            $this->_mEmployees->created_by = authUser()->id;
            $this->_mEmployees->ip_address = getClientIpAddress();
            // print_var($this->_mEmployees);
            $this->_mEmployees->save();

            // print_var($req['educationDetails']);
            // die;
            //insert single data or multi data for employee education
            $empEdudata = '';
            if ($req['educationDetails'] != "") {
                foreach ($req['educationDetails'] as $key => $ob) {
                    $edu_file_name = "";
                    $eduFile_name = '';
                    // if ($ob->uploadEduDoc != "") {
                    //     //$uploadEduDoc = $req->uploadEduDoc;
                    //     $edu_file_name = $this->_mEmployees->empNo . '-' . $ob->uploadEduDoc->getClientOriginalName();
                    //     $path = public_path('school/employees/');
                    //     $file_name = 'school/employees/' . $this->_mEmployees->empNo . '-' . $ob->uploadEduDoc->getClientOriginalName();
                    //     $req->file('uploadEduDoc')->move($path, $edu_file_name);
                    // }

                    if ($req->uploadEduDoc != "") {
                        $uploadEduDoc = $req->uploadEduDoc;
                        $edu_file_name = $req->empNo . '-' . $uploadEduDoc->getClientOriginalName();
                        $path = public_path('school/employees/');
                        $eduFile_name = 'school/employees/' . $req->empNo . '-' . $uploadEduDoc->getClientOriginalName();
                        $req->file('uploadEduDoc')->move($path, $edu_file_name);
                    }

                    // $mEmpEduObject = new EmployeeEducation();
                    // $insert = [
                    //     $mEmpEduObject->emp_tbl_id = $this->_mEmployees->id,
                    //     $mEmpEduObject->exam_passed_name = $ob['examPassed'],
                    //     $mEmpEduObject->board_uni_inst = $ob['board'],
                    //     $mEmpEduObject->passing_year = $ob['passingYear'],
                    //     $mEmpEduObject->div_grade_name = $ob['divGrade'],
                    //     $mEmpEduObject->marks_obtained = $ob['marksObtained'],
                    //     $mEmpEduObject->total_marks = $ob['totalMarks'],
                    //     $mEmpEduObject->percentage = $ob['percentage'],
                    //     $mEmpEduObject->upload_edu_doc = $eduFile_name,
                    //     $mEmpEduObject->academic_year = $fy,
                    //     $mEmpEduObject->school_id = authUser()->school_id,
                    //     $mEmpEduObject->created_by = authUser()->id,
                    //     $mEmpEduObject->ip_address = getClientIpAddress()
                    // ];
                    // $mEmpEduObject->save($insert);

                    $mEmployeeEducation["emp_tbl_id"] =  $this->_mEmployees->id;
                    $mEmployeeEducation["exam_passed_name"] =  $ob['examPassed'];
                    $mEmployeeEducation["board_uni_inst"] =  $ob['board'];
                    $mEmployeeEducation["passing_year"] =  $ob['passingYear'];
                    $mEmployeeEducation["div_grade_name"] =  $ob['divGrade'];
                    $mEmployeeEducation["marks_obtained"] =  $ob['marksObtained'];
                    $mEmployeeEducation["total_marks"] =  $ob['totalMarks'];
                    $mEmployeeEducation["percentage"] =  $ob['percentage'];
                    $mEmployeeEducation["upload_edu_doc"] =  $edu_file_name;
                    $mEmployeeEducation["academic_year"] =  $fy;
                    $mEmployeeEducation["school_id"] =  authUser()->school_id;
                    $mEmployeeEducation["created_by"] =  authUser()->id;
                    $mEmployeeEducation["ip_address"] =  getClientIpAddress();
                    $empEdudata = $this->_mEmployeeEducation->insertData($mEmployeeEducation);
                    // print_var($empEdudata->id);
                }
            }
            // DB::rollback();
            // DB::commit();
            // dd();
            //insert single data or multi data for experience 
            $empExpdata = '';
            if ($req['experienceDetails'] != "") {
                foreach ($req['experienceDetails'] as $ob) {
                    $exp_file_name = "";
                    $expFile_name = '';
                    if ($req->uploadExpLetterDocs != "") {
                        $uploadExpLetterDocs = $req->uploadExpLetterDocs;
                        $exp_file_name = $req->empNo . '-' . $uploadExpLetterDocs->getClientOriginalName();
                        $path = public_path('school/employees/');
                        $expFile_name = 'school/employees/' . $req->empNo . '-' . $uploadExpLetterDocs->getClientOriginalName();
                        $req->file('uploadExpLetterDocs')->move($path, $exp_file_name);
                    }
                    $mEmployeeExperience["emp_tbl_id"] =  $this->_mEmployees->id;
                    $mEmployeeExperience["name_of_org"] =  $ob['nameOfOrg'];
                    $mEmployeeExperience["position_head"] =  $ob['positionHead'];
                    $mEmployeeExperience["period_from"] =  $ob['periodFrom'];
                    $mEmployeeExperience["period_to"] =  $ob['periodTo'];
                    $mEmployeeExperience["salary"] =  $ob['salary'];
                    $mEmployeeExperience["pay_grade"] =  $ob['payGrade'];
                    $mEmployeeExperience["upload_exp_letter"] =  $expFile_name;
                    $mEmployeeExperience["academic_year"] =  $fy;
                    $mEmployeeExperience["school_id"] =  authUser()->school_id;
                    $mEmployeeExperience["created_by"] =  authUser()->id;
                    $mEmployeeExperience["ip_address"] =  getClientIpAddress();
                    $empExpdata = $this->_mEmployeeExperience->insertData($mEmployeeExperience);

                    // $this->_mEmployeeExperience->emp_tbl_id = $this->_mEmployees->id;
                    // $this->_mEmployeeExperience->name_of_org = $ob['nameOfOrg'];
                    // $this->_mEmployeeExperience->position_head = $ob['positionHead'];
                    // $this->_mEmployeeExperience->period_from = $ob['periodFrom'];
                    // $this->_mEmployeeExperience->period_to = $ob['periodTo'];
                    // $this->_mEmployeeExperience->salary = $ob['salary'];
                    // $this->_mEmployeeExperience->pay_grade = $ob['payGrade'];
                    // $this->_mEmployeeExperience->upload_exp_letter = $exp_file_name;
                    // $this->_mEmployeeExperience->academic_year = $fy;
                    // $this->_mEmployeeExperience->school_id = authUser()->school_id;
                    // $this->_mEmployeeExperience->created_by = authUser()->id;
                    // $this->_mEmployeeExperience->ip_address = getClientIpAddress();
                    // $this->_mEmployeeExperience->save();
                }
            }

            //insert single data or multi data for experience 
            $empFamilydata = "";
            if ($req['familyDetails'] != "") {
                foreach ($req['familyDetails'] as $ob) {
                    $fam_file_name = "";
                    $famFile_name = "";
                    if ($req->uploadFMemberImage != "") {
                        $uploadFMemberImage = $req->uploadFMemberImage;
                        $fam_file_name = $req->empNo . '-' . $uploadFMemberImage->getClientOriginalName();
                        $path = public_path('school/employees/');
                        $famFile_name = 'school/employees/' . $req->empNo . '-' . $uploadFMemberImage->getClientOriginalName();
                        $req->file('uploadFMemberImage')->move($path, $fam_file_name);
                    }

                    $mEmployeeFamily["emp_tbl_id"] =  $this->_mEmployees->id;
                    $mEmployeeFamily["f_member_name"] =  $ob['fMemberName'];
                    $mEmployeeFamily["f_member_relation_name"] =  $ob['fMemberRelation'];
                    $mEmployeeFamily["f_member_dob"] =  $ob['fMemberDob'];
                    $mEmployeeFamily["upload_f_member_image"] =  $famFile_name;
                    $mEmployeeFamily["academic_year"] =  $fy;
                    $mEmployeeFamily["school_id"] =  authUser()->school_id;
                    $mEmployeeFamily["created_by"] =  authUser()->id;
                    $mEmployeeFamily["ip_address"] =  getClientIpAddress();
                    $empFamilydata = $this->_mEmployeeFamily->insertData($mEmployeeFamily);

                    // $this->_mEmployeeFamily->emp_tbl_id = $this->_mEmployees->id;
                    // $this->_mEmployeeFamily->f_member_name = $ob['fMemberName'];
                    // $this->_mEmployeeFamily->f_member_relation_name = $ob['fMemberRelation'];
                    // $this->_mEmployeeFamily->f_member_dob = $ob['fMemberDob'];
                    // $this->_mEmployeeFamily->upload_f_member_image = $fam_file_name;
                    // $this->_mEmployeeFamily->academic_year = $fy;
                    // $this->_mEmployeeFamily->school_id = authUser()->school_id;
                    // $this->_mEmployeeFamily->created_by = authUser()->id;
                    // $this->_mEmployeeFamily->ip_address = getClientIpAddress();
                    // $this->_mEmployeeFamily->save();
                }
            }
            // DB::rollback();
            // DB::commit();
            // dd();
            //return response
            $result['basic_details'] = $this->_mEmployees;
            // $result['educationDetails'] = $mEmpEduObject;
            $result['educationDetails'] = $empEdudata;
            $result['experienceDetails'] = $empExpdata;
            $result['familyDetails'] = $empFamilydata;

            return responseMsgs(true, "Successfully Saved", $result, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function addUser($req)
    {
        $pass = Str::random(10);
        $school_id = '123';
        $userType = 'Employee';
        $mObjectU = new User();
        $insert = [
            $mObjectU->name        = $req['first_name'],
            $mObjectU->email       = $req['email'],
            $mObjectU->password    = Hash::make($pass),
            $mObjectU->c_password  = $pass,
            $mObjectU->school_id   = $school_id,
            $mObjectU->user_id     = $req['emp_no'],
            $mObjectU->user_type   = $userType,
            $mObjectU->ip_address  = getClientIpAddress()
        ];
        $mObjectU->save($insert);
    }

    /**
     * | Add data
     */
    public function edit(Request $req)
    {
        //validation
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
            // 'empNo' => 'required|string',
            // 'salutation' => 'required|string',
            // 'firstName' => 'required|string',
            // 'lastName' => 'required|string',
            // 'gender' => 'required|string',
            // 'category' => 'required|string',
            // 'dob' => 'required|string',
            // 'doj' => 'required|string',
            // 'mobile' => 'required|numeric',
            // 'bloodGroup' => 'required|string',
            // 'department' => 'required|numeric',
            // 'employeeType' => 'required|numeric',
            // 'maritalStatus'  => 'required|string',
            // 'teachingTitle' => 'required|numeric',
            // 'disability' => 'required|string'
            // 'aadharNo' => 'required|numeric|digits:12',
            // 'email' => 'string|email',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            //variable
            $result = array();
            $file_name = '';

            //check if employee no existing or not 
            $isExists = $this->_mEmployees->readEmployeeGroup($req);
            if ($isExists && $isExists->where('id', '!=', $req->id)->isNotEmpty())
                throw new Exception("Employee ID Already Existing");

            $isExists = $this->_mEmployees->readEmployeeAadharGroup($req);
            if ($isExists && $isExists->where('id', '!=', $req->id)->isNotEmpty())
                throw new Exception("Aadhar No Already Existing");

            //get financial year
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));

            if ($req->uploadImage != "") {
                $uploadImage = $req->uploadImage;
                $get_file_name = $req->aadharNo . '-' . $uploadImage->getClientOriginalName();
                $path = public_path('school/employees/');
                $file_name = 'school/employees/' . $req->aadharNo . '-' . $uploadImage->getClientOriginalName();
                $req->file('uploadImage')->move($path, $get_file_name);
            }

            // $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));

            //check if employee no existing or not 
            // $isExists = $this->_mEmployees->readEmployeeGroup($req->id);
            // if ($isExists && $isExists->where('id', '!=', $req->id)->isNotEmpty())
            //     throw new Exception("Employee ID  or Aadhar No Already Existing");

            // $isExists = $this->_mEmployees->readEmployeeGroup($req);
            // if (collect($isExists)->isNotEmpty())
            //     throw new Exception("Employee ID or Aadhar No Already Existing");

            // if ($req->uploadImage != "") {
            //     $uploadImage = $req->uploadImage;
            //     $file_name = $req->empNo . '-' . $uploadImage->getClientOriginalName();
            //     $path = public_path('school/employees/');
            //     $req->file('uploadImage')->move($path, $req->empNo . '-' . $file_name);
            // }

            $getData = $this->_mEmployees::findOrFail($req->id);

            if (!$getData)
                throw new Exception("Employee Not Found!");

            $metaReqs = [
                'middle_name' => $req->middleName,
                'email' => $req->email,
                // 'aadhar_no' => $req->aadharNo,
                'p_address1' => $req->pAddress1,
                'p_address2' => $req->pAddress2,
                'p_locality' => $req->pLocality,
                'p_landmark' => $req->pLandmark,
                'p_country_id' => $req->pCountry,
                'p_state_id' => $req->pState,
                'p_district_id' => $req->pDistrict,
                'p_pincode' => $req->pPincode,
                'c_address1' => $req->cAddress1,
                'c_address2' => $req->cAddress2,
                'c_locality' => $req->cLocality,
                'c_landmark' => $req->cLandmark,
                'c_country_id' => $req->cCountry,
                'c_state_id' => $req->cState,
                'c_district_id' => $req->cDistrict,
                'c_pincode' => $req->cPincode,
                'fathers_name' => $req->fathersName,
                'fathers_qualification_name' => $req->fathersQualification,
                'fathers_occupation_name' => $req->fathersOccupation,
                'mothers_name' => $req->mothersName,
                'mothers_qualification_name' => $req->mothersQualification,
                'mothers_occupation_name' => $req->mothersOccupation,
                'bank_id' => $req->bank,
                'account_no' => $req->accountNo,
                'account_type' => $req->accountType,
                'ifsc_code' => $req->ifscCode,
                'branch_name' => $req->branchName,
                'nominee_name' => $req->nomineeName,
                'nominee_relation_name' => $req->nomineeRelation,
                'pan_no' => $req->panNo,
                'epf_no' => $req->epfNo,
                'uan_no' => $req->uanNo,
                'esi_no' => $req->esiNo,
                'nps_no' => $req->npsNo,
                'version_no' => $getData->version_no + 1,
                'updated_at' => Carbon::now()
            ];

            if (isset($req->status)) {                  // In Case of Deactivation or Activation 
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs = array_merge($metaReqs, [
                    'status' => $status
                ]);
            }
            //$updateData = $this->_mEmployees::findOrFail($req->id);
            $getData->update($metaReqs);

            //insert single data or multi data for employee education 
            if ($req['educationDetails'] != "") {
                $mObjectEdu =  $this->_mEmployeeEducation::where(['emp_tbl_id' => $req->id], ['status' => 1]);
                $mObjectEdu->delete();
                $arrEdu = array();
                foreach ($req['educationDetails'] as $ob) {
                    $edu_file_name = "";

                    if ($req->uploadEduDoc != "") {
                        $uploadEduDoc = $req->uploadEduDoc;
                        $edu_file_name = $uploadEduDoc->getClientOriginalName();
                        $path = public_path('school/employees/');
                        $req->file('uploadEduDoc')->move($path, $this->_mEmployees->emp_no . '-' . $edu_file_name);
                    }
                    $this->_mEmployeeEducation->emp_tbl_id = $req->id;
                    $this->_mEmployeeEducation->exam_passed_name = $ob['examPassed'];
                    $this->_mEmployeeEducation->board_uni_inst = $ob['board'];
                    $this->_mEmployeeEducation->passing_year = $ob['passingYear'];
                    $this->_mEmployeeEducation->div_grade_name = $ob['divGrade'];
                    $this->_mEmployeeEducation->marks_obtained = $ob['marksObtained'];
                    $this->_mEmployeeEducation->total_marks = $ob['totalMarks'];
                    $this->_mEmployeeEducation->percentage = $ob['percentage'];
                    $this->_mEmployeeEducation->upload_edu_doc = $edu_file_name;
                    $this->_mEmployeeEducation->academic_year = $fy;
                    $this->_mEmployeeEducation->school_id = authUser()->school_id;
                    $this->_mEmployeeEducation->created_by = authUser()->id;
                    $this->_mEmployeeEducation->ip_address = getClientIpAddress();
                    $this->_mEmployeeEducation->save();
                    $arrEdu[] = $this->_mEmployeeEducation;
                }
            }

            //insert single data or multi data for experience 
            if ($req['experienceDetails'] != "") {
                $mObjectExp =  $this->_mEmployeeExperience::where(['emp_tbl_id' => $req->id], ['status' => 1]);
                $mObjectExp->delete();
                $arrExp = array();
                foreach ($req['experienceDetails'] as $ob) {
                    $exp_file_name = "";

                    if ($req->uploadExpLetterDocs != "") {
                        $uploadExpLetterDocs = $req->uploadExpLetterDocs;
                        $exp_file_name = $uploadExpLetterDocs->getClientOriginalName();
                        $path = public_path('school/employees/');
                        $req->file('uploadExpLetterDocs')->move($path, $this->_mEmployees->emp_no . '-' . $exp_file_name);
                    }

                    $this->_mEmployeeExperience->emp_tbl_id = $req->id;
                    $this->_mEmployeeExperience->name_of_org = $ob['nameOfOrg'];
                    $this->_mEmployeeExperience->position_head = $ob['positionHead'];
                    $this->_mEmployeeExperience->period_from = $ob['periodFrom'];
                    $this->_mEmployeeExperience->period_to = $ob['periodTo'];
                    $this->_mEmployeeExperience->salary = $ob['salary'];
                    $this->_mEmployeeExperience->pay_grade = $ob['payGrade'];
                    $this->_mEmployeeExperience->upload_edu_doc = $edu_file_name;
                    $this->_mEmployeeExperience->academic_year = $fy;
                    $this->_mEmployeeExperience->school_id = authUser()->school_id;
                    $this->_mEmployeeExperience->created_by = authUser()->id;
                    $this->_mEmployeeExperience->ip_address = getClientIpAddress();
                    $this->_mEmployeeExperience->save();
                    $arrExp[] = $this->_mEmployeeExperience;
                }
            }

            //insert single data or multi data for experience 
            if ($req['familyDetails'] != "") {
                $mObjectFam =  $this->_mEmployeeFamily::where(['emp_tbl_id' => $req->id], ['status' => 1]);
                $mObjectFam->delete();
                $arrFam = array();
                foreach ($req['familyDetails'] as $ob) {
                    $fam_file_name = "";
                    if ($req->uploadFMemberImage != "") {
                        $uploadFMemberImage = $req->uploadFMemberImage;
                        $fam_file_name = $uploadFMemberImage->getClientOriginalName();
                        $path = public_path('school/employees/');
                        $req->file('uploadFMemberImage')->move($path, $this->_mEmployees->emp_no . '-' . $fam_file_name);
                    }

                    $this->_mEmployeeFamily->emp_tbl_id = $this->_mEmployees->id;
                    $this->_mEmployeeFamily->f_member_name = $ob['fMemberName'];
                    $this->_mEmployeeFamily->f_member_relation_name = $ob['fMemberRelation'];
                    $this->_mEmployeeFamily->f_member_dob = $ob['fMemberDob'];
                    $this->_mEmployeeFamily->upload_edu_doc = $fam_file_name;
                    $this->_mEmployeeFamily->academic_year = $fy;
                    $this->_mEmployeeFamily->school_id = authUser()->school_id;
                    $this->_mEmployeeFamily->created_by = authUser()->id;
                    $this->_mEmployeeFamily->ip_address = getClientIpAddress();
                    $this->_mEmployeeFamily->save();
                    $arrFam[] = $this->_mEmployeeFamily;
                }
            }

            //return response
            $result['basic_details'] = $this->_mEmployees;
            $result['educationDetails'] = $arrEdu;
            $result['experienceDetails'] = $arrExp;
            $result['familyDetails'] = $arrFam;
            return responseMsgs(true, "Successfully Updated", $arrEdu, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }



    //View by id
    public function show(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            // $Banks = $this->_mBanks::findOrFail($req->id);
            $show = $this->_mEmployees->getGroupById($req->id);
            if (collect($show)->isEmpty())
                throw new Exception("Record Not Found!");
            return responseMsgs(true, "", $show, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //check duplicate aadhar existing
    public function getDuplicate1(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'aadharNo' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $checkData = $this->_mEmployees->getDuplicateAadhar($req);
            if (collect($checkData)->isEmpty())
                throw new Exception("Record Not Found!");
            return responseMsgs(true, "", $checkData, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function getDuplicateAadhar(Request $req)
    {
        //validation 
        $validator = Validator::make($req->all(), [
            'aadharNo' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            // $data1 = array();
            $msg = '';
            $data = Employee::select('id')
                ->where([['aadhar_no', '=', $req->aadharNo], ['status', '=', '1']])->first();
            if ($data != "") {
                $msg = "Employee Aadhar Already Existing";
                $data1 = $data;
            } else {
                //$msg = "Employee Not Found";
                $data1 = ['aadhar_no' => $req->aadharNo, 'message' => '', 'value' => 'false'];
            }
            return responseMsgs(true, $msg, "", "API_ID_132", "", "146ms", "post",  $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), "", "API_ID_132", "", "", "post", $req->deviceId ?? "");
        }
    }

    /**
     * | View data
     */
    public function retrieveAll(Request $req)
    {
        try {
            $getLists = $this->_mEmployees->retrieve();
            return responseMsgs(true, "All Employees List", $getLists, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Delete data
     */
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
            $emp = $this->_mEmployees::findOrFail($req->id);
            // if ($emp->status == 0)
            //     throw new Exception("Records Already Deleted");
            $emp->update($metaReqs);
            return responseMsgs(true, "Deleted Successfully", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }


    // Edit records
    public function updateRole(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric',
            'roleId' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            // $isExists = $this->_mEmployees->readRoleExist($req);
            // if ($isExists && $isExists->where('id', '!=', $req->id)->isNotEmpty())
            //     throw new Exception("Role Already Existing");
            $getData = $this->_mEmployees::findOrFail($req->id);
            $metaReqs = [
                'role_id' => $req->roleId,
                'version_no' => $getData->version_no + 1
            ];
            $metaReqs = array_merge($metaReqs, [
                'json_logs' => trim($getData->json_logs . "," . json_encode($metaReqs), ",")
            ]);
            if (isset($req->status)) {                  // In Case of Deactivation or Activation 
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs = array_merge($metaReqs, [
                    'status' => $status
                ]);
            }
            $getData->update($metaReqs);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Records Updated Successfully", [], "API_3.9", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_3.9", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
