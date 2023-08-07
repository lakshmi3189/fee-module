<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class FmFeeHead extends Model
{
  use HasFactory;
  protected $guarded = [];

  public function store(array $req)
  {
    FmFeeHead::create($req);
  }

  public function getExist($req)
  {
    return FmFeeHead::where('id', $req->id)->get();
    // return FmFeeHead::where(DB::raw('upper(fee_head_name)'), strtoupper($req->feeHeadName))->get();
  }

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

  /*Count all Active Records*/
  public function countActive()
  {
    return FmFeeHead::where('status', 1)->count();
  }

  //Get Records by name
  public function searchByName($req)
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
      ->where(DB::raw('upper(fee_head_name)'), 'LIKE', '%' . strtoupper($req->search) . '%');
  }
}
