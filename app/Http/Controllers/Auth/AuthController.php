<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\VerfiyEmail;
use App\Models\User;
use App\Models\VerfactionEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    function login()
    {
        return  view('auth.login');
    }

    function register()
    {
        return  view('auth.register');
    }

    function signin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();

            // Check if user is banned
            if ($user->banned) {
                Auth::logout();
                return redirect()->route('login')->with('errorlogin', 'Your account has been banned. Please contact the administrator.');
            }

            // if ($user->email_verified_at === null) {
            //     Auth::logout();
            //     return redirect()->back()->withErrors(['errorlogin' => 'يرجى التحقق من بريدك الإلكتروني لتفعيل الحساب.']);
            // }
            $role = $user->type;
            $roleName = optional($user->role)->name; // استخدم optional لتجنب الخطأ إن لم يكن له role
            $request->session()->regenerate();
            if ($role === 'admin') {
                return redirect()->route('admin.index');
            } elseif ($role === 'employee') {
                if ($roleName === 'accountant') {
                    return redirect()->route('admin.index'); // لوحة تحكم المحاسب
                }
                return redirect()->route('admin.index'); // لوحة تحكم الموظف
            } elseif ($role === 'client') {
                return redirect()->route('login');
            }
        }

        return redirect()->route('admin.index');

        // return redirect()->back();
    }

    function signup(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'password_confirmation' => 'required|same:password',
        ]);
        $createuser = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 'password_confirmation'=> $request->password_confirmation,
        ]);

        // mail verfiction:link,otp
        if ($createuser) {
            $otp = mt_rand(100000, 999999); // بتولد ارقام عشوائية مكونة من 6لا خانات
            $token = Str::random(50); // 50 خانة

            VerfactionEmail::create([
                'email' => $createuser->email,
                'otp' => $otp,
                'token' => $token,
                'expire' => 10,
            ]);

            $verfactionurl = url('verfactionemail/' . $token);
            Mail::to($createuser->email)->send(new VerfiyEmail($verfactionurl, $otp));

            return redirect()->route('login')->with('verify', 'تم تسجيل الحساب بنجاح وتم ارسال رابط التفعيل عبر الايميل ');
        }

        return to_route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return  redirect()->route('login');
    }
}
