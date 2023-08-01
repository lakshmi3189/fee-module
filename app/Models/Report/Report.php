<?php

namespace App\Models\Report;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FeeStructure\FeeCollection;

use DB;

class Report extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function feeCollections()
    {
        return $this->hasMany(FeeCollection::class, 'student_id', 'id');
    }

    /*Read all Active Records*/
    public function feeStatus()
    {
        return DB::table('fee_collections as a')
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
            ->orderBy('a.month_id')
            ->where('status', 1)
            ->get();
    }
}
