<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Carbon;
use DB;

class UserLog extends Model
{
    use HasFactory;
    protected $guarded = [];

    /*Add Records*/
    public function store(array $req)
    {
        UserLog::create($req);
    }

    // //change password
    // public function updateLog($userName, $token)
    // {
    //     $data = UserLog::select('id', 'user_name', 'remember_token')
    //         ->where('user_name', $userName)
    //         ->where('remember_token', $token)
    //         ->first();

    //     if ($data->remember_token != "") {
    //         $edit = [
    //             'logout_at' => Carbon::now()
    //         ];
    //         $data->update($edit);
    //         return $data;
    //     }
    //     return false;
    // }
}
