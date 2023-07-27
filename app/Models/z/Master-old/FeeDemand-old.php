<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use Illuminate\Support\Facades\Config;

//
// use App\Models\Master\FeeHeadType;
// use App\Models\Master\FeeHead;
// use App\Models\Master\ClassFeeMaster;
// use App\Models\Master\FeeDefinition;
// use App\Models\Master\DiscountGroup;
// use App\Models\Master\DiscountGroupMap;
// use App\Models\Master\StudentBusfeeMaster;
// use App\Models\Student\Student; 


class FeeDemand extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function store(array $req)
    {
        FeeDemand::create($req);
    }

    /* Get all records month wise for fee collection */
    public function getFees($studentId, $monthName = null)
    {

        $data = array();
        // echo $baseUrl = config('app.url');

        // $viewAll = FeeDemand::select(
        //     '*',
        //     DB::raw("
        //     CASE 
        //     WHEN status = '0' THEN 'Deactivated'  
        //     WHEN status = '1' THEN 'Active'
        //     END as status,
        //     TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        //     TO_CHAR(created_at,'HH12:MI:SS AM') as time
        //     ")
        // )
        //     ->where('student_id', $id)
        //     ->where('status', 1)
        //     ->get();

        //getting month name
        // $file = storage_path() . "/local-database/months.json";
        // $getMonthName =  file_get_contents($file);
        // print_r($getMonthName);
        //$m = json_decode($getMonthName);
        // echo "<pre>";
        // print_r($m);
        // echo $m->{'monthName'};
        // die;
        // foreach ($m['monthName'] as $i => $v) {
        //     echo $v['monthName'] . '<br/>';
        // }
        //---not working 



        //Get month 
        // DB::enableQueryLog();
        // $getMonthRecords = DB::table('months')
        //     ->select(
        //         DB::raw("month_name")
        //     )
        //     ->get();

        $from = DB::table('fee_demands AS fee_demands')
            ->join('class_fee_masters as class_fee_masters', 'class_fee_masters.id', '=', 'fee_demands.class_fee_master_id')
            ->join('students as students', 'students.id', '=', 'fee_demands.student_id')
            ->join('class_masters as class_masters', 'class_masters.id', '=', 'class_fee_masters.class_id')
            // ->join('subjects as c', 'c.id', '=', 's.subject_id')
            ->join('section_group_maps as sections', 'sections.id', '=', 'class_fee_masters.section_id')
            ->join('fee_heads as fee_heads', 'fee_heads.id', '=', 'fee_demands.fee_head_id')
            ->where('students.id', $studentId)
            ->where('students.status', 1);
        if ($monthName) {

            // $from = $from->where('fee_demands.month_name', $monthName);
        }

        $selectData = $from->select(
            DB::raw(" fee_demands.month_name,fee_demands.fee_head_id,fee_demands.amount,fee_heads.fee_head,
            sections.section_name,                
            class_masters.class_name,
            students.admission_no,CONCAT_WS(students.first_name,' ',students.middle_name,' ',students.last_name) as full_name,                
          CASE WHEN students.status = '0' THEN 'Deactivated'  
          WHEN students.status = '1' THEN 'Active'
          END as status,
          TO_CHAR(fee_demands.created_at::date,'dd-mm-yyyy') as date,
          TO_CHAR(fee_demands.created_at,'HH12:MI:SS AM') as time
          ")
        )->get();
        // $selectMonth = DB::table("fee_demands")->select(
        //     DB::raw("Distinct(fee_demands.month_name) AS month_name 
        //   ")
        // )->orderBy("month_name")->get();

        $selectMonth = Config::get("month");
        // dd($selectMonth);

        $selectFeeHead = $from->select(
            DB::raw(" Distinct(fee_heads.fee_head) AS fee_head 
          ")
        )->get();
        // print_var($selectFeeHead);
        // die;

        // dd($selectMonth);
        // $viewAll = DB::table('fee_demands AS fee_demands')
        //     ->select(
        //         DB::raw(" fee_demands.month_name,fee_demands.fee_head_id,fee_demands.amount,fee_heads.fee_head,
        //         sections.section_name,                
        //         class_masters.class_name,
        //         students.admission_no,CONCAT_WS(students.first_name,' ',students.middle_name,' ',students.last_name) as full_name,                
        //       CASE WHEN students.status = '0' THEN 'Deactivated'  
        //       WHEN students.status = '1' THEN 'Active'
        //       END as status,
        //       TO_CHAR(fee_demands.created_at::date,'dd-mm-yyyy') as date,
        //       TO_CHAR(fee_demands.created_at,'HH12:MI:SS AM') as time
        //       ")
        //     )
        //     ->join('class_fee_masters as class_fee_masters', 'class_fee_masters.id', '=', 'fee_demands.class_fee_master_id')
        //     ->join('students as students', 'students.id', '=', 'fee_demands.student_id')
        //     ->join('class_masters as class_masters', 'class_masters.id', '=', 'class_fee_masters.class_id')
        //     // ->join('subjects as c', 'c.id', '=', 's.subject_id')
        //     ->join('sections as sections', 'sections.id', '=', 'class_fee_masters.section_id')
        //     ->join('fee_heads as fee_heads', 'fee_heads.id', '=', 'fee_demands.fee_head_id')
        //     ->where('students.id', $studentId)
        //     ->where('students.status', 1)
        //     ->where('fee_demands.month_name', $monthName)
        //     ->get();
        // dd(DB::getQueryLog());
        // return $viewAll;
        // echo "<pre>";;
        // dd($selectData);
        $data["monthly_fee"] = collect($selectMonth)->map(function ($val, $key) use ($selectData, $selectFeeHead) {
            $monthName = $val;
            $testData = $selectData->where("month_name", $monthName);

            // $finalData["fullName"] = $selectData->full_name;
            $finalData["month_name"] = $monthName;
            $finalData["fee"] = (array)null;
            $finalData["total"] = $testData->sum("amount");

            foreach ($selectFeeHead as $val1) {
                if ($testData->where("fee_head", $val1->fee_head)->sum("amount") != 0) {
                    $finalData["fee"][] = [
                        "amount" => $testData->where("fee_head", $val1->fee_head)->sum("amount"),
                        "fee_head" => $val1->fee_head,
                        "fee_ids" => $testData->where("fee_head", $val1->fee_head)->pluck("fee_head_id")->implode(","),
                    ];
                }
            }
            return $finalData;
        });

        $data["grandTotal"] = $data["monthly_fee"]->sum("total");
        $data["studentDetails"] = [
            "sectionName" => ($selectData[0]->section_name) ?? "",
            "className" => ($selectData[0]->class_name) ?? "",
            "admissionNo" => ($selectData[0]->admission_no) ?? "",
            "fullName" => ($selectData[0]->full_name) ?? "",
            "status" => ($selectData[0]->status) ?? "",
        ];
        return collect($data);
    }


    // private $_dmStudents;
    // private $_dmClassFeeMasters;

    // public function __construct()
    // {
    //     $this->_dmStudents = new Student();
    //     $this->_dmClassFeeMasters = new ClassFeeMaster();
    // } 

    // public function readStudentGroup()
    // {
    //     //Get student id and class id
    //     $getStd = $this->_dmStudents::select('id','class_id')->where('status','1')->get();

    //     //Get class fee by class fee id and class is
    //     $getClassFee = $this->_dmClassFeeMasters::where('status','1')->get();

    //     return $getClassFee;
    // }


    //     /**
    //     * | Add Records
    //     */
    //    public function store(array $req){
    //     FeeDemand::create($req);
    //    }

    //     /**
    //     * | Read Records by ID
    //     */
    //    public function readFeeDemandById($feeDemand)
    //    {
    //        return FeeDemand::where('student_id', $feeDemand) //student_id, class_fee_master_id
    //            ->where('status', 1)
    //            ->get();
    //    }
}
