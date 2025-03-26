<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | ESS-Absensi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="flex flex-col items-center justify-center min-h-screen bg-cover bg-center"
    style="background-image: url('http://ess.intens.co.id/background.png'); background-size: cover; background-position: center; min-height: 100vh;">

    <!-- Logo -->
    <img src="http://ess.intens.co.id/icon_intens.png" alt="Logo Intens" class="w-40 mb-4">

    <!-- Card Login -->
    <div class="p-6 rounded-lg shadow-lg w-96" style="background-color: rgba(255, 255, 255, 0.5);">
        <h2 class="text-2xl font-bold mb-4 text-center">Login</h2>
        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700">Email</label>
                <input type="email" name="email" class="w-full p-2 border border-gray-300 rounded mt-1" required>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700">Password</label>
                <input type="password" name="password" class="w-full p-2 border border-gray-300 rounded mt-1" required>
            </div>
            <button type="submit" class="w-full bg-blue-500 text-white p-2 rounded hover:bg-blue-600">
                Login
            </button>
        </form>
    </div>

    <!-- Notifikasi SweetAlert2 -->
    @if (session('success'))
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Login Berhasil!',
            text: '{{ session("success") }}',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = "{{ url()->previous() }}";
        });
    </script>
    @endif

    @if (session('error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Gagal!',
            text: '{{ session("error") }}',
        });
    </script>
    @endif

</body>

</html>