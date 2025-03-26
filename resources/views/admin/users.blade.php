<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">
    <div class="max-w-4xl mx-auto mt-10 p-6 bg-white shadow-md rounded-lg">
        <h2 class="text-2xl font-bold mb-4">Kelola Pengguna</h2>

        <a href="{{ route('admin.dashboard') }}" class="text-blue-600">← Kembali ke Dashboard</a>

        <!-- Tombol Tambah User -->
        <button onclick="openModal('addModal')" class="mt-4 bg-blue-600 text-white px-4 py-2 rounded-lg">
            + Tambah User
        </button>

        <table class="w-full mt-4 border-collapse border border-gray-300">
            <thead>
                <tr class="bg-gray-200">
                    <th class="border border-gray-300 p-2">Nama</th>
                    <th class="border border-gray-300 p-2">Email</th>
                    <th class="border border-gray-300 p-2">Role</th>
                    <th class="border border-gray-300 p-2">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr class="text-center">
                    <td class="border border-gray-300 p-2">{{ $user->name }}</td>
                    <td class="border border-gray-300 p-2">{{ $user->email }}</td>
                    <td class="border border-gray-300 p-2">{{ ucfirst($user->role) }}</td>
                    <td class="border border-gray-300 p-2">
                        <button onclick="editUser({{ $user }})" class="text-blue-600">Edit</button>
                        <form action="{{ route('admin.deleteUser', $user->id) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 ml-2">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- MODAL TAMBAH / EDIT USER -->
    <div id="userModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 flex justify-center items-center">
        <div class="bg-white p-6 rounded-lg w-1/3">
            <h3 id="modalTitle" class="text-lg font-semibold mb-4">Tambah User</h3>

            <form id="userForm" method="POST">
                @csrf
                <input type="hidden" id="userId" name="id">

                <label class="block">Nama:</label>
                <input type="text" id="name" name="name" required class="w-full border p-2 mb-3">

                <label class="block">Email:</label>
                <input type="email" id="email" name="email" required class="w-full border p-2 mb-3">

                <label class="block">Password:</label>
                <input type="password" id="password" name="password" class="w-full border p-2 mb-3">

                <label class="block">Role:</label>
                <select id="role" name="role" class="w-full border p-2 mb-3">
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>

                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Simpan</button>
                <button type="button" onclick="closeModal()" class="ml-2 bg-gray-500 text-white px-4 py-2 rounded-lg">Batal</button>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('userForm').reset();
            document.getElementById('userId').value = '';
            document.getElementById('modalTitle').innerText = 'Tambah User';
            document.getElementById('userForm').action = "{{ route('admin.storeOrUpdateUser') }}";
            document.getElementById('password').required = true;
            document.getElementById('userModal').classList.remove('hidden');
        }


        function editUser(user) {
            document.getElementById('userId').value = user.id;
            document.getElementById('name').value = user.name;
            document.getElementById('email').value = user.email;
            document.getElementById('role').value = user.role;
            document.getElementById('password').required = false;
            document.getElementById('modalTitle').innerText = 'Edit User';
            document.getElementById('userForm').action = "{{ route('admin.storeOrUpdateUser') }}";
            document.getElementById('userModal').classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('userModal').classList.add('hidden');
        }
    </script>
</body>

</html>