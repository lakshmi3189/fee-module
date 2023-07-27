<?php

namespace App\Models\Employee;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;

class EmployeeFamily extends Model
{
    use HasFactory;
    protected $guarded = [];

    //insert
    public function insertData($req) {         
        // $userId = authUser()->id; 
        $mObject = new EmployeeFamily();        
        $insert = [
          $mObject->emp_tbl_id = $req['emp_tbl_id'],    
          $mObject->f_member_name = $req['f_member_name'],
          $mObject->f_member_relation_name = $req['f_member_relation_name'],
            $mObject->f_member_dob = $req['f_member_dob'],
            $mObject->upload_f_member_image = $req['upload_f_member_image'], 
            $mObject->academic_year = $req['academic_year'],          
            $mObject->school_id = $req['school_id'],          
            $mObject->created_by = $req['created_by'],          
            $mObject->ip_address = $req['ip_address']
        ];
        $mObject->save($insert);
        return $mObject;
      }

      //using id generator
      // public function empId(){
      //   $empData = new Employee();
      //   $emp_id['empId'] = $empData->emp_id;
      //   $id = IdGenerator::generate([
      //     'table' => 'class_tables',
      //     'field' => 'student_id',
      //     'length' => 11,
      //     'prefix' => $req->class.'/'.date('y').'/',
      //     'reset_on_prefix_change' => true,
      //   ]);        
      // }
      
      //view all 
      // public static function list() {
      //   $viewAll = EmployeeFamily::select('id','name','emp_id','email','mobile')->orderBy('id','desc')->get();    
      //   return $viewAll;
      // }
  
      // //view by id
      // public function listById($req) {
      //   $data = EmployeeFamily::where('id', $req->id)
      //         ->first();
      //     return $data;     
      // }   
  
      // //update
      // public function updateData($req) {
      //   $data = EmployeeFamily::find($req->id);
      //   if (!$data)
      //         throw new Exception("Record Not Found!");
      //   $edit = [
      //     'name' => $req->name
      //   ];
      //   $data->update($edit);
      //   return $data;        
      // }
  
      // //delete 
      // public function deleteData($req) {
      //   $data = EmployeeFamily::find($req->id);
      //   $data->is_deleted = "1";
      //   $data->save();
      //   return $data; 
      // }
  
      // //truncate
      // public function truncateData() {
      //   $data = EmployeeFamily::truncate();
      //   return $data;        
      // } 
}
