<?php

namespace App\Models\Master;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsCategory extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function getExist($req)
  {
    return MsCategory::where(DB::raw('upper(category_name)'), strtoupper($req->categoryName))->get();
  }

  /*Read all Active Records*/
  public function active()
  {
    return MsCategory::select(
      DB::raw("id,category_name,
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

  /*Read all Records by*/
  public function retrieve()
  {
    return MsCategory::select(
      DB::raw("id,category_name,
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
