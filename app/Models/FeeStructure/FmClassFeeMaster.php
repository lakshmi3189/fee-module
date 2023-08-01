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
      ->orderBy('a.class_id')
      ->orderBy('a.month_id');
    // ->where('status', 1)
    // ->get();
  }

  public function getFeeHeadByFyIdAndClassId1($req)
  {
    $from = DB::table('fm_class_fee_masters as a')
      ->join('ms_financial_years as b', 'b.id', '=', 'a.fy_id')
      ->join('ms_classes as c', 'c.id', '=', 'a.class_id')
      ->join('fm_fee_head_types as d', 'd.id', '=', 'a.fee_head_type_id')
      ->join('fm_fee_heads as e', 'e.id', '=', 'a.fee_head_id')
      ->join('ms_months as f', 'f.id', '=', 'a.month_id')
      ->where('a.status', 1)
      ->where('a.fy_id', $req->fyId)
      ->where('a.class_id', $req->classId)
      ->select(
        DB::raw("
            b.financial_year, c.class_name, d.fee_head_type, e.fee_head_name, f.month_name, f.id as month_id,
            a.fee_amount, a.description, a.fee_head_id,
            CASE 
                WHEN a.status = '0' THEN 'Deactivated'  
                WHEN a.status = '1' THEN 'Active'
            END as status,
            TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
            TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      );

    $fees = $from->get();

    $feeColl = DB::table('fee_collections')->get();

    // Extract the month_ids from the second query (fee_collections)
    $secondQueryMonthIds = $feeColl->pluck('month_id')->toArray();

    // To get month-wise fees and check if the month_id exists in the second query
    $monthWiseFees = [];
    foreach ($fees as $fee) {
      $monthId = $fee->month_id;
      if (!isset($monthWiseFees[$monthId])) {
        $monthWiseFees[$monthId] = [];
      }

      if (in_array($monthId, $secondQueryMonthIds)) {
        $monthWiseFees[$monthId][] = $fee;
        $monthWiseFees[$monthId]['message'] = 'Month is existing in the second query.';
      } else {
        $monthWiseFees[$monthId][] = $fee;
      }
    }

    // Convert the result to JSON format
    // $outputJson = json_encode($monthWiseFees);


    return $monthWiseFees;
    // Convert the result to JSON format
    //$outputJson = json_encode($monthWiseFees);
  }


  public function getFeeHeadByFyIdAndClassId($req)
  {
    $from = DB::table('fm_class_fee_masters as a')
      ->join('ms_financial_years as b', 'b.id', '=', 'a.fy_id')
      ->join('ms_classes as c', 'c.id', '=', 'a.class_id')
      ->join('fm_fee_head_types as d', 'd.id', '=', 'a.fee_head_type_id')
      ->join('fm_fee_heads as e', 'e.id', '=', 'a.fee_head_id')
      ->join('ms_months as f', 'f.id', '=', 'a.month_id')
      ->where('a.status', 1)
      ->where('a.fy_id', $req->fyId)
      ->where('a.class_id', $req->classId);
    $fees = $from->select(
      DB::raw("
          b.financial_year,c.class_name,d.fee_head_type,e.fee_head_name,f.month_name,f.id as month_id,
          a.fee_amount,a.description,a.fee_head_id,
          CASE 
          WHEN a.status = '0' THEN 'Deactivated'  
          WHEN a.status = '1' THEN 'Active'
          END as status,
          TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
          TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
          ")
    )->get();

    // $feeColl = DB::table('fee_collections')->get();
    $groupedData = collect($fees)->groupBy(function ($item) {
      return $item->month_id . '|' . $item->month_name;
    });
    $monthlyFee = [];
    foreach ($groupedData as $key => $fees) {
      list($monthId, $monthName) = explode('|', $key);
      $total = 0;
      $feeItems = [];
      foreach ($fees as $fee) {
        $total += isset($fee->fee_amount) ? $fee->fee_amount : 0;
        $feeItems[] = [
          'fee_id' => isset($fee->fee_head_id) ? $fee->fee_head_id : '',
          'fee_head' => isset($fee->fee_head_name) ? $fee->fee_head_name : '',
          'amount' => isset($fee->fee_amount) ? $fee->fee_amount : 0,
        ];
      }
      $monthlyFee[] = [
        'month_id' => $monthId,
        'month_name' => $monthName,
        'fee' => $feeItems,
        'total' => $total,
      ];
    }
    $output = [
      'monthly_fee' => $monthlyFee,
    ];
    return $output;

    // $groupedData = collect($fees)->groupBy('month_name');
    // $monthlyFee = $groupedData->map(function ($fees, $monthName) {
    //   $total = $fees->sum('fee_amount');
    //   $feeItems = $fees->map(function ($fee) {
    //     return [
    //       'fee_ids' => $fee->fee_head_id,
    //       'fee_head' => $fee->fee_head_name,
    //       'amount' => $fee->fee_amount,
    //     ];
    //   });

    //   return [
    //     'month_name' => $monthName,
    //     'fee' => $feeItems->all(),
    //     'total' => $total,
    //   ];
    // });
    // $output = [
    //   'monthly_fee' => $monthlyFee->values()->all(),
    // ];

  }
}
