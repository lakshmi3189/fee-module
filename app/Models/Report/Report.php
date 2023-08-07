<?php

namespace App\Models\Report;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\FeeStructure\FeeCollection;
use App\Models\Student\Student;
use Exception;
use DB;

class Report extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function fyClassMonthWiseFeeReport($req)
    {
        $query = FeeCollection::select(
            DB::raw("*,              
            TO_CHAR(updated_at::date,'dd-mm-yyyy') as date,
            TO_CHAR(updated_at,'HH12:MI:SS AM') as updated_time
            ")
        )
            ->where('fy_id', $req->fyId)
            ->where('class_id', $req->classId);

        // If monthId is provided and not null, add the where clause for month_id
        if ($req->monthId) {
            $query->where('month_id', $req->monthId);
        }
        $feeCollection = $query->get();
        $monthNames = $feeCollection->pluck('month_name')->unique();
        if (collect($feeCollection)->isEmpty())
            throw new Exception("Data Not Found");

        $studentIds = $feeCollection->pluck('student_id')->unique();
        $students = Student::select('id', 'admission_no', 'full_name')->whereIn('id', $studentIds)->get();
        if (collect($students)->isEmpty())
            throw new Exception("No Students Found with Fee Collection for the Given Criteria");

        $finaldata = $monthNames->map(function ($monthName) use ($feeCollection, $students) {
            return $students->map(function ($student, $key) use ($feeCollection, $monthName) {
                $studentFee = $feeCollection->where("student_id", $student->id)->where('month_name', $monthName);
                $admissionNo = ($studentFee->values())[0]["admission_no"] ?? null;
                $className = ($studentFee->values())[0]["class_name"] ?? null;
                $classId = ($studentFee->values())[0]["class_id"] ?? null;
                $fyName = ($studentFee->values())[0]["fy_name"] ?? null;
                $fyId = ($studentFee->values())[0]["fy_id"] ?? null;
                $feehead = $studentFee->pluck("fee_head_name")->unique();
                $isPaid = ($studentFee->values())[0]["fy_name"] ?? null;
                $payDate = ($studentFee->values())[0]["date"] ?? null;

                $feeDetails = $feehead->map(function ($headName, $key) use ($studentFee) {
                    $color = '';
                    $fee = $studentFee->where('fee_head_name', $headName);
                    $cond = $fee->sum("due_amount");
                    if ($cond > 0) {
                        $color = "red";
                    }
                    $payDate = ($fee->values())[0]["date"] ?? null;
                    return [
                        'dt' => $payDate,
                        'feeHeadName' => $headName,
                        'amount' => $fee->sum("fee_amount"),
                        'receivedAmount' => $fee->sum("received_amount"),
                        'dueAmount' => $fee->sum("due_amount"),
                        'color' => $color,
                    ];
                })->values();
                return [
                    'admissionNo' => $admissionNo,
                    'fyId' => $fyId,
                    'fyName' => $fyName,
                    'classId' => $classId,
                    'class' => $className,
                    'monthName' => $monthName,
                    'updatedDate' => $payDate,
                    'studentDtl' => $student,
                    'feeDtl' => $feeDetails,
                ];
            });
        });

        return $finaldata->values()->toArray();
    }

    // public function feeCollections()
    // {
    //     return $this->hasMany(FeeCollection::class, 'student_id', 'id');
    // }

    /*Read all Active Records*/
    // public function feeStatus()
    // {
    //     return DB::table('fee_collections as a')
    //         ->select(
    //             DB::raw("
    //   b.financial_year,c.class_name,d.fee_head_type,e.fee_head_name,f.month_name,a.fee_amount,a.description,
    //   CASE 
    //   WHEN a.status = '0' THEN 'Deactivated'  
    //   WHEN a.status = '1' THEN 'Active'
    //   END as status,
    //   TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
    //   TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
    //   ")
    //         )
    //         ->join('ms_financial_years as b', 'b.id', '=', 'a.fy_id')
    //         ->join('ms_classes as c', 'c.id', '=', 'a.class_id')
    //         ->join('fm_fee_head_types as d', 'd.id', '=', 'a.fee_head_type_id')
    //         ->join('fm_fee_heads as e', 'e.id', '=', 'a.fee_head_id')
    //         ->join('ms_months as f', 'f.id', '=', 'a.month_id')
    //         ->where('a.status', 1)
    //         ->orderBy('a.month_id')
    //         ->where('status', 1)
    //         ->get();
    // }
}
