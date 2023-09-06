<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
   public function index(){
      return view('admin/dashboard/index');
   }

   public function login(){
      return view('admin/login/index');
   }

   public function attempt(Request $request)
    {
        $request->validate([
            'email' => 'bail|required|email|max:255',
            'password' => 'bail|required|min:6'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return back()->withErrors("E-mail e/ou senha inválidos.")->withInput();
        }

        if (!Utils::passwordIsValid($request->password, $user->password, $user->salt)) {
            return back()->withErrors("E-mail e/ou senha inválidos.")->withInput();
        }

        if($user->role == "user"){
         flash('Usuário não Cadastrado!')->error();
         return back();
        }
   

        session([
            'authenticated' => true,
            'userData' => $user
        ]);

        return redirect('/home');
    }
}