@extends('admin.layout')
@section('title', 'Kelola Karyawan')

@section('content')
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px; align-items: start;">
    <div class="card">
        <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 24px;">
            <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--text-main);">Tambah Karyawan Baru</h3>
            <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Tambahkan akses login karyawan baru.</p>
        </div>
        <form action="{{ route('admin.employees.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" required placeholder="Cth: Budi Santoso">
            </div>
            <div class="form-group">
                <label>Username (Login)</label>
                <input type="text" name="username" required placeholder="Cth: budi123">
            </div>
            <div class="form-group">
                <label>Password (Login)</label>
                <input type="text" name="password" required placeholder="Cth: 123456">
            </div>
            <div class="form-group">
                <label>Posisi / Bagian</label>
                <input type="text" name="position" required placeholder="Cth: Sales Marketing">
            </div>
            <div class="form-group">
                <label>Status Kerja</label>
                <select name="status" required>
                    <option value="Karyawan">Karyawan Tetap</option>
                    <option value="Part-Time">Part-Time</option>
                    <option value="Training">Training</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 8px;">Simpan Karyawan</button>
        </form>
    </div>

    <div class="card">
        <div style="border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h3 style="font-size: 1.1rem; font-weight: 600; color: var(--text-main);">Daftar Karyawan Aktif</h3>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 4px;">Kelola karyawan yang memiliki akses sistem.</p>
            </div>
            <span class="badge" style="background: #eff6ff; color: var(--primary);">{{ $employees->count() }} orang</span>
        </div>
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Karyawan</th>
                        <th>Posisi</th>
                        <th>Status</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $emp)
                    <tr>
                        <td style="color: var(--text-muted); font-weight: 500;">#{{ $emp->id }}</td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-main);">{{ $emp->name }}</div>
                            <div style="display: flex; align-items: center; gap: 4px; color: var(--text-muted); font-size: 0.8rem; margin-top: 4px;">
                                <i class="ph ph-user"></i> {{ $emp->username }}
                            </div>
                        </td>
                        <td>{{ $emp->position }}</td>
                        <td><span class="badge" style="background: #f1f5f9; color: var(--text-muted);">{{ $emp->status }}</span></td>
                        <td style="text-align: right;">
                            <form action="{{ route('admin.employees.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('Yakin ingin mencabut akses karyawan ini?');" style="display: inline-block;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline" style="padding: 6px 12px; color: var(--danger);"><i class="ph ph-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 32px 0;">
                            <i class="ph ph-users" style="font-size: 2rem; color: var(--border-color); margin-bottom: 8px;"></i>
                            <p>Belum ada karyawan yang terdaftar.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
