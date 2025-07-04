
<?php
session_start();
require_once 'config.php';

$message = '';
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $role = $_POST['role'];
    $nim = trim($_POST['nim'] ?? '');

    // Validasi input
    if (empty($nama) || empty($email) || empty($password) || empty($confirm_password) || empty($role)) {
        $message = "Semua field harus diisi!";
    } elseif ($password !== $confirm_password) {
        $message = "Password dan konfirmasi password tidak sama!";
    } elseif (strlen($password) < 6) {
        $message = "Password minimal 6 karakter!";
    } elseif ($role === 'mahasiswa' && empty($nim)) {
        $message = "NIM harus diisi untuk mahasiswa!";
    } else {
        // Cek apakah email sudah ada
        $sql = "SELECT id FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $message = "Email sudah terdaftar!";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user baru
            $sql = "INSERT INTO users (nama, email, password, role, nim) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $nama, $email, $hashed_password, $role, $nim);
            
            if ($stmt->execute()) {
                $success = true;
                $message = "Registrasi berhasil! Silakan login.";
            } else {
                $message = "Terjadi kesalahan saat registrasi. Silakan coba lagi.";
            }
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.9);
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <!-- Back to home -->
        <div class="text-center mb-8">
            <a href="../index.php" class="text-white hover:text-gray-200 transition-colors duration-200">
                <svg class="w-6 h-6 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Beranda
            </a>
        </div>

        <!-- Register Card -->
        <div class="glass-effect p-8 shadow-2xl">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-800 mb-2">Daftar SIMPRAK</h1>
                <p class="text-gray-600">Buat akun baru untuk mengakses sistem</p>
            </div>

            <?php if ($message): ?>
                <div class="<?php echo $success ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700'; ?> border px-4 py-3 mb-6">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!$success): ?>
            <form method="POST" action="" class="space-y-6">
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" required 
                           class="w-full px-4 py-3 border border-gray-200 focus:border-blue-500 focus:outline-none transition-colors duration-200"
                           placeholder="Masukkan nama lengkap">
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input type="email" id="email" name="email" required 
                           class="w-full px-4 py-3 border border-gray-200 focus:border-blue-500 focus:outline-none transition-colors duration-200"
                           placeholder="Masukkan email">
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                    <select id="role" name="role" required onchange="toggleNim()" 
                            class="w-full px-4 py-3 border border-gray-200 focus:border-blue-500 focus:outline-none transition-colors duration-200">
                        <option value="">Pilih Role</option>
                        <option value="mahasiswa">Mahasiswa</option>
                        <option value="asisten">Asisten</option>
                    </select>
                </div>

                <div id="nim-field" class="hidden">
                    <label for="nim" class="block text-sm font-medium text-gray-700 mb-2">NIM</label>
                    <input type="text" id="nim" name="nim" 
                           class="w-full px-4 py-3 border border-gray-200 focus:border-blue-500 focus:outline-none transition-colors duration-200"
                           placeholder="Masukkan NIM">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                    <input type="password" id="password" name="password" required 
                           class="w-full px-4 py-3 border border-gray-200 focus:border-blue-500 focus:outline-none transition-colors duration-200"
                           placeholder="Masukkan password (min. 6 karakter)">
                </div>

                <div>
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" required 
                           class="w-full px-4 py-3 border border-gray-200 focus:border-blue-500 focus:outline-none transition-colors duration-200"
                           placeholder="Ulangi password">
                </div>

                <button type="submit" 
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white py-3 px-4 font-medium transition-colors duration-200 shadow-lg">
                    Daftar
                </button>
            </form>
            <?php endif; ?>

            <div class="mt-8 text-center">
                <p class="text-gray-600">
                    Sudah punya akun? 
                    <a href="login.php" class="text-blue-600 hover:text-blue-500 font-medium">Login di sini</a>
                </p>
            </div>
        </div>
    </div>

    <script>
        function toggleNim() {
            const role = document.getElementById('role').value;
            const nimField = document.getElementById('nim-field');
            const nimInput = document.getElementById('nim');
            
            if (role === 'mahasiswa') {
                nimField.classList.remove('hidden');
                nimInput.required = true;
            } else {
                nimField.classList.add('hidden');
                nimInput.required = false;
                nimInput.value = '';
            }
        }
    </script>
</body>
</html>
