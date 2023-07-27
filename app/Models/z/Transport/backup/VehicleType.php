<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VehicleType extends Model
{
    use HasFactory;
    protected $table = 'vehicle_types';
    protected $guarded = [];

    /*Add Records*/
    public function store(array $req)
    {
        VehicleType::create($req);
    }
    // Read Records by name
    public function readVehicleTypeGroup($vehiclesTypeName, $maxSeatingNo)
    {
        return VehicleType::where('vehicle_type_name', $vehiclesTypeName)
            ->where('max_seating_no', $maxSeatingNo)
            ->where('status', 1)
            ->get();
    }
}
