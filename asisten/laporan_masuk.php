
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Kelola User';
$activePage = 'kelola_user';
$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = $_POST['role'];
        $nim = isset($_POST['nim']) ? trim($_POST['nim']) : null;
        
        if (empty($nama) || empty($email) || empty($password) || empty($role)) {
            $message = "Semua field harus diisi!";
        } else {
            
            $check_sql = "SELECT id FROM users WHERE email = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("s", $email);
            $check_stmt->execute();
            
            if ($check_stmt->get_result()->num_rows > 0) {
                $message = "Email sudah terdaftar!";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (nama, email, password, role, nim) VALUES (?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sssss", $nama, $email, $hashed_password, $role, $nim);
                
                if ($stmt->execute()) {
                    $message = "User berhasil ditambahkan!";
                    $success = true;
                } else {
                    $message = "Gagal menambahkan user!";
                }
            }
        }
    } elseif ($action == 'edit') {
        $id = $_POST['id'];
        $nama = trim($_POST['nama']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        $nim = isset($_POST['nim']) ? trim($_POST['nim']) : null;
        
        if (empty($nama) || empty($email) || empty($role)) {
            $message = "Nama, email, dan role harus diisi!";
        } else {
            $sql = "UPDATE users SET nama = ?, email = ?, role = ?, nim = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $nama, $email, $role, $nim, $id);
            
            if ($stmt->execute()) {
                $message = "User berhasil diupdate!";
                $success = true;
            } else {
                $message = "Gagal mengupdate user!";
            }
        }
    } elseif ($action == 'delete') {
        $id = $_POST['id'];
        
        // Don't allow deleting self
        if ($id == $_SESSION['user_id']) {
            $message = "Tidak dapat menghapus akun sendiri!";
        } else {
            $sql = "DELETE FROM users WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = "User berhasil dihapus!";
                $success = true;
            } else {
                $message = "Gagal menghapus user!";
            }
        }
    }
}

// Get all users
$users_sql = "SELECT id, nama, email, role, nim, created_at FROM users ORDER BY created_at DESC";
$users_result = $conn->query($users_sql);

// Get user for editing if requested
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_sql = "SELECT * FROM users WHERE id = ?";
    $edit_stmt = $conn->prepare($edit_sql);
    $edit_stmt->bind_param("i", $edit_id);
    $edit_stmt->execute();
    $edit_user = $edit_stmt->get_result()->fetch_assoc();
}

require_once 'templates/header.php';
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Kelola Pengguna</h1>
        <p class="text-gray-600 mt-2">Manajemen akun mahasiswa dan asisten</p>
    </div>

    <!-- Messages -->
    <?php if ($message): ?>
        <div class="mb-6 p-4 border-l-4 <?php echo $success ? 'bg-green-50 border-green-400 text-green-700' : 'bg-red-50 border-red-400 text-red-700'; ?>">
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add/Edit User Form -->
        <div class="lg:col-span-1">
            <div class="bg-white shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-purple-600">
                    <h2 class="text-xl font-bold text-white">
                        <?php echo $edit_user ? 'Edit Pengguna' : 'Tambah Pengguna'; ?>
                    </h2>
                </div>
                <div class="p-6">
                    <form method="POST" action="" class="space-y-6">
                        <input type="hidden" name="action" value="<?php echo $edit_user ? 'edit' : 'add'; ?>">
                        <?php if ($edit_user): ?>
                            <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                        <?php endif; ?>

                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                            <input type="text" id="nama" name="nama" required 
                                   value="<?php echo $edit_user ? htmlspecialchars($edit_user['nama']) : ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo $edit_user ? htmlspecialchars($edit_user['email']) : ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        </div>

                        <?php if (!$edit_user): ?>
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                            <input type="password" id="password" name="password" required 
                                   class="w-full px-4 py-3 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        </div>
                        <?php endif; ?>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                            <select id="role" name="role" required onchange="toggleNIM()"
                                    class="w-full px-4 py-3 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                                <option value="">Pilih Role</option>
                                <option value="mahasiswa" <?php echo ($edit_user && $edit_user['role'] == 'mahasiswa') ? 'selected' : ''; ?>>Mahasiswa</option>
                                <option value="asisten" <?php echo ($edit_user && $edit_user['role'] == 'asisten') ? 'selected' : ''; ?>>Asisten</option>
                            </select>
                        </div>

                        <div id="nim-field" style="display: <?php echo ($edit_user && $edit_user['role'] == 'mahasiswa') ? 'block' : 'none'; ?>">
                            <label for="nim" class="block text-sm font-medium text-gray-700 mb-2">NIM</label>
                            <input type="text" id="nim" name="nim" 
                                   value="<?php echo $edit_user ? htmlspecialchars($edit_user['nim']) : ''; ?>"
                                   class="w-full px-4 py-3 border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200">
                        </div>

                        <div class="flex space-x-3">
                            <button type="submit" 
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-3 px-6 font-medium transition-all duration-200 transform hover:scale-105">
                                <?php echo $edit_user ? 'Update' : 'Tambah'; ?> Pengguna
                            </button>
                            <?php if ($edit_user): ?>
                                <a href="kelola_user.php" 
                                   class="bg-gray-500 hover:bg-gray-600 text-white py-3 px-6 font-medium transition-colors duration-200">
                                    Batal
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Users List -->
        <div class="lg:col-span-2">
            <div class="bg-white shadow-sm border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-500 to-teal-600">
                    <h2 class="text-xl font-bold text-white">Daftar Pengguna</h2>
                </div>
                <div class="overflow-hidden">
                    <?php if ($users_result->num_rows > 0): ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIM</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Daftar</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php while ($user = $users_result->fetch_assoc()): ?>
                                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold text-sm mr-3">
                                                        <?php echo strtoupper(substr($user['nama'], 0, 1)); ?>
                                                    </div>
                                                    <div class="text-sm font-medium text-gray-900">
                                                        <?php echo htmlspecialchars($user['nama']); ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($user['email']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex px-2 py-1 text-xs font-semibold <?php echo $user['role'] == 'asisten' ? 'bg-purple-100 text-purple-800' : 'bg-blue-100 text-blue-800'; ?>">
                                                    <?php echo ucfirst($user['role']); ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo $user['nim'] ? htmlspecialchars($user['nim']) : '-'; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?php echo date('d M Y', strtotime($user['created_at'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <div class="flex space-x-2">
                                                    <a href="?edit=<?php echo $user['id']; ?>" 
                                                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 text-xs transition-colors duration-200">
                                                        Edit
                                                    </a>
                                                    <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                        <form method="POST" action="" class="inline" onsubmit="return confirm('Yakin ingin menghapus user ini?')">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                                            <button type="submit" 
                                                                    class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 text-xs transition-colors duration-200">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-12">
                            <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Pengguna</h3>
                            <p class="text-gray-600">Tambahkan pengguna pertama menggunakan form di samping</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
function toggleNIM() {
    const roleSelect = document.getElementById('role');
    const nimField = document.getElementById('nim-field');
    const nimInput = document.getElementById('nim');
    
    if (roleSelect.value === 'mahasiswa') {
        nimField.style.display = 'block';
        nimInput.required = true;
    } else {
        nimField.style.display = 'none';
        nimInput.required = false;
        nimInput.value = '';
    }
}
</script>

<?php require_once 'templates/footer.php'; ?>
