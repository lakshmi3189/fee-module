<?php

namespace App\Http\Controllers\API\Student;

use App\Http\Controllers\Controller;
use App\Models\Master\MsCategory;
use App\Models\Master\MsClass;
use App\Models\Master\MsSection;
use App\Models\Master\MsMonth;
use App\Models\Master\MsFinancialYear;
use Illuminate\Http\Request;
use App\Models\Student\Student;
use App\Models\Master\ReceiptCounter;
use App\Models\FeeStructure\FeeCollection;
use Illuminate\Support\Str;
use Exception;
use Validator;
use DB;

/*
Created By : Umesh Kumar 
Created On : 25-July-2023 
Code Status : Open 
Description : For Uploading CSV file 
*/

class StudentController extends Controller
{
    private $_mStudents;
    private $_mReceiptCounters;
    private $_mMsMonths;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mStudents = new Student();
        $this->_mMsMonths = new MsMonth();
        $this->_mReceiptCounters = new ReceiptCounter();
    }

    /**
     * |add bulk data using csv
     */
    public function storeCSV(Request $req)
    {
        //validation
        $validator = Validator::make($req->all(), [
            'uploadCSV' => 'required|mimes:csv|max:2048'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {

            $file = $req->file('uploadCSV');
            $filename = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $tempPath = $file->getRealPath();
            $fileSize = $file->getSize();
            $mimeType = $file->getMimeType();
            // Valid File Extensions
            $valid_extension = array("csv");

            // 2MB in Bytes
            $maxFileSize = 2097152;

            // Check file extension
            if (in_array(strtolower($extension), $valid_extension)) {
                // Check file size
                if ($fileSize <= $maxFileSize) {
                    // File upload location
                    $location = 'uploads';
                    // Upload file
                    $file->move($location, $filename);
                    // Import CSV to Database
                    $filepath = public_path($location . "/" . $filename);

                    // Reading file
                    $file = fopen($filepath, "r");
                    $importData_arr = array();
                    $i = 0;
                    while (($filedata = fgetcsv($file, 1000, ",")) !== FALSE) {
                        $num = count($filedata);
                        // Skip first row (Remove below comment if you want to skip the first row)
                        if ($i == 0) {
                            $i++;
                            continue;
                        }
                        for ($c = 0; $c < $num; $c++) {
                            $importData_arr[$i][] = $filedata[$c];
                        }
                        $i++;
                    }
                    fclose($file);

                    foreach ($importData_arr as $data) {

                        // getting class id...
                        $className = $data[3];
                        $classObj = MsClass::where('class_name', $className)->firstOrFail();
                        $classId = $classObj->id;

                        // getting section id...
                        $sectionName = $data[4];
                        $sectionObj = MsSection::where('section', $sectionName)->firstOrFail();
                        $sectionId = $sectionObj->id;

                        // getting Category id...
                        $categoryName = $data[11];
                        $categoryObj = MsCategory::where('category_name', $categoryName)->firstOrFail();
                        $categoryId = $categoryObj->id;

                        //getting fy id..
                        $fyName = $data[12];
                        $fyObj = MsFinancialYear::where('financial_year', $fyName)->firstOrFail();
                        $fyId = $fyObj->id;

                        // getting gender id...
                        $genderId = null;
                        $genderName = $data[9];
                        if ($genderName == 'male' || $genderName == 'Male' || $genderName == 'MALE') {
                            $genderId = 1;
                        } elseif ($genderName == 'female' || $genderName == 'Female' || $genderName == 'FEMALE') {
                            $genderId = 2;
                        } else {
                            $genderId = 3;
                        }

                        //getting mid session data
                        $midSession = null;
                        $midSession = $data[16];
                        if ($midSession == 'yes' || $midSession == 'Yes' || $midSession == 'YES') {
                            $midSession = 1;
                        } else {
                            $midSession = 0;
                        }

                        // getting month id...
                        $monthName = $data[17];
                        $monthNameObj = MsMonth::where('month_name', $monthName)->firstOrFail();
                        $monthId = $monthNameObj->id;



                        // $disabilityId = null;
                        // $disability = $data[10];
                        // $data[10] == 'no' ? 0 : 1
                        // if ($disability == 'yes' || $disability == 'Yes' || $disability == 'YES') {
                        //     $disabilityId = 1;
                        // }  else {
                        //     $disabilityId = 0;
                        // }

                        $insertData = array(
                            'admission_date' => $data[0],
                            'roll_no' => $data[1],
                            'full_name' => $data[2],
                            'class_id' => $classId,
                            'class_name' => $data[3],
                            'section_id' => $sectionId,
                            'section_name' => $data[4],
                            'dob' => $data[5],
                            'admission_no' => Str::title($data[6]),
                            'gender_id' => $genderId,
                            'gender_name' => $data[9],
                            'email' => $data[7],
                            'mobile' => $data[8],
                            'disability' =>  $data[10] == 'yes' ? 1 : 0,
                            'category_id' => $categoryId,
                            'category_name' => $data[11],
                            'fy_id' => $fyId,
                            'financial_year' => $data[12],
                            'is_parent_staff' => $data[13] == 'yes' ? 1 : 0,
                            'created_by' => 1,
                            'ip_address' => getClientIpAddress(),
                            'version_no' => 1,
                            'status' => $data[14] == 'active' ? 1 : 0,
                            'father_name' => $data[15],
                            'is_mid_session' => $midSession,
                            'month_id' => $monthId,
                        );

                        $insertData = array_merge($insertData, [
                            'json_logs' => trim(json_encode($insertData), ",")
                        ]);
                        // Check for duplicate data before inserting
                        $duplicateData = Student::where('admission_no', $data[6])->first();
                        if (!$duplicateData) {
                            Student::create($insertData);
                            // $this->_mStudents->csv($insertData);
                        }
                    }
                }
            }
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Uploaded Successfully", [], "API_3.1", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_3.1", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Retrieve All
     */

    public function retrieveAll(Request $req)
    {
        try {
            $getData = $this->_mStudents->retrieve();
            $perPage = $req->perPage ? $req->perPage : 10;
            $paginater = $getData->paginate($perPage);
            // if ($paginater == "")
            //     throw new Exception("Data Not Found");
            $list = [
                "current_page" => $paginater->currentPage(),
                "perPage" => $perPage,
                "last_page" => $paginater->lastPage(),
                "data" => $paginater->items(),
                "total" => $paginater->total()
            ];
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View All Records", $list, "API_3.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_3.2", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | show data by id
     */
    public function showStudentByClassAndAdmissionNo(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId'        => 'required',
            'classId'     => 'required',
            'searchId'     => 'required',
            // 'admissionNo' => 'required',
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            // print_var($req->all());
            // die;
            if ($req->searchId == "admission" && $req->admissionNo != "") {
                $show = $this->_mStudents->getStudentByClassAndAdmissionNo($req);
                // print_var($show);
                // die;
            } elseif ($req->searchId == "name" && $req->fatherName != "") {
                $show = $this->_mStudents->getStudentByClassIdAndNameAndFatherName($req);
            } else {
                if ($req->searchId == "admission") {
                    throw new Exception("Please Enter Admission No.");
                }
                if ($req->searchId == "name") {
                    throw new Exception("Please Enter Name & Father Name");
                }
            }

            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $show, "API_3.3", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_3.3", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Add 
     */
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId' => 'required',
            'monthId' => 'required|numeric',
            'rollNo' => 'required|numeric',
            'admissionDate' => 'required|date',
            'dob' => 'required|date',
            'fullName'     => 'required|string',
            'fatherName'     => 'required|string',
            'email'     => 'string|nullable',
            'mobile' => 'required|numeric',
            'admissionNo'  => 'required|string',
            'classId'    => 'required|string',
            'sectionId'  => 'required|string',
            'gender'   => 'required|string',
            'specialAbility'  => 'required',
            'isMidSession'  => 'required|numeric',
            'quotaId' => 'required'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $isExists = $this->_mStudents->readStudentGroup($req);
            if (collect($isExists)->isNotEmpty())
                throw new Exception("Admissio No & Financial Year Already Existing");

            //getting class name...
            $classObj = MsClass::where('id', $req->classId)->firstOrFail();
            $className = $classObj->class_name;

            // getting section id...
            $sectionObj = MsSection::where('id', $req->sectionId)->firstOrFail();
            $sectionName = $sectionObj->section;

            // getting Category id...
            $categoryObj = MsCategory::where('id', $req->categoryId)->firstOrFail();
            $categoryName = $categoryObj->category_name;

            //getting fy id..
            $fyObj = MsFinancialYear::where('id', $req->fyId)->firstOrFail();
            $fyName = $fyObj->financial_year;

            // getting gender id...
            $genderName = null;
            $genderId = $req->gender;
            if ($genderId == 1) {
                $genderName = 'Male';
            } elseif ($genderId == 2) {
                $genderName = 'female';
            } else {
                $genderName = 'Others';
            }

            $metaReqs = array(
                'admission_date' => $req->admissionDate,
                'full_name' => $req->fullName,
                'father_name' => $req->fatherName,
                'class_id' => $req->classId,
                'class_name' => $className,
                'roll_no' => $req->rollNo,
                'dob' => $req->dob,
                'email' => $req->email,
                'mobile' => $req->mobile,
                'section_id' => $req->sectionId,
                'section_name' => $sectionName,
                'admission_no' => Str::title($req->admissionNo),
                'gender_id' => $req->gender,
                'is_mid_session' => $req->isMidSession,
                'gender_name' => $genderName,
                'disability' => $req->specialAbility,
                'category_id' => $req->categoryId,
                'month_id' => $req->monthId,
                'category_name' => $categoryName,
                'financial_year' => $fyName,
                'fy_id' => $req->fyId,
                'is_parent_staff' => $req->quotaId == 'yes' ? 1 : 0,
                'created_by' => 1,
                'ip_address' => getClientIpAddress(),
            );
            $metaReqs = array_merge($metaReqs, [
                'json_logs' => trim(json_encode($metaReqs), ",")
            ]);
            // dd($metaReqs);
            $data = ['Full Name' => $req->fullName];
            $this->_mStudents->store($metaReqs);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Student Registration Done Successfully", $data, "API_3.4", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_3.4", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function countActiveStudent(Request $req)
    {
        try {
            $rowCount = $this->_mStudents->countActive();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Total Count of All Active Students", $rowCount, "API_3.5", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_3.5", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function countAllStudent(Request $req)
    {
        try {
            $rowCount = $this->_mStudents->countAll();
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "Total Students", $rowCount, "API_3.6", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_3.6", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function showStudentFeesHistory(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'fyId' => 'required|numeric',
            'classId'    => 'required|numeric',
            'searchId'     => 'required'
            // 'admissionNo'  => 'required|string',
            // 'fullName'  => 'string',
            // 'fatherName'  => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            // Fetch the student details based on the provided admission number
            // $student = Student::where(DB::raw('upper(admission_no)'), strtoupper($req->admissionNo))->first();
            if ($req->searchId == "admission" && $req->admissionNo != "") {
                $student = $this->_mStudents->getStudentByClassAndAdmissionNo($req);
            } elseif ($req->searchId == "name" && $req->fatherName != "") {
                $student = $this->_mStudents->getStudentByClassIdAndNameAndFatherName($req);
            } else {
                if ($req->searchId == "admission") {
                    throw new Exception("Please Enter Admission No.");
                }
                if ($req->searchId == "name") {
                    throw new Exception("Please Enter Name & Father Name");
                }
            }

            if (!$student)
                throw new Exception("Student Not Found");

            // Fetch the data from the database based on the provided input
            $data = FeeCollection::where('fy_id', $req->fyId)
                ->where('class_id', $req->classId)
                ->where('student_id', $student->id)
                ->orWhere(DB::raw('upper(admission_no)'), strtoupper($req->admissionNo))
                ->get();

            if ($data->isEmpty())
                throw new Exception("Fees History Not Found");

            // Initialize an empty array to store the formatted data
            $formattedData = [];

            // Group the fee collection data by 'month_id'
            $groupedData = $data->groupBy('month_id');

            foreach ($groupedData as $monthId => $collection) {
                // Get the month name based on the month_id (assuming month names are static)
                $monthName = $this->getMonthName($monthId);

                // Initialize an empty array to store the fee details for each month
                $feeDetails = [];

                foreach ($collection as $record) {
                    $feeDetails[] = [
                        'feeCollId' => $record->id,
                        'stdId' => $student->id,
                        'feeHeadName' => $record->fee_head_name,
                        'amount' => $record->fee_amount,
                        'receivedAmount' => $record->received_amount,
                        'dueAmount' => $record->due_amount,
                        'color' => $record->due_amount > 0 ? '#fff1f2' : '',
                    ];
                }

                // Add the formatted data for each month to the main array
                $formattedData[] = [
                    'monthName' => $monthName,
                    'monthId' => $monthId,
                    'fyId' => $req->fyId,
                    'classId' => $req->classId,
                    'admission_no' => $req->admissionNo,
                    'feeDtl' => $feeDetails,
                ];
            }

            //count query execution time
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Fee History", $formattedData, "API_3.7", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_3.7", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // Helper function to get the month name based on month_id
    private function getMonthName($monthId)
    {
        $months = $this->_mMsMonths->getMonthList();
        return $months[$monthId] ?? 'Unknown';
    }
}
