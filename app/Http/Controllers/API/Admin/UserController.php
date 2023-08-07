<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\Admin\User;
use App\Models\Admin\UserLog;
use Carbon\Carbon;
use Validator;
use DB;
use Exception;


/*
Created By : Lakshmi kumari 
Created On : 25-Jul-2023 
Code Status : Open 
*/

class UserController extends Controller
{
    private $_mUserLog;
    private $_mUsers;

    public function __construct()
    {
        DB::enableQueryLog();
        $this->_mUserLog = new UserLog();
        $this->_mUsers = new User();
    }

    /**
     * | Login for users 
     * | Description: Login of a user with sanctum token.
     */
    public function login(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'userName' => 'required',
            'password' => 'required'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            //check email existing or not
            $mUser = User::where('user_name', $req->userName)->first();
            $mDeviceId = $req->deviceId ?? "";
            if (!$mUser) {
                $msg = "Oops! Given username does not exist";
                return responseMsg(false, $msg, "");
            }

            // check if user deleted
            if ($mUser->is_deleted == 1) {
                $msg = "Cant logged in!! You Have Been Suspended or Deleted !";
                return responseMsg(false, $msg, "");
            }

            //check if user and password is existing   
            if ($mUser && Hash::check($req->password, $mUser->password)) {
                $token = $mUser->createToken('auth_token')->plainTextToken;
                $mUser->remember_token = $token;
                $mUser->save();
                $this->userLogIn($req, $token);
                $data1 = ['id' => $mUser->id, 'name' => $mUser->name, 'userName' => $mUser->user_name, 'email' => $mUser->email, 'token' => $token, 'token_type' => 'Bearer'];
                $queryTime = collect(DB::getQueryLog())->sum("time");
                return responseMsgsT(true, "Login successfully", $data1, "API_1.1", "", responseTime(), "POST", $mDeviceId, $token);
            } else
                throw new Exception("Password is incorrect");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "API_1.1", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    // Store logs
    public function userLogIn($req, $token)
    {
        try {
            $metaReqs = [
                'user_name' => $req->userName,
                'remember_token' => $token,
                'ip_address' => getClientIpAddress(),
                'version_no' => 0,
                'login_at' => Carbon::now()
            ];
            $metaReqs = array_merge($metaReqs, [
                'json_logs' => trim(json_encode($metaReqs), ",")
            ]);
            $this->_mUserLog->store($metaReqs);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "--", "", "", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "", responseTime(), "POST", $req->deviceId ?? "");
        }
    }

    /**
     * | Logout
     */
    public function logout()
    {
        try {
            $id = auth()->user()->id;
            $user = User::where('id', $id)->first();
            $userName = $user->user_name;
            $token = $user->remember_token;
            $this->userLogOut($userName, $token);
            // $user->remember_token = null;
            // $user->save();
            // $user->tokens()->delete();
            return responseMsgs(true, "Logged out successfully", "", "API_1.5", "", responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_1.5", responseTime(), "POST", "");
        }
    }

    // Store logs
    public function userLogOut($userName, $token)
    {
        try {
            $getData = UserLog::select('*')
                ->where('user_name', $userName)
                ->where('remember_token', $token)
                ->first();
            $metaReqs = [
                'version_no' => $getData->version_no + 1,
                'logout_at' => Carbon::now()
            ];
            $metaReqs = array_merge($metaReqs, [
                'json_logs' => trim($getData->json_logs . "," . json_encode($metaReqs), ",")
            ]);
            // print_var($metaReqs);
            // die;
            $getData->update($metaReqs);
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "--", "", "", $queryTime, responseTime(), "POST", "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "", responseTime(), "POST",  "");
        }
    }

    /**
     * | Registration for users 
     * | Description: This user will Admin or others and can create or view application activity.
     */
    // public function register(Request $req)
    // {
    //     //validation
    //     $validator = Validator::make($req->all(), [
    //         'name' => 'required|string|max:30',
    //         'email' => 'required|email|unique:users|max:100',
    //         'password' => 'required|string|max:30'
    //     ]);
    //     if ($validator->fails())
    //         return responseMsgs(false, $validator->errors(), []);
    //     try {
    //         $mObject = new User();
    //         $data = $mObject->insertData($req);
    //         // $data1 = ["name" => $req->name, "email" =>$req->email, "password" =>$req->password, "UserId"=>$data->$genUserID];
    //         return responseMsgs(true, "User Registration Done Successfully", [], "", "API-1.1", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API-1.1", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }

    /**
     * | View 
     */
    //show data by id
    public function showProfile(Request $req)
    {
        $validator = Validator::make($req->all(), [
            'id' => 'required|numeric',
            // 'token' => 'string|nullable'
        ]);
        if ($validator->fails())
            return responseMsgs(false, $validator->errors(), []);
        try {
            $show = $this->_mUsers->getGroupById($req);
            if (collect($show)->isEmpty())
                throw new Exception("Data Not Found");
            $queryTime = collect(DB::getQueryLog())->sum("time");
            return responseMsgsT(true, "View Records", $show, "API_1.2", $queryTime, responseTime(), "POST", $req->deviceId ?? "");
        } catch (Exception $e) {
            return responseMsgs(false, $e->getMessage(), [], "", "API_1.2", responseTime(), "POST", $req->deviceId ?? "");
        }
    }




    /**
     * | Edit 
     */
    // public function editProfile(Request $req)
    // {
    //     try {
    //         $mObject = new User();
    //         $data = $mObject->updateProfile($req);
    //         return responseMsgs(true, "Records updated successfully", $data, "", "API-1.4", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API-1.4", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }


    /**
     * | Change Password 
     */
    // public function changePassword(Request $req)
    // {
    //     //Description: Change password of authenticate user's using sanctum token
    //     try {
    //         $mObject = new User();
    //         $data = $mObject->updatePassword($req);
    //         return responseMsgs(true, "Password changed successfully", $data, "", "API-1.6", responseTime(), "POST", $req->deviceId ?? "");
    //     } catch (Exception $e) {
    //         return responseMsgs(false, $e->getMessage(), [], "", "API-1.6", responseTime(), "POST", $req->deviceId ?? "");
    //     }
    // }
}

//================================================= End User API ===========================================================
