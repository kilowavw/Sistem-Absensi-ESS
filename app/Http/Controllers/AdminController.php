<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Attendance;

class AdminController extends Controller
{

    // Tampilkan Dashboard Admin
    public function dashboard()
    {
        $totalUsers = User::count();
        $totalAttendance = Attendance::count();

        // Rata-rata jam masuk (dalam menit)
        $averageCheckIn = Attendance::whereNotNull('clock_in')->get()
            ->map(function ($attendance) {
                return \Carbon\Carbon::parse($attendance->clock_in)->diffInMinutes('00:00');
            })
            ->average();

        // Konversi rata-rata menit ke format HH:MM
        $averageCheckInFormatted = $averageCheckIn ? gmdate("H:i", $averageCheckIn * 60) : '-';

        // Hitung keterlambatan (misalnya jika masuk setelah pukul 09:00)
        $averageLate = Attendance::whereTime('clock_in', '>', '09:00:00')->count();

        // Ambil semua pengguna dengan role "user" saja
        $users = User::with('attendances')->where('role', 'user')->get();

        return view('admin.dashboard', compact('totalUsers', 'totalAttendance', 'averageCheckInFormatted', 'averageLate', 'users'));
    }




    // Kelola User
    public function manageUsers()
    {
        $users = User::all();
        return view('admin.users', compact('users'));
    }

    // Lihat Riwayat Absensi Semua User
    public function attendanceHistory()
    {
        $attendances = Attendance::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.attendance', compact('attendances'));
    }


    public function createUser()
    {
        return view('admin.create-user');
    }

    public function storeUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:user,admin',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role,
        ]);

        return redirect()->route('admin.users')->with('success', 'User berhasil ditambahkan');
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        return view('admin.edit-user', compact('user'));
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|in:user,admin',
        ]);

        $user->update($request->only('name', 'email', 'role'));

        return redirect()->route('admin.users')->with('success', 'User berhasil diperbarui');
    }

    public function storeOrUpdateUser(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . ($request->id ?? ''),
            'password' => $request->id ? 'nullable|min:6' : 'required|min:6',
            'role' => 'required|in:user,admin',
        ]);

        $user = $request->id ? User::findOrFail($request->id) : new User;

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        $user->save();

        return redirect()->route('admin.users')->with('success', $request->id ? 'User diperbarui' : 'User ditambahkan');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus');
    }
}
