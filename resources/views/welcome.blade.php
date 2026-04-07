<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Presensi Karyawan</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <!-- Menggunakan icon dari Phosphor Icons untuk tampilan modern -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
</head>
<body>
    <div class="app-container">
        <!-- Main Content Area -->
        <main class="app-main">
            <!-- SECTION: HOME (Beranda Presensi) -->
            <section id="section-home" class="view-section active">

                <!-- Hero Card (Background Image Area) -->
                <div class="hero-card" style="background-image: url('{{ asset('backround.jpg') }}');">
                    <div class="hero-overlay"></div>
                    <div class="hero-content">
                        <p id="hero-date">Selasa, 10 Maret 2025</p>
                        <h1 id="hero-title">Halo, <strong>{{ auth('employee')->user()->name }}</strong></h1>
                        <p id="hero-subtitle">{{ auth('employee')->user()->position }} • {{ auth('employee')->user()->status }}</p>

                        <!-- Main Workflow Button -->
                        <button id="main-action-btn" class="btn-main btn-white mt-4" onclick="handleMainAction()">Masuk</button>
                    </div>
                </div>

                <!-- Secondary Actions -->
                <div class="action-grid-2">
                    <button class="btn-dark" onclick="openIzinModal()">Izin</button>
                    <button class="btn-neon" onclick="recordAttendance('Lembur')">Lembur</button>
                </div>
                <form action="{{ route('employee.logout') }}" method="POST" style="margin-top: 15px;">
                    @csrf
                    <button type="submit" class="btn-danger" style="width: 100%; border: none; padding: 12px; border-radius: 8px; color: white; background: #dc3545; cursor: pointer; font-weight: bold; font-size: 1rem;"><i class="ph ph-sign-out"></i> Logout Akun Ini</button>
                </form>

                <!-- Timeline Hari Ini -->
                <div class="timeline-section">
                    <h3 class="section-title-sm">Hari Ini</h3>
                    <div class="timeline-container" id="timeline-container">
                        <!-- Rendered by JS -->
                        <p class="empty-state">Belum ada aktivitas hari ini.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <!-- Modal Izin -->
    <div id="modal-izin" class="modal-overlay">
        <div class="modal-content">
            <h3>Pilih Jenis Izin</h3>
            <div class="form-group mt-3">
                <select id="select-izin">
                    <option value="Izin Sakit">Sakit</option>
                    <option value="Izin Keperluan Lain">Keperluan Lainnya</option>
                </select>
            </div>
            <div class="modal-actions">
                <button class="btn btn-outline" onclick="closeIzinModal()">Batal</button>
                <button class="btn btn-primary" onclick="submitIzin()">Kirim Izin</button>
            </div>
        </div>
    </div>

    <!-- Custom Alert Modal -->
    <div id="custom-alert" class="modal-overlay" style="z-index: 10000;">
        <div class="modal-content" style="max-width: 320px; text-align: center; padding: 24px;">
            <div id="custom-alert-message" style="margin-bottom: 24px; font-size: 1rem; color: var(--text-main); line-height: 1.5;"></div>
            <button class="btn-main btn-dark" onclick="closeCustomAlert()" style="padding: 12px; margin-top: 0; width: 100%;">OK</button>
        </div>
    </div>
    <!-- Custom Confirm Modal -->
    <div id="custom-confirm" class="modal-overlay" style="z-index: 10000;">
        <div class="modal-content" style="max-width: 320px; text-align: center; padding: 24px;">
            <div id="custom-confirm-message" style="margin-bottom: 24px; font-size: 1rem; color: var(--text-main); line-height: 1.5;"></div>
            <div style="display: flex; gap: 12px; justify-content: center;">
                <button class="btn btn-outline" onclick="closeCustomConfirm()" style="flex: 1;">Batal</button>
                <button class="btn-main btn-dark" id="btn-custom-confirm-yes" style="flex: 1; margin: 0; padding: 12px; border-radius: 12px;">Ya</button>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>
