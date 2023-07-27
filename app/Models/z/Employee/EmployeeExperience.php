<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class EmployeeExperience extends Model
{
    use HasFactory;
    protected $guarded = [];

    //insert
  public function insertData($req)
  {
    // $userId = authUser()->id; 
    $mObject = new EmployeeExperience();
    $insert = [
        $mObject->emp_tbl_id = $req['emp_tbl_id'],     
        $mObject->name_of_org = $req['name_of_org'],
        $mObject->position_head = $req['position_head'],
        $mObject->period_from = $req['period_from'],
        $mObject->period_to = $req['period_to'],
        $mObject->salary = $req['salary'],
        $mObject->pay_grade = $req['pay_grade'],          
        $mObject->upload_exp_letter = $req['upload_exp_letter'],          
        $mObject->academic_year = $req['academic_year'],          
        $mObject->school_id = $req['school_id'],          
        $mObject->created_by = $req['created_by'],          
        $mObject->ip_address = $req['ip_address']
    ];
    $mObject->save($insert);
    return $mObject;
  }






    /* old code*/
    // protected $table = 'employee_experiences';
    // protected $fillable = [
    //   'name_of_org',            
    //   'position_head',          
    //   'period_from',             
    //   'period_to',               
    //   'salary', 
    //   'pay_grade',                    
    //   'upload_exp_letter' 
    // ];

    // //insert
    // public function insertData($req) { 
    //     $emp_id = '001';
    //     $user_created_by = 'admin';
    //     $school_id = '234';
    //     // $userId = authUser()->id; 
    //     $mObject = new EmployeeExperience();        
    //     $insert = [
    //       $mObject->emp_tbl_id = $emp_tbl_id,
    //       $mObject->emp_id = $emp_id, 
    //       $mObject->school_id = $school_id,          
    //       $mObject->name_of_org = $req['name_of_org'],
    //       $mObject->position_head = $req['position_head'],
    //       $mObject->period_from = $req['period_from'],
    //       $mObject->period_to = $req['period_to'],
    //       $mObject->salary_paygrade = $req['salary_paygrade'],
    //       $mObject->upload_exp_letter_docs = $req['upload_exp_letter_docs'],          
    //       $mObject->user_created_by = $user_created_by
    //     ];
    //     $mObject->save($insert);
    //     return $mObject;
    //   }

    //   //using id generator
    //   // public function empId(){
    //   //   $empData = new Employee();
    //   //   $emp_id['empId'] = $empData->emp_id;
    //   //   $id = IdGenerator::generate([
    //   //     'table' => 'class_tables',
    //   //     'field' => 'student_id',
    //   //     'length' => 11,
    //   //     'prefix' => $req->class.'/'.date('y').'/',
    //   //     'reset_on_prefix_change' => true,
    //   //   ]);        
    //   // }
      
    //   //view all 
    //   public static function list() {
    //     $viewAll = EmployeeExperience::select('id','emp_id','name_of_org','position_head','period_from','period_to')
    //     ->orderBy('id','desc')->get();    
    //     return $viewAll;
    //   }
  
    //   //view by id
    //   public function listById($req) {
    //     $data = EmployeeExperience::where('id', $req->id)
    //           ->first();
    //       return $data;     
    //   }   
  
    //   //update
    //   public function updateData($req) {
    //     $data = EmployeeExperience::find($req->id);
    //     if (!$data)
    //           throw new Exception("Record Not Found!");
    //     $edit = [
    //       'name_of_org' => $req->name_of_org,
    //       'position_head' => $req->position_head,
    //       'period_from' => $req->period_from,
    //       'period_to' => $req->period_to
    //     ];
    //     $data->update($edit);
    //     return $data;        
    //   }
  
    //   //delete 
    //   public function deleteData($req) {
    //     $data = EmployeeExperience::find($req->id);
    //     $data->is_deleted = "1";
    //     $data->save();
    //     return $data; 
    //   }
  
    //   //truncate
    //   public function truncateData() {
    //     $data = EmployeeExperience::truncate();
    //     return $data;        
    //   } 
}
