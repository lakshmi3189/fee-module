<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Support\Collection;
// use Illuminate\Support\Carbon;
// use Illuminate\Support\Str;
use DB;
// use Exception;

class MsClass extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function store(array $req)
  {
    MsClass::create($req);
  }

  public function getExist($req)
  {
    return MsClass::where('id', $req)->get();
    // return MsClass::where(DB::raw('upper(class_name)'), strtoupper($req->className))->get();
  }

  /*Read all Records by*/
  public function retrieve()
  {
    return MsClass::select(
      DB::raw("id,class_name,
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
    return MsClass::select(
      DB::raw("id,class_name,
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

  /*Count all Active Records*/
  public function countActive()
  {
    return MsClass::where('status', 1)->count();
  }

  //Get Records by name
  public function searchByName($req)
  {
    return MsClass::select(
      DB::raw("id,class_name,
        CASE 
          WHEN status = '0' THEN 'Deactivated'  
          WHEN status = '1' THEN 'Active'
        END as status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time
      ")
    )
      ->where(DB::raw('upper(class_name)'), 'LIKE', '%' . strtoupper($req->search) . '%');
  }
}
