<?php

namespace App\Http\Controllers\API\Transport;

use App\Models\Transport\VehicleType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class VehicleTypeController extends Controller
{
    //global variable
    private $_mVehicleTypes;

    public function __construct()
    {
        $this->_mVehicleTypes = new VehicleType();
    }
    // Add records
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'vehiclesTypeName' => 'required|string',
            'maxSeatingNo' => 'required|integer'

        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {

            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));

            $isGroupExists = $this->_mVehicleTypes->readVehicleTypeGroup(Str::ucfirst($req->vehiclesTypeName), $req->maxSeatingNo);

            if (collect($isGroupExists)->isNotEmpty())
                throw new Exception("Vehicle Type Name Already Existing");
            $metaReqs = [
                'vehicle_type_name' => Str::ucfirst($req->vehiclesTypeName),
                'max_seating_no' => $req->maxSeatingNo,
                'academic_year' => $fy,
                'school_id' => authUser()->school_id,
                'created_by' => authUser()->id,
                'ip_address' => getClientIpAddress()
            ];
            // print_r($metaReqs);die;
            $this->_mVehicleTypes->store($metaReqs);
            return responseMsgs(true, "Successfully Saved", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
    // Edit records
    public function edit(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'vehiclesTypeName' => 'required|string',
            'maxSeatingNo' => 'required|integer'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));

            /* $isGroupExists = $this->_mVehicleType->readBankGroup(Str::ucfirst($req->bankName));

           if (collect($isGroupExists)->isNotEmpty())
               throw new Exception("Bank Name Already Existing");*/
            $getData = $this->_mVehicleTypes::findOrFail($req->id);
            $metaReqs = [
                'vehicle_type_name' => Str::ucfirst($req->vehiclesTypeName),
                'max_seating_no' => $req->maxSeatingNo,
                'version_no' => $getData->version_no + 1,
                'updated_at' => Carbon::now()
            ];
            if (isset($req->status)) {                  // In Case of Deactivation or Activation
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs = array_merge($metaReqs, [
                    'status' => $status
                ]);
            }
            $vehicleTypes = $this->_mVehicleTypes::findOrFail($req->id);
            $vehicleTypes->update($metaReqs);
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
            $vehicleTypes = $this->_mVehicleTypes::findOrFail($req->id);
            return responseMsgs(true, "", $vehicleTypes, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
    //View All
    public function retrieveAll(Request $req)
    {
        try {
            $vehicleTypes = $this->_mVehicleTypes::orderByDesc('id')->where('status', '1')->get();
            return responseMsgs(true, "", $vehicleTypes, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
    public function delete(Request $req)
    {
        try {
            if (isset($req->status)) {                  // In Case of Deactivation or Activation
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs =  [
                    'status' => $status
                ];
            }
            $vehicleTypes = $this->_mVehicleTypes::findOrFail($req->id);
            if ($vehicleTypes->status == 0)
                throw new Exception("Records Already Deleted");
            $vehicleTypes->update($metaReqs);
            return responseMsgs(true, "Deleted Successfully", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
