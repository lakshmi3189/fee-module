<?php

namespace App\Models\Transport;

use Carbon\Carbon;
use DB;
use Exception;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Exists;

class TmPickupPoint extends Model
{
    use HasFactory;
    protected $fillable = [
        'pickup_point_name',
        'pickup_point_address'
    ];

    //insert
    public function insertData($req) {      
        $mObject = new TmPickupPoint();
        $ip = getClientIpAddress();
        $createdBy = 'Admin';
        $schoolId = '123';
        $pickupPointName = Str::ucFirst($req->pickupPointName);
        $pickupPointAddress = Str::ucFirst($req->pickupPointAddress);
        $insert = [
          $mObject->pickup_point_name = str::ucFirst($req['pickupPointName']),
          $mObject->pickup_point_address = str::ucFirst($req['pickupPointAddress']),
          $mObject->ip_address = $ip,
          $mObject->created_by = $createdBy,
          $mObject->school_id = $schoolId,
          $mObject->academic_year = $req['adacemicYear'],

        ];         
        $checkExist = TmPickupPoint::where([['pickup_point_address','=',$pickupPointAddress],['pickup_point_name','=',$pickupPointName],['is_deleted','=','0']])->count(); 
        $checkDeleted = TmPickupPoint::where([['pickup_point_address','=',$pickupPointAddress],['pickup_point_name','=',$pickupPointName],['is_deleted','=','1']])->count();

        if($checkExist > 0){
          throw new Exception("PickUp Point name is already existing on this address !");
        }
        if($checkDeleted >= 0){
            $mObject->save($insert);
        }
        return $mObject;
    }

      
    //view all 
    public static function list() {
        // $viewAll = FeeHead::select('id','class_name')
        $getArr = array();
        $viewAll = TmPickupPoint::select(DB::raw("
        id, pickup_point_name, pickup_point_address,created_by,
        (CASE 
        WHEN is_deleted = '0' THEN 'Active' 
        WHEN is_deleted = '1' THEN 'Not Active'
        END) AS status, 
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time
        "))
        ->where('is_deleted',0)
        ->orderBy('id','asc')
        ->get();

        foreach ($viewAll as $value) {
            $dataArr['id'] = $value->id;
            $dataArr['pickupPointName'] = $value->pickup_point_name;
            $dataArr['pickupPointAddress'] = $value->pickup_point_address;
            $dataArr['date'] = $value->date;
            $dataArr['time'] = $value->time;
            $dataArr['status'] = $value->status;
            $getArr[]=$dataArr; 
        } 
        return $getArr;
    }
  
    //view by id
    public function listById($req) {
        $data = TmPickupPoint::where('id', $req->id)->first();
        return $data;     
    }   
  
    //update
    public function updateData($req) {
        $id = $req->id;
        
        $data = TmPickupPoint::where('is_deleted',0)->find($id);
        $getVersion  = $data->version_no; 
        $incVersion =  number_format($getVersion + 1) ;
        $pickupPointName = Str::ucFirst($req->pickupPointName);
        $pickupPointAddress = Str::ucFirst($req->pickupPointAddress);
        if (!$data)
            throw new Exception("Records Not Found!");

        $checkExist = TmPickupPoint::where([['pickup_point_address','=',$pickupPointAddress],['pickup_point_name','=',$pickupPointName],['is_deleted','=','0']])->count(); 

        if($checkExist > 0){
            throw new Exception("PickUp Point name is already existing on this address !");
        }
        $edit = [
            'pickup_point_name' => str::ucFirst($pickupPointName),
            'pickup_point_address' => str::ucFirst($pickupPointAddress),
            'updated_at' => Carbon::now(),
            'version_no' => $incVersion,
            
        ];
        if(TmPickupPoint::where('id',$id)->exists()){
            $data->update($edit);
        }
        return $data;        
    }
  
    //delete 
    public function deleteData($req) {
        $data = TmPickupPoint::find($req->id);
        if ((!$data)||($data->is_deleted == "1"))
            throw new Exception("Records Not Found!");
        $data->is_deleted = "1";
        $data->save();
        return $data; 
    }
  
    //truncate
    // public function truncateData() {
    //     $data = TmPickupPoint::truncate();
    //     return $data;        
    // }
 
}
