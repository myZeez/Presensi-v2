@extends('admin.layout')
@section('title', 'Dashboard Admin')

@section('content')
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px;">
    <div class="card" style="display: flex; flex-direction: column; gap: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">Total Karyawan</h3>
            <div style="padding: 10px; background: #eff6ff; border-radius: 12px; color: var(--primary);">
                <i class="ph ph-users" style="font-size: 1.5rem;"></i>
            </div>
        </div>
        <div style="font-size: 2.25rem; font-weight: 700; color: var(--text-main);">{{ $employeeCount }}</div>
        <div style="display: flex; align-items: center; gap: 6px; color: #10b981; font-size: 0.85rem; font-weight: 500;">
            <i class="ph ph-trend-up"></i><span>Karyawan Aktif</span>
        </div>
    </div>

    <div class="card" style="display: flex; flex-direction: column; gap: 8px;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3 style="color: var(--text-muted); font-size: 0.95rem; font-weight: 600;">Presensi Hari Ini</h3>
            <div style="padding: 10px; background: #f0fdf4; border-radius: 12px; color: #16a34a;">
                <i class="ph ph-clock-counter-clockwise" style="font-size: 1.5rem;"></i>
            </div>
        </div>
        <div style="font-size: 2.25rem; font-weight: 700; color: var(--text-main);">{{ $todayAttendance }}</div>
        <div style="display: flex; align-items: center; gap: 6px; color: var(--text-muted); font-size: 0.85rem; font-weight: 500;">
            <i class="ph ph-calendar-blank"></i><span>{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</span>
        </div>
    </div>
</div>
@endsection
