
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Lihat Nilai';
$activePage = 'nilai';

// Get user's grades
$sql = "SELECT l.*, m.judul_modul, p.nama_praktikum, u.nama as asisten_nama
        FROM laporan l
        JOIN modul m ON l.modul_id = m.id
        JOIN praktikum p ON m.praktikum_id = p.id
        JOIN users u ON p.asisten_id = u.id
        WHERE l.mahasiswa_id = ?
        ORDER BY l.tanggal_upload DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$reports = $stmt->get_result();

// Calculate statistics
$total_reports = $reports->num_rows;
$reports->data_seek(0);
$graded_count = 0;
$total_score = 0;
$temp_reports = [];

while ($row = $reports->fetch_assoc()) {
    $temp_reports[] = $row;
    if ($row['nilai'] !== null) {
        $graded_count++;
        $total_score += $row['nilai'];
    }
}

$average_score = $graded_count > 0 ? $total_score / $graded_count : 0;

require_once 'templates/header_mahasiswa.php';
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
        <!-- Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white card-shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_reports; ?></h3>
                        <p class="text-sm text-gray-500">Total Laporan</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white card-shadow p-6 border-l-4 border-green-500">
                <div class="flex items-center">
                    <div class="bg-green-100 p-3 mr-4">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $graded_count; ?></h3>
                        <p class="text-sm text-gray-500">Sudah Dinilai</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white card-shadow p-6 border-l-4 border-yellow-500">
                <div class="flex items-center">
                    <div class="bg-yellow-100 p-3 mr-4">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_reports - $graded_count; ?></h3>
                        <p class="text-sm text-gray-500">Menunggu Nilai</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white card-shadow p-6 border-l-4 border-purple-500">
                <div class="flex items-center">
                    <div class="bg-purple-100 p-3 mr-4">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo number_format($average_score, 1); ?></h3>
                        <p class="text-sm text-gray-500">Rata-rata Nilai</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Reports Table -->
        <div class="bg-white card-shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Riwayat Nilai Laporan</h2>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Praktikum & Modul</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Upload</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nilai</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Feedback</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Asisten</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (!empty($temp_reports)): ?>
                            <?php foreach ($temp_reports as $row): ?>
                                <tr class="hover:bg-gray-50">
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
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($row['nilai'] !== null): ?>
                                            <div class="flex items-center">
                                                <span class="text-2xl font-bold 
                                                    <?php 
                                                    echo $row['nilai'] >= 80 ? 'text-green-600' : 
                                                         ($row['nilai'] >= 60 ? 'text-yellow-600' : 'text-red-600'); 
                                                    ?>">
                                                    <?php echo number_format($row['nilai'], 1); ?>
                                                </span>
                                                <span class="text-sm text-gray-500 ml-1">/100</span>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($row['feedback']): ?>
                                            <div class="max-w-xs">
                                                <p class="text-sm text-gray-900 truncate" title="<?php echo htmlspecialchars($row['feedback']); ?>">
                                                    <?php echo htmlspecialchars($row['feedback']); ?>
                                                </p>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <?php echo htmlspecialchars($row['asisten_nama']); ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                                    </svg>
                                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Laporan</h3>
                                    <p class="text-gray-600">Anda belum mengupload laporan apapun</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
