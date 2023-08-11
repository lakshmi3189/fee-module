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
            ->where('class_id', $req->classId)
            ->orderByDesc('updated_at');

        // If monthId is provided and not null, add the where clause for month_id
        if ($req->monthId) {
            $query->where('month_id', $req->monthId);
        }
        $feeCollection = $query->get();
        $monthNames = $feeCollection->pluck('month_name')->unique();
        if (collect($feeCollection)->isEmpty())
            throw new Exception("Data Not Found");

        $studentIds = $feeCollection->pluck('student_id')->unique();
        $students = Student::select(
            DB::raw("id,admission_no,full_name,
            CASE 
                WHEN is_mid_session = '1' THEN 'Mid Session'  
                WHEN is_mid_session = '0' THEN 'Regular Session'
            END as is_mid_session
            ")
        )->whereIn('id', $studentIds)->get();
        // $students = Student::select('id', 'admission_no', 'full_name')->whereIn('id', $studentIds)->get();
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


    public function getFyAndMonthByReport($req)
    {
        $monthSums = DB::table('fee_collections')
            ->select(
                'fy_id',
                'month_id',
                'month_name',
                DB::raw('SUM(fee_amount) as total_fee'),
                DB::raw('SUM(received_amount) as total_received'),
                DB::raw('SUM(due_amount) as total_due')
            )
            ->where('fy_id', $req->fyId)
            ->where('status', 1)
            ->groupBy('fy_id', 'month_id', 'month_name')
            ->orderBy('month_id')
            ->get();
        $chartData = [];
        foreach ($monthSums as $monthSum) {
            $total = 0;
            $chartData[] = [
                'monthId' => $monthSum->month_id,
                'monthName' => $monthSum->month_name,
                'feeDtl' => [
                    [
                        'totalAmount' => $monthSum->total_fee,
                        'totalReceived' => $monthSum->total_received,
                        'totalDue' => $monthSum->total_due,
                    ],
                ],
            ];
        }
        return $chartData;
    }


    public function getFyByReport($req)
    {


        $fyTotals = DB::table('fee_collections')
            ->select(
                DB::raw('SUM(fee_amount) as totalFyFee'),
                DB::raw('SUM(received_amount) as totalFyReceive'),
                DB::raw('SUM(due_amount) as totalFyDue')
            )
            ->where('fy_id', $req->fyId)
            ->first();
        return $fyTotals;
    }
}
