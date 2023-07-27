<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class FmFeeHead extends Model
{
  use HasFactory;
  protected $guarded = [];

  /*Read all Active Records*/
  public function active()
  {
    return FmFeeHead::select(
      DB::raw("id,fee_head_name,
      CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
      ")
    )
      ->where('status', 1)
      ->orderBy('fee_head_name')
      ->get();
  }

  /*Read all Records by*/
  public function retrieve()
  {
    return FmFeeHead::select(
      DB::raw("id,fee_head_name,
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
