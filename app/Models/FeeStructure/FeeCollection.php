<?php

namespace App\Models\FeeStructure;

use DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

use function PHPUnit\Framework\isEmpty;

class FeeCollection extends Model
{
  use HasFactory;
  protected $guarded = [];

  /*Add Records*/
  public function store(array $req)
  {
    FeeCollection::create($req);
  }

  public function readFeeCollectionGroup($feeData, $req)
  {
    return FeeCollection::where('month_id', $feeData['monthId'])
      ->where('admission_no', $req->admissionNo)
      ->where('fy_id', $req->fyId)
      ->where('class_id', $req->classId)
      ->where('month_id', $feeData['monthId'])
      ->where('fee_head_id', $feeData['feeHeadId'])
      ->where('status', 1)
      ->get();
  }
  //
  public function getReceiptNoExist($req)
  {
    return FeeCollection::where(DB::raw('upper(receipt_no)'), strtoupper($req->receiptNo))->get();
    // return FeeCollection::where('receipt_no', $req->receiptNo)->get();
  }

  /*Read Records by ID*/
  public function getGroupByReceiptNo($req)
  {
    return DB::table('fee_collections as a')
      ->select(
        DB::raw("fy_name,month_name,admission_no,class_name,payment_mode,payment_date,fee_head_name,fee_amount,received_amount,
        due_amount,receipt_no,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.payment_date::date,'dd-mm-yyyy') as payment_date,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->where('a.receipt_no', $req->receiptNo)
      ->orderBy('a.id')
      ->get();
  }
}
