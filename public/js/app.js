// === Override Default Alert ===
window.alert = function(message) {
    const alertModal = document.getElementById('custom-alert');
    const alertMsg = document.getElementById('custom-alert-message');
    if (alertModal && alertMsg) {
        alertMsg.innerText = message;
        alertModal.classList.add('active');
    } else {
        console.log("Alert:", message);
    }
};

function closeCustomAlert() {
    const alertModal = document.getElementById('custom-alert');
    if (alertModal) alertModal.classList.remove('active');
}

// === Custom Confirm ===
function showCustomConfirm(message, onConfirm) {
    const confirmModal = document.getElementById('custom-confirm');
    const confirmMsg = document.getElementById('custom-confirm-message');
    const btnYes = document.getElementById('btn-custom-confirm-yes');

    if (confirmModal && confirmMsg && btnYes) {
        confirmMsg.innerText = message;

        // Remove old event listeners
        const newBtnYes = btnYes.cloneNode(true);
        btnYes.parentNode.replaceChild(newBtnYes, btnYes);

        newBtnYes.addEventListener('click', () => {
            closeCustomConfirm();
            if(typeof onConfirm === 'function') onConfirm();
        });

        confirmModal.classList.add('active');
    }
}

function closeCustomConfirm() {
    const confirmModal = document.getElementById('custom-confirm');
    if (confirmModal) confirmModal.classList.remove('active');
}

// Profile Management is bypassed in Laravel
let currentUser = { name: "Karyawan", position: "Posisi", status: "Karyawan" }; // Dummy to pass local JS validation for now
let attendanceData = JSON.parse(localStorage.getItem('attendance_data')) || [];
let timeOffset = 0; // Offset untuk menyesuaikan waktu dari server

async function syncServerTime() {
    try {
        const response = await fetch('https://worldtimeapi.org/api/timezone/Asia/Jakarta');
        if (response.ok) {
            const data = await response.json();
            const serverDate = new Date(data.datetime);
            const deviceDate = new Date();
            timeOffset = serverDate.getTime() - deviceDate.getTime();
            console.log("Waktu disinkronkan dengan server. Offset: ", timeOffset, "ms");
            updateClock();
        }
    } catch (error) {
        console.error("Gagal sinkronisasi waktu dari server, menggunakan waktu perangkat:", error);
    }
}

function getTrueDate() {
    return new Date(Date.now() + timeOffset);
}

// === Initialization ===
document.addEventListener('DOMContentLoaded', () => {
    syncServerTime();
    updateClock();
    setInterval(updateClock, 1000);
    initReportFilter();
    renderTodayStatus();
});

function initReportFilter() {
    const filter = document.getElementById('report-month-filter');
    if(filter && filter.options.length === 0) {
        const now = getTrueDate();
        for (let i = 0; i <= 3; i++) {
            let d = new Date(now.getFullYear(), now.getMonth() - i, 1);
            let monthStr = `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}`;
            let label = d.toLocaleDateString('id-ID', { month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' });

            let opt = document.createElement('option');
            opt.value = monthStr;
            opt.innerText = label;
            filter.appendChild(opt);
        }
    }
}

// === UI Updaters ===
function updateClock() {
    const now = getTrueDate();

    // Date formatting specifically for Hero
    const optionsDate = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'Asia/Jakarta' };
    const dateEl = document.getElementById('hero-date');
    if(dateEl) dateEl.innerText = now.toLocaleDateString('id-ID', optionsDate);
}

function getTodayString() {
    // Format YYYY-MM-DD menggunakan zona waktu Jakarta (WIB)
    return getTrueDate().toLocaleDateString('en-CA', { timeZone: 'Asia/Jakarta' });
}

// === Navigation ===
function switchTab(tabId, el) {
    // Hide all sections
    document.querySelectorAll('.view-section').forEach(sec => sec.classList.remove('active'));
    // Show target section
    document.getElementById(`section-${tabId}`).classList.add('active');

    // Update Nav UI
    document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
    el.classList.add('active');

    // Tab specific logic
    if(tabId === 'report') renderReport();
    if(tabId === 'settings') fillFormIfDataExist();
}

// === Profile Management ===
function checkProfile() {
    const greeting = document.getElementById('hero-title');
    const role = document.getElementById('hero-subtitle');

    // Setting up the dashboard profile view
    const mainGreeting = document.getElementById('hero-title');
    const mainRole = document.getElementById('hero-subtitle');

    if(currentUser) {
        if(mainGreeting) mainGreeting.innerHTML = `Halo, <strong>${currentUser.name}</strong>`;
        if(mainRole) mainRole.innerText = `${currentUser.position} • ${currentUser.status}`;
    } else {
        if(mainGreeting) mainGreeting.innerHTML = `Halo, <strong>Karyawan</strong>`;
        if(mainRole) mainRole.innerText = `Silakan atur profil Anda`;
    }
}

function saveProfile() {
    const name = document.getElementById('input-name').value;
    const position = document.getElementById('input-position').value;
    const status = document.getElementById('input-status').value;

    if(!name || !position) {
        alert("Nama dan Posisi tidak boleh kosong!");
        return;
    }

    currentUser = { name, position, status };
    localStorage.setItem('user_profile', JSON.stringify(currentUser));

    alert("Profil berhasil disimpan!");
    checkProfile();
}

function fillFormIfDataExist() {
    if(currentUser) {
        document.getElementById('input-name').value = currentUser.name;
        document.getElementById('input-position').value = currentUser.position;
        document.getElementById('input-status').value = currentUser.status;
    }
}

function clearAllData() {
    showCustomConfirm("Apakah Anda yakin ingin menghapus SEMUA data di perangkat ini? Data tidak bisa dikembalikan.", () => {
        localStorage.removeItem('user_profile');
        localStorage.removeItem('attendance_data');
        currentUser = null;
        attendanceData = [];
        checkProfile();
        renderTodayStatus();
        document.getElementById('input-name').value = "";
        document.getElementById('input-position').value = "";
        alert("Semua data telah dibersihkan.");
    });
}

// === Konfigurasi Geofencing ===
const OFFICE_LAT = -2.2146623349058703;
const OFFICE_LNG = 113.90432819106759;
const MAX_RADIUS = 30;

function getDistanceFromLatLonInM(lat1, lon1, lat2, lon2) {
    var R = 6371000;
    var dLat = deg2rad(lat2-lat1);
    var dLon = deg2rad(lon2-lon1);
    var a =
        Math.sin(dLat/2) * Math.sin(dLat/2) +
        Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) *
        Math.sin(dLon/2) * Math.sin(dLon/2);
    var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
    return R * c;
}

function deg2rad(deg) { return deg * (Math.PI/180); }

// === Core Presensi Logic ===

function handleMainAction() {
    if(!currentUser) {
        alert("Harap lengkapi profil di menu Pengaturan terlebih dahulu.");
        switchTab('settings', document.querySelectorAll('.nav-item')[2]);
        return;
    }

    const todayStr = getTodayString();
    const todayRecords = attendanceData.filter(r => r.date === todayStr);

    const hasIzin = todayRecords.some(r => r.type.includes('Izin'));
    if (hasIzin) {
        alert("Anda sudah berstatus Izin hari ini. Tombol ini tidak berfungsi.");
        return;
    }

    const hasMasuk = todayRecords.some(r => r.type === 'Masuk');
    const hasIstirahat = todayRecords.some(r => r.type === 'Istirahat');
    const hasMasukKembali = todayRecords.some(r => r.type === 'Masuk Kembali');
    const hasPulang = todayRecords.some(r => r.type === 'Pulang');

    if (!hasMasuk) {
        recordAttendance('Masuk');
    } else if (!hasIstirahat) {
        recordAttendance('Istirahat');
    } else if (!hasMasukKembali) {
        recordAttendance('Masuk Kembali');
    } else if (!hasPulang) {
        recordAttendance('Pulang');
    }
}

function recordAttendance(type, notes = "") {
    if(!currentUser) {
        alert("Harap lengkapi profil di menu Pengaturan terlebih dahulu.");
        return;
    }

    const todayStr = getTodayString();
    const todayRecords = attendanceData.filter(r => r.date === todayStr);
    const hasIzin = todayRecords.some(r => r.type.includes('Izin'));

    if (hasIzin && !type.includes('Izin')) {
        alert("Anda sudah berstatus Izin hari ini.");
        return;
    }

    // Jika Izin, tidak butuh cek lokasi fisik
    if (type.includes('Izin')) {
        saveAttendanceData(type, notes);
        return;
    }

    // Cek Geofencing untuk absen harian dan lembur
    if (!navigator.geolocation) {
        alert("Browser atau perangkat HP Anda tidak mendukung fitur GPS/Lokasi.");
        return;
    }

    const mainBtn = document.getElementById('main-action-btn');
    const originalText = mainBtn && !type.includes('Lembur') ? mainBtn.innerText : '';
    if(mainBtn && !type.includes('Lembur')) mainBtn.innerText = 'Mencari GPS...';

    navigator.geolocation.getCurrentPosition(
        (position) => {
            // Laporan sukses membaca GPS
            if(mainBtn && !type.includes('Lembur')) mainBtn.innerText = originalText;

            const userLat = position.coords.latitude;
            const userLng = position.coords.longitude;
            const distance = getDistanceFromLatLonInM(OFFICE_LAT, OFFICE_LNG, userLat, userLng);

            // Validasi jarak
            if (distance <= MAX_RADIUS) {
                saveAttendanceData(type, notes, userLat, userLng);
            } else {
                alert(`Gagal Absen! Anda berada di luar radius kantor.\n\nJarak Anda: ${Math.round(distance)} meter\nRadius batas: ${MAX_RADIUS} meter.`);
            }
        },
        (error) => {
            // Laporan gagal baca GPS
            if(mainBtn && !type.includes('Lembur')) mainBtn.innerText = originalText;
            console.error(error);

            let errorMessage = "Presensi gagal karena GPS tidak terbaca! Pastikan Anda menyalakan akses Lokasi (GPS).";
            if (error.code === 1) {
                errorMessage = "Akses lokasi ditolak oleh iOS/Browser. Silakan buka Pengaturan > Privasi > Layanan Lokasi (atau pengaturan Safari/Chrome) dan izinkan akses lokasi untuk situs ini.";
            } else if (error.code === 2) {
                errorMessage = "Sinyal GPS tidak tersedia atau lemah. Cobalah berpindah ke area luar ruangan atau pastikan koneksi internet stabil.";
            } else if (error.code === 3) {
                errorMessage = "Pencarian GPS Timeout. Sinyal GPS butuh waktu lama untuk merespons, silakan coba lagi.";
            }

            alert(errorMessage);
        },
        // Sedikit rileks untuk perangkat iOS
        { enableHighAccuracy: true, timeout: 20000, maximumAge: 10000 }
    );
}

function saveAttendanceData(type, notes = "", lat = null, lng = null) {
    const now = getTrueDate();
    const dateStr = getTodayString();
    const timeStr = now.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false, timeZone: 'Asia/Jakarta' }).replace(/\./g, ':');

    // Cegah double entry untuk state yang sama di cache lokal sementara
    const todayRecords = attendanceData.filter(r => r.date === dateStr);

    if (todayRecords.some(r => r.type === type) && type !== 'Lembur') {
        alert(`Anda sudah melakukan absensi ${type} hari ini.`);
        return;
    }

    const record = {
        date: dateStr,
        time: timeStr,
        type: type,
        timestamp: now.getTime(),
        notes: notes,
        latitude: lat,
        longitude: lng
    };

    // Kirim data ke backend Laravel
    fetch('/attendance', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(record)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            attendanceData.push(record);
            localStorage.setItem('attendance_data', JSON.stringify(attendanceData));

            // Validasi notifikasi
            if (type === 'Lembur') {
                alert('Lembur berhasil dicatat!');
            } else if (type.includes('Izin')) {
                alert(`Pengajuan ${type} berhasil dicatat.`);
            } else {
                alert(`Presensi ${type} berhasil pada pukul ${record.time}`);
            }

            renderTodayStatus();
        } else {
            alert('Gagal menyimpan presensi ke server. Coba lagi.');
        }
    })
    .catch(error => {
        console.error('Error saving attendance:', error);
        alert('Terjadi kesalahan jaringan saat menyimpan presensi.');
    });
}

// === Izin Handling ===
function openIzinModal() { document.getElementById('modal-izin').classList.add('active'); }
function closeIzinModal() { document.getElementById('modal-izin').classList.remove('active'); }

function submitIzin() {
    const type = document.getElementById('select-izin').value;
    const todayRecords = attendanceData.filter(r => r.date === getTodayString());

    if(todayRecords.some(r => r.type.includes('Izin'))) {
        alert("Anda sudah mengajukan Izin untuk hari ini.");
        closeIzinModal();
        return;
    }

    recordAttendance(type);
    closeIzinModal();
}

// === Rendering UI ===
function renderTodayStatus() {
    const todayStr = getTodayString();
    const todayRecords = attendanceData.filter(r => r.date === todayStr);

    // Update Main Action Button Look and Function
    const mainBtn = document.getElementById('main-action-btn');
    if (mainBtn) {
        const hasIzin = todayRecords.some(r => r.type.includes('Izin'));
        const hasMasuk = todayRecords.some(r => r.type === 'Masuk');
        const hasIstirahat = todayRecords.some(r => r.type === 'Istirahat');
        const hasMasukKembali = todayRecords.some(r => r.type === 'Masuk Kembali');
        const hasPulang = todayRecords.some(r => r.type === 'Pulang');

        if (hasIzin) {
            mainBtn.innerText = 'Izin (Selesai)';
            mainBtn.className = 'btn-main btn-disabled mt-4';
            mainBtn.disabled = true;
        } else if (!hasMasuk) {
            mainBtn.innerText = 'Masuk';
            mainBtn.className = 'btn-main btn-white mt-4';
            mainBtn.disabled = false;
        } else if (!hasIstirahat) {
            mainBtn.innerText = 'Istirahat';
            mainBtn.className = 'btn-main btn-green mt-4';
            mainBtn.disabled = false;
        } else if (!hasMasukKembali) {
            mainBtn.innerText = 'Masuk'; // Tampilkan sebagai "Masuk"
            mainBtn.className = 'btn-main btn-white mt-4';
            mainBtn.disabled = false;
        } else if (!hasPulang) {
            mainBtn.innerText = 'Pulang';
            mainBtn.className = 'btn-main btn-yellow mt-4';
            mainBtn.disabled = false;
        } else {
            mainBtn.innerText = 'Selesai';
            mainBtn.className = 'btn-main btn-disabled mt-4';
            mainBtn.disabled = true;
        }
    }

    // Render Timeline Horizontal
    const timelineContainer = document.getElementById('timeline-container');
    if(!timelineContainer) return;

    if(todayRecords.length === 0) {
        timelineContainer.innerHTML = '<p class="empty-state">Belum ada aktivitas hari ini.</p>';
        return;
    }

    let timelineHtml = '';
    // Optional: Sort chronological
    const sortedRecords = [...todayRecords].sort((a,b) => a.timestamp - b.timestamp);

    sortedRecords.forEach(r => {
        // Tampilkan 'Masuk Kembali' sebagai 'Masuk' biar rapi
        let displayType = r.type === 'Masuk Kembali' ? 'Masuk' : r.type;
        // Hanya jam dan menit
        let displayTime = r.time.split(':').slice(0,2).join(':');

        let dotLembur = r.type === 'Lembur' ? '<div class="dot-lembur"></div>' : '';

        timelineHtml += `
            <div class="timeline-item">
                <div class="time">${displayTime}</div>
                <div class="label">${displayType}</div>
                ${dotLembur}
            </div>
        `;
    });

    timelineContainer.innerHTML = timelineHtml;
}

// === Report & Alpa Logic ===
function renderReport(monthStr) {
    const reportListContainer = document.getElementById('monthly-report-list');

    let totalHadir = 0;
    let totalIzin = 0;
    let totalAlpa = 0;
    let html = '';

    // Gunakan monthStr atau ambil dari filter dropdown
    let targetMonthStr = monthStr && typeof monthStr === 'string' ? monthStr : null;
    const filter = document.getElementById('report-month-filter');

    if (!targetMonthStr && filter) {
        targetMonthStr = filter.value;
    } else if (filter) {
        filter.value = targetMonthStr;
    }

    if (!targetMonthStr) {
        const now = getTrueDate();
        targetMonthStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
    }

    // Kelompokkan data per tanggal
    const recordsByDate = {};
    attendanceData.forEach(r => {
        if(r.date.startsWith(targetMonthStr)) {
            if(!recordsByDate[r.date]) {
                recordsByDate[r.date] = [];
            }
            recordsByDate[r.date].push(r);
        }
    });

    // Sort by date descending (terbaru di atas)
    const sortedDates = Object.keys(recordsByDate).sort((a,b) => b.localeCompare(a));

    sortedDates.forEach(dateStr => {
        const dayRecords = recordsByDate[dateStr];
        const loopDate = new Date(dateStr);
        const dayStrRender = loopDate.toLocaleDateString('id-ID', { weekday: 'short', day: 'numeric', month: 'short', timeZone: 'Asia/Jakarta'});

        const isIzin = dayRecords.some(r => r.type.includes('Izin'));
        const isMasuk = dayRecords.some(r => r.type === 'Masuk');
        const isPulang = dayRecords.some(r => r.type === 'Pulang');

        let statusClass = '';
        let statusText = '';
        let isKurang9Jam = false;

        if(isIzin) {
            totalIzin++;
            statusClass = 'izin';
            const izinRec = dayRecords.find(r => r.type.includes('Izin'));
            statusText = izinRec.type;
        } else {
            totalHadir++;
            statusClass = 'hadir';
            statusText = 'Hadir';

            // Pengecekan Presensi 9 Jam. Jika masuk, tapi tidak pulang.
            if(isMasuk && !isPulang && dateStr !== getTodayString()) {
                 statusText = 'Hadir (Tidak Tuntas)';
            } else if(isMasuk && isPulang) {
                 const tIn = dayRecords.find(r=>r.type==='Masuk').timestamp;
                 const tOut = dayRecords.find(r=>r.type==='Pulang').timestamp;
                 const diffHours = (tOut - tIn) / (1000 * 60 * 60);
                 if (diffHours < 9) {
                     isKurang9Jam = true;
                     statusText = 'Alpa';
                     statusClass = 'alpa';
                     totalAlpa++;
                     totalHadir--; // kurangi jumlah hadir yang ditambahkan di atas
                 }
            }
        }

        // Generate timeline UI for Report
        let timelineHtml = '<div class="timeline-container" style="padding: 12px; margin-top: 12px; border-radius: 12px; gap: 16px;">';

        if(isIzin) {
            timelineHtml += `
            <div class="timeline-item">
                <div class="time" style="font-size: 1.1rem; color: #111;">-</div>
                <div class="label" style="font-size: 0.8rem;">Izin</div>
            </div>`;
        } else {
            const sortedDayRecords = [...dayRecords].sort((a,b) => a.timestamp - b.timestamp);
            sortedDayRecords.forEach(r => {
                let displayType = r.type === 'Masuk Kembali' ? 'Masuk' : r.type;
                let displayTime = r.time.split(':').slice(0,2).join(':');
                timelineHtml += `
                    <div class="timeline-item">
                        <div class="time" style="font-size: 1.1rem; color: #111;">${displayTime}</div>
                        <div class="label" style="font-size: 0.8rem;">${displayType}</div>
                    </div>
                `;
            });
        }
        timelineHtml += '</div>';

        let warningHtml = isKurang9Jam ? '<div style="color:var(--danger); font-size: 0.8rem; margin-top: 8px;">*(Kurang 9 Jam)</div>' : '';

        html += `
        <div class="report-card ${statusClass}" style="flex-direction: column; align-items: stretch; gap: 0;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div class="report-date">${dayStrRender}</div>
                <div style="font-weight:600; font-size: 0.9rem; color: ${statusClass === 'alpa' ? 'var(--danger)' : ''};">${statusText}</div>
            </div>
            ${timelineHtml}
            ${warningHtml}
        </div>
        `;
    });

    if(html === '') {
        html = '<p class="empty-state">Belum ada data presensi pada bulan ini.</p>';
    }

    reportListContainer.innerHTML = html;

    document.getElementById('count-masuk').innerText = totalHadir;
    document.getElementById('count-izin').innerText = totalIzin;
    document.getElementById('count-alpa').innerText = totalAlpa;
}

// === Fitur Cetak PDF ===
function printPDF() {
    if(!currentUser) {
        alert("Profil belum diatur. Harap isi profil terlebih dahulu.");
        return;
    }

    let targetMonthStr;
    const filter = document.getElementById('report-month-filter');
    if (filter && filter.value) {
        targetMonthStr = filter.value;
    } else {
        const now = getTrueDate();
        targetMonthStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`;
    }

    const [year, month] = targetMonthStr.split('-');
    const dt = new Date(year, month - 1, 1);
    const monthName = dt.toLocaleDateString('id-ID', { month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta' });

    let printWindow = window.open('', '', 'width=800,height=600');

    let tableRows = '';
    const recordsByDate = {};

    // Ambil rekap bulan yang dipilih saja
    attendanceData.forEach(r => {
        if(r.date.startsWith(targetMonthStr)) {
            if(!recordsByDate[r.date]) {
                recordsByDate[r.date] = [];
            }
            recordsByDate[r.date].push(r);
        }
    });

    // Urutkan maju (tanggal awal -> akhir) untuk laporan
    const sortedDates = Object.keys(recordsByDate).sort((a,b) => a.localeCompare(b));

    sortedDates.forEach(dateStr => {
        const dayRecords = recordsByDate[dateStr];
        const loopDate = new Date(dateStr);
        const dayStr = loopDate.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Jakarta'});

        let masuk = dayRecords.find(r => r.type === 'Masuk')?.time || '-';
        let istirahat = dayRecords.find(r => r.type === 'Istirahat')?.time || '-';
        let masukKembali = dayRecords.find(r => r.type === 'Masuk Kembali')?.time || '-';
        let pulang = dayRecords.find(r => r.type === 'Pulang')?.time || '-';
        let lembur = dayRecords.find(r => r.type === 'Lembur')?.time || '-';
        let izin = dayRecords.find(r => r.type.includes('Izin'))?.type || '-';

        let status = 'Hadir';
        if(izin !== '-') {
            status = 'Izin';
        } else if (masuk !== '-' && pulang !== '-') {
             const tIn = dayRecords.find(r=>r.type==='Masuk').timestamp;
             const tOut = dayRecords.find(r=>r.type==='Pulang').timestamp;
             const diffHours = (tOut - tIn) / (1000 * 60 * 60);
             if (diffHours < 9) status = 'Alpa (Kurang 9 Jam)';
        } else if (masuk !== '-' && pulang === '-' && dateStr !== getTodayString()) {
             status = 'Hadir (Tidak Tuntas)';
        }

        // Hilangkan detik dari format time
        const formatTime = (t) => t !== '-' ? t.split(':').slice(0,2).join(':') : '-';

        tableRows += `
            <tr>
                <td>${dayStr}</td>
                <td>${formatTime(masuk)}</td>
                <td>${formatTime(istirahat)}</td>
                <td>${formatTime(masukKembali)}</td>
                <td>${formatTime(pulang)}</td>
                <td>${formatTime(lembur)}</td>
                <td>${status}</td>
            </tr>
        `;
    });

    if(sortedDates.length === 0) {
        tableRows = '<tr><td colspan="7" style="text-align: center;">Belum ada data pada bulan ini.</td></tr>';
    }

    const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Cetak Presensi</title>
            <style>
                body { font-family: Arial, sans-serif; padding: 20px; color: #111; }
                .header { text-align: center; margin-bottom: 30px; }
                .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; }
                .info { margin-bottom: 20px; }
                .info table { width: 100%; max-width: 400px; border: none; }
                .info td { padding: 4px 0; font-size: 14px; border: none; }
                table.data { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 13px; }
                table.data th, table.data td { border: 1px solid #111; padding: 10px; text-align: center; }
                table.data th { background-color: #f4f4f4; font-weight: bold; }
                @media print {
                    @page { margin: 1cm; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <h1>Presensi Karyawan Cellcom</h1>
            </div>
            <div class="info">
                <table>
                    <tr><td style="width:120px;"><strong>Nama</strong></td><td>: ${currentUser.name}</td></tr>
                    <tr><td><strong>Jabatan/Status</strong></td><td>: ${currentUser.position} / ${currentUser.status}</td></tr>
                    <tr><td><strong>Bulan</strong></td><td>: ${monthName}</td></tr>
                </table>
            </div>
            <table class="data">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Masuk</th>
                        <th>Istirahat</th>
                        <th>Masuk<br>Kembali</th>
                        <th>Pulang</th>
                        <th>Lembur</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    ${tableRows}
                </tbody>
            </table>
            <script>
                window.onload = function() {
                    window.print();
                };
            </script>
        </body>
        </html>
    `;

    printWindow.document.write(htmlContent);
    printWindow.document.close();
}
