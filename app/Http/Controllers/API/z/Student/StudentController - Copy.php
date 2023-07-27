<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Http\Traits\CustomTraits;
use Illuminate\Http\Request;
use App\Models\Student\Student;
use App\Models\Admin\User;
use App\Models\Student\StudentSibling;
use App\Models\Student\StudentTransport;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

/*
Created By : Lakshmi kumari 
Created On : 19-May-2023 
Code Status : Open 
Description : Student registration is working in two way 1: Offline Mode -- 2: Online Mode
Here custom trait file helpful to store user id and school id for online registartion mode
*/

class StudentController extends Controller
{
    private $_mStudents;
    private $_tCustome;
    private $_mStudentTransport;
    private $_mStudentSibling;

    public function __construct()
    {
        $this->_mStudents = new Student();
        $this->_tCustome = new CustomTraits();
        $this->_mStudentTransport = new StudentTransport();
        $this->_mStudentSibling = new StudentSibling();
    }

    /**---------------------------------------------------------------------------------------------
     * |For BLL 
     */
    public function searchStudentByAdmission(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'admissionNo' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            //$data = array();           
            $searchAdmNo = new Student();
            $data  = $searchAdmNo->readStudentGroup($req->admissionNo);
            return responseMsgs(true, "Records", $data, "API_ID_132", "", "146ms", "post", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_132", "", "", "post", $req->deviceId ?? "");
        }
    }
    /*-------------------------------------------------------------------------------------------*/

    public function store(Request $req)
    {
        //Description: add students basic details 
        $validator = Validator::make($req->all(), [
            'rollNo'                       => 'required|string',
            'classId'                      => 'required|integer',
            'className'                    => 'required|string|max:20',
            'sectionId'                    => 'required|integer',
            'sectionName'                  => 'required|string|max:20',
            'firstName'                    => 'required|string|max:20',
            'middleName'                   => 'string|max:20',
            'lastName'                     => 'required|string|max:20',
            'Email'                        => 'string|email|max:255',
            'admissionDate'                => 'required|date',
            'Mobile'                       => 'required|numeric|digits:10|regex:/[0-9]/',
            'Dob'                          => 'required|date',
            'aadharNo'                     => 'required|numeric|digits:12',
            'disability'                   => 'required|string|max:10',
            'genderId'                     => 'required|integer',
            'genderName'                   => 'required|string|max:20',
            'categoryId'                   => 'required|integer',
            'categoryName'                 => 'required|string|max:100',
            'casteId'                      => 'required|integer',
            'casteName'                    => 'required|string|max:100',
            'bloodGroupId'                 => 'required|integer',
            'bloodGroupName'               => 'required|string|max:10',
            'religionId'                   => 'required|integer',
            'religionName'                 => 'required|string|max:50',
            'houseWardId'                  => 'required|integer',
            'houseWardName'                => 'required|string|max:50',
            'uploadImage'                  => 'file|mimes:jpg,png,jpeg|max:255',
            'pAddress1'                    => 'string|max:255',
            'pAddress2'                    => 'string|max:255',
            'pLocality'                    => 'string|max:255',
            'pLandmark'                    => 'string|max:255',
            'pCountry'                     => 'string|max:50',
            'pState'                       => 'string|max:50',
            'pDistrict'                    => 'string|max:50',
            'pPincode'                     => 'numeric|digits:6',
            'cAddress1'                    => 'string|max:255',
            'cAddress2'                    => 'string|max:255',
            'cLocality'                    => 'string|max:255',
            'cLandmark'                    => 'string|max:255',
            'cCountry'                     => 'string|max:50',
            'cState'                       => 'string|max:50',
            'cDistrict'                    => 'string|max:50',
            'cPincode'                     => 'numeric|digits:6',
            'fathersName'                  => 'string|max:50',
            'fathersQualificationName'     => 'string|max:50',
            'fathersOccupationName'        => 'string|max:50',
            // 'fathersAnnualIncome'         => 'string|max:10',
            'mothersName'                  => 'string|max:50',
            'mothersQualificationName'     => 'string|max:50',
            'mothersOccupationName'        => 'string|max:50',
            // 'mothersAnnualIncome'         => 'string|max:10',
            'bankName'                     => 'string|max:50',
            'accountNo'                    => 'numeric',
            'accountType'                  => 'string|max:20',
            'ifscCode'                     => 'string|max:20',
            'branchName'                   => 'string|max:50'

        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $isExists = $this->_mStudents->readStudentGroup($req->admissionNo);
            if (collect($isExists)->isNotEmpty())
                throw new Exception("Student already existing");

            $result = array();
            $file_name = '';
            $fy = getFinancialYear(Carbon::now()->format('Y-M-D'));

            //Using trait file for user id and school id for std. online reg 
            $userId =  $this->_tCustome->forOnlineRegUser();
            $schoolId =  $this->_tCustome->forOnlineRegSchool();

            if ($req->uploadImage != "") {
                // $uploadImage = $req->uploadImage;
                $get_file_name = $req->empNo . '-' . $req->uploadImage->getClientOriginalName();
                $path = public_path('school/students/');
                $file_name = 'school/students/' . $req->empNo . '-' . $req->uploadImage->getClientOriginalName();
                $req->file('uploadImage')->move($path, $get_file_name);
            }

            $this->_mStudents->admission_no = $req['admissionNo'];
            $this->_mStudents->roll_no = $req['rollNo'];
            $this->_mStudents->first_name = $req['firstName'];
            $this->_mStudents->middle_name = $req['middleName'];
            $this->_mStudents->last_name = $req['lastName'];
            $this->_mStudents->class_id = $req['classId'];
            $this->_mStudents->class_name = $req['className'];
            $this->_mStudents->section_id = $req['sectionId'];
            $this->_mStudents->section_name = $req['sectionName'];
            $this->_mStudents->dob = $req['Dob'];
            $this->_mStudents->admission_date = $req['admissionDate'];
            $this->_mStudents->gender_id = $req['genderId'];
            $this->_mStudents->gender_name = $req['genderName'];
            $this->_mStudents->blood_group_id = $req['bloodGroupId'];
            $this->_mStudents->blood_group_name = $req['bloodGroupName'];
            $this->_mStudents->email = $req['Email'];
            $this->_mStudents->mobile = $req['Mobile'];
            $this->_mStudents->aadhar_no = $req['aadharNo'];
            $this->_mStudents->disability = $req['disability'];
            $this->_mStudents->category_id = $req['categoryId'];
            $this->_mStudents->category_name = $req['categoryName'];
            $this->_mStudents->caste_id = $req['casteId'];
            $this->_mStudents->caste_name = $req['casteName'];
            $this->_mStudents->religion_id = $req['religionId'];
            $this->_mStudents->religion_name = $req['religionName'];
            $this->_mStudents->house_ward_id = $req['houseWardId'];
            $this->_mStudents->house_ward_name = $req['houseWardName'];
            $this->_mStudents->upload_image = $file_name;
            $this->_mStudents->last_school_name = $req['lastSchoolName'];
            $this->_mStudents->last_school_address = $req['lastSchoolAddress'];
            $this->_mStudents->admission_mid_session = $req['admissionMidSession'];
            $this->_mStudents->admission_month = $req['admissionMonth'];
            $this->_mStudents->fathers_name = $req['fathersName'];
            $this->_mStudents->fathers_mob_no = $req['fathersMobNo'];
            $this->_mStudents->fathers_qualification_id = $req['fathersQualificationId'];
            $this->_mStudents->fathers_qualification_name = $req['fathersQualificationName'];
            $this->_mStudents->fathers_occupation_id = $req['fathersOccupationId'];
            $this->_mStudents->fathers_occupation_name = $req['fathersOccupationName'];
            $this->_mStudents->fathers_email = $req['fathersEmail'];
            $this->_mStudents->fathers_aadhar = $req['fathersAadhar'];
            $this->_mStudents->fathers_image = '';
            $this->_mStudents->fathers_annual_income = $req['fathersAnnualIncome'];
            $this->_mStudents->mothers_name = $req['mothersName'];
            $this->_mStudents->mothers_mob_no = $req['mothersMobNo'];
            $this->_mStudents->mothers_qualification_id = $req['mothersQualificationId'];
            $this->_mStudents->mothers_qualification_name = $req['mothersQualificationName'];
            $this->_mStudents->mothers_occupation_id = $req['mothersOccupationId'];
            $this->_mStudents->mothers_occupation_name = $req['mothersOccupationName'];
            $this->_mStudents->mothers_email = $req['mothersEmail'];
            $this->_mStudents->mothers_aadhar = $req['mothersAadhar'];
            $this->_mStudents->mothers_image = '';
            $this->_mStudents->mothers_annual_income = $req['mothersAnnualIncome'];
            $this->_mStudents->guardian_name = $req['guardianName'];
            $this->_mStudents->guardian_mob_no = $req['guardianMobNo'];
            $this->_mStudents->guardian_qualification_id = $req['guardianQualificationId'];
            $this->_mStudents->guardian_qualification_name = $req['guardianQualificationName'];
            $this->_mStudents->guardian_occupation_id = $req['guardianOccupationId'];
            $this->_mStudents->guardian_occupation_name = $req['guardianOccupationName'];
            $this->_mStudents->guardian_email = $req['guardianEmail'];
            $this->_mStudents->guardian_aadhar = $req['guardianAadhar'];
            $this->_mStudents->guardian_image = '';
            $this->_mStudents->guardian_annual_income = $req['guardianAnnualIncome'];
            // $this->_mStudents->guardian_relation = $req['guardian_relation'];
            $this->_mStudents->p_address1 = $req['pAddress1'];
            $this->_mStudents->p_address2 = $req['pAddress2'];
            $this->_mStudents->p_locality = $req['pLocality'];
            $this->_mStudents->p_landmark = $req['pLandmark'];
            $this->_mStudents->p_country_id = $req['pCountryId'];
            $this->_mStudents->p_country_name = $req['pCountryName'];
            $this->_mStudents->p_state_id = $req['pStateId'];
            $this->_mStudents->p_state_name = $req['pStateName'];
            $this->_mStudents->p_district_id = $req['pDistrictId'];
            $this->_mStudents->p_district_name = $req['pDistrictName'];
            $this->_mStudents->p_pincode = $req['pPincode'];
            $this->_mStudents->c_address1 = $req['cAddress1'];
            $this->_mStudents->c_address2 = $req['cAddress2'];
            $this->_mStudents->c_locality = $req['cLocality'];
            $this->_mStudents->c_landmark = $req['cLandmark'];
            $this->_mStudents->c_country_id = $req['cCountryId'];
            $this->_mStudents->c_country_name = $req['cCountryName'];
            $this->_mStudents->c_state_id = $req['cStateId'];
            $this->_mStudents->c_state_name = $req['cStateName'];
            $this->_mStudents->c_district_id = $req['cDistrictId'];
            $this->_mStudents->c_district_name = $req['cDistrictName'];
            $this->_mStudents->c_pincode = $req['cPincode'];
            $this->_mStudents->hobbies = $req['Hobbies'];
            $this->_mStudents->bank_id = $req['bank_id'];
            $this->_mStudents->bank_name = $req['bank_name'];
            $this->_mStudents->account_no = $req['accountNo'];
            $this->_mStudents->ifsc_code = $req['ifscCode'];
            $this->_mStudents->branch_name = $req['branchName'];
            $this->_mStudents->is_transport = $req['isTransport'];
            $this->_mStudents->academic_year =   $fy;
            $this->_mStudents->ip_address = getClientIpAddress();
            if (authUser() != "") {
                $this->_mStudents->created_by   = authUser()->id;
                $this->_mStudents->school_id = authUser()->school_id;
            } else {
                $this->_mStudents->created_by   = $userId;
                $this->_mStudents->school_id = $schoolId;
            }
            $this->_mStudents->save();

            //insert single data and multi data for student transport
            if ($req['transportDetails'] != "") {
                foreach ($req['transportDetails'] as $key => $ob) {
                    $mStudentTransport["std_tbl_id"] =  $this->_mStudents->id;
                    $mStudentTransport["route_id"] =  $ob['routeId'];
                    $mStudentTransport["pick_up_point_id"] =  $ob['pickUpPointId'];
                    $mStudentTransport["academic_year"] =  $fy;
                    $mStudentTransport["school_id"] =  authUser()->school_id;
                    $mStudentTransport["created_by"] =  authUser()->id;
                    $mStudentTransport["ip_address"] =  getClientIpAddress();
                    $stdTransportData = $this->_mStudentTransport->insertData($mStudentTransport);
                    // print_var($stdTransportata->id);
                }
            }

            //insert single data and multi data for student sibling 
            if ($req['siblingDetails'] != "") {
                foreach ($req['siblingDetails'] as $key => $ob) {
                    $mStudentSibling["std_tbl_id"] =  $this->_mStudents->id;
                    $mStudentSibling["sibling_name"] =  $ob['siblingName'];
                    $mStudentSibling["sibling_class"] =  $ob['siblingClass'];
                    $mStudentSibling["sibling_section"] =  $ob['siblingSection'];
                    $mStudentSibling["siblingAdmissionNo"] =  $ob['sibling_admission_no'];
                    $mStudentSibling["sibling_roll_no"] =  $ob['siblingRollNo'];
                    $mStudentSibling["academic_year"] =  $fy;
                    $mStudentSibling["school_id"] =  authUser()->school_id;
                    $mStudentSibling["created_by"] =  authUser()->id;
                    $mStudentSibling["ip_address"] =  getClientIpAddress();
                    $stdSiblingData = $this->_mStudentSibling->insertData($mStudentSibling);
                    // print_var($stdTransportata->id);
                }
            }

            //return response
            $result['basicDetails'] = $this->_mStudents;
            $result['transportDetails'] = $stdTransportData;
            $result['siblingDetails'] = $stdSiblingData;
            return responseMsgs(true, "Student Registration Done Successfully", $result, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Update Student table
     */
    public function edit(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id'                           => 'required|numeric',
            'rollNo'                       => 'required|string',
            'classId'                      => 'required|integer',
            'className'                    => 'required|string|max:20',
            'sectionId'                    => 'required|integer',
            'sectionName'                  => 'required|string|max:20',
            'firstName'                    => 'required|string|max:20',
            'middleName'                   => 'string|max:20',
            'lastName'                     => 'required|string|max:20',
            'Email'                        => 'string|email|max:255',
            'Mobile'                       => 'required|numeric|digits:10|regex:/[0-9]/',
            'Dob'                          => 'required|date',
            'aadharNo'                     => 'required|numeric|digits:12',
            'disability'                   => 'required|string|max:10',
            'genderId'                     => 'required|integer',
            'genderName'                   => 'required|string|max:20',
            'categoryId'                   => 'required|integer',
            'categoryName'                 => 'required|string|max:100',
            'casteId'                      => 'required|integer',
            'casteName'                    => 'required|string|max:100',
            'bloodGroupId'                 => 'required|integer',
            'bloodGroupName'               => 'required|string|max:10',
            'religionId'                   => 'required|integer',
            'religionName'                 => 'required|string|max:50',
            'houseWardId'                  => 'required|integer',
            'houseWardName'                => 'required|string|max:50',
            'uploadImage'                  => 'string|upload_image|mimes:jpg,png,jpeg|max:255',
            'pAddress1'                    => 'string|max:255',
            'pAddress2'                    => 'string|max:255',
            'pLocality'                    => 'string|max:255',
            'pLandmark'                    => 'string|max:255',
            'pCountry'                     => 'string|max:50',
            'pState'                       => 'string|max:50',
            'pDistrict'                    => 'string|max:50',
            'pPincode'                     => 'numeric|digits:6',
            'cAddress1'                    => 'string|max:255',
            'cAddress2'                    => 'string|max:255',
            'cLocality'                    => 'string|max:255',
            'cLandmark'                    => 'string|max:255',
            'cCountry'                     => 'string|max:50',
            'cState'                       => 'string|max:50',
            'cDistrict'                    => 'string|max:50',
            'cPincode'                     => 'numeric|digits:6',
            'fathersName'                  => 'string|max:50',
            'fathersQualificationName'     => 'string|max:50',
            'fathersOccupationName'        => 'string|max:50',
            // 'fathersAnnualIncome'         => 'string|max:10',
            'mothersName'                  => 'string|max:50',
            'mothersQualificationName'     => 'string|max:50',
            'mothersOccupationName'        => 'string|max:50',
            // 'mothersAnnualIncome'         => 'string|max:10',
            'bankName'                     => 'string|max:50',
            'accountNo'                    => 'numeric',
            'accountType'                  => 'string|max:20',
            'ifscCode'                     => 'string|max:20',
            'branchName'                   => 'string|max:50'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            $isExists = $this->_mStudents->readStudentGroup($req->admissionNo);
            if ($isExists && $isExists->where('id', '!=', $req->id)->isNotEmpty())
                throw new Exception("Student Already Existing");
            $getData = $this->_mStudents::findOrFail($req->id);

            // $this->_mStudents-> admission_no = $req['admissionNo'];
            $this->_mStudents->roll_no = $req['rollNo'];
            $this->_mStudents->first_name = $req['firstName'];
            $this->_mStudents->middle_name = $req['middleName'];
            $this->_mStudents->last_name = $req['lastName'];
            $this->_mStudents->class_id = $req['classId'];
            $this->_mStudents->class_name = $req['className'];
            $this->_mStudents->section_id = $req['sectionId'];
            $this->_mStudents->section_name = $req['sectionName'];
            $this->_mStudents->dob = $req['Dob'];
            $this->_mStudents->admission_date = $req['admissionDate'];
            $this->_mStudents->gender_id = $req['genderId'];
            $this->_mStudents->gender_name = $req['genderName'];
            $this->_mStudents->blood_group_id = $req['bloodGroupId'];
            $this->_mStudents->blood_group_name = $req['bloodGroupName'];
            $this->_mStudents->email = $req['Email'];
            $this->_mStudents->mobile = $req['Mobile'];
            $this->_mStudents->aadhar_no = $req['aadharNo'];
            $this->_mStudents->disability = $req['disability'];
            $this->_mStudents->category_id = $req['categoryId'];
            $this->_mStudents->category_name = $req['categoryName'];
            $this->_mStudents->caste_id = $req['casteId'];
            $this->_mStudents->caste_name = $req['casteName'];
            $this->_mStudents->religion_id = $req['religionId'];
            $this->_mStudents->religion_name = $req['religionName'];
            $this->_mStudents->house_ward_id = $req['houseWardId'];
            $this->_mStudents->house_ward_name = $req['houseWardName'];
            $this->_mStudents->upload_image = $req['uploadImage'];
            $this->_mStudents->last_school_name = $req['lastSchoolName'];
            $this->_mStudents->last_school_address = $req['lastSchoolAddress'];
            $this->_mStudents->admission_mid_session = $req['admissionMidSession'];
            $this->_mStudents->admission_month = $req['admissionMonth'];
            $this->_mStudents->fathers_name = $req['fathersName'];
            $this->_mStudents->fathers_mob_no = $req['fathersMobNo'];
            $this->_mStudents->fathers_qualification_id = $req['fathersQualificationId'];
            $this->_mStudents->fathers_qualification_name = $req['fathersQualificationName'];
            $this->_mStudents->fathers_occupation_id = $req['fathersOccupationId'];
            $this->_mStudents->fathers_occupation_name = $req['fathersOccupationName'];
            $this->_mStudents->fathers_email = $req['fathersEmail'];
            $this->_mStudents->fathers_aadhar = $req['fathersAadhar'];
            $this->_mStudents->fathers_image = '';
            $this->_mStudents->fathers_annual_income = $req['fathersAnnualIncome'];
            $this->_mStudents->mothers_name = $req['mothersName'];
            $this->_mStudents->mothers_mob_no = $req['mothersMobNo'];
            $this->_mStudents->mothers_qualification_id = $req['mothersQualificationId'];
            $this->_mStudents->mothers_qualification_name = $req['mothersQualificationName'];
            $this->_mStudents->mothers_occupation_id = $req['mothersOccupationId'];
            $this->_mStudents->mothers_occupation_name = $req['mothersOccupationName'];
            $this->_mStudents->mothers_email = $req['mothersEmail'];
            $this->_mStudents->mothers_aadhar = $req['mothersAadhar'];
            $this->_mStudents->mothers_image = '';
            $this->_mStudents->mothers_annual_income = $req['mothersAnnualIncome'];
            $this->_mStudents->guardian_name = $req['guardianName'];
            $this->_mStudents->guardian_mob_no = $req['guardianMobNo'];
            $this->_mStudents->guardian_qualification_id = $req['guardianQualificationId'];
            $this->_mStudents->guardian_qualification_name = $req['guardianQualificationName'];
            $this->_mStudents->guardian_occupation_id = $req['guardianOccupationId'];
            $this->_mStudents->guardian_occupation_name = $req['guardianOccupationName'];
            $this->_mStudents->guardian_email = $req['guardianEmail'];
            $this->_mStudents->guardian_aadhar = $req['guardianAadhar'];
            $this->_mStudents->guardian_image = '';
            $this->_mStudents->guardian_annual_income = $req['guardianAnnualIncome'];
            // $this->_mStudents->guardian_relation = $req['guardian_relation'];
            $this->_mStudents->p_address1 = $req['pAddress1'];
            $this->_mStudents->p_address2 = $req['pAddress2'];
            $this->_mStudents->p_locality = $req['pLocality'];
            $this->_mStudents->p_landmark = $req['pLandmark'];
            $this->_mStudents->p_country_id = $req['pCountryId'];
            $this->_mStudents->p_country_name = $req['pCountryName'];
            $this->_mStudents->p_state_id = $req['pStateId'];
            $this->_mStudents->p_state_name = $req['pStateName'];
            $this->_mStudents->p_district_id = $req['pDistrictId'];
            $this->_mStudents->p_district_name = $req['pDistrictName'];
            $this->_mStudents->p_pincode = $req['pPincode'];
            $this->_mStudents->c_address1 = $req['cAddress1'];
            $this->_mStudents->c_address2 = $req['cAddress2'];
            $this->_mStudents->c_locality = $req['cLocality'];
            $this->_mStudents->c_landmark = $req['cLandmark'];
            $this->_mStudents->c_country_id = $req['cCountryId'];
            $this->_mStudents->c_country_name = $req['cCountryName'];
            $this->_mStudents->c_state_id = $req['cStateId'];
            $this->_mStudents->c_state_name = $req['cStateName'];
            $this->_mStudents->c_district_id = $req['cDistrictId'];
            $this->_mStudents->c_district_name = $req['cDistrictName'];
            $this->_mStudents->c_pincode = $req['cPincode'];
            $this->_mStudents->hobbies = $req['Hobbies'];
            $this->_mStudents->bank_id = $req['bank_id'];
            $this->_mStudents->bank_name = $req['bank_name'];
            $this->_mStudents->account_no = $req['accountNo'];
            $this->_mStudents->ifsc_code = $req['ifscCode'];
            $this->_mStudents->branch_name = $req['branchName'];
            $this->_mStudents->is_transport = $req['isTransport'];

            $this->_mStudents->version_no = $getData->version_no + 1;
            $this->_mStudents->updated_at = Carbon::now();

            if (isset($req->status)) {                  // In Case of Deactivation or Activation 
                $status = $req->status == 'deactive' ? 0 : 1;
                $this->_mStudents->status = $status;
            }
            $data = $this->_mStudents;
            $this->_mStudents->update();
            // $feeHead = $this->_mStudents::findOrFail($req->id);
            // $feeHead->update($metaReqs);
            return responseMsgs(true, "Successfully Updated", [$data], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Get Student Group By Id
     */
    public function show(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $std = $this->_mStudents->getGroupById($req->id);
            return responseMsgs(true, "", $std, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Retrieve All
     */
    public function retrieveAll(Request $req)
    {
        try {
            $std = $this->_mStudents->retrieveAll();
            return responseMsgs(true, "", $std, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
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
            $std = $this->_mStudents::findOrFail($req->id);
            if ($std->status == 0)
                throw new Exception("Records Already Deleted");
            $std->update($metaReqs);
            return responseMsgs(true, "Deleted Successfully", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function searchStdByAdmNo(Request $req)
    {
        //Description: Get records by id
        try {
            $data = array();
            $validator = Validator::make($req->all(), [
                'admissionNo' => 'required|string'
            ]);
            if ($validator->fails()) {
                $errors = $validator->errors();
                return response()->json([
                    'error' => $errors
                ], 400);
            }
            $mDeviceId = $req->deviceId ?? "";
            if ($validator->passes()) {
                $searchAdmNo = new Student();
                $data1  = $searchAdmNo->searchAdmNo($req);
                $msg = $data1['message'];
                $data = $data1;
                // $data = $data1['emp_id'];
                // $mDeviceId = $req->deviceId ?? "";
                return responseMsgs(true, $msg, $data, "API_ID_132", "", "146ms", "post", $mDeviceId);
            }
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_132", "", "", "post", $mDeviceId);
        }
    }

    public function viewStudent(Request $req)
    {
        //Description: Get all records 
        try {
            $data = Student::list();
            $mDeviceId = $req->deviceId ?? "";
            return responseMsgs(true, "View all records", $data, "API_ID_131", "", "186ms", "get", $mDeviceId);
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), $data, "API_ID_131", "", "", "get", $mDeviceId);
        }
    }

    // Get Student Id Details...........
    public function getIdCard(Request $req)
    {

        $validator = Validator::make($req->all(), [
            'admissionNo' => 'required|string',
            'classId' => 'required|numeric',
            'sectionId' => 'required|numeric'
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
            $getAll = $this->_mStudents->getStudentIdDetails($req, $studentId);
            if (collect($getAll)->isEmpty())
                throw new Exception("Data Not Found");
            return responseMsgs(true, "", $getAll, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
