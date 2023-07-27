<?php

namespace App\Models\FeeStructure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class FmClassFeeMaster extends Model
{
  use HasFactory;
  protected $guarded = [];

  public function readClassFeeMastersGroup($ob)
  {
    return FmClassFeeMaster::where('fy_id', $ob['fyId'])
      ->where('class_id', $ob['classId'])
      ->where('fee_head_id', $ob['feeHeadId'])
      ->where('month_id', $ob['monthId'])
      ->where('status', 1)
      ->get();
  }

  // /*Read Records by name*/
  // public function readClassFeeMastersGroup($req)
  // {
  //   return FmClassFeeMaster::where('fy_id', $req->fyId)
  //     ->where('class_id', $req->classId)
  //     ->where('fee_head_id', $req->feeHeadId)
  //     ->where('status', 1)
  //     ->get();
  // }

  // /*Read all Active Records*/
  // public function active()
  // {
  //   return ClassFeeMaster::select(
  //     DB::raw("id,fee_head_name,
  //     CASE 
  //       WHEN status = '0' THEN 'Deactivated'  
  //       WHEN status = '1' THEN 'Active'
  //     END as status,
  //     TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
  //     TO_CHAR(created_at,'HH12:MI:SS AM') as time
  //     ")
  //   )
  //     ->where('status', 1)
  //     ->orderBy('fee_head_name')
  //     ->get();
  // }

  /*Read all Records by*/
  // public function retrieve()
  // {
  //   return FmClassFeeMaster::select(
  //     DB::raw("id,fee_head_name,
  //     CASE 
  //       WHEN status = '0' THEN 'Deactivated'  
  //       WHEN status = '1' THEN 'Active'
  //     END as status,
  //     TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
  //     TO_CHAR(created_at,'HH12:MI:SS AM') as time
  //     ")
  //   )
  //     ->orderByDesc('id');
  //   // ->get();
  // }


  public function retrieve()
  {
    // $schoolId = authUser()->school_id;
    return DB::table('fm_class_fee_masters as a')
      ->select(
        DB::raw("
        b.financial_year,c.class_name,d.fee_head_type,e.fee_head_name,f.month_name,a.fee_amount,a.description,
        CASE 
        WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('ms_financial_years as b', 'b.id', '=', 'a.fy_id')
      ->join('ms_classes as c', 'c.id', '=', 'a.class_id')
      ->join('fm_fee_head_types as d', 'd.id', '=', 'a.fee_head_type_id')
      ->join('fm_fee_heads as e', 'e.id', '=', 'a.fee_head_id')
      ->join('ms_months as f', 'f.id', '=', 'a.month_id')
      ->where('a.status', 1)
      ->orderBy('a.month_id');
    // ->where('status', 1)
    // ->get();
  }


  public function getFeeHeadByFyIdAndClassId($req)
  {
    return DB::table('fm_class_fee_masters as a')
      ->select(
        DB::raw("
        b.financial_year,c.class_name,d.fee_head_type,e.fee_head_name,f.month_name,a.fee_amount,a.description,
        CASE 
        WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('ms_financial_years as b', 'b.id', '=', 'a.fy_id')
      ->join('ms_classes as c', 'c.id', '=', 'a.class_id')
      ->join('fm_fee_head_types as d', 'd.id', '=', 'a.fee_head_type_id')
      ->join('fm_fee_heads as e', 'e.id', '=', 'a.fee_head_id')
      ->join('ms_months as f', 'f.id', '=', 'a.month_id')
      ->where('a.status', 1)
      ->where('a.fy_id', $req->fyId)
      ->where('a.class_id', $req->classId)
      ->orderBy('a.id')
      ->get();
  }


  // public function getFees($studentId, $monthName = null)
  // {

  //     $data = array();

  //     $from = DB::table('fee_demands AS fee_demands')
  //         ->join('class_fee_masters as class_fee_masters', 'class_fee_masters.id', '=', 'fee_demands.class_fee_master_id')
  //         ->join('students as students', 'students.id', '=', 'fee_demands.student_id')
  //         ->join('class_masters as class_masters', 'class_masters.id', '=', 'class_fee_masters.class_id')
  //         // ->join('subjects as c', 'c.id', '=', 's.subject_id')
  //         ->join('section_group_maps as sections', 'sections.id', '=', 'class_fee_masters.section_id')
  //         ->join('fee_heads as fee_heads', 'fee_heads.id', '=', 'fee_demands.fee_head_id')
  //         ->where('students.id', $studentId)
  //         ->where('students.status', 1);
  //     if ($monthName) {

  //         // $from = $from->where('fee_demands.month_name', $monthName);
  //     }

  //     $selectData = $from->select(
  //         DB::raw(" fee_demands.month_name,fee_demands.fee_head_id,fee_demands.amount,fee_heads.fee_head,
  //         sections.section_name,                
  //         class_masters.class_name,
  //         students.admission_no,CONCAT_WS(students.first_name,' ',students.middle_name,' ',students.last_name) as full_name,                
  //       CASE WHEN students.status = '0' THEN 'Deactivated'  
  //       WHEN students.status = '1' THEN 'Active'
  //       END as status,
  //       TO_CHAR(fee_demands.created_at::date,'dd-mm-yyyy') as date,
  //       TO_CHAR(fee_demands.created_at,'HH12:MI:SS AM') as time
  //       ")
  //     )->get();
  //     // $selectMonth = DB::table("fee_demands")->select(
  //     //     DB::raw("Distinct(fee_demands.month_name) AS month_name 
  //     //   ")
  //     // )->orderBy("month_name")->get();

  //     $selectMonth = Config::get("month");
  //     // dd($selectMonth);

  //     $selectFeeHead = $from->select(
  //         DB::raw(" Distinct(fee_heads.fee_head) AS fee_head 
  //       ")
  //     )->get();

  //     $data["monthly_fee"] = collect($selectMonth)->map(function ($val, $key) use ($selectData, $selectFeeHead) {
  //         $monthName = $val;
  //         $testData = $selectData->where("month_name", $monthName);

  //         // $finalData["fullName"] = $selectData->full_name;
  //         $finalData["month_name"] = $monthName;
  //         $finalData["fee"] = (array)null;
  //         $finalData["total"] = $testData->sum("amount");

  //         foreach ($selectFeeHead as $val1) {
  //             if ($testData->where("fee_head", $val1->fee_head)->sum("amount") != 0) {
  //                 $finalData["fee"][] = [
  //                     "amount" => $testData->where("fee_head", $val1->fee_head)->sum("amount"),
  //                     "fee_head" => $val1->fee_head,
  //                     "fee_ids" => $testData->where("fee_head", $val1->fee_head)->pluck("fee_head_id")->implode(","),
  //                 ];
  //             }
  //         }
  //         return $finalData;
  //     });

  //     $data["grandTotal"] = $data["monthly_fee"]->sum("total");
  //     $data["studentDetails"] = [
  //         "sectionName" => ($selectData[0]->section_name) ?? "",
  //         "className" => ($selectData[0]->class_name) ?? "",
  //         "admissionNo" => ($selectData[0]->admission_no) ?? "",
  //         "fullName" => ($selectData[0]->full_name) ?? "",
  //         "status" => ($selectData[0]->status) ?? "",
  //     ];
  //     return collect($data);
  // }
}
