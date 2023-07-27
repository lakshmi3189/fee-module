<?php

namespace App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;                     // using laravel methods 
use Illuminate\Support\Facades\Hash;            // hash password generate
use Haruncpi\LaravelIdGenerator\IdGenerator;    //id generator via laravel package
use Illuminate\Support\Facades\URL;             // getting url
use Illuminate\Support\Carbon;                  // for current timestamp
use Exception;                                  // exception handling
use DB;                                         // using db

use App\Models\Employee\EmployeeEducation;      // model
use App\Models\Employee\EmployeeExperience;     // model
use App\Models\Employee\EmployeeFamily;         // model
use App\Models\Admin\User;                      // model
// use App\Http\Traits\CustomTraits;

/*=================================================== Employee =========================================================
Created By : Lakshmi kumari 
Created On : 20-Apr-2023 
Code Status : Open 
*/

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
      'emp_no',                  
      'salutation_id',          
      'salutation_name',              
      'first_name',             
      'middle_name',             
      'last_name',                           
      'email',                  
      'mobile',                  
      'dob',                     
      'doj',                    
      'aadhar_no',
      'disability',              
      'gender_id',               
      'gender_name',                       
      'category_id',               
      'category_name',              
      'blood_group_id',         
      'blood_group_name',       
      'department_id',          
      'department_name',         
      'employee_type_id',        
      'employee_type_name',      
      'teaching_title_id',        
      'teaching_title_name',
      'marital_status_id',      
      'marital_status_name',          
      'upload_image',           
      'p_address1',              
      'p_address2',              
      'p_locality',              
      'p_landmark',              
      'p_country_name',               
      'p_state_name',                 
      'p_district_name',              
      'p_pincode' ,              
      'c_address1',              
      'c_address2',              
      'c_locality',              
      'c_landmark',              
      'c_country_name',              
      'c_state_name',             
      'c_district_name',              
      'c_pincode',              
      'fathers_name',            
      'fathers_qualification_name',  
      'fathers_occupation_name',     
      'fathers_annual_income',  
      'mothers_name',           
      'mothers_qualification_name',   
      'mothers_occupation_name',     
      'mothers_annual_income',
      'bank_name',               
      'account_no' ,             
      'account_type',            
      'ifsc_code',              
      'branch_name',             
      'nominee_name',            
      'nominee_relation_name',        
      'pan_no',                 
      'epf_no',                 
      'uan_no',                 
      'esi_no',                 
      'nps_no'         
    ];   

    //insert employees all details
    public function insertData($req) { 
      // DB::beginTransaction();
      //start-code-version : 1----------------------------------------------------      
      //id gene code start
      // $empIdGene = UniqueIdGenerator::generate([
      //   'table' => 'employees', 
      //   'field' => 'emp_no', 
      //   'length' => 4
      // ]); 

      // $id = UniqueIdGenerator::generate(['table' => 'invoices', 'length' => 10, 'prefix' =>'INV-']);

      // $CustomTraits = new CustomTraits;
      // $ipAddress = $CustomTraits->getIp();
      // $baseUrl = $CustomTraits->baseUrl();  
      //end-code-version : 1----------------------------------------------------------

      //start-code-version : 2------------------------------------------------------
      $empIdGene = IdGenerator::generate([
        'table' => 'employees', 
        'field' => 'emp_no', 
        'length' => 8, 
        'prefix' => date('Y'),
        // 'prefix' => date('Y').'/',
        'reset_on_prefix_change' => true
      ]);
      // die;
      //id gen end
      
      $ip = getClientIpAddress();
      // $emp_no = $empIdGene; //using laravel idGenerator
      $emp_no = $req->emp_no; //getting emp no from request for existing emp
      // echo $emp_no; die;
      $user_created_by = 'Admin'; //user id or role from users table
      $school_id = '123'; //need to use idGenerator
      // $userId = authUser()->id; 
      $file_name = '';
      $userType = 'Employee'; //to identify users type like:std,emp,etc from users table

      $mObject = new Employee(); 
         
      $baseUrl = baseURL(); //called it from helper file - getting server ip
      $localBaseURL = "http://127.0.0.1:8000"; //using for localhost / local ip
      $upload_image = "";
      $result = array();

      $empEducation = $empExperience = $empFamily = "";
      // $baseUrl1 = config('app.url')
      // $path = $baseUrl.'/school/employees/'.$emp_no;
      
      if($req->upload_image!=""){
        $upload_image = $req->upload_image;
        $file_name = $upload_image->getClientOriginalName();
        $path = public_path('school/employees/'.$emp_no);
        // $path = $baseUrl.'/school/employees/'.$emp_no;
        $move = $req->file('upload_image')->move($path,$file_name);         
      } 
      $mObject->emp_no = $emp_no;
      $mObject->salutation_id = $req['salutation_id'];
      $mObject->salutation_name = $req['salutation_name'];
      $mObject->first_name = $req['first_name'];
      $mObject->middle_name = $req['middle_name'];
      $mObject->last_name = $req['last_name'];
      $mObject->email = $req['email'];
      $mObject->mobile = $req['mobile'];
      $mObject->dob = $req['dob'];
      $mObject->doj = $req['doj'];
      $mObject->aadhar_no = $req['aadhar_no'];
      $mObject->disability = $req['disability'];
      $mObject->gender_id = $req['gender_id'];
      $mObject->gender_name = $req['gender_name'];
      $mObject->category_id = $req['category_id'];
      $mObject->category_name = $req['category_name'];
      $mObject->blood_group_id = $req['blood_group_id'];
      $mObject->blood_group_name = $req['blood_group_name'];
      $mObject->department_id = $req['department_id'];
      $mObject->department_name = $req['department_name'];
      $mObject->employee_type_id = $req['employee_type_id'];
      $mObject->employee_type_name = $req['employee_type_name'];
      $mObject->teaching_title_id = $req['teaching_title_id'];
      $mObject->teaching_title_name = $req['teaching_title_name'];
      $mObject->marital_status_id = $req['marital_status_id'];
      $mObject->marital_status_name = $req['marital_status_name'];
      $mObject->upload_image = $file_name;
      $mObject->p_address1 = $req['p_address1'];
      $mObject->p_address2 = $req['p_address2'];
      $mObject->p_locality = $req['p_locality'];
      $mObject->p_landmark = $req['p_landmark'];
      $mObject->p_country_id = $req['p_country_id'];
      $mObject->p_country_name = $req['p_country_name'];
      $mObject->p_state_id = $req['p_state_id'];
      $mObject->p_state_name = $req['p_state_name'];
      $mObject->p_district_id = $req['p_district_id'];
      $mObject->p_district_name = $req['p_district_name'];
      $mObject->p_pincode = $req['p_pincode'];
      $mObject->c_address1 = $req['c_address1'];
      $mObject->c_address2 = $req['c_address2'];
      $mObject->c_locality = $req['c_locality'];
      $mObject->c_landmark = $req['c_landmark'];
      $mObject->c_country_id = $req['c_country_id'];
      $mObject->c_country_name = $req['c_country_name'];
      $mObject->c_state_id = $req['c_state_id'];
      $mObject->c_state_name = $req['c_state_name'];
      $mObject->c_district_id = $req['c_district_id'];
      $mObject->c_district_name = $req['c_district_name'];
      $mObject->c_pincode = $req['c_pincode'];
      $mObject->fathers_name = $req['fathers_name'];
      $mObject->fathers_qualification_id = $req['fathers_qualification_id'];
      $mObject->fathers_qualification_name = $req['fathers_qualification_name'];
      $mObject->fathers_occupation_id = $req['fathers_occupation_id'];
      $mObject->fathers_occupation_name = $req['fathers_occupation_name'];
      // $mObject->fathers_annual_income = $req['fathers_annual_income'];
      $mObject->mothers_name = $req['mothers_name'];
      $mObject->mothers_qualification_id = $req['mothers_qualification_id'];
      $mObject->mothers_qualification_name = $req['mothers_qualification_name'];
      $mObject->mothers_occupation_id = $req['mothers_occupation_id'];
      $mObject->mothers_occupation_name = $req['mothers_occupation_name'];
      // $mObject->mothers_annual_income = $req['mothers_annual_income'];          
      $mObject->bank_id = $req['bank_id'];
      $mObject->bank_name = $req['bank_name'];
      $mObject->account_no = $req['account_no'];
      $mObject->account_type = $req['account_type'];
      $mObject->ifsc_code = $req['ifsc_code'];
      $mObject->branch_name = $req['branch_name'];
      $mObject->nominee_name = $req['nominee_name'];
      $mObject->nominee_relation_id = $req['nominee_relation_id'];
      $mObject->nominee_relation_name = $req['nominee_relation_name'];
      $mObject->pan_no = $req['pan_no'];
      $mObject->epf_no = $req['epf_no'];
      $mObject->uan_no = $req['uan_no'];
      $mObject->esi_no = $req['esi_no'];
      $mObject->nps_no = $req['nps_no'];          
      $mObject->created_by = $user_created_by;
      $mObject->ip_address = $ip;
      $mObject->school_id = $school_id;
      // print_r($mObject); die;
      $mObject->save(); 
      
      //add user
      $pass = Str::random(10);                
      $mObjectU = new User();
      $insert = [
        $mObjectU->name        = $req['first_name'],
        $mObjectU->email       = $req['email'],          
        $mObjectU->password    = Hash::make($pass),
        $mObjectU->c_password  = $pass,
        $mObjectU->school_id   = $school_id,
        $mObjectU->user_id     = $emp_no,
        $mObjectU->user_type   = $userType,
        $mObjectU->ip_address  = $ip
      ];
      // print_r($insert);die;
      $mObjectU->save($insert);
      $userData = array();
      $userData = $mObjectU->$pass; 
      
      //insert single data and multi data for employee education 
      if($req['education_details']!=""){
        // echo $examPassed = $req['education_details']['exam_passed_id']; die;
        // for($i=0; $i < count(); $i++){
        // }
        foreach ($req['education_details'] as $ob) {
          $empEducation = new EmployeeEducation;
            // $upload_edu_doc = "";
            // $edu_file_name = ""; 
            // if($req->upload_edu_doc!=""){
            //   $upload_edu_doc = $req->upload_edu_doc;
            //   $edu_file_name = $upload_edu_doc->getClientOriginalName();
            //   $path = public_path('school/employees/'.$emp_no);
            //   // $path = $localBaseURL.'/school/employees/'.$emp_no;
            //   // $path = $baseUrl.'/school/employees/'.$emp_no;
            //   $move = $req->file('upload_edu_doc')->move($path,$edu_file_name);         
            // } 
              
            $empEducation->emp_tbl_id = $mObject->id;
            $empEducation->exam_passed_id = $ob['exam_passed_id'];
            $empEducation->exam_passed_name = $ob['exam_passed_name'];
            $empEducation->board_uni_inst = $ob['board_uni_inst'];
            $empEducation->passing_year = $ob['passing_year'];
            $empEducation->div_grade_id = $ob['div_grade_id'];
            $empEducation->div_grade_name = $ob['div_grade_name'];
            $empEducation->marks_obtained = $ob['marks_obtained'];
            $empEducation->total_marks = $ob['total_marks'];
            $empEducation->percentage = $ob['percentage'];
            $empEducation->upload_edu_doc = "";
            // $empEducation->upload_edu_doc = $edu_file_name;
            $empEducation->save();          
        }
      } 
      
      //insert single data and multi data for employee experience 
      if($req['experience_details']!=""){
        // echo $baseUrl; die;
        foreach ($req['experience_details'] as $ob) {
            $empExperience = new EmployeeExperience;  
          // print_r($ob);
            // $upload_exp_letter = "";
            // $exp_file_name  = "";
                   
            // if($ob->upload_exp_letter!=""){
            //   $upload_exp_letter = $ob->upload_exp_letter;
            //   $exp_file_name = $upload_exp_letter->getClientOriginalName();
            //   $path = public_path('school/employees/'.$emp_no);
            //   // $path = $baseUrl.'/school/employees/'.$emp_no;
            //   // $path = $localBaseURL.'/school/employees/'.$emp_no;
            //   $move = $ob->file('upload_exp_letter')->move($path,$exp_file_name);
            // }   

            $empExperience->emp_tbl_id = $mObject->id;
            $empExperience->name_of_org = $ob['name_of_org'];
            $empExperience->position_head = $ob['position_head'];
            $empExperience->period_from = $ob['period_from'];
            $empExperience->period_to = $ob['period_to'];
            $empExperience->salary = $ob['salary'];
            $empExperience->pay_grade = $ob['pay_grade'];
            $empExperience->upload_exp_letter = "";
            // $empExperience->upload_exp_letter = $exp_file_name;
            $empExperience->save();          
        }
      } 

      //insert single data and multi data for employee experience 
      if($req['family_details']!=""){
        foreach ($req['family_details'] as $ob) {
            $empFamily = new EmployeeFamily;
            // $upload_f_member_image = "";
            // $family_file_name = "";
             // if($ob->upload_f_member_image!=""){
            //   $upload_f_member_image = $ob->upload_f_member_image;
            //   $family_file_name = $upload_f_member_image->getClientOriginalName();
            //   $path = public_path('school/employees/'.$emp_no);
            //   // $path = $baseUrl.'/school/employees/'.$emp_no;
            //   // $path = $localBaseURL.'/school/employees/'.$emp_no;
            //   $move = $ob->file('upload_f_member_image')->move($path,$family_file_name);
            // } 
            $empFamily->emp_tbl_id = $mObject->id;
            $empFamily->f_member_name = $ob['f_member_name'];
            $empFamily->f_member_relation_id = $ob['f_member_relation_id'];
            $empFamily->f_member_relation_name = $ob['f_member_relation_name'];
            $empFamily->f_member_dob = $ob['f_member_dob'];
            $empFamily->upload_f_member_image = "";
            // $empFamily->upload_f_member_image = $family_file_name;
            $empFamily->save();          
        }
      }      
      
      // $addUser = $this->addUser($req);
      // print_r($addUser); die;
      
      $result['basic_details'] = $mObject;
      $result['education_details'] = $empEducation;
      $result['experience_details'] = $empExperience;
      $result['family_details'] = $empFamily;
      $resul['password'] = $userData;
      return $result;
      // return $mObject;
      //end-code-version:2---------------------------------------------------------------
    }

    //Description: created a new function and called it in just above function to store some records in auth table
    public function addUser($req){
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
        $mObjectU->ip_address  = $ip
      ];
      $mObjectU->save($insert);  
    }

    //view all 
    public static function list() { 
      //select all employees data     
      $viewAll = Employee::select( 
      'id','emp_no','salutation_id','salutation_name','first_name','middle_name','last_name',
      DB::raw("CONCAT_WS(first_name,' ',middle_name,' ',last_name) as full_name,
      (CASE 
      WHEN is_deleted = '0' THEN 'Active' 
      WHEN is_deleted = '1' THEN 'Not Active'
      END) AS status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time"),
      'email','mobile','dob','doj','aadhar_no','disability',
      'gender_id','gender_name','category_id','category_name',            
      'blood_group_id','blood_group_name','department_id','department_name',
      'employee_type_id','employee_type_name','teaching_title_id','teaching_title_name',
      'marital_status_id','marital_status_name','upload_image',
      'p_address1','p_address2','p_locality','p_landmark','p_country_id','p_country_name',
      'p_state_id','p_state_name','p_district_id','p_district_name','p_pincode',            
      'c_address1','c_address2','c_locality','c_landmark','c_country_id','c_country_name',
      'c_state_id','c_state_name','c_district_id','c_district_name','c_pincode',
      'fathers_name','fathers_qualification_id','fathers_qualification_name',
      'fathers_occupation_id','fathers_occupation_name','fathers_annual_income',
      'mothers_name','mothers_qualification_id','mothers_qualification_name',
      'mothers_occupation_id','mothers_occupation_name','mothers_annual_income',
      'bank_id','bank_name','account_no','account_type','ifsc_code','branch_name','nominee_name',
      'nominee_relation_id','nominee_relation_name','pan_no','epf_no','uan_no','esi_no','nps_no',
      'emp_no','school_id'
      )
      ->where('is_deleted',0)
      ->orderBy('emp_no','asc')
      ->get();
      // print_r($viewAll); die; 

      $baseUrl = baseURL(); //called it from helper file
      $getAllData = array();
      foreach($viewAll as $v){
        $path = '';
        $emp_no = $v->emp_no;
        $fileUrl = $baseUrl.'/school/employees/'.$emp_no.'/' ;
        $filePath = $fileUrl.$v->upload_image;
        $defaultPath = $baseUrl.'/global-img/default-user-img.png';
        if($v->upload_image==""){ $path =  $defaultPath; }
        if($v->upload_image!=""){ $path =  $filePath; }
        $dataArr = array();
        $dataArr['id'] = $v->id;
        $dataArr['emp_no'] = $emp_no;
        // $dataArr['full_name'] = $v->full_name;
        $dataArr['first_name'] = $v->first_name;
        $dataArr['middle_name'] = $v->middle_name;
        $dataArr['last_name'] = $v->last_name;
        $dataArr['full_name'] = $v['first_name'].' '.$v['middle_name'].' '.$v['last_name'];
        $dataArr['gender_name'] = $v->gender_name;
        $dataArr['category_name'] = $v->category_name;
        $dataArr['dob'] = $v->dob;
        $dataArr['doj'] = $v->doj;
        $dataArr['mobile'] = $v->mobile;
        $dataArr['email'] = $v->email;
        $dataArr['blood_group_name'] = $v->blood_group_name;
        $dataArr['department_name'] = $v->department_name;
        $dataArr['upload_images'] = $path;
        $dataArr['p_address1'] = $v->p_address1;
        $dataArr['p_address2'] = $v->p_address2;
        $dataArr['p_locality'] = $v->p_locality;
        $dataArr['p_landmark'] = $v->p_landmark;
        $dataArr['p_country_name'] = $v->p_country_name;
        $dataArr['p_state_name'] = $v->p_state_name;
        $dataArr['p_district_name'] = $v->p_district_name;
        $dataArr['p_pincode'] = $v->p_pincode;
        $dataArr['c_address1'] = $v->c_address1;
        $dataArr['c_address2'] = $v->c_address2;
        $dataArr['c_locality'] = $v->c_locality;
        $dataArr['c_landmark'] = $v->c_landmark;
        $dataArr['c_country_name'] = $v->c_country_name;
        $dataArr['c_state_name'] = $v->c_state_name;
        $dataArr['c_district_name'] = $v->c_district_name;
        $dataArr['c_pincode'] = $v->c_pincode;
        $dataArr['fathers_name'] = $v->fathers_name;
        $dataArr['fathers_qualification_name'] = $v->fathers_qualification_name;
        $dataArr['fathers_occupation_name'] = $v->fathers_occupation_name;
        $dataArr['fathers_annual_income'] = $v->fathers_annual_income;
        $dataArr['mothers_name'] = $v->mothers_name;
        $dataArr['mothers_occupation_name'] = $v->mothers_occupation_name;
        $dataArr['mothers_annual_income'] = $v->mothers_annual_income;
        $dataArr['bank_name'] = $v->bank_name;
        $dataArr['account_no'] = $v->account_no;
        $dataArr['account_type'] = $v->account_type;
        $dataArr['ifsc_code'] = $v->ifsc_code;
        $dataArr['branch_name'] = $v->branch_name;
        $dataArr['nominee_name'] = $v->nominee_name;
        $dataArr['nominee_relation_name'] = $v->nominee_relation_name;
        $dataArr['pan_no'] = $v->pan_no;
        $dataArr['epf_no'] = $v->epf_no;
        $dataArr['uan_no'] = $v->uan_no;
        $dataArr['esi_no'] = $v->esi_no;
        $dataArr['nps_no'] = $v->nps_no;
        $dataArr['status'] = $v->status;
        $dataArr['date'] = $v->date;
        $dataArr['time'] = $v->time;
        $getAllData[]=$dataArr;
      } 
      // print_r($getAllData); die;     
      return $getAllData;
    }
  
    //view by id
    public function listById($req) { 
      $data = array();
      $id =  $req->id;
      $empBasicDetails = Employee::select(
        'id','emp_no','salutation_id','salutation_name',
        'first_name','middle_name','last_name',                 
        'email','mobile','dob','doj','aadhar_no','disability',
        'gender_id','gender_name','category_id','category_name',            
        'blood_group_id','blood_group_name','department_id','department_name',
        'employee_type_id','employee_type_name','teaching_title_id','teaching_title_name',
        'marital_status_id','marital_status_name','upload_image',
        'p_address1','p_address2','p_locality','p_landmark',
        'p_country_id','p_country_name','p_state_id','p_state_name',
        'p_district_id','p_district_name','p_pincode',            
        'c_address1','c_address2','c_locality','c_landmark',
        'c_country_id','c_country_name','c_state_id','c_state_name',
        'c_district_id','c_district_name','c_pincode','fathers_name',
        'fathers_qualification_id','fathers_qualification_name',
        'fathers_occupation_id','fathers_occupation_name',
        'fathers_annual_income','mothers_name',
        'mothers_qualification_id','mothers_qualification_name',
        'mothers_occupation_id','mothers_occupation_name',
        'mothers_annual_income','bank_id','bank_name','account_no','account_type',
        'ifsc_code','branch_name','nominee_name',
        'nominee_relation_id','nominee_relation_name',
        'pan_no','epf_no','uan_no','esi_no','nps_no',
        'created_by','school_id',
        DB::raw("(CASE 
        WHEN is_deleted = '0' THEN 'Active' 
        WHEN is_deleted = '1' THEN 'Not Active'
        END) AS status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time")            
      )
      ->where(['id'=>$id],['is_deleted'=>0])
      // ->where('id', $req->id)
      ->first();
      // print_r($empBasicDetails);die;
      $baseUrl = baseURL(); //called it from helper file      
      $emp_no = $empBasicDetails->emp_no;
      // $id = $empBasicDetails->id;
      $path = $baseUrl.'/school/employees/'.$emp_no.'/';
      $imgPath = $path.$empBasicDetails->upload_image;
      $empBasicDetails['upload_image'] = $imgPath;
      // print_r($empBasicDetails);
      $empExp = EmployeeExperience::select(
        'id','emp_tbl_id',
        'name_of_org','position_head',
        'period_from','period_to',
        'salary','pay_grade','upload_exp_letter',
        DB::raw("(CASE 
        WHEN is_deleted = '0' THEN 'Active' 
        WHEN is_deleted = '1' THEN 'Not Active'
        END) AS status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time")                        
      )
      ->where(['emp_tbl_id'=>$id],['is_deleted'=>0])
      // ->where('emp_tbl_id', $req->id)
      // ->where('emp_tbl_id', $id)
      ->get();

      $empFamily = EmployeeFamily::select(
        'id','emp_tbl_id',            
        'f_member_name',
        'f_member_relation_id','f_member_relation_name',
        'f_member_dob','upload_f_member_image',
        DB::raw("(CASE 
        WHEN is_deleted = '0' THEN 'Active' 
        WHEN is_deleted = '1' THEN 'Not Active'
        END) AS status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time")                      
      )
      ->where(['emp_tbl_id'=>$id],['is_deleted'=>0])
      // ->where('emp_tbl_id', $req->id)
      // ->where('emp_tbl_id', $id)
      ->get();

      $empEdu = EmployeeEducation::select(
        'id','emp_tbl_id',
        'exam_passed_id','exam_passed_name',
        'board_uni_inst','passing_year',
        'div_grade_id','div_grade_name',
        'marks_obtained','total_marks',
        'percentage','upload_edu_doc',
        DB::raw("(CASE 
        WHEN is_deleted = '0' THEN 'Active' 
        WHEN is_deleted = '1' THEN 'Not Active'
        END) AS status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time")                       
      )
      ->where(['emp_tbl_id'=>$id],['is_deleted'=>0])
      // ->where('emp_tbl_id', $id)
      // ->where('emp_tbl_id', $req->id)
      ->get();

      $data['basic_details']=$empBasicDetails;
      $data['education_details']=$empEdu;
      $data['experience_details']=$empExp;
      $data['family_details']=$empFamily;
      // $data['message'] = $msg;
      // if($empBasicDetails==""){
      //   $msg = "Employee no. not found";
      // }
      
      return $data;  
    }  

    //Search emp by using emp id
    public function searchEmpId($req) { 
      $checkExist = Employee::where([['emp_no','=',$req->emp_no],['is_deleted','=','0']])->count();
      $data = array(); 
      if($checkExist > 0){
        $data =  ['emp_no' => $req->emp_no, 'message'=>'Employee No. already existing','value' =>'true'];
      }
      if($checkExist == 0){
        $data = ['emp_no' => $req->emp_no,'message'=>'Employee No. not found','value' =>'false'];
      }
      return $data;
    } 

    //update
    public function updateData($req) {
      // $data = Employee::find($req->id);
      $id = $req->id;
      $version_no = "1";
      $result = array();
      $mObjectBasic = Employee::where(['id'=>$id],['is_deleted'=>0]);

      if (!$mObjectBasic)
          throw new Exception("Records Not Found!");                 
      $editBasic = [
        'salutation_id' => $req->salutation_id,
        'salutation_name' => $req->salutation_name,
        'first_name' => $req->first_name,
        'middle_name' => $req->middle_name,
        'last_name' => $req->last_name,
        'email' => $req->email,
        'mobile' => $req->mobile,
        'dob' => $req->dob,
        'doj' => $req->doj,
        'aadhar_no' => $req->aadhar_no,
        'disability' => $req->disability,
        'gender_id' => $req->gender_id,
        'gender_name' => $req->gender_name,
        'category_id' => $req->category_id,
        'category_name' => $req->category_name,
        'blood_group_id' => $req->blood_group_id,
        'blood_group_name' => $req->blood_group_name,
        'department_id' => $req->department_id,
        'department_name' => $req->department_name,
        'employee_type_id' => $req->employee_type_id,
        'employee_type_name' => $req->employee_type_name,
        'teaching_title_id' => $req->teaching_title_id,
        'teaching_title_name' => $req->teaching_title_name,
        'marital_status_id' => $req->marital_status_id,
        'marital_status_name' => $req->marital_status_name,
        'upload_image' => '',
        'p_address1' => $req->p_address1,
        'p_address2' => $req->p_address2,
        'p_locality' => $req->p_locality,
        'p_landmark' => $req->p_landmark,
        'p_country_id' => $req->p_country_id,
        'p_country_name' => $req->p_country_name,
        'p_state_id' => $req->p_state_id,
        'p_state_name' => $req->p_state_name,
        'p_district_id' => $req->p_district_id,
        'p_district_name' => $req->p_district_name,
        'p_pincode' => $req->p_pincode,
        'c_address1' => $req->c_address1,
        'c_address2' => $req->c_address2,
        'c_locality' => $req->c_locality,
        'c_landmark' => $req->c_landmark,
        'c_country_id' => $req->c_country_id,
        'c_country_name' => $req->c_country_name,
        'c_state_id' => $req->c_state_id,
        'c_state_name' => $req->c_state_name,
        'c_district_id' => $req->c_district_id,
        'c_district_name' => $req->c_district_name,
        'c_pincode' => $req->c_pincode,
        'fathers_name' => $req->fathers_name,
        'fathers_qualification_id' => $req->fathers_qualification_id,
        'fathers_qualification_name' => $req->fathers_qualification_name,
        'fathers_occupation_id' => $req->fathers_occupation_id,
        'fathers_occupation_name' => $req->fathers_occupation_name,
        'fathers_annual_income' => $req->fathers_annual_income,
        'mothers_name' => $req->mothers_name,
        'mothers_qualification_id' => $req->mothers_qualification_id,
        'mothers_occupation_id' => $req->mothers_occupation_id,
        'mothers_occupation_name' => $req->mothers_occupation_name,
        'mothers_annual_income' => $req->mothers_annual_income,
        'bank_id' => $req->bank_id,
        'bank_name' => $req->bank_name,
        'account_no' => $req->account_no,
        'account_type' => $req->account_type,
        'ifsc_code' => $req->ifsc_code,
        'branch_name' => $req->branch_name,
        'nominee_name' => $req->nominee_name,
        'nominee_relation_id' => $req->nominee_relation_id,
        'nominee_relation_name' => $req->nominee_relation_name,
        'pan_no' => $req->pan_no,
        'epf_no' => $req->epf_no,
        'uan_no' => $req->uan_no,
        'esi_no' => $req->esi_no,
        'nps_no' => $req->nps_no,
        'updated_at' => Carbon::now(),
        'version_no' => $version_no
      ];
      // print_r($mObjectBasic); die;
      $mObjectBasic->update($editBasic);
      // $editUser = $this->editUser($req);

      $user = User::where('email', $req->email)->first();
      $editUser = [
        'email' => $req->email,
        'name' => $req->first_name.' '.$req->middle_name.' '.$req->last_name,
        'updated_at' => Carbon::now()
      ];
      $user->update($editUser);
      // return $mObjectBasic;
 
      //insert data for employee education 
      $education_details = $req['education_details'];          
      if($education_details!=""){ 
        $mObjectEdu = EmployeeEducation::where(['emp_tbl_id'=>$id],['is_deleted'=>0]);  
        $mObjectEdu->delete();
        foreach ($education_details as $ob) {
          $empEducation = new EmployeeEducation;              
          $empEducation->emp_tbl_id = $id;
          $empEducation->exam_passed_id = $ob['exam_passed_id'];
          $empEducation->exam_passed_name = $ob['exam_passed_name'];
          $empEducation->board_uni_inst = $ob['board_uni_inst'];
          $empEducation->passing_year = $ob['passing_year'];
          $empEducation->div_grade_id = $ob['div_grade_id'];
          $empEducation->div_grade_name = $ob['div_grade_name'];
          $empEducation->marks_obtained = $ob['marks_obtained'];
          $empEducation->total_marks = $ob['total_marks'];
          $empEducation->percentage = $ob['percentage'];
          $empEducation->upload_edu_doc = "";
          $empEducation->save();          
        }
      }
      
      //insert data for employee experience
      $experience_details = $req['experience_details'];
      if($experience_details!=""){
        $mObjectExp = EmployeeExperience::where(['emp_tbl_id'=>$id],['is_deleted'=>0]);
        $mObjectExp->delete(); 
        foreach ($experience_details as $ob) {
          $empExperience = new EmployeeExperience;
          $empExperience->emp_tbl_id = $id;
          $empExperience->name_of_org = $ob['name_of_org'];
          $empExperience->position_head = $ob['position_head'];
          $empExperience->period_from = $ob['period_from'];
          $empExperience->period_to = $ob['period_to'];
          $empExperience->salary = $ob['salary'];
          $empExperience->pay_grade = $ob['pay_grade'];
          $empExperience->upload_exp_letter = "";
          $empExperience->save();          
        }
      }
      
      //insert data for employees families 
      $family_details = $req['family_details'];      
       if($family_details!=""){
        $mObjectFam = EmployeeFamily::where(['emp_tbl_id'=>$id],['is_deleted'=>0]);
        $mObjectFam->delete();
        foreach ($family_details as $ob) {
          $empFamily = new EmployeeFamily;            
          $empFamily->emp_tbl_id = $id;
          $empFamily->f_member_name = $ob['f_member_name'];
          $empFamily->f_member_relation_id = $ob['f_member_relation_id'];
          $empFamily->f_member_relation_name = $ob['f_member_relation_name'];
          $empFamily->f_member_dob = $ob['f_member_dob'];
          $empFamily->upload_f_member_image = "";
          $empFamily->save();          
        }
      }
      
      $result['basic_details'] = $mObjectBasic;
      $result['education_details'] = $empEducation;
      $result['experience_details'] = $empExperience;
      $result['family_details'] = $empFamily;
      return $result;
    }

    //edit users details
    // public function editUser($req){
    //   $data = User::select('id','name','email')
    //   ->find($req->id);
    //   $email = $req->email;
    //   $name = $req->first_name.' '.$req->middle_name.' '.$req->last_name;

    //   $data = User::where('email', $email)->get();
    //   if (!$data)
    //           throw new Exception("Employees email id not found!");
    //   $edit = [
    //       'email' => $email,
    //       'name' => $name,
    //       'updated_at' => Carbon::now()
    //   ];
    //   $data->update($edit);
    //   return $data; 
    // }
  
    //delete 
    public function deleteData($req) {
      $data = array();
      $id = $req->id;
      $data = Employee::find($id);
      if (!$data)
            throw new Exception("Records Not Found!");
      $data->is_deleted = "1";
      $data->save();
      
      $getEmpId = Employee::select('id')->where(['id'=>$id],['is_deleted'=>0])->first();
      $getEmpTblId = $getEmpId->id;
      
      $dataEdu = EmployeeEducation::where('emp_tbl_id', $getEmpTblId)->get();
      $dataExp = EmployeeExperience::where('emp_tbl_id', $getEmpTblId)->get();
      $dataFam = EmployeeFamily::where('emp_tbl_id', $getEmpTblId)->get();

      foreach ($dataEdu as $d) { 
        $d->is_deleted = "1";
        $d->save();
      }
      foreach ($dataExp as $d) { 
        $d->is_deleted = "1";
        $d->save();
      }
      foreach ($dataFam as $d) { 
        $d->is_deleted = "1";
        $d->save();
      }      
      return $data; 
    }

    //truncate
    public function truncateData() {
      // $data = array();
      // $data = Employee::truncate();
      // $data = EmployeeEducation::truncate();
      // $data = EmployeeExperience::truncate();
      // $data = EmployeeFamily::truncate();
      // return $data;    
    } 

}
