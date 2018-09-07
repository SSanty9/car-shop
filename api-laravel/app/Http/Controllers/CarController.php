<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Helpers\JwtAuth;
use App\Car;


class CarController extends Controller
{
    public function index(){
        $cars = Car::all();
        return response()->json(array(
            'cars' => $cars,
            'status' =>'success'
        ),200);
    }

    public function show($id){
        $car = Car::find($id);
        if(is_object($car)){
            $car = Car::find($id)->load('user');

            return response()->json(array(
                'car'=>$car,
                'status'=>'success'
            ),200);
        }else{
            return response()->json(array(
                'message'=>'The car doesn\'t exist',
                'status'=>'error'
            ),200);
        }


    }

    public function store (Request $request){
        $hash =  $request->header('Authorization', null);
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Gather data by post
            $json = $request->input('json', null);
            $params = json_decode($json);
            $params_array = json_decode($json, true);

            //Save the car
            $user = $jwtAuth->checkToken($hash, true);

            //Validation
            $validated = \Validator::make($params_array,[
                'title' => 'required|min:5',
                'description' => 'required',
                'price' => 'required',
                'status' => 'required'
            ]);

            if($validated->fails()){
                return response()->json($validated->errors(),400);
            }

            $car = new Car();
            $car->user_id = $user->sub;
            $car->title = $params->title;
            $car->description = $params->description;
            $car->price = $params->price;
            $car->status = $params->status;

            $car->save();

            $data = array(
                'car' =>$car,
                'status' => 'success',
                'code' =>200
            );

        }else{
            //Return error
            $data = array(
                'message' =>'Login incorrect',
                'status' => 'error',
                'code' =>300
            );
        }
        return response()->json($data,200);
    }

    public function update($id, Request $request){
        $hash =  $request->header('Authorization', null);
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Gather data by post
            $json = $request->input('json',null);
            $params = json_decode($json);

            $params_array = json_decode($json, true);

            //Validation
            $validated = \Validator::make($params_array,[
                'title' => 'required|min:5',
                'description' => 'required',
                'price' => 'required',
                'status' => 'required'
            ]);
            if($validated->fails()){
                return response()->json($validated->errors(),400);
            }

            //Unset params which I don't need
            unset($params_array['id']);
            unset($params_array['user_id']);
            unset($params_array['created_at']);
            unset($params_array['user']);
            //Update Car
            $car = Car::where('id',$id)->update($params_array);

            //Return response
            $data = array(
                'car' => $params,
                'status'=>'success',
                'code' => 200
            );

        }else{
            //Return error
            $data = array(
                'message' =>'Incorrect Login incorrect',
                'status' => 'error',
                'code' =>300
            );
        }
        return response()->json($data,200);
    }

    public function destroy($id, Request $request){
        $hash = $request->header('Authorization',null);
        $jwtAuth = new JwtAuth();
        $checkToken = $jwtAuth->checkToken($hash);

        if($checkToken){
            //Check if exist the car
            $car = Car::find($id);

            //Delete Car
            $car->delete();

            //Return response
            $data = array(
                'car'=>$car,
                'status'=> 'success',
                'code' => 200
            );

        }else{
            $data = array(
                'status'=> 'error',
                'code' => 400,
                'message' =>'Incorrect Login'
            );
        }

        return response()->json($data, 200);
    }
}
