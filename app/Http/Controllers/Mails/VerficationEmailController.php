<?php

namespace App\Http\Controllers\Mails;

use App\Http\Controllers\Controller;
use App\Models\User;
use Carbon\Carbon;
use App\Models\VerfactionEmail;
use Illuminate\Http\Request;

class VerficationEmailController extends Controller
{
    public function verficationemailpage($token) 
    {
        $verfaction = VerfactionEmail::where('token', $token)->first();

        if (!$verfaction) {
            abort(404);
        }
        return view('auth.verifyEmailpage');
    }

    public function verifyemail(Request $request)
    {
        $verfaction = VerfactionEmail::where('otp', $request->otp)->first();

        if (!$verfaction) {
            return redirect()->back()->with('error', 'رمز otp غير صحيح تحقق من البريد الالكتروني');
        }

        $user = User::where('email', $verfaction->email)->first();
        $user->email_verified_at = Carbon::now(); // الوقت الحالي
        $user->save();
        $verfaction->delete();
        return redirect()->route('auth.login')->with('success', 'تم تفعيل حسابك بنجاح ');
    }
}
