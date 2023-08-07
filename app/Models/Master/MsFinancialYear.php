<?php

namespace App\Models\Master;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MsFinancialYear extends Model
{
  use HasFactory;
  protected $guarded = [];

  public function getExist($req)
  {
    return MsFinancialYear::select('id', 'financial_year')->where('id', $req)->get();
    // return MsFinancialYear::where(DB::raw('upper(id)'), strtoupper($req->fyName))->get();
  }

  /*Read all Active Records*/
  public function active()
  {
    return MsFinancialYear::select(
      DB::raw("id,financial_year,
      CASE 
        WHEN status = '0' THEN 'Deactivated'  
        WHEN status = '1' THEN 'Active'
      END as status,
      TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
      TO_CHAR(created_at,'HH12:MI:SS AM') as time
      ")
    )
      ->where('status', 1)
      ->orderBy('financial_year')
      ->get();
  }

  //Get Records by name
  public function searchByName($req)
  {
    return MsFinancialYear::select(
      DB::raw("id,financial_year,
        CASE 
          WHEN status = '0' THEN 'Deactivated'  
          WHEN status = '1' THEN 'Active'
        END as status,
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time
      ")
    )
      ->where(DB::raw('upper(financial_year)'), 'LIKE', '%' . strtoupper($req->search) . '%');
  }

  /*Read all Records by*/
  public function retrieve()
  {
    return MsFinancialYear::select(
      DB::raw("id,financial_year,
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

  public function store(array $req)
  {
    MsFinancialYear::create($req);
  }
}
