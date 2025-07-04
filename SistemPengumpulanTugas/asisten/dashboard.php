
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is asisten
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'asisten') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
$user_id = $_SESSION['user_id'];

// Get statistics
$stats = [];

// Total praktikum yang diampu
$sql = "SELECT COUNT(*) as total FROM praktikum WHERE asisten_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['praktikum'] = $stmt->get_result()->fetch_assoc()['total'];

// Total modul
$sql = "SELECT COUNT(*) as total FROM modul m JOIN praktikum p ON m.praktikum_id = p.id WHERE p.asisten_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['modul'] = $stmt->get_result()->fetch_assoc()['total'];

// Total laporan masuk
$sql = "SELECT COUNT(*) as total FROM laporan l 
        JOIN modul m ON l.modul_id = m.id 
        JOIN praktikum p ON m.praktikum_id = p.id 
        WHERE p.asisten_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['laporan'] = $stmt->get_result()->fetch_assoc()['total'];

// Laporan belum dinilai
$sql = "SELECT COUNT(*) as total FROM laporan l 
        JOIN modul m ON l.modul_id = m.id 
        JOIN praktikum p ON m.praktikum_id = p.id 
        WHERE p.asisten_id = ? AND l.status = 'submitted'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stats['belum_dinilai'] = $stmt->get_result()->fetch_assoc()['total'];

// Recent reports
$sql = "SELECT l.*, m.judul_modul, p.nama_praktikum, u.nama as mahasiswa_nama
        FROM laporan l 
        JOIN modul m ON l.modul_id = m.id 
        JOIN praktikum p ON m.praktikum_id = p.id 
        JOIN users u ON l.mahasiswa_id = u.id
        WHERE p.asisten_id = ? AND l.status = 'submitted'
        ORDER BY l.tanggal_upload DESC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$recent_reports = $stmt->get_result();

require_once 'templates/header.php';
?>

<style>
    .stat-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.3);
        transform-style: preserve-3d;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .stat-card:hover {
        transform: translateY(-12px) rotateX(8deg) rotateY(5deg);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.2);
    }
    
    .stat-icon {
        background: linear-gradient(135deg, var(--icon-color-1), var(--icon-color-2));
        transition: all 0.3s ease;
        filter: drop-shadow(0 8px 16px rgba(0,0,0,0.1));
    }
    
    .stat-card:hover .stat-icon {
        transform: scale(1.15) rotateY(15deg);
        filter: drop-shadow(0 12px 24px rgba(0,0,0,0.2));
    }
    
    .content-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.95), rgba(255,255,255,0.8));
        backdrop-filter: blur(25px);
        border: 1px solid rgba(255,255,255,0.2);
        transition: all 0.3s ease;
    }
    
    .content-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
    }
    
    .welcome-section {
        background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
        position: relative;
        overflow: hidden;
    }
    
    .welcome-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><radialGradient id="a" cx="50%" cy="0%" r="50%"><stop offset="0%" stop-color="rgba(255,255,255,.1)"/><stop offset="100%" stop-color="rgba(255,255,255,0)"/></radialGradient></defs><rect width="100" height="20" fill="url(%23a)"/></svg>');
        opacity: 0.3;
    }
    
    .action-card {
        background: linear-gradient(135deg, rgba(255,255,255,0.9), rgba(255,255,255,0.7));
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.3);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        transform-style: preserve-3d;
    }
    
    .action-card:hover {
        transform: translateY(-10px) rotateX(5deg);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
    }
    
    .action-icon {
        transition: all 0.3s ease;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
    }
    
    .action-card:hover .action-icon {
        transform: scale(1.2) rotateY(15deg);
        filter: drop-shadow(0 8px 16px rgba(0,0,0,0.2));
    }
</style>

<main class="flex-1 overflow-x-hidden overflow-y-auto bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 p-8">
    <!-- Welcome Section -->
    <div class="mb-10">
        <div class="welcome-section text-white p-10 shadow-2xl relative">
            <div class="relative z-10">
                <h1 class="text-5xl font-bold mb-4">Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>!</h1>
                <p class="text-gray-300 text-xl">Kelola praktikum dan nilai mahasiswa dengan mudah dan efisien</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <div class="stat-card p-8" style="--icon-color-1: #3b82f6; --icon-color-2: #1d4ed8;">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stats['praktikum']; ?></h3>
                    <p class="text-sm text-gray-600 font-medium">Praktikum Diampu</p>
                </div>
                <div class="stat-icon p-5 text-white w-20 h-20 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card p-8" style="--icon-color-1: #10b981; --icon-color-2: #059669;">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stats['modul']; ?></h3>
                    <p class="text-sm text-gray-600 font-medium">Total Modul</p>
                </div>
                <div class="stat-icon p-5 text-white w-20 h-20 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card p-8" style="--icon-color-1: #8b5cf6; --icon-color-2: #7c3aed;">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stats['laporan']; ?></h3>
                    <p class="text-sm text-gray-600 font-medium">Total Laporan</p>
                </div>
                <div class="stat-icon p-5 text-white w-20 h-20 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                    </svg>
                </div>
            </div>
        </div>

        <div class="stat-card p-8" style="--icon-color-1: #ef4444; --icon-color-2: #dc2626;">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stats['belum_dinilai']; ?></h3>
                    <p class="text-sm text-gray-600 font-medium">Belum Dinilai</p>
                </div>
                <div class="stat-icon p-5 text-white w-20 h-20 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Recent Reports -->
        <div class="content-card shadow-2xl overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-purple-500 to-indigo-600">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Laporan Terbaru
                </h2>
            </div>
            <div class="p-8">
                <?php if ($recent_reports->num_rows > 0): ?>
                    <div class="space-y-6">
                        <?php while ($row = $recent_reports->fetch_assoc()): ?>
                            <div class="border border-gray-200 p-6 bg-gradient-to-r from-gray-50 to-purple-50 transition-all duration-300 transform hover:scale-105">
                                <div class="flex justify-between items-start mb-3">
                                    <h3 class="font-bold text-gray-800 text-lg"><?php echo htmlspecialchars($row['judul_modul']); ?></h3>
                                    <span class="px-3 py-1 text-xs font-bold bg-yellow-100 text-yellow-800">
                                        Belum Dinilai
                                    </span>
                                </div>
                                <p class="text-sm text-gray-600 mb-2"><?php echo htmlspecialchars($row['nama_praktikum']); ?></p>
                                <p class="text-sm text-blue-600 font-medium">Mahasiswa: <?php echo htmlspecialchars($row['mahasiswa_nama']); ?></p>
                                <p class="text-xs text-gray-500 mt-3">
                                    Upload: <?php echo date('d M Y, H:i', strtotime($row['tanggal_upload'])); ?>
                                </p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                    <div class="mt-6 text-center">
                        <a href="laporan_masuk.php" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-6 py-3 font-medium transition-all duration-300 transform hover:scale-105">
                            Lihat Semua Laporan
                        </a>
                    </div>
                <?php else: ?>
                    <div class="text-center py-16">
                        <div class="w-24 h-24 bg-gradient-to-br from-gray-200 to-gray-300 mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <p class="text-gray-500 text-lg">Tidak ada laporan baru</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="content-card shadow-2xl overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-green-500 to-teal-600">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Aksi Cepat
                </h2>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 gap-6">
                    <a href="kelola_praktikum.php" class="action-card p-8 border-2 border-blue-200 hover:border-blue-300 transition-all duration-400">
                        <div class="flex items-center">
                            <div class="action-icon w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 mr-6 flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg mb-2">Kelola Praktikum</h3>
                                <p class="text-sm text-gray-600">Tambah atau edit praktikum</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="kelola_modul.php" class="action-card p-8 border-2 border-green-200 hover:border-green-300 transition-all duration-400">
                        <div class="flex items-center">
                            <div class="action-icon w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 mr-6 flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg mb-2">Kelola Modul</h3>
                                <p class="text-sm text-gray-600">Upload materi dan kelola modul</p>
                            </div>
                        </div>
                    </a>
                    
                    <a href="laporan_masuk.php" class="action-card p-8 border-2 border-purple-200 hover:border-purple-300 transition-all duration-400">
                        <div class="flex items-center">
                            <div class="action-icon w-16 h-16 bg-gradient-to-br from-purple-500 to-violet-600 mr-6 flex items-center justify-center">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800 text-lg mb-2">Nilai Laporan</h3>
                                <p class="text-sm text-gray-600">Beri nilai dan feedback</p>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'templates/footer.php'; ?>
