
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is an asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['tambah_praktikum'])) {
        $nama_praktikum = $_POST['nama_praktikum'];
        $deskripsi = $_POST['deskripsi'];
        
        $sql = "INSERT INTO praktikum (nama_praktikum, deskripsi, asisten_id) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $nama_praktikum, $deskripsi, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Praktikum berhasil ditambahkan!";
        } else {
            $error_message = "Gagal menambahkan praktikum!";
        }
    }
    
    if (isset($_POST['edit_praktikum'])) {
        $id = $_POST['praktikum_id'];
        $nama_praktikum = $_POST['nama_praktikum'];
        $deskripsi = $_POST['deskripsi'];
        
        $sql = "UPDATE praktikum SET nama_praktikum = ?, deskripsi = ? WHERE id = ? AND asisten_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $nama_praktikum, $deskripsi, $id, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Praktikum berhasil diupdate!";
        } else {
            $error_message = "Gagal mengupdate praktikum!";
        }
    }
    
    if (isset($_POST['hapus_praktikum'])) {
        $id = $_POST['praktikum_id'];
        
        $sql = "DELETE FROM praktikum WHERE id = ? AND asisten_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $id, $user_id);
        
        if ($stmt->execute()) {
            $success_message = "Praktikum berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus praktikum!";
        }
    }
}

// Get praktikum list
$praktikum_sql = "SELECT * FROM praktikum WHERE asisten_id = ? ORDER BY created_at DESC";
$praktikum_stmt = $conn->prepare($praktikum_sql);
$praktikum_stmt->bind_param("i", $user_id);
$praktikum_stmt->execute();
$praktikum_result = $praktikum_stmt->get_result();

// Get praktikum for editing
$edit_praktikum = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $edit_sql = "SELECT * FROM praktikum WHERE id = ? AND asisten_id = ?";
    $edit_stmt = $conn->prepare($edit_sql);
    $edit_stmt->bind_param("ii", $edit_id, $user_id);
    $edit_stmt->execute();
    $edit_result = $edit_stmt->get_result();
    $edit_praktikum = $edit_result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Praktikum - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'templates/header.php'; ?>
    
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Kelola Mata Praktikum</h1>
                <p class="text-gray-600 mt-2">Manajemen mata praktikum yang Anda ampu</p>
            </div>

            <!-- Messages -->
            <?php if (isset($success_message)): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 mb-6">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 mb-6">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <!-- Form Tambah/Edit Praktikum -->
            <div class="bg-white shadow-lg mb-8 p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">
                    <?php echo $edit_praktikum ? 'Edit' : 'Tambah'; ?> Praktikum
                </h2>
                <form method="POST" class="space-y-4">
                    <?php if ($edit_praktikum): ?>
                        <input type="hidden" name="praktikum_id" value="<?php echo $edit_praktikum['id']; ?>">
                    <?php endif; ?>
                    
                    <div>
                        <label for="nama_praktikum" class="block text-sm font-medium text-gray-700 mb-2">Nama Praktikum</label>
                        <input type="text" id="nama_praktikum" name="nama_praktikum" required 
                               value="<?php echo htmlspecialchars($edit_praktikum['nama_praktikum'] ?? ''); ?>"
                               class="w-full px-4 py-3 border border-gray-200 focus:border-blue-500 focus:outline-none transition-colors duration-200">
                    </div>
                    
                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" rows="4" 
                                  class="w-full px-4 py-3 border border-gray-200 focus:border-blue-500 focus:outline-none transition-colors duration-200"><?php echo htmlspecialchars($edit_praktikum['deskripsi'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <button type="submit" name="<?php echo $edit_praktikum ? 'edit_praktikum' : 'tambah_praktikum'; ?>" 
                                class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 font-medium transition-colors duration-200">
                            <?php echo $edit_praktikum ? 'Update' : 'Tambah'; ?> Praktikum
                        </button>
                        
                        <?php if ($edit_praktikum): ?>
                            <a href="kelola_praktikum.php" 
                               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 font-medium transition-colors duration-200">
                                Batal
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Daftar Praktikum -->
            <div class="bg-white shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Daftar Praktikum</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Praktikum</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deskripsi</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Dibuat</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php while ($praktikum = $praktikum_result->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($praktikum['nama_praktikum']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900 max-w-xs truncate">
                                            <?php echo htmlspecialchars($praktikum['deskripsi'] ?? 'Tidak ada deskripsi'); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-500">
                                            <?php echo date('d/m/Y', strtotime($praktikum['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                        <a href="kelola_modul.php?praktikum_id=<?php echo $praktikum['id']; ?>" 
                                           class="text-green-600 hover:text-green-900">Kelola Modul</a>
                                        <a href="?edit=<?php echo $praktikum['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-900">Edit</a>
                                        <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus praktikum ini?')">
                                            <input type="hidden" name="praktikum_id" value="<?php echo $praktikum['id']; ?>">
                                            <button type="submit" name="hapus_praktikum" 
                                                    class="text-red-600 hover:text-red-900">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($praktikum_result->num_rows == 0): ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Praktikum</h3>
                        <p class="text-gray-600">Tambahkan praktikum pertama Anda menggunakan form di atas</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'templates/footer.php'; ?>
</body>
</html>
