<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | ESS-Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.15/index.global.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.3.0/dist/exceljs.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        fetch("{{ route('user.autoClockOut') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Otomatis Keluar Diaktifkan',
                        text: "Kemarin Anda Lupa Keluar, Mohon Isi Aktifitas kemarin di hari ini",
                        confirmButtonText: 'OK'
                    }).then(() => {
                        location.reload();
                    });
                }
            })
            .catch(error => console.error('Error:', error));
    });
</script>


<body class="flex items-center justify-center bg-gray-100">
    <div class="bg-white p-6 rounded-lg shadow-lg w-96 text-center">

        <!-- Jam Real-time -->
        <h2 class="text-xl font-bold mb-2"> Waktu Sekarang</h2>
        <span id="realTimeClock">Memuat...</span>
        <p id="clock" class="text-gray-700 text-lg font-mono"></p>

        <h2 class="text-2xl font-bold mt-6">Halo, {{ $user->name }}</h2>
        <p class="text-gray-600 mb-4">{{ now()->translatedFormat('l, d M Y') }}</p>

        <!-- Tombol Masuk / Keluar -->
        <form id="attendanceForm" method="POST">
            @csrf
            @if (!$attendance)
            <!-- Tombol Masuk -->
            <button id="masukButton" type="button" class="w-full bg-green-500 text-white p-2 rounded hover:bg-green-600">
                Masuk
            </button>
            @elseif (!$attendance->clock_out)
            <!-- Form Aktivitas -->
            <textarea id="activityInput" placeholder="Isi aktivitas sebelum keluar..." class="w-full p-2 border rounded mb-2" required></textarea>
            <button id="submitActivityButton" class="w-full bg-red-500 text-white p-2 rounded hover:bg-red-600">
                Kirim Aktivitas & Keluar
            </button>
            @elseif ($attendance && $attendance->clock_out)
            <button id="cancelClockOutBtn" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                Batalkan Clock Out
            </button>
            @else
            <!-- Status Selesai -->
            <button class="w-full bg-gray-400 text-white p-2 rounded cursor-not-allowed" disabled>
                Absen Selesai
            </button>
            @endif
        </form>

        <!-- Kalender -->
        <h3 class="text-lg font-bold mt-6 mb-2">Riwayat Absensi</h3>
        <div id="calendar"></div>
        <!-- History Absensi -->
        <h3 class="text-lg font-bold mt-6 mb-2">Detail Absensi</h3>
        <table class="w-full border-collapse">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border p-2">Tanggal</th>
                    <th class="border p-2">Masuk</th>
                    <th class="border p-2">Keluar</th>
                    <th class="border p-2">Durasi</th>
                    <th class="border p-2">Aktivitas</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($attendanceHistory as $record)
                <tr class="text-center">
                    <td class="border p-2">{{ \Carbon\Carbon::parse($record->date)->translatedFormat('d M Y') }}</td>
                    <td class="border p-2">{{ \Carbon\Carbon::parse($record->clock_in)->format('H:i') }}</td>
                    <td class="border p-2">
                        {{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '-' }}
                    </td>
                    <td class="border p-2">
                        {{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_in)->diff(\Carbon\Carbon::parse($record->clock_out))->format('%H:%I') : '-' }}
                    </td>
                    <td class="border p-2 text-left">
                        <details>
                            <summary class="cursor-pointer text-blue-500">Lihat</summary>
                            <p class="mt-1 text-sm text-gray-700">{{ $record->aktivitas ?? 'Tidak ada' }}</p>
                        </details>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $attendanceHistory->links('pagination::tailwind') }}
        </div>

        <button onclick="exportToExcel()" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600 mt-4">
            Ekspor ke Excel
        </button>

        <form action="{{ route('logout') }}" method="POST" class="mt-4">
            @csrf
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">
                Logout
            </button>
        </form>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.15/index.global.min.js"></script>
    <script>
        document.getElementById('masukButton')?.addEventListener('click', function() {
            // Ambil lokasi berbasis IP sebelum mengirim permintaan absen masuk
            fetch("https://ipinfo.io/json")
                .then(response => response.json())
                .then(data => {
                    let city = data.city || "Bandung";
                    let region = data.region || "West Java";
                    let country = data.country || "Tidak diketahui";
                    let lokasi = `${city}, ${region}, ${country}`; // Format: "city, region, country"

                    // Kirim data absen masuk dengan lokasi
                    fetch("{{ route('user.toggleAttendance') }}", {
                        method: "POST",
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            lokasi: lokasi
                        })
                    }).then(() => {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: "Nanti Jangan Lupa Isi Aktivitas Sebelum Keluar",
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // Refresh halaman setelah sukses
                        });
                    });
                })
        });


        document.getElementById('submitActivityButton')?.addEventListener('click', function(event) {
            event.preventDefault();

            let aktivitas = document.getElementById('activityInput').value.trim();

            // Mencegah break line dengan mengganti semua newlines (\n) menjadi spasi
            aktivitas = aktivitas.replace(/\n/g, ' ');

            if (aktivitas === "") {
                Swal.fire({
                    title: 'Terjadi Kesalahan!',
                    text: 'Aktivitas tidak boleh kosong',
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
                return;
            }

            fetch("{{ route('user.submitActivity') }}", {
                    method: "POST",
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        aktivitas: aktivitas
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: "Aktivitas tersimpan! Semoga harimu menyenangkan",
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // Refresh halaman setelah sukses
                        });
                        // Sembunyikan form aktivitas
                        document.getElementById('activityForm').style.display = 'none';

                        // Aktifkan tombol keluar
                        const keluarButton = document.getElementById('keluarButton');
                        keluarButton.disabled = false;
                        keluarButton.classList.remove('bg-gray-400', 'cursor-not-allowed');
                        keluarButton.classList.add('bg-red-500', 'hover:bg-red-600');
                        keluarButton.innerText = 'Keluar';
                    }
                });
        });

        // Update Jam Real-Time
        function getLocation() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        let latitude = position.coords.latitude;
                        let longitude = position.coords.longitude;
                        updateTimeFromCoords(latitude, longitude);
                    },
                    function(error) {
                        console.warn("GPS tidak diizinkan, mencoba IP-based lokasi...");
                        fetchIPBasedLocation(); // Jika GPS gagal, pakai IP API
                    }
                );
            } else {
                console.warn("Geolocation tidak didukung, mencoba IP-based lokasi...");
                fetchIPBasedLocation();
            }
        }

        function fetchIPBasedLocation() {
            fetch("https://ipinfo.io/json")
                .then(response => response.json())
                .then(data => {
                    let loc = data.loc.split(","); // "lat,lon" jadi array
                    let latitude = parseFloat(loc[0]);
                    let longitude = parseFloat(loc[1]);
                    let city = data.city;
                    let region = data.region;
                    let country = data.country;

                    updateTimeFromCoords(latitude, longitude, city, region, country);
                })
                .catch(error => {
                    console.error("Gagal mendapatkan lokasi dari IP:", error);
                    document.getElementById("realTimeClock").innerText = "Lokasi tidak ditemukan.";
                });
        }

        function updateTimeFromCoords(latitude, longitude, city = "Tidak diketahui", region = "", country = "") {
            let now = new Date();
            let formattedTime = now.toLocaleTimeString("id-ID", {
                hour: "2-digit",
                minute: "2-digit",
                second: "2-digit"
            });

            document.getElementById("realTimeClock").innerHTML = `
        ${formattedTime} <br> 
        Lokasi: ${city}, ${region}, ${country} <br> 
        Koordinat: (${latitude.toFixed(5)}, ${longitude.toFixed(5)})
    `;

            setInterval(() => {
                let now = new Date();
                let formattedTime = now.toLocaleTimeString("id-ID", {
                    hour: "2-digit",
                    minute: "2-digit",
                    second: "2-digit"
                });
                document.getElementById("realTimeClock").innerHTML = `
            ${formattedTime} <br> 
            Lokasi: ${city}, ${region}, ${country} <br> 
            Koordinat: (${latitude.toFixed(5)}, ${longitude.toFixed(5)})
        `;
            }, 1000);
        }

        // **Mulai deteksi lokasi**
        getLocation();


        // Kalender FullCalendar
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'id',
                events: [
                    @foreach($attendanceHistory as $record) {
                        title: '{{ $record->clock_out ? "Hadir - " . Str::limit($record->aktivitas, 20) : "Belum Keluar" }}',
                        start: '{{ $record->date }}',
                        color: '{{ $record->clock_out ? "green" : "yellow" }}'
                    },
                    @endforeach
                ]
            });
            calendar.render();
        });

        // Kirim aktivitas dan aktifkan tombol keluar
        document.getElementById('activityForm')?.addEventListener('submit', function(event) {
            event.preventDefault();

            fetch(this.action, {
                    method: 'POST',
                    body: new FormData(this),
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        document.getElementById('keluarButton').disabled = false;
                        document.getElementById('keluarButton').classList.remove('bg-gray-400', 'cursor-not-allowed');
                        document.getElementById('keluarButton').classList.add('bg-red-500', 'hover:bg-red-600');
                    }
                });
        });


        // Batalkan Clockout
        document.getElementById("cancelClockOutBtn").addEventListener("click", function(event) {
            event.preventDefault(); // Mencegah form submit langsung

            fetch("{{ route('user.cancelClockOut') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            location.reload(); // Refresh halaman setelah sukses
                        });
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    Swal.fire({
                        title: 'Terjadi Kesalahan!',
                        text: 'Coba lagi nanti.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
        });

        // Ekspor ke Excel
        function exportToExcel() {
            const workbook = new ExcelJS.Workbook();
            const worksheet = workbook.addWorksheet("Riwayat Absensi");

            // ====== 1. Judul Laporan ====== //
            worksheet.mergeCells("A1:F1");
            const titleCell = worksheet.getCell("A1");
            titleCell.value = "Riwayat Absensi Karyawan";
            titleCell.font = {
                size: 20,
                bold: true,
                color: {
                    argb: "FFFFFF"
                }
            };
            titleCell.fill = {
                type: "pattern",
                pattern: "solid",
                fgColor: {
                    argb: "4CAF50"
                }
            };
            titleCell.alignment = {
                horizontal: "center",
                vertical: "middle"
            };

            // ====== 2. Header Tabel ====== //
            const headers = ["Tanggal", "Masuk", "Keluar", "Durasi Kerja", "lokasi", "Aktivitas"];
            const headerRow = worksheet.addRow(headers);

            headerRow.eachCell((cell) => {
                cell.font = {
                    bold: true,
                    color: {
                        argb: "FFFFFF"
                    }
                };
                cell.fill = {
                    type: "pattern",
                    pattern: "solid",
                    fgColor: {
                        argb: "2196F3"
                    }
                };
                cell.alignment = {
                    horizontal: "center",
                    vertical: "middle"
                };
            });

            // ====== 3. Isi Data Absensi ====== //
            @foreach($attendanceHistory as $record)
            worksheet.addRow([
                "{{ \Carbon\Carbon::parse($record->date)->translatedFormat('d M Y') }}",
                "{{ \Carbon\Carbon::parse($record->clock_in)->format('H:i') }}",
                "{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_out)->format('H:i') : '-' }}",
                "{{ $record->clock_out ? \Carbon\Carbon::parse($record->clock_in)->diff(\Carbon\Carbon::parse($record->clock_out))->format('%H:%I') : '-' }}",
                "{{ $record->lokasi}}",
                "{{ $record->aktivitas ? $record->aktivitas : 'Belum ada aktivitas' }}"
            ]);
            @endforeach

            // ====== 4. Styling Data ====== //
            worksheet.eachRow((row, rowNumber) => {
                row.eachCell((cell) => {
                    cell.border = {
                        top: {
                            style: "thin"
                        },
                        left: {
                            style: "thin"
                        },
                        bottom: {
                            style: "thin"
                        },
                        right: {
                            style: "thin"
                        }
                    };
                    cell.alignment = {
                        vertical: "middle",
                        wrapText: true
                    };

                    // Warna sel alternatif (zebra stripes)
                    if (rowNumber > 2 && rowNumber % 2 === 0) {
                        cell.fill = {
                            type: "pattern",
                            pattern: "solid",
                            fgColor: {
                                argb: "F9FAFB"
                            }
                        };
                    }
                });
            });

            // ====== 5. Atur Lebar Kolom ====== //
            worksheet.columns = [{
                    width: 15
                }, // Tanggal
                {
                    width: 10
                }, // Masuk
                {
                    width: 10
                }, // Keluar
                {
                    width: 12
                }, // Durasi Kerja
                {
                    width: 20
                }, // lokasi
                {
                    width: 30
                } // Aktivitas
            ];

            // ====== 6. Simpan File Excel ====== //
            workbook.xlsx.writeBuffer().then((buffer) => {
                const blob = new Blob([buffer], {
                    type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                });
                const link = document.createElement("a");
                link.href = URL.createObjectURL(blob);
                link.download = "Riwayat_Absensi.xlsx";
                link.click();
            });
        }
    </script>

</body>

</html>