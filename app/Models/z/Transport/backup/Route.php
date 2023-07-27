<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use App\Http\Traits\CustomTraits;
use Illuminate\Support\Carbon;
use DB;
use Exception;

class Route extends Model
{
    use HasFactory;
    protected $fillable = [
        'route_name'
    ];

    //insert
    public function insertData($req) {      
        $mObject = new Route();
        $routeName = Str::ucFirst($req->routeName);
        $ip = getClientIpAddress();
        $createdBy = 'Admin';
        $schoolId = '123';
        $insert = [
          $mObject->school_id = $schoolId,
          $mObject->route_name   = Str::ucFirst($req['routeName']),
          $mObject->created_by = $createdBy,
          $mObject->ip_address = $ip
        ]; 
        // print_r($insert); die; 
        $checkExist = Route::where([['route_name','=',$routeName],['is_deleted','=','0']])->count(); 
        $checkDeleted = Route::where([['route_name','=',$routeName],['is_deleted','=','1']])->count();
        // print_r($checkDeleted); die; 
        if($checkExist > 0){
          throw new Exception("Route name is already existing!");
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
        $viewAll = Route::select(DB::raw("
        id, route_name,created_by,
        (CASE 
        WHEN is_deleted = '0' THEN 'Active' 
        WHEN is_deleted = '1' THEN 'Not Active'
        END) AS status, 
        TO_CHAR(created_at::date,'dd-mm-yyyy') as date,
        TO_CHAR(created_at,'HH12:MI:SS AM') as time
        "))
        ->where('is_deleted',0)
        ->orderBy('route_name','asc')
        ->get();

        foreach ($viewAll as $value) {
            $dataArr['id'] = $value->id;
            $dataArr['routeName'] = $value->route_name;
            $dataArr['date'] = $value->date;
            $dataArr['time'] = $value->time;
            $dataArr['status'] = $value->status;
            $getArr[]=$dataArr; 
        } 
        return $getArr;
    }
  
    //view by id
    public function listById($req) {
        $data = Route::where('id', $req->id)->first();
        return $data;     
    }   
  
    //update
    public function updateData($req) {
        $data = Route::find($req->id);
        // $getVersion  = $data->version_no; 
        // $incVersion =  number_format($getVersion + 1) ;
        $id = $req->id;
        $routeName = $req->routeName;
        if (!$data)
                throw new Exception("Records Not Found!");
        $edit = [
            'route_name' => str::ucFirst($req->routeName),
            'updated_at' => Carbon::now(),
            'version_no' => '1'
        ];
        // //validation 
        // $checkExist = FeeHead::where([['fee_head_name','=',$feeHeadName],['is_deleted','=','0']])->count(); 
        // if($checkExist > 0){
        //     throw new Exception("Fee Head name is already existing!");
        // }
        // if(FeeHead::where('id',$id)->exists()){
        //     $data->update($edit);
        // }      
        $data->update($edit);
        return $edit;        
    }
  
    //delete 
    public function deleteData($req) {
        $data = Route::find($req->id);
        if ((!$data)||($data->is_deleted == "1"))
            throw new Exception("Records Not Found!");
        $data->is_deleted = "1";
        $data->save();
        return $data; 
    }
  
    // //truncate
    // public function truncateData() {
    //     // $data = FeeHead::truncate();
    //     // return $data;        
    // }
}
