<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Helpers\JwtAuth;
use App\Http\Requests;
use Illuminate\Support\Facades\DB;
use App\User;

class UserController extends Controller {

    public function register(Request $request){
        $json = $request->input('json', null);
        $params = json_decode($json);


        $email = (!is_null($json) && isset($params->email)) ? $params->email : null;
        $name = (!is_null($json) && isset($params->name)) ? $params->name : null;
        $surname = (!is_null($json) && isset($params->surname)) ? $params->surname : null;
        $role = 'ROLE_USER';
        $password= (!is_null($json) && isset($params->password)) ? $params->password : null;

        if(!is_null($email) && !is_null($name) && !is_null($password)){

            $user = new User();
            $user->email = $email;
            $user->name = $name;
            $user->surname = $surname;
            $user->role = $role;

            $pwd = hash('sha256',$password);
            $user->password = $pwd;

            //Check duplicated user
            $isset_user = User::where('email', '=', $email)->first();

            if(is_null($isset_user) || count($isset_user) == 0){
            //if(count($isset_user) == 0){
                //Save User
                $user->save();
                $data = array(
                    'status' =>'success',
                    'code'=>200,
                    'message' => 'User created correctly'
                );
            }else{
                //Don't save user
                $data = array(
                    'status' =>'error',
                    'code'=>400,
                    'message' => 'User not created'
                );
            }
        }else{
            $data = array(
                'status' =>'error',
                'code'=>400,
                'message' => 'User not created'
            );
        }
        return response()->json($data, 200);
    }

    public function login(Request $request){
        $jwtAuth = new JwtAuth();

        //Post Data
        $json = $request->input('json', null);
        $params = json_decode($json);

        $email = (!is_null($json) && isset($params->email)) ? $params->email : null ;
        $password = (!is_null($json) && isset($params->password)) ? $params->password : null ;
        $getToken = (!is_null($json) && isset($params->gettoken)) ? $params->gettoken : null ;

        //Encode the password
        $pwd = hash('sha256', $password);

        if(!is_null($email) && !is_null($password) && ($getToken==null || $getToken =='false')){
            $signup = $jwtAuth->signup($email, $pwd);

        }elseif ($getToken != null){
            $signup = $jwtAuth->signup($email, $pwd, $getToken);
        }else{
            $signup= array(
                'status' =>'error',
                'message' => 'Send your data by post'
            );
        }

        return response()->json($signup,200);
    }
}
