<?php

namespace app\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use \Auth;

class LoginController extends Controller
{

    use AuthenticatesUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest', ['except' => 'logout']);
    }

    public function login(Request $request)
    {
        $this->validate($request, [
            'email'     => 'required',
            'password'  => 'required',
            'recaptcha' => 'required|captcha',
        ]);

        if (Auth::once($request->except(['_token', 'recaptcha']))) {
            Auth::user()->updateToken()->save();

            return response()->json(['token' => Auth::user()->api_token]);
        }

        return response()
            ->json([
                'message' => 'Credenciales no validas',
                'errors' => [
                    'email' => ['Credenciales no validas']
                ]
            ], 422);
    }
}
