<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function profile()
    {
        $user = Auth::user();
        return view('admin.profile', compact('user'));
    }

    public function profile_save(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'password' => 'nullable|min:8|confirmed',
            'avatar' => 'nullable||image|mimes:png,jpg,svg,jpeg',
        ]);

        $data = $request->except(['token', '_method', 'avatar', 'password', 'password_confirmation']);

        if ($request->password) {
            $data['password'] = $request->password;
        }

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('uploads', 'custom');
        }

        /** @var App/Models/User $user */

        $user = Auth::user();
        $user->update([$data]);

        return redirect()->back();
    }
}
