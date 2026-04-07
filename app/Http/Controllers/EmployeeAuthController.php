<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class EmployeeAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('employee_login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        // TRUE argument dictates "remember me" functionality
        if (Auth::guard('employee')->attempt($credentials, true)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ]);
    }

    public function logout(Request $request)
    {
        Auth::guard('employee')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    public function storeAttendance(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required',
            'type' => 'required',
            'latitude' => 'nullable',
            'longitude' => 'nullable',
            'notes' => 'nullable'
        ]);

        $attendance = Attendance::create([
            'employee_id' => Auth::guard('employee')->id(),
            'date' => $request->date,
            'time' => $request->time,
            'type' => $request->type,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'notes' => $request->notes,
        ]);

        return response()->json(['success' => true, 'data' => $attendance]);
    }
}
