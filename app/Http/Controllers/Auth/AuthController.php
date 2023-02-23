<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Requests\Auth\LoginRequest;


class AuthController extends Controller 
{   
    public function index(){
        return "hola";
    }
    
    public function register(Request $request){
        try {
            $rules= [
                'name' => 'required', 
                'email' => 'required|min:6|email',
                'password' => 'required|confirmed|min:6'
                    ];
            $messege=[
                'required'=>"El campo :attribute es requerido.",
                'min'=>"El campo :attribute requiere de mas caracteres.",
                'email'=>"En el campo :attribute no se ingreso un correo .",
                'confirmed'=>"El campo :attribute requiere confirmación"
                    ];
            $validator = Validator::make($request->all(), $rules, $messege);
            if ($validator->fails()){
                return $validator->errors();   
            }
            else{  
                if(User::where('email', $request->email)->exists()){
                return response()->json("El email ingresado ya esta en uso"); 
                }   
                else{
                    $user= User::create([
                        'name'=>$request->name,
                        'email'=>$request->email,
                        'password'=>$request->password
                    ]);
                    $token= JWTAuth::fromUSer($user);
                    return response()->json([
                        'user'=>$user,
                        'token'=>$token
                    ], 201);
                }
            }
        }catch( exception $e){}    
    }
    public function login(LoginRequest $request){
        $credencials = $request->only('email', 'password');
        $rules= ['email' => 'required|min:6|email',
                'password' => 'required|min:6'];
        
        $messege=['required'=>"El campo :attribute es requerido.",
                'min'=>"El campo :attribute requiere de mas caracteres.",
                'email'=>"En el campo :attribute no se ingreso un correo ."];
        $validator = Validator::make($request->all(), $rules, $messege);
        if ($validator->fails()){
            return $validator->errors();   
        }
        else{
            try{
                if($token = JWTAuth::attempt($credencials)){
                    return response()->json([
                            'error'=>'Crendenciales invalidas|Invalid credencials'
                    ],400);
                }
            }
            catch(JWTExection $e){
                return response()->json([
                    'error'=>'token no creado'
            ],500);
            } 
            return response()->json(compact('token'));
        }
    }
}

/*esta es la funcion que yo mismo hice para guardar el usuario
                    $user = new User();
                    $user->name=$request->name;
                    $user->email=$request->email;
                    $user->password=$request->password;
                    $user->save();
                    return response()->json("Registro realizado con exito."); */