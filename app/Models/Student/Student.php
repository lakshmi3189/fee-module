<?php

namespace App\Models\Student;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\FeeStructure\FeeCollection;

use DB;

class Student extends Model
{
  use HasApiTokens, HasFactory, Notifiable;
  protected $guarded = [];

  /*Add Records*/
  public function store(array $req)
  {
    Student::create($req);
  }

  public function readStudentGroup($req)
  {
    return Student::where(DB::raw('upper(admission_no)'), strtoupper($req->admissionNo))
      //where('admission_no', $req->admissionNo)
      // ->where('financial_year', $req->financialYear)
      //where(DB::raw('upper(admission_no)'), strtoupper($req->admissionNo))->get();
      ->where('status', 1)
      ->get();
  }
  /*Read all Records by*/
  public function retrieve()
  {
    return DB::table('students as a')
      ->select(
        DB::raw("a.admission_no,a.roll_no,a.full_name,a.gender_name,a.email,a.mobile,a.disability,a.father_name,
        e.category_name,
        c.class_name,
        d.section,
        b.financial_year,
        CASE 
        WHEN a.is_parent_staff = '0' THEN 'No'  
        WHEN a.is_parent_staff = '1' THEN 'Yes'
        END as is_parent_staff,
        CASE 
        WHEN a.disability = '0' THEN 'No'  
        WHEN a.disability = '1' THEN 'Yes'
        END as disability,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.dob::date,'dd-mm-yyyy') as dob,
        TO_CHAR(a.admission_date::date,'dd-mm-yyyy') as admission_date,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('ms_financial_years as b', 'b.financial_year', '=', 'a.financial_year')
      ->join('ms_classes as c', 'c.id', '=', 'a.class_id')
      ->join('ms_sections as d', 'd.id', '=', 'a.section_id')
      ->join('ms_categories as e', 'e.id', '=', 'a.category_id')
      ->where('a.status', 1)
      // ->orderBy('a.class_id')
      // ->orderBy('a.admission_no')
      ->orderByDesc('a.id');
  }



  public function getStudentByClassAndAdmissionNoWiseHistory($req)
  {

    // Fetch the data from the database
    $data = FeeCollection::all();

    // Initialize an empty array to store the formatted data
    $formattedData = [];

    // Group the data by 'fy_name', 'class_name', and 'admission_no'
    $groupedData = $data->groupBy(['fy_name', 'class_name', 'admission_no']);

    foreach ($groupedData as $key => $collection) {
      // Extract the financial year, class, and admission number from the key
      list($fyName, $className, $admissionNo) = explode('_', $key);

      // Initialize an empty array to store the fee details for each month
      $feeDetails = [];

      foreach ($collection as $record) {
        $feeDetails[] = [
          'id' => $record->id,
          'feeHeadName' => $record->fee_head_name,
          'amount' => $record->fee_amount,
          'receivedAmount' => $record->received_amount,
          'dueAmount' => $record->due_amount,
        ];
      }

      // Add the formatted data for each admission number to the main array
      $formattedData[] = [
        'fyName' => $fyName,
        'class' => $className,
        'admission_no' => $admissionNo,
        'feeHistory' => [
          [
            'monthName' => 'January', // You can dynamically get the month name based on month_id if needed.
            'feeDtl' => $feeDetails,
          ],
          [
            'monthName' => 'February', // You can dynamically get the month name based on month_id if needed.
            'feeDtl' => $feeDetails,
          ],
          // Add other months here as needed...
        ],
      ];
    }

    // Convert the formatted data to JSON and return it
    return response()->json($formattedData);
  }

  //search student by class wise and admission no wise  
  public function getStudentByClassAndAdmissionNo($req)
  {
    // print_var($req);
    return DB::table('students as a')
      ->select(
        DB::raw("a.admission_no,a.roll_no,a.full_name,a.gender_name,a.email,a.mobile,a.disability,a.father_name,a.id,
        e.category_name,
        c.class_name,
        d.section,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.dob::date,'dd-mm-yyyy') as dob,
        TO_CHAR(a.admission_date::date,'dd-mm-yyyy') as admission_date,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('ms_financial_years as b', 'b.financial_year', '=', 'a.financial_year')
      ->join('ms_classes as c', 'c.id', '=', 'a.class_id')
      ->join('ms_sections as d', 'd.id', '=', 'a.section_id')
      ->join('ms_categories as e', 'e.id', '=', 'a.category_id')
      ->where('a.fy_id', $req->fyId)
      ->where('a.class_id', $req->classId)
      ->whereRaw('LOWER(a.admission_no) LIKE ?', [strtolower("%$req->admissionNo%")])
      ->where('a.status', 1)
      ->orderBy('a.id')
      ->first();
  }

  //search student by class wise and name and father name wise
  public function getStudentByClassIdAndNameAndFatherName($req)
  {
    return DB::table('students as a')
      ->select(
        DB::raw("a.admission_no,a.roll_no,a.full_name,a.gender_name,a.email,a.mobile,a.disability,a.father_name,a.id,
        e.category_name,
        c.class_name,
        d.section,
        CASE WHEN a.status = '0' THEN 'Deactivated'  
        WHEN a.status = '1' THEN 'Active'
        END as status,
        TO_CHAR(a.dob::date,'dd-mm-yyyy') as dob,
        TO_CHAR(a.admission_date::date,'dd-mm-yyyy') as admission_date,
        TO_CHAR(a.created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(a.created_at,'HH12:MI:SS AM') as time
        ")
      )
      ->join('ms_financial_years as b', 'b.financial_year', '=', 'a.financial_year')
      ->join('ms_classes as c', 'c.id', '=', 'a.class_id')
      ->join('ms_sections as d', 'd.id', '=', 'a.section_id')
      ->join('ms_categories as e', 'e.id', '=', 'a.category_id')
      ->where('a.fy_id', $req->fyId)
      ->where('a.class_id', $req->classId)
      ->whereRaw('LOWER(a.full_name) LIKE ?', [strtolower("%$req->fullName%")])
      ->whereRaw('LOWER(a.father_name) LIKE ?', [strtolower("%$req->fatherName%")])
      ->where('a.status', 1)
      ->orderBy('a.id')
      ->first();
  }


  public static function csv($data)
  {

    // $value = DB::table('users')->where('username', $data['username'])->get();
    // if ($value->count() == 0) {
    DB::table('students')->insert($data);
    // }
  }

  /*Count all Active Records*/
  public function countActive()
  {
    return Student::where('status', 1)->count();
  }

  /*Count all Active Records*/
  public function countAll()
  {
    return Student::count();
  }

  public function feeCollections()
  {
    return $this->hasMany(FeeCollection::class, 'student_id', 'id');
  }
}
