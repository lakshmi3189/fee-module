<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Vehicle extends Model
{
    use HasFactory;
    protected $table = 'vehicles';
    protected $guarded = [];

    /*Add Records*/
    public function store(array $req)
    {
        Vehicle::create($req);
    }
    //Read Records by name
    public function readVehicleGroup($vehicleNo, $registrationNo, $chasisNo)
    {
        return Vehicle::where('vehicle_no', $vehicleNo)
            ->where('registration_no', $registrationNo)
            ->where('chasis_no', $chasisNo)
            ->where('status', 1)
            ->get();
    }

    /*Read Records by ID*/
    public function getGroupById($id)
    {
        return DB::table('vehicles as a')
            ->select('a.*', 'b.vehicle_type_name', 'b.max_seating_no')
            ->join('vehicle_types as b', 'b.id', '=', 'a.vehicle_types_id')
            ->where('a.id', $id)
            ->first();
    }
    /*Read all Records by*/
    public function retrieveAll()
    {
        return DB::table('vehicles as a')
            ->select('a.*', 'b.vehicle_type_name', 'b.max_seating_no')
            ->join('vehicle_types as b', 'b.id', '=', 'a.vehicle_types_id')
            ->orderByDesc('a.id')
            ->get();
    }
}
