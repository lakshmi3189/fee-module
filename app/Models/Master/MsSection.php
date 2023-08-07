<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Exception;
use DB;

class MsSection extends Model
{
  use HasFactory;

  protected $guarded = [];

  public function getExist($req)
  {
    return MsSection::where(DB::raw('upper(section)'), strtoupper($req->sectionName))->get();
  }

  public function retrieve()
  {
    return MsSection::select(
      DB::raw("id,section,
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
    return MsSection::select(
      DB::raw("id,section,
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
    return MsSection::where('status', 1)->count();
  }
}
