
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Praktikum Saya';
$activePage = 'praktikum_saya';

// Get user's enrolled praktikum
$sql = "SELECT p.*, u.nama as asisten_nama, pd.status, pd.tanggal_daftar 
        FROM pendaftaran pd 
        JOIN praktikum p ON pd.praktikum_id = p.id 
        JOIN users u ON p.asisten_id = u.id 
        WHERE pd.mahasiswa_id = ?
        ORDER BY pd.tanggal_daftar DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$enrolled_praktikum = $stmt->get_result();

require_once 'templates/header_mahasiswa.php';
?>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
    <div class="max-w-7xl mx-auto">
        <!-- Stats Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <?php
            // Get stats
            $total_enrolled = $enrolled_praktikum->num_rows;
            $enrolled_praktikum->data_seek(0); // Reset pointer
            
            $approved_count = 0;
            $pending_count = 0;
            while ($row = $enrolled_praktikum->fetch_assoc()) {
                if ($row['status'] == 'approved') $approved_count++;
                if ($row['status'] == 'pending') $pending_count++;
            }
            $enrolled_praktikum->data_seek(0); // Reset pointer again
            ?>
            
            <div class="bg-white card-shadow p-6 border-l-4 border-blue-500">
                <div class="flex items-center">
                    <div class="bg-blue-100 p-3 mr-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $total_enrolled; ?></h3>
                        <p class="text-sm text-gray-500">Total Praktikum</p>
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
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $approved_count; ?></h3>
                        <p class="text-sm text-gray-500">Diterima</p>
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
                        <h3 class="text-2xl font-bold text-gray-800"><?php echo $pending_count; ?></h3>
                        <p class="text-sm text-gray-500">Menunggu</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Praktikum List -->
        <div class="bg-white card-shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Daftar Praktikum Saya</h2>
            </div>
            
            <div class="p-6">
                <?php if ($enrolled_praktikum->num_rows > 0): ?>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <?php while ($row = $enrolled_praktikum->fetch_assoc()): ?>
                            <div class="border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
                                <div class="flex justify-between items-start mb-4">
                                    <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
                                    <span class="px-3 py-1 text-xs font-medium 
                                        <?php 
                                        echo $row['status'] == 'approved' ? 'bg-green-100 text-green-800' : 
                                             ($row['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); 
                                        ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </div>
                                
                                <p class="text-gray-600 mb-3"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                
                                <div class="text-sm text-gray-500 mb-4">
                                    <p><strong>Asisten:</strong> <?php echo htmlspecialchars($row['asisten_nama']); ?></p>
                                    <p><strong>Tanggal Daftar:</strong> <?php echo date('d M Y', strtotime($row['tanggal_daftar'])); ?></p>
                                </div>
                                
                                <?php if ($row['status'] == 'approved'): ?>
                                    <div class="flex space-x-3">
                                        <a href="detail_praktikum.php?id=<?php echo $row['id']; ?>" 
                                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium transition-colors duration-200">
                                            Lihat Detail
                                        </a>
                                        <a href="laporan.php?praktikum_id=<?php echo $row['id']; ?>" 
                                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-sm font-medium transition-colors duration-200">
                                            Upload Laporan
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Praktikum</h3>
                        <p class="text-gray-600 mb-4">Anda belum mendaftar ke praktikum apapun</p>
                        <a href="praktikum.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 font-medium transition-colors duration-200">
                            Cari Praktikum
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
