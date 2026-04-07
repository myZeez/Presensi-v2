<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Attendance;

class AdminController extends Controller
{
    public function dashboard()
    {
        $employeeCount = Employee::count();
        $todayAttendance = Attendance::where('date', date('Y-m-d'))->count();
        return view('admin.dashboard', compact('employeeCount', 'todayAttendance'));
    }

    public function employees()
    {
        $employees = Employee::all();
        return view('admin.employees', compact('employees'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'username' => 'required|unique:employees,username',
            'password' => 'required|min:4',
            'position' => 'required',
            'status' => 'required'
        ]);

        Employee::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt($request->password),
            'position' => $request->position,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Karyawan berhasil ditambahkan!');
    }

    public function destroyEmployee(Employee $employee)
    {
        $employee->delete();
        return redirect()->back()->with('success', 'Karyawan dihapus!');
    }

    public function attendances(Request $request)
    {
        $month = $request->get('month', date('Y-m')); // Format: YYYY-MM

        $startOfMonth = $month . '-01';
        $endOfMonth = date('Y-m-t', strtotime($startOfMonth));

        $employees = Employee::all();
        $attendances = Attendance::whereBetween('date', [$startOfMonth, $endOfMonth])
                                ->get()
                                ->groupBy(function($item) {
                                    return $item->employee_id . '_' . date('j', strtotime($item->date));
                                });

        // Generate matrix: $matrix[employee_id][day] = 'Status/Type' atau waktu
        $matrix = [];
        foreach ($employees as $emp) {
            for ($day = 1; $day <= date('t', strtotime($startOfMonth)); $day++) {
                $key = $emp->id . '_' . $day;
                if (isset($attendances[$key])) {
                    $izin = $attendances[$key]->filter(function($a) { return str_contains($a->type, 'Izin'); })->first();

                    if ($izin) {
                        $matrix[$emp->id][$day] = [
                            'type' => 'izin',
                            'izin_type' => $izin->type,
                            'notes' => $izin->notes
                        ];
                    } else {
                        $masuk = $attendances[$key]->where('type', 'Masuk')->first();
                        $istirahat = $attendances[$key]->where('type', 'Istirahat')->first();
                        $masukKembali = $attendances[$key]->where('type', 'Masuk Kembali')->first();
                        $pulang = $attendances[$key]->where('type', 'Pulang')->first();
                        $lembur = $attendances[$key]->where('type', 'Lembur')->first();

                        $matrix[$emp->id][$day] = [
                            'type' => 'hadir',
                            'masuk' => $masuk ? \Carbon\Carbon::parse($masuk->time)->format('H:i') : '-',
                            'istirahat' => $istirahat ? \Carbon\Carbon::parse($istirahat->time)->format('H:i') : '-',
                            'masuk_kembali' => $masukKembali ? \Carbon\Carbon::parse($masukKembali->time)->format('H:i') : '-',
                            'pulang' => $pulang ? \Carbon\Carbon::parse($pulang->time)->format('H:i') : '-',
                            'lembur' => $lembur ? \Carbon\Carbon::parse($lembur->time)->format('H:i') : '-',
                        ];
                    }
                } else {
                    $matrix[$emp->id][$day] = ['type' => 'alpa', 'label' => '-'];
                }
            }
        }

        $daysInMonth = date('t', strtotime($startOfMonth));
        return view('admin.attendances', compact('employees', 'matrix', 'month', 'daysInMonth'));
    }
}
