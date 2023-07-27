<?php

namespace App\Models\FeeStructure;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FmDailyFeeCollection extends Model
{
  use HasFactory;

  public function retrieve()
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
      ->join('fm_daily_fee_collections as f', 'f.student_id', '=', 'a.id')
      // ->where('a.id', $req->fyId)
      // ->where('a.class_id', $req->classId)
      // ->where('a.admission_no', $req->admissionNo)
      ->orderBy('a.id');
  }


  // public function retrieve2($req)
  // {
  //     $fy = new MsFinancialYear(); 
  //     $fyId = $fy->where('id', $req->fyId)->first();

  //     return Student::select(
  //         '*',
  //     DB::raw("
  //     CASE 
  //         WHEN status = '0' THEN 'Deactivated'  
  //         WHEN status = '1' THEN 'Active'
  //     END as status,
  //     TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
  //     TO_CHAR(created_at,'HH12:MI:SS AM') as time
  //     ")
  //     )->join('financial_years as f','f.financial_year', '=','')
  //     ->where('financial_year',$req->fy)
  //     ->where('class_id', $req->classId)
  //     ->where('admission_no' , $req->admissionNo)
  //     ->orderBy('id');
  // }
}
