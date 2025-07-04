
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Kelola Modul';
$activePage = 'kelola_modul';
$user_id = $_SESSION['user_id'];

$message = '';
$success = false;

// Handle CRUD operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_modul'])) {
        $praktikum_id = $_POST['praktikum_id'];
        $judul_modul = $_POST['judul_modul'];
        $deskripsi = $_POST['deskripsi'];
        $deadline = $_POST['deadline'];
        $status = $_POST['status'];
        
        $file_modul = null;
        if (isset($_FILES['file_modul']) && $_FILES['file_modul']['error'] == 0) {
            $upload_dir = '../uploads/modul/';
            $file_name = time() . '_' . $_FILES['file_modul']['name'];
            $upload_path = $upload_dir . $file_name;
            
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            if (move_uploaded_file($_FILES['file_modul']['tmp_name'], $upload_path)) {
                $file_modul = $file_name;
            }
        }
        
        $sql = "INSERT INTO modul (praktikum_id, judul_modul, deskripsi, file_modul, deadline, status) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("isssss", $praktikum_id, $judul_modul, $deskripsi, $file_modul, $deadline, $status);
        
        if ($stmt->execute()) {
            $success = true;
            $message = 'Modul berhasil ditambahkan!';
        } else {
            $message = 'Gagal menambahkan modul!';
        }
    }
    
    if (isset($_POST['edit_modul'])) {
        $modul_id = $_POST['modul_id'];
        $judul_modul = $_POST['judul_modul'];
        $deskripsi = $_POST['deskripsi'];
        $deadline = $_POST['deadline'];
        $status = $_POST['status'];
        
        $file_modul_update = '';
        if (isset($_FILES['file_modul']) && $_FILES['file_modul']['error'] == 0) {
            $upload_dir = '../uploads/modul/';
            $file_name = time() . '_' . $_FILES['file_modul']['name'];
            $upload_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['file_modul']['tmp_name'], $upload_path)) {
                $file_modul_update = ", file_modul = '$file_name'";
            }
        }
        
        $sql = "UPDATE modul SET judul_modul = ?, deskripsi = ?, deadline = ?, status = ? $file_modul_update WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $judul_modul, $deskripsi, $deadline, $status, $modul_id);
        
        if ($stmt->execute()) {
            $success = true;
            $message = 'Modul berhasil diupdate!';
        } else {
            $message = 'Gagal mengupdate modul!';
        }
    }
    
    if (isset($_POST['delete_modul'])) {
        $modul_id = $_POST['modul_id'];
        
        $sql = "DELETE FROM modul WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $modul_id);
        
        if ($stmt->execute()) {
            $success = true;
            $message = 'Modul berhasil dihapus!';
        } else {
            $message = 'Gagal menghapus modul!';
        }
    }
}

// Get praktikum list for this asisten
$sql = "SELECT * FROM praktikum WHERE asisten_id = ? ORDER BY nama_praktikum";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$praktikum_list = $stmt->get_result();

// Get modules
$sql = "SELECT m.*, p.nama_praktikum 
        FROM modul m 
        JOIN praktikum p ON m.praktikum_id = p.id 
        WHERE p.asisten_id = ? 
        ORDER BY p.nama_praktikum, m.deadline";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$modules = $stmt->get_result();

require_once 'templates/header.php';
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Success/Error Messages -->
        <?php if (!empty($message)): ?>
            <div class="mb-6 p-4 <?php echo $success ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Add Module Form -->
        <div class="bg-white card-shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Tambah Modul Baru</h2>
            </div>
            <div class="p-6">
                <form method="POST" enctype="multipart/form-data" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Praktikum</label>
                            <select name="praktikum_id" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Pilih Praktikum</option>
                                <?php 
                                $praktikum_list->data_seek(0);
                                while ($row = $praktikum_list->fetch_assoc()): 
                                ?>
                                    <option value="<?php echo $row['id']; ?>"><?php echo htmlspecialchars($row['nama_praktikum']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Judul Modul</label>
                            <input type="text" name="judul_modul" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                        <textarea name="deskripsi" rows="3" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">File Materi</label>
                            <input type="file" name="file_modul" accept=".pdf,.doc,.docx,.ppt,.pptx" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Format: PDF, DOC, DOCX, PPT, PPTX</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deadline</label>
                            <input type="datetime-local" name="deadline" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                    </div>
                    
                    <button type="submit" name="add_modul" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 font-medium transition-colors duration-200">
                        Tambah Modul
                    </button>
                </form>
            </div>
        </div>

        <!-- Modules List -->
        <div class="bg-white card-shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Daftar Modul</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Praktikum & Modul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Deadline</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">File</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($modules->num_rows > 0): ?>
                            <?php while ($row = $modules->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_praktikum']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['judul_modul']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d M Y, H:i', strtotime($row['deadline'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold <?php echo $row['status'] == 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php if ($row['file_modul']): ?>
                                            <a href="../uploads/modul/<?php echo htmlspecialchars($row['file_modul']); ?>" class="text-blue-600 hover:text-blue-500">
                                                Download
                                            </a>
                                        <?php else: ?>
                                            <span class="text-gray-400">Tidak ada</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button onclick="editModule(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="text-blue-600 hover:text-blue-500 mr-3">
                                            Edit
                                        </button>
                                        <form method="POST" class="inline" onsubmit="return confirm('Yakin ingin menghapus modul ini?')">
                                            <input type="hidden" name="modul_id" value="<?php echo $row['id']; ?>">
                                            <button type="submit" name="delete_modul" class="text-red-600 hover:text-red-500">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Modul</h3>
                                    <p class="text-gray-600">Tambahkan modul pertama Anda</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Edit Modul</h3>
                </div>
                <form method="POST" enctype="multipart/form-data" id="editForm">
                    <div class="p-6 space-y-4">
                        <input type="hidden" name="modul_id" id="edit_modul_id">
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Judul Modul</label>
                            <input type="text" name="judul_modul" id="edit_judul_modul" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea name="deskripsi" id="edit_deskripsi" rows="3" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">File Materi (Opsional)</label>
                            <input type="file" name="file_modul" accept=".pdf,.doc,.docx,.ppt,.pptx" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deadline</label>
                            <input type="datetime-local" name="deadline" id="edit_deadline" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select name="status" id="edit_status" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="aktif">Aktif</option>
                                <option value="tidak_aktif">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors duration-200">
                            Batal
                        </button>
                        <button type="submit" name="edit_modul" class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 transition-colors duration-200">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
function editModule(module) {
    document.getElementById('edit_modul_id').value = module.id;
    document.getElementById('edit_judul_modul').value = module.judul_modul;
    document.getElementById('edit_deskripsi').value = module.deskripsi;
    document.getElementById('edit_deadline').value = module.deadline.replace(' ', 'T');
    document.getElementById('edit_status').value = module.status;
    document.getElementById('editModal').classList.remove('hidden');
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>

<?php require_once 'templates/footer.php'; ?>
