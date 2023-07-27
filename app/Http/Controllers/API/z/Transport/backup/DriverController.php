<?php

namespace App\Http\Controllers\API\Transport;

use App\Http\Controllers\Controller;
use App\Models\Transport\Driver;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DriverController extends Controller
{
    /**
     * | Created On-31-05-2023 
     * | Created By- Umesh Kumar
     * | Driver Crud Operations
    */

    private $_mDrivers;

    public function __construct()
    {
        $this->_mDrivers = new Driver();
    }
    // Add records
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'driverName' => 'required|string',
            'mobile' => 'required|numeric|digits:10|unique:drivers,mobile',
            'email' => 'required|string|unique:drivers,email',
            'address' => 'required|string',
            'licenseNo' => 'required|string|unique:drivers,license_no',
            'aadharNo' => 'required|string|unique:drivers,aadhar_no',
            'panNo' => 'string|unique:drivers,pan_no|nullable',
            'photoDoc' => 'image|mimes:jpeg,png,jpg|max:2048|nullable', // under process
            'aadharDoc' => 'mimes:pdf,doc,docx|max:2048|nullable', // under process
            'licenseDoc' => 'mimes:pdf,doc,docx|max:2048|nullable', // under process
            'panDoc' => 'mimes:pdf,doc,docx|max:2048|nullable', // under process
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));
            $isExists = $this->_mDrivers->readDriverGroup($req->email);
            if (collect($isExists)->isNotEmpty())
                throw new Exception("Driver Already existing");

            $metaReqs=[
                'driver_name' => Str::title($req->driverName),
                'mobile' => $req->mobile,
                'email' => $req->email,
                'address' => $req->address,
                'license_no' => $req->licenseNo,
                'aadhar_no' => $req->aadharNo,
                'pan_no' => $req->panNo,
                'photo_doc' => $req->photoDoc,
                'aadhar_doc' => $req->aadharDoc,
                'license_doc' => $req->licenseDoc,
                'pan_doc' => $req->panDoc,
                'academic_year' => $fy,
                'school_id' => authUser()->school_id,
                'created_by' => authUser()->id,
                'ip_address' => getClientIpAddress()
            ];
            $this->_mDrivers->store($metaReqs);
            return responseMsgs(true, "Successfully Saved", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }


    // Edit records
    public function edit(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'driverName' => 'required|string',
            'mobile' => 'required|numeric|digits:10|unique:drivers,mobile',
            'email' => 'required|string|unique:drivers,email',
            'address' => 'required|string',
            'licenseNo' => 'required|string|unique:drivers,license_no',
            'aadharNo' => 'required|string|unique:drivers,aadhar_no',
            'panNo' => 'string|unique:drivers,pan_no|nullable',
            'photoDoc' => 'image|mimes:jpeg,png,jpg|max:2048|nullable', // under process
            'aadharDoc' => 'mimes:pdf,doc,docx|max:2048|nullable', // under process
            'licenseDoc' => 'mimes:pdf,doc,docx|max:2048|nullable', // under process
            'panDoc' => 'mimes:pdf,doc,docx|max:2048|nullable', // under process
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $isExists = $this->_mDrivers->readDriverGroup($req->licenseNo);
            if ($isExists && $isExists->where('id', '!=', $req->id)->isNotEmpty())
                throw new Exception("Driver Already existing");

            $getData = $this->_mDrivers::findOrFail($req->id);            
            $metaReqs = [ 
                'driver_name' => Str::title($req->driverName),
                'mobile' => $req->mobile,
                'email' => $req->email,
                'address' => $req->address,
                'license_no' => $req->licenseNo,
                'aadhar_no' => $req->aadharNo,
                'pan_no' => $req->panNo,
                'photo_doc' => $req->photoDoc,
                'aadhar_doc' => $req->aadharDoc,
                'license_doc' => $req->licenseDoc,
                'pan_doc' => $req->panDoc,
                'version_no' => $getData->version_no + 1,
                'updated_at' => Carbon::now()
            ];

            if (isset($req->status)) {                  // In Case of Deactivation or Activation 
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs = array_merge($metaReqs, [
                    'status' => $status
                ]);
            }

            $department = $this->_mDrivers::findOrFail($req->id);
            $department->update($metaReqs);
            return responseMsgs(true, "Successfully Updated", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //View by id
    public function show(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $department = $this->_mDrivers::findOrFail($req->id);
            return responseMsgs(true, "", $department, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //View All
    public function retrieveAll(Request $req)
    {
        try {
            $department = $this->_mDrivers::orderByDesc('id')->where('status','1')->get();
            return responseMsgs(true, "", $department, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    //Delete
    public function delete(Request $req)
    {
        try { 
            $metaReqs = [
                'status' => $req->status
            ];
        $department = $this->_mDrivers::findOrFail($req->id);
        if($department->status==0)
            throw new Exception("Records Already Deleted");
        $department->update($metaReqs);
        return responseMsgs(true, "Deleted Successfully", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
