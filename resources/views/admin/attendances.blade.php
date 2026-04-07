@extends('admin.layout')
@section('title', 'Laporan Rekap Presensi')

@section('content')
<div class="card" style="margin-bottom: 24px;">
    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 16px;">
        <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--text-main);">Filter Laporan Presensi</h3>
        <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Pilih bulan untuk melihat rekap kehadiran seluruh karyawan.</p>
    </div>
    <form method="GET" action="{{ route('admin.attendances') }}" style="display: flex; gap: 16px; align-items: flex-end; flex-wrap: wrap;">
        <div class="form-group" style="margin-bottom: 0; min-width: 250px; flex: 1;">
            <label style="font-size: 0.85rem;">Pilih Bulan</label>
            <input type="month" name="month" value="{{ $month }}" style="background: #f8fafc;">
        </div>
        <div style="display: flex; gap: 12px; flex: 1; min-width: 250px;">
            <button type="submit" class="btn btn-primary" style="flex: 1;"><i class="ph ph-funnel"></i> Tampilkan Laporan</button>
            <a href="{{ route('admin.attendances') }}" class="btn btn-outline" style="flex: 0; padding: 10px 16px;"><i class="ph ph-arrow-counter-clockwise"></i></a>
        </div>
    </form>
</div>

<div class="card">
    <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--text-main);">Rekapitulasi Presensi Bulanan</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Periode bulan {{ \Carbon\Carbon::parse($month.'-01')->translatedFormat('F Y') }}</p>
        </div>
    </div>
    <div class="table-responsive" style="max-height: 600px; overflow: auto; padding-bottom: 15px;">
        <table style="min-width: 1800px; border-collapse: collapse; margin-top: 0;">
            <thead>
                <tr>
                    <th style="min-width: 220px; position: sticky; left: 0; top: 0; background: #f8fafc; z-index: 12; border-right: 1px solid var(--border-color); border-bottom: 2px solid var(--border-color);">Karyawan</th>
                    @for($i = 1; $i <= $daysInMonth; $i++)
                        <th style="text-align: center; min-width: 140px; padding: 12px 4px; position: sticky; top: 0; background: #f8fafc; z-index: 11; border-bottom: 2px solid var(--border-color); border-right: 1px solid #f1f5f9;">Tgl {{ $i }}</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $emp)
                <tr>
                    <td style="position: sticky; left: 0; background: #fff; z-index: 10; border-right: 1px solid var(--border-color); box-shadow: 1px 0 3px rgba(0,0,0,0.05); padding: 16px;">
                        <div style="font-weight: 600; color: var(--text-main); font-size: 1.05rem;">{{ $emp->name }}</div>
                        <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">{{ $emp->position }}</div>
                    </td>
                    @for($i = 1; $i <= $daysInMonth; $i++)
                        @php
                            $data = $matrix[$emp->id][$i];
                        @endphp
                        <td style="text-align: center; padding: 8px; border-bottom: 1px solid #f1f5f9; border-right: 1px solid #f1f5f9; vertical-align: top;">
                            @if($data['type'] === 'hadir')
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px; font-size: 0.75rem; text-align: left; background: #f8fafc; padding: 6px; border-radius: 6px;">
                                    <div style="color: #16a34a;"><strong style="color:#64748b;">M:</strong> {{ $data['masuk'] }}</div>
                                    <div style="color: #0284c7;"><strong style="color:#64748b;">I:</strong> {{ $data['istirahat'] }}</div>
                                    <div style="color: #0284c7;"><strong style="color:#64748b;">K:</strong> {{ $data['masuk_kembali'] }}</div>
                                    <div style="color: #dc2626;"><strong style="color:#64748b;">P:</strong> {{ $data['pulang'] }}</div>
                                </div>
                                @if($data['lembur'] !== '-')
                                    <div style="font-size: 0.7rem; margin-top: 4px; color: #7c3aed; background: #f3e8ff; padding: 2px 4px; border-radius: 4px; display: inline-block;">Lembur: {{ $data['lembur'] }}</div>
                                @endif
                            @elseif($data['type'] === 'izin')
                                <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; height: 100%; min-height: 50px;">
                                    <span class="badge" style="font-size: 0.7rem; color: #b45309; background: #fef9c3; padding: 4px 8px; border-radius: 6px; width: 100%;">{{ strtoupper($data['izin_type']) }}</span>
                                    @if($data['notes'])
                                        <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 4px; line-height: 1.2;">{{ Str::limit($data['notes'], 25) }}</div>
                                    @endif
                                </div>
                            @else
                                <div style="display: flex; align-items: center; justify-content: center; height: 100%; min-height: 50px;">
                                    <span style="font-size: 1rem; color: #cbd5e1;">-</span>
                                </div>
                            @endif
                        </td>
                    @endfor
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $daysInMonth + 1 }}" style="text-align: center; color: var(--text-muted); padding: 32px 0;">
                        <i class="ph ph-users" style="font-size: 2rem; color: var(--border-color); margin-bottom: 8px; display: block;"></i>
                        Belum ada karyawan terdaftar.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
