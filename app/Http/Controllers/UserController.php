<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{

    public function loginPage() {

        return view('auth.login');
    }

    public function registerPage() {
        return view('auth.register');
    }

    public function logout() {
        Auth::logout();
        return to_route('auth.login');
    }

    public function doLogin(Request $request) {

        $credentials = $request->validate([
            'email' => 'email|required',
            'password' => 'min:3|max:10|required'
        ]);
        if(Auth::attempt($credentials)){
            $request->session()->regenerate();
            $user = Auth::user();
            return redirect()->intended(route('home'));
        }
        return to_route('auth.login')->withErrors([
            'error' => 'There\'s an error in your authentication account. Please verify your account and try again.'
        ]);
    }

    public function doRegister(Request $request){
        $credentials = $request->validate([
            'username' => ['required','min:3','max:20'],
            'email' => ['email','required'],
            'password' => 'min:3|max:10'
        ]);

        User::create([
            'name' => $credentials['username'],
            'email' => $credentials['email'],
            'password' => $credentials['password']
        ]);
        return to_route('salle');
    }
}
