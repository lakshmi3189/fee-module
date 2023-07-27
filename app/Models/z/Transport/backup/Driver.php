<?php

namespace App\Models\Transport;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;
    protected $guarded=[];

    /*Add Records*/
    public function store(array $req){
      Driver::create($req);
    }

    /*Read Records by name*/
    public function readDriverGroup($license)
    {
        return Driver::where('license_no', $license)
            ->where('status', 1)
            ->get();
    }

    /*Read Records by ID*/
    // public function readDriverById($id)
    // {
    //     return Driver::where('id', $id)
    //         ->where('status', 1)
    //         ->firstOrFail();
    // }
}
