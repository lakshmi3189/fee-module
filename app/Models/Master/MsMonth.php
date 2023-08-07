<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class MsMonth extends Model
{
  use HasFactory;
  protected $guarded = [];

  public function getExist($req)
  {
    return MsMonth::where(DB::raw('upper(month_name)'), strtoupper($req->monthName))->get();
  }

  /*Read all Records by*/
  public function retrieve()
  {
    return MsMonth::select(
      DB::raw("id,month_name,
      CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
      ")
    )
      // ->where('status', 1)
      ->orderBy('id');
    // ->get();
  }

  /*Read all Active Records*/
  public function active()
  {
    return MsMonth::select(
      DB::raw("id,month_name,
      CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
      ")
    )
      ->where('status', 1)
      ->orderBy('id')
      ->get();
  }

  public function getMonthList()
  {
    $activeMonths = MsMonth::select('id', 'month_name')
      ->where('status', 1)
      ->orderBy('id')
      ->get();
    $months = [];
    foreach ($activeMonths as $month) {
      $months[$month->id] = $month->month_name;
    }
    return $months;
  }
}
