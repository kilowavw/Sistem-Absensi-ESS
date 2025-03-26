<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        roboto: ["Roboto", "sans-serif"],
                    },
                },
            },
        };
    </script>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/exceljs@4.3.0/dist/exceljs.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/FileSaver.js/2.0.5/FileSaver.min.js"></script>

    <style>
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f9f9f9;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .glass {
            background: rgba(255, 255, 255, 0.8);
            border-radius: 16px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
            backdrop-filter: blur(8.5px);
            -webkit-backdrop-filter: blur(8.5px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }
    </style>

</head>

<body class="bg-gray-100">
    <div class="max-w-4xl w-full mx-auto mt-10 p-8 glass shadow-lg rounded-2xl">

        <!-- Jam Real-Time -->
        <div class="text-center text-xl font-semibold text-gray-800 mb-4">
            <span id="realTimeClock">00:00:00</span> WIB
        </div>

        <h2 class="text-3xl font-semibold text-gray-800 mb-6 text-center">Dashboard Admin</h2>
        <button onclick="exportToExcel()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg mt-4">
            <i class="fa-solid fa-file-excel"></i> Ekspor ke Excel
        </button>
        <button onclick="exportDailyReport()" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg mt-4">
            <i class="fa-solid fa-file-excel"></i> Ekspor ke Absen Harian
        </button>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="p-6 bg-white text-gray-800 rounded-xl shadow-sm">
                <h3 class="text-lg font-medium mb-2">Total Pengguna</h3>
                <p class="text-3xl font-bold">{{ $totalUsers }}</p>
            </div>
            <div class="p-6 bg-white text-gray-800 rounded-xl shadow-sm">
                <h3 class="text-lg font-medium mb-2">Total Absensi</h3>
                <p class="text-3xl font-bold">{{ $totalAttendance }}</p>
            </div>
            <div class="p-6 bg-white text-gray-800 rounded-xl shadow-sm">
                <h3 class="text-lg font-medium mb-2">Rata-rata Jam Masuk</h3>
                <p class="text-xl font-semibold">{{ $averageCheckInFormatted ?? '-' }}</p>
            </div>
            <div class="p-6 bg-white text-gray-800 rounded-xl shadow-sm">
                <h3 class="text-lg font-medium mb-2">Jumlah Keterlambatan</h3>
                <p class="text-3xl font-bold">{{ $averageLate }}</p>
            </div>
        </div>

        <div class="mt-8 flex flex-col md:flex-row gap-4 justify-center">
            <a href="{{ route('admin.users') }}" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-3 rounded-xl transition duration-300">
                Kelola User
            </a>
            <a href="{{ route('admin.attendance') }}" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-3 rounded-xl transition duration-300">
                Riwayat Absensi
            </a>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white px-6 py-3 rounded-xl transition duration-300">
                    Logout
                </button>
            </form>
        </div>
    </div>



    <script>
        function exportToExcel() {
            const workbook = new ExcelJS.Workbook();
            workbook.creator = "Absensi App";
            workbook.created = new Date();

            const users = @json($users);

            if (users.length === 0) {
                alert("Tidak ada data pengguna untuk diekspor.");
                return;
            }

            users.forEach(user => {
                const worksheet = workbook.addWorksheet(user.name);

                // ====== 1. JUDUL HEADER ====== //
                worksheet.mergeCells("A1:G1");
                const titleCell = worksheet.getCell("A1");
                titleCell.value = "Laporan Absensi Karyawan";
                titleCell.font = {
                    size: 16,
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

                // ====== 2. HEADER TABEL ====== //
                const headerRow = worksheet.addRow([
                    "Hari", "Tanggal", "Jam Masuk", "Jam Keluar", "Total Waktu", "Lokasi", "Aktivitas"
                ]);

                headerRow.eachCell(cell => {
                    cell.font = {
                        bold: true,
                        color: {
                            argb: "FFFFFF"
                        },
                        size: 12
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
                        vertical: "middle",
                        wrapText: true
                    };
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
                });

                // ====== 3. ATUR LEBAR KOLOM ====== //
                worksheet.columns = [{
                        width: 15
                    }, // Hari
                    {
                        width: 15
                    }, // Tanggal
                    {
                        width: 10
                    }, // Jam Masuk
                    {
                        width: 10
                    }, // Jam Keluar
                    {
                        width: 12
                    }, // Total Waktu
                    {
                        width: 20
                    }, // Lokasi
                    {
                        width: 30
                    } // Aktivitas
                ];

                // ====== 4. HELPERS ====== //
                function getDayName(dateString) {
                    const days = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];
                    return days[new Date(dateString).getDay()];
                }

                // ====== 5. ISI DATA ABSENSI ====== //
                user.attendances.forEach(attendance => {
                    const clockIn = attendance.clock_in ? new Date(attendance.clock_in) : null;
                    const clockOut = attendance.clock_out ? new Date(attendance.clock_out) : null;

                    let totalTime = "-";
                    if (clockIn && clockOut) {
                        const diffMs = clockOut - clockIn;
                        totalTime = new Date(diffMs).toISOString().substr(11, 8);
                    }

                    const row = worksheet.addRow([
                        getDayName(attendance.date),
                        attendance.date,
                        clockIn ? clockIn.toLocaleTimeString() : '-',
                        clockOut ? clockOut.toLocaleTimeString() : '-',
                        totalTime,
                        attendance.lokasi || "-",
                        attendance.aktivitas || "-"
                    ]);

                    // ====== 6. STYLING ISI DATA ====== //
                    row.eachCell((cell, colNumber) => {
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
                            horizontal: "center",
                            vertical: "middle",
                            wrapText: true
                        };

                        // Warna sel bergantian (Zebra Stripes)
                        if (row.number % 2 === 0) {
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

                // ====== 7. JIKA DATA KOSONG ====== //
                if (user.attendances.length === 0) {
                    const emptyRow = worksheet.addRow(["-", "-", "-", "-", "-", "-", "Belum ada data absensi"]);
                    emptyRow.eachCell(cell => {
                        cell.alignment = {
                            horizontal: "center",
                            vertical: "middle"
                        };
                        cell.font = {
                            italic: true,
                            color: {
                                argb: "FF757575"
                            }
                        };
                    });
                }

            });

            // ====== 8. SIMPAN FILE EXCEL ====== //
            workbook.xlsx.writeBuffer().then(buffer => {
                saveAs(new Blob([buffer], {
                    type: "application/octet-stream"
                }), "Laporan_Absensi.xlsx");
            });
        }


        function fetchLocationByIP() {
            fetch("http://ip-api.com/json/")
                .then(response => response.json())
                .then(data => {
                    if (data.status === "success") {
                        let latitude = data.lat;
                        let longitude = data.lon;
                        let timeZone = data.timezone;
                        let city = data.city;
                        let region = data.regionName; // Nama provinsi
                        let country = data.country;

                        updateTime(timeZone, latitude, longitude, city, region, country);
                    } else {
                        console.error("Gagal mendapatkan lokasi:", data.message);
                        document.getElementById("realTimeClock").innerText = "Lokasi tidak ditemukan.";
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    document.getElementById("realTimeClock").innerText = "Gagal memuat lokasi.";
                });
        }

        function updateTime(timeZone, latitude, longitude, city, region, country) {
            function displayTime() {
                let now = new Date();
                let options = {
                    timeZone,
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit'
                };
                let formattedTime = new Intl.DateTimeFormat('id-ID', options).format(now);

                document.getElementById("realTimeClock").innerHTML = `
            ${formattedTime} <br> 
            Lokasi: ${city}, ${region}, ${country} <br> 
            Koordinat: (${latitude.toFixed(5)}, ${longitude.toFixed(5)})
        `;
            }

            setInterval(displayTime, 1000);
            displayTime();
        }

        // Panggil fungsi untuk mendapatkan lokasi berdasarkan IP
        fetchLocationByIP();


        //Daily Report
        function exportDailyReport() {
            const workbook = new ExcelJS.Workbook();
            workbook.creator = "Absensi App";
            workbook.created = new Date();

            const users = @json($users);
            const today = new Date().toISOString().split("T")[0]; // Format YYYY-MM-DD

            // ====== 1. CEK JIKA DATA KOSONG ====== //
            const filteredUsers = users.map(user => ({
                ...user,
                attendances: user.attendances.filter(att => att.date === today) // Ambil hanya data hari ini
            })).filter(user => user.attendances.length > 0); // Hanya user yang punya absensi hari ini

            if (filteredUsers.length === 0) {
                alert("Tidak ada data absensi untuk hari ini.");
                return;
            }

            const worksheet = workbook.addWorksheet("Daily Report");

            // ====== 2. JUDUL HEADER ====== //
            worksheet.mergeCells("A1:E1");
            const titleCell = worksheet.getCell("A1");
            titleCell.value = `Daily Report - ${today}`;
            titleCell.font = {
                size: 16,
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

            // ====== 3. HEADER TABEL ====== //
            const headerRow = worksheet.addRow(["Nama", "Jam Masuk", "Jam Keluar", "Total Waktu", "Aktivitas"]);
            headerRow.eachCell(cell => {
                cell.font = {
                    bold: true,
                    color: {
                        argb: "FFFFFF"
                    },
                    size: 12
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
                    vertical: "middle",
                    wrapText: true
                };
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
            });

            // ====== 4. ATUR LEBAR KOLOM ====== //
            worksheet.columns = [{
                    width: 20
                }, // Nama
                {
                    width: 10
                }, // Jam Masuk
                {
                    width: 10
                }, // Jam Keluar
                {
                    width: 12
                }, // Total Waktu
                {
                    width: 30
                } // Aktivitas
            ];

            // ====== 5. ISI DATA ABSENSI ====== //
            filteredUsers.forEach(user => {
                user.attendances.forEach(attendance => {
                    const clockIn = attendance.clock_in ? new Date(attendance.clock_in) : null;
                    const clockOut = attendance.clock_out ? new Date(attendance.clock_out) : null;

                    let totalTime = "-";
                    if (clockIn && clockOut) {
                        const diffMs = clockOut - clockIn;
                        totalTime = new Date(diffMs).toISOString().substr(11, 8);
                    }

                    const row = worksheet.addRow([
                        user.name,
                        clockIn ? clockIn.toLocaleTimeString() : '-',
                        clockOut ? clockOut.toLocaleTimeString() : '-',
                        totalTime,
                        attendance.aktivitas || "-"
                    ]);

                    // ====== 6. STYLING ISI DATA ====== //
                    row.eachCell((cell, colNumber) => {
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
                            horizontal: "center",
                            vertical: "middle",
                            wrapText: true
                        };

                        // Warna sel bergantian (Zebra Stripes)
                        if (row.number % 2 === 0) {
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
            });

            // ====== 7. AUTO-FIT TINGGI BARIS ====== //
            worksheet.eachRow(row => {
                row.height = 25;
            });

            // ====== 8. FREEZE HEADER ====== //
            worksheet.views = [{
                state: "frozen",
                ySplit: 2
            }];

            // ====== 9. SIMPAN FILE EXCEL ====== //
            workbook.xlsx.writeBuffer().then(buffer => {
                saveAs(new Blob([buffer], {
                    type: "application/octet-stream"
                }), `Daily_Report_${today}.xlsx`);
            });
        }
    </script>



</body>

</html>