<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body class="bg-gray-100 p-6">

    <div class="max-w-6xl mx-auto bg-white p-6 rounded-xl shadow-lg">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-3xl font-semibold text-gray-800">Riwayat Absensi</h2>
            <a href="{{ route('admin.dashboard') }}" class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg transition duration-300">
                <i class="fa-solid fa-arrow-left"></i> Kembali
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr class="bg-gray-800 text-white">
                        <th class="py-3 px-6 text-left"><i class="fa-solid fa-user"></i> Nama User</th>
                        <th class="py-3 px-6 text-left"><i class="fa-solid fa-calendar"></i> Tanggal</th>
                        <th class="py-3 px-6 text-left"><i class="fa-solid fa-clock"></i> Jam Masuk</th>
                        <th class="py-3 px-6 text-left"><i class="fa-solid fa-clock"></i> Jam Keluar</th>
                        <th class="py-3 px-6 text-left"><i class="fa-solid fa-hourglass-half"></i> Total Jam</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                    <tr class="border-b border-gray-200">
                        <td class="py-3 px-6">{{ $attendance->user->name }}</td>
                        <td class="py-3 px-6">{{ \Carbon\Carbon::parse($attendance->date)->format('d-m-Y') }}</td>
                        <td class="py-3 px-6">{{ $attendance->clock_in ? \Carbon\Carbon::parse($attendance->clock_in)->format('H:i:s') : '-' }}</td>
                        <td class="py-3 px-6">{{ $attendance->clock_out ? \Carbon\Carbon::parse($attendance->clock_out)->format('H:i:s') : '-' }}</td>
                        <td class="py-3 px-6">
                            {{ $attendance->clock_in && $attendance->clock_out 
                            ? \Carbon\Carbon::parse($attendance->clock_out)->diff($attendance->clock_in)->format('%H:%I:%S') 
                            : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</body>

</html>