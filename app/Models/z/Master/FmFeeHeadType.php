<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class FmFeeHeadType extends Model
{
  use HasFactory;
  protected $guarded = [];

  /*Read all Active Records*/
  public function active()
  {
    return FmFeeHeadType::select(
      DB::raw("id,fee_head_type,
      CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
      ")
    )
      ->where('status', 1)
      ->orderBy('fee_head_type')
      ->get();
  }

  /*Read all Records by*/
  public function retrieve()
  {
    return FmFeeHeadType::select(
      DB::raw("id,fee_head_type,
      CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
      ")
    )
      ->orderByDesc('id');
    // ->get();
  }
}
