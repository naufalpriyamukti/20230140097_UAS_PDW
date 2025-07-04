
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Laporan Masuk';
$activePage = 'laporan_masuk';
$user_id = $_SESSION['user_id'];

$message = '';
$success = false;

// Handle grading
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['grade_report'])) {
    $laporan_id = $_POST['laporan_id'];
    $nilai = $_POST['nilai'];
    $feedback = $_POST['feedback'];
    
    $sql = "UPDATE laporan SET nilai = ?, feedback = ?, status = 'graded' WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("dsi", $nilai, $feedback, $laporan_id);
    
    if ($stmt->execute()) {
        $success = true;
        $message = 'Nilai berhasil diberikan!';
    } else {
        $message = 'Gagal memberikan nilai!';
    }
}

// Filters
$filter_praktikum = isset($_GET['praktikum']) ? $_GET['praktikum'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_mahasiswa = isset($_GET['mahasiswa']) ? $_GET['mahasiswa'] : '';

// Build query with filters
$where_conditions = ["p.asisten_id = ?"];
$params = [$user_id];
$param_types = "i";

if ($filter_praktikum) {
    $where_conditions[] = "p.id = ?";
    $params[] = $filter_praktikum;
    $param_types .= "i";
}

if ($filter_status) {
    $where_conditions[] = "l.status = ?";
    $params[] = $filter_status;
    $param_types .= "s";
}

if ($filter_mahasiswa) {
    $where_conditions[] = "u.nama LIKE ?";
    $params[] = "%$filter_mahasiswa%";
    $param_types .= "s";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Get reports
$sql = "SELECT l.*, m.judul_modul, p.nama_praktikum, u.nama as mahasiswa_nama, u.nim
        FROM laporan l 
        JOIN modul m ON l.modul_id = m.id 
        JOIN praktikum p ON m.praktikum_id = p.id 
        JOIN users u ON l.mahasiswa_id = u.id
        $where_clause
        ORDER BY l.tanggal_upload DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param($param_types, ...$params);
$stmt->execute();
$reports = $stmt->get_result();

// Get praktikum list for filter
$sql = "SELECT * FROM praktikum WHERE asisten_id = ? ORDER BY nama_praktikum";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$praktikum_list = $stmt->get_result();

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

        <!-- Filters -->
        <div class="bg-white card-shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Filter Laporan</h2>
            </div>
            <div class="p-6">
                <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Praktikum</label>
                        <select name="praktikum" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Praktikum</option>
                            <?php while ($row = $praktikum_list->fetch_assoc()): ?>
                                <option value="<?php echo $row['id']; ?>" <?php echo $filter_praktikum == $row['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($row['nama_praktikum']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Semua Status</option>
                            <option value="submitted" <?php echo $filter_status == 'submitted' ? 'selected' : ''; ?>>Submitted</option>
                            <option value="graded" <?php echo $filter_status == 'graded' ? 'selected' : ''; ?>>Graded</option>
                            <option value="late" <?php echo $filter_status == 'late' ? 'selected' : ''; ?>>Late</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Mahasiswa</label>
                        <input type="text" name="mahasiswa" value="<?php echo htmlspecialchars($filter_mahasiswa); ?>" placeholder="Cari nama..." class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 font-medium transition-colors duration-200 mr-2">
                            Filter
                        </button>
                        <a href="laporan_masuk.php" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 font-medium transition-colors duration-200">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="bg-white card-shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Daftar Laporan Masuk</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Mahasiswa</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Praktikum & Modul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Upload</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if ($reports->num_rows > 0): ?>
                            <?php while ($row = $reports->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['mahasiswa_nama']); ?></div>
                                            <div class="text-sm text-gray-500">NIM: <?php echo htmlspecialchars($row['nim']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($row['nama_praktikum']); ?></div>
                                            <div class="text-sm text-gray-500"><?php echo htmlspecialchars($row['judul_modul']); ?></div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo date('d M Y, H:i', strtotime($row['tanggal_upload'])); ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold
                                            <?php 
                                            echo $row['status'] == 'graded' ? 'bg-green-100 text-green-800' : 
                                                 ($row['status'] == 'submitted' ? 'bg-blue-100 text-blue-800' : 'bg-yellow-100 text-yellow-800'); 
                                            ?>">
                                            <?php echo ucfirst($row['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <?php echo $row['nilai'] ? number_format($row['nilai'], 1) : '-'; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="../uploads/laporan/<?php echo htmlspecialchars($row['file_laporan']); ?>" class="text-blue-600 hover:text-blue-500 mr-3">
                                            Download
                                        </a>
                                        <button onclick="gradeReport(<?php echo htmlspecialchars(json_encode($row)); ?>)" class="text-green-600 hover:text-green-500">
                                            <?php echo $row['status'] == 'graded' ? 'Edit Nilai' : 'Beri Nilai'; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Laporan</h3>
                                    <p class="text-gray-600">Belum ada laporan yang masuk</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Grade Modal -->
    <div id="gradeModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white max-w-md w-full">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Beri Nilai Laporan</h3>
                </div>
                <form method="POST" id="gradeForm">
                    <div class="p-6 space-y-4">
                        <input type="hidden" name="laporan_id" id="grade_laporan_id">
                        
                        <div id="reportInfo" class="bg-gray-50 p-4 mb-4">
                            <!-- Report info will be populated by JavaScript -->
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nilai (0-100)</label>
                            <input type="number" name="nilai" id="grade_nilai" min="0" max="100" step="0.1" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Feedback</label>
                            <textarea name="feedback" id="grade_feedback" rows="4" class="w-full px-3 py-2 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Berikan feedback untuk mahasiswa..."></textarea>
                        </div>
                    </div>
                    <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
                        <button type="button" onclick="closeGradeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors duration-200">
                            Batal
                        </button>
                        <button type="submit" name="grade_report" class="px-4 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors duration-200">
                            Simpan Nilai
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
function gradeReport(report) {
    document.getElementById('grade_laporan_id').value = report.id;
    document.getElementById('grade_nilai').value = report.nilai || '';
    document.getElementById('grade_feedback').value = report.feedback || '';
    
    // Populate report info
    document.getElementById('reportInfo').innerHTML = `
        <div class="text-sm">
            <p><strong>Mahasiswa:</strong> ${report.mahasiswa_nama} (${report.nim})</p>
            <p><strong>Praktikum:</strong> ${report.nama_praktikum}</p>
            <p><strong>Modul:</strong> ${report.judul_modul}</p>
            <p><strong>Upload:</strong> ${new Date(report.tanggal_upload).toLocaleDateString('id-ID')}</p>
        </div>
    `;
    
    document.getElementById('gradeModal').classList.remove('hidden');
}

function closeGradeModal() {
    document.getElementById('gradeModal').classList.add('hidden');
}
</script>

<?php require_once 'templates/footer.php'; ?>
