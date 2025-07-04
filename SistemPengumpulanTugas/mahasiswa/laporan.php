
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Upload Laporan';
$activePage = 'laporan';

$message = '';
$success = false;

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_laporan'])) {
    $modul_id = $_POST['modul_id'];
    $mahasiswa_id = $_SESSION['user_id'];
    
    // Check if file was uploaded
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] == 0) {
        $upload_dir = '../uploads/laporan/';
        $file_name = time() . '_' . $_FILES['file_laporan']['name'];
        $upload_path = $upload_dir . $file_name;
        
        // Create directory if not exists
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        if (move_uploaded_file($_FILES['file_laporan']['tmp_name'], $upload_path)) {
            // Check if laporan already exists
            $check_sql = "SELECT id FROM laporan WHERE mahasiswa_id = ? AND modul_id = ?";
            $check_stmt = $conn->prepare($check_sql);
            $check_stmt->bind_param("ii", $mahasiswa_id, $modul_id);
            $check_stmt->execute();
            $existing = $check_stmt->get_result();
            
            if ($existing->num_rows > 0) {
                // Update existing laporan
                $sql = "UPDATE laporan SET file_laporan = ?, tanggal_upload = NOW(), status = 'submitted' WHERE mahasiswa_id = ? AND modul_id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("sii", $file_name, $mahasiswa_id, $modul_id);
            } else {
                // Insert new laporan
                $sql = "INSERT INTO laporan (mahasiswa_id, modul_id, file_laporan, status) VALUES (?, ?, ?, 'submitted')";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("iis", $mahasiswa_id, $modul_id, $file_name);
            }
            
            if ($stmt->execute()) {
                $success = true;
                $message = 'Laporan berhasil diupload!';
            } else {
                $message = 'Gagal menyimpan laporan ke database!';
            }
        } else {
            $message = 'Gagal mengupload file!';
        }
    } else {
        $message = 'Silakan pilih file untuk diupload!';
    }
}

// Get available modules for upload
$sql = "SELECT m.*, p.nama_praktikum, l.file_laporan, l.status as laporan_status, l.nilai, l.feedback
        FROM modul m 
        JOIN praktikum p ON m.praktikum_id = p.id 
        JOIN pendaftaran pd ON pd.praktikum_id = p.id 
        LEFT JOIN laporan l ON l.modul_id = m.id AND l.mahasiswa_id = ?
        WHERE pd.mahasiswa_id = ? AND pd.status = 'approved' AND m.status = 'aktif'
        ORDER BY m.deadline ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $_SESSION['user_id'], $_SESSION['user_id']);
$stmt->execute();
$modules = $stmt->get_result();

require_once 'templates/header_mahasiswa.php';
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
        <!-- Success/Error Messages -->
        <?php if (!empty($message)): ?>
            <div class="mb-6 p-4 <?php echo $success ? 'bg-green-50 border border-green-200 text-green-700' : 'bg-red-50 border border-red-200 text-red-700'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Upload Laporan Section -->
        <div class="bg-white card-shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Daftar Modul & Upload Laporan</h2>
            </div>
            
            <div class="p-6">
                <?php if ($modules->num_rows > 0): ?>
                    <div class="space-y-6">
                        <?php while ($row = $modules->fetch_assoc()): ?>
                            <div class="border border-gray-200 p-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($row['judul_modul']); ?></h3>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($row['nama_praktikum']); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm <?php echo strtotime($row['deadline']) < time() ? 'text-red-600' : 'text-gray-600'; ?>">
                                            <strong>Deadline:</strong> <?php echo date('d M Y, H:i', strtotime($row['deadline'])); ?>
                                        </p>
                                        <?php if ($row['laporan_status']): ?>
                                            <span class="inline-block mt-2 px-3 py-1 text-xs font-medium
                                                <?php 
                                                echo $row['laporan_status'] == 'graded' ? 'bg-green-100 text-green-800' : 
                                                     ($row['laporan_status'] == 'submitted' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'); 
                                                ?>">
                                                <?php echo ucfirst($row['laporan_status']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                
                                <!-- Download Material -->
                                <?php if ($row['file_modul']): ?>
                                    <div class="mb-4">
                                        <a href="../uploads/modul/<?php echo htmlspecialchars($row['file_modul']); ?>" 
                                           class="inline-flex items-center text-blue-600 hover:text-blue-500">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                            </svg>
                                            Download Materi
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Upload Form -->
                                <?php if (strtotime($row['deadline']) > time()): ?>
                                    <form method="POST" enctype="multipart/form-data" class="border-t pt-4">
                                        <input type="hidden" name="modul_id" value="<?php echo $row['id']; ?>">
                                        
                                        <div class="flex items-center space-x-4">
                                            <div class="flex-1">
                                                <input type="file" name="file_laporan" accept=".pdf,.doc,.docx" 
                                                       class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                                <p class="text-xs text-gray-500 mt-1">Format: PDF, DOC, DOCX (Max: 5MB)</p>
                                            </div>
                                            <button type="submit" name="upload_laporan" 
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 font-medium transition-colors duration-200">
                                                <?php echo $row['file_laporan'] ? 'Update' : 'Upload'; ?>
                                            </button>
                                        </div>
                                    </form>
                                <?php else: ?>
                                    <div class="border-t pt-4">
                                        <p class="text-red-600 text-sm">Deadline telah terlewat</p>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Current Upload Status -->
                                <?php if ($row['file_laporan']): ?>
                                    <div class="mt-4 p-3 bg-gray-50 border-l-4 border-blue-500">
                                        <p class="text-sm font-medium text-gray-800">File Terupload:</p>
                                        <p class="text-sm text-gray-600"><?php echo htmlspecialchars($row['file_laporan']); ?></p>
                                        <?php if ($row['nilai']): ?>
                                            <div class="mt-2">
                                                <p class="text-sm"><strong>Nilai:</strong> <?php echo $row['nilai']; ?></p>
                                                <?php if ($row['feedback']): ?>
                                                    <p class="text-sm"><strong>Feedback:</strong> <?php echo htmlspecialchars($row['feedback']); ?></p>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Modul</h3>
                        <p class="text-gray-600">Belum ada modul yang tersedia untuk upload laporan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
