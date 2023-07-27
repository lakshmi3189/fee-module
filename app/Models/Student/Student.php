<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use DB;

class Student extends Model
{
  use HasApiTokens, HasFactory, Notifiable;
  protected $guarded = [];

  /*Read all Records by*/
  public function retrieve()
  {
    return DB::table('students as a')
      ->select(
        DB::raw("a.admission_no,a.roll_no,a.full_name,a.gender_name,a.email,a.mobile,a.disability,
        e.category_name,
        c.class_name,
        d.section,
        b.financial_year,
        CASE 
        WHEN a.is_parent_staff = '0' THEN 'No'  
        WHEN a.is_parent_staff = '1' THEN 'Yes'
        END as is_parent_staff,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.dob::date,'dd-mm-yyyy') as dob,
        TO_CHAR(a.admission_date::date,'dd-mm-yyyy') as admission_date,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('ms_financial_years as b', 'b.financial_year', '=', 'a.financial_year')
      ->join('ms_classes as c', 'c.id', '=', 'a.class_id')
      ->join('ms_sections as d', 'd.id', '=', 'a.section_id')
      ->join('ms_categories as e', 'e.id', '=', 'a.category_id')
      ->where('a.status', 1)
      ->orderBy('a.admission_no');


    //   // $schoolId = authUser()->school_id;
    //   return Student::select(
    //     DB::raw("id,admission_no,roll_no,full_name,class_name,section_name,gender_name,email,mobile,
    //       disability,category_name,financial_year,is_parent_staff,      
    //     CASE 
    //     WHEN is_parent_staff = '0' THEN 'No'  
    //     WHEN is_parent_staff = '1' THEN 'Yes'
    //     END as is_parent_staff,
    //     CASE 
    //     WHEN status = '0' THEN 'Deactivated'  
    //     WHEN status = '1' THEN 'Active'
    //     END as status,
    //     TO_CHAR(dob::date,'dd-mm-yyyy') as dob,
    //     TO_CHAR(admission_date::date,'dd-mm-yyyy') as admission_date
    // ")
    //   )
    //     ->where('status', 1)
    //     ->orderBy('class_name');
    //   // ->get();
  }

  public function getStudentByClassAndAdmissionNo($req)
  {
    return DB::table('students as a')
      ->select(
        DB::raw("a.admission_no,a.roll_no,a.full_name,a.gender_name,a.email,a.mobile,a.disability,
        e.category_name,
        c.class_name,
        d.section,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.dob::date,'dd-mm-yyyy') as dob,
        TO_CHAR(a.admission_date::date,'dd-mm-yyyy') as admission_date,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('ms_financial_years as b', 'b.financial_year', '=', 'a.financial_year')
      ->join('ms_classes as c', 'c.id', '=', 'a.class_id')
      ->join('ms_sections as d', 'd.id', '=', 'a.section_id')
      ->join('ms_categories as e', 'e.id', '=', 'a.category_id')
      ->where('a.id', $req->fyId)
      ->where('a.class_id', $req->classId)
      ->where('a.admission_no', $req->admissionNo)
      ->orderBy('a.id')
      ->first();
  }


  public static function csv($data)
  {

    // $value = DB::table('users')->where('username', $data['username'])->get();
    // if ($value->count() == 0) {
    DB::table('students')->insert($data);
    // }
  }
}
