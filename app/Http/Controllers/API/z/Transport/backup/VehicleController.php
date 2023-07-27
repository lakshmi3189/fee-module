<?php

namespace App\Http\Controllers\API\Transport;

use App\Models\Transport\Vehicle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class VehicleController extends Controller
{
    //global variable
    private $_mVehicles;

    public function __construct()
    {
        $this->_mVehicles = new Vehicle();
    }
    // Add records
    public function store(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'vehicleNo' => 'required|string',
            'registrationNo' => 'required|string',
            'chasisNo' => 'required|string',
            'vehiclesTypesId' => 'required|string'

        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {

            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));

            $isGroupExists = $this->_mVehicles->readVehicleGroup(Str::uppert($req->vehicleNo), $req->registrationNo, $req->chasisNo);

            if (collect($isGroupExists)->isNotEmpty())
                throw new Exception("Vehicle number, Registration Number, chasis Number Already Existing");
            $metaReqs = [
                'vehicle_no' => Str::upper($req->vehicleNo),
                'registration_no' => $req->registrationNo,
                'chasis_no' => $req->chasisNo,
                'vehicles_types_id' => $req->vehiclesTypesId,
                'academic_year' => $fy,
                'school_id' => authUser()->school_id,
                'created_by' => authUser()->id,
                'ip_address' => getClientIpAddress()
            ];
            // print_r($metaReqs);die;
            $this->_mVehicles->store($metaReqs);
            return responseMsgs(true, "Successfully Saved", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
    // Edit records
    public function edit(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'vehicleNo' => 'required|string',
            'registrationNo' => 'required|string',
            'chasisNo' => 'required|string',
            'vehiclesTypesId' => 'required|string'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);

        try {
            $fy =  getFinancialYear(Carbon::now()->format('Y-m-d'));

            $isGroupExists = $this->_mVehicles->readVehicleGroup(Str::upper($req->vehicleNo), $req->registrationNo, $req->chasisNo);

            if (collect($isGroupExists)->isNotEmpty())
                throw new Exception("Vehicle number, Registration Number, chasis Number Already Existing");
            $getData = $this->_mVehicles::findOrFail($req->id);
            $metaReqs = [
                'vehicle_no' => Str::upper($req->vehicleNo),
                'registration_no' => $req->registrationNo,
                'chasis_no' => $req->chasisNo,
                'vehicles_types_id' => $req->vehiclesTypesId,
                'version_no' => $getData->version_no + 1,
                'updated_at' => Carbon::now()
            ];
            if (isset($req->status)) {                  // In Case of Deactivation or Activation
                $status = $req->status == 'deactive' ? 0 : 1;
                $metaReqs = array_merge($metaReqs, [
                    'status' => $status
                ]);
            }
            $vehicles = $this->_mVehicles::findOrFail($req->id);
            $vehicles->update($metaReqs);
            return responseMsgs(true, "Successfully Updated", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
    /**
     * | Get Discont Group By Id
     */
    public function show(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            // $feeHead = $this->_mFeeHeads::findOrFail($req->id);
            $vehicles = $this->_mVehicles->getGroupById($req->id);
            // print_r($feeHead);die;
            return responseMsgs(true, "", $vehicles, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    public function retrieveAll(Request $req)
    {
        try {
            $vehicles = $this->_mVehicles->retrieveAll();
            return responseMsgs(true, "", $vehicles, "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
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
            $vehicles = $this->_mVehicles::findOrFail($req->id);
            if ($vehicles->status == 0)
                throw new Exception("Records Already Deleted");
            $vehicles->update($metaReqs);
            return responseMsgs(true, "Deleted Successfully", [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "1.0", responseTime(), "POST", $req->deviceId ?? "");
        }
    }
}
