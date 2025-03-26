<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class UserController extends Controller
{
    // Menampilkan dashboard user
    public function dashboard()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        // Cek absensi hari ini
        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        // Ambil semua absensi user untuk kalender & riwayat dengan pagination
        $attendanceHistory = Attendance::where('user_id', $user->id)
            ->orderBy('date', 'desc') // ORDER BY sebelum paginate!
            ->paginate(5);

        return view('user.dashboard', compact('user', 'attendance', 'attendanceHistory'));
    }

    // Proses Clock In / Clock Out
    public function toggleAttendance(Request $request)
    {
        $user = Auth::user();
        $today = now()->toDateString();
        $now = now();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            // Ambil lokasi dari request (dikirim dari frontend)
            $lokasi = $request->input('lokasi', 'Bandung, West Java, ID');

            // Clock In + Simpan lokasi
            Attendance::create([
                'user_id' => $user->id,
                'date' => $today,
                'clock_in' => $now,
                'status' => 'present',
                'lokasi' => $lokasi, // Simpan lokasi di database
            ]);
        }

        return redirect()->route('user.dashboard');
    }


    public function submitActivity(Request $request)
    {
        $request->validate(['aktivitas' => 'required|string']);

        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->whereNull('clock_out')
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Absensi tidak ditemukan.'], 404);
        }

        // Simpan aktivitas dan Clock Out
        $attendance->update([
            'aktivitas' => $request->aktivitas,
            'clock_out' => now()
        ]);

        return response()->json(['success' => true]);
    }

    public function cancelClockOut(Request $request)
    {
        $attendance = Attendance::where('user_id', auth()->id())
            ->whereDate('date', today())
            ->whereNotNull('clock_out')
            ->first();

        if (!$attendance) {
            return response()->json(['success' => false, 'message' => 'Tidak ada Clock Out untuk dibatalkan.'], 404);
        }

        // Batalkan Clock Out dengan menghapus data clock_out dan aktivitasnya
        $attendance->update([
            'clock_out' => null,
            'aktivitas' => null,
        ]);

        return response()->json(['success' => true, 'message' => 'Clock Out berhasil dibatalkan.']);
    }

    public function autoClockOut(Request $request)
    {
        $yesterday = now()->subDay()->toDateString();
        $userId = Auth::id();

        // Cari absensi kemarin yang belum Clock Out
        $attendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $yesterday)
            ->whereNull('clock_out')
            ->first();

        if ($attendance) {
            $attendance->update([
                'clock_out' => "$yesterday 23:59:59",
                'aktivitas' => 'User lupa clock out. Mohon lihat hari setelahnya untuk melihat aktivitas hari ini.'
            ]);

            return response()->json(['success' => true, 'message' => 'Auto Clock Out dilakukan.']);
        }

        return response()->json(['success' => false, 'message' => 'Tidak ada absensi yang perlu diupdate.']);
    }
}
