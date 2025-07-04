
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$pageTitle = 'Dashboard';
$activePage = 'dashboard';
require_once 'templates/header_mahasiswa.php';

// Get user's enrolled praktikum
$sql = "SELECT p.nama_praktikum, p.deskripsi, u.nama as asisten_nama 
        FROM pendaftaran pd 
        JOIN praktikum p ON pd.praktikum_id = p.id 
        JOIN users u ON p.asisten_id = u.id 
        WHERE pd.mahasiswa_id = ? AND pd.status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$praktikum_list = $stmt->get_result();

// Get recent modules
$sql = "SELECT m.judul_modul, m.deadline, p.nama_praktikum, m.id as modul_id
        FROM modul m 
        JOIN praktikum p ON m.praktikum_id = p.id 
        JOIN pendaftaran pd ON pd.praktikum_id = p.id 
        WHERE pd.mahasiswa_id = ? AND pd.status = 'approved' AND m.status = 'aktif'
        ORDER BY m.deadline ASC LIMIT 5";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$recent_modules = $stmt->get_result();

// Get user's report stats
$sql = "SELECT 
        COUNT(*) as total_laporan,
        SUM(CASE WHEN l.status = 'graded' THEN 1 ELSE 0 END) as laporan_dinilai,
        SUM(CASE WHEN l.status = 'submitted' THEN 1 ELSE 0 END) as laporan_pending,
        AVG(CASE WHEN l.nilai IS NOT NULL THEN l.nilai ELSE NULL END) as rata_nilai
        FROM laporan l
        JOIN modul m ON l.modul_id = m.id
        JOIN praktikum p ON m.praktikum_id = p.id
        JOIN pendaftaran pd ON pd.praktikum_id = p.id
        WHERE pd.mahasiswa_id = ? AND pd.status = 'approved'";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
                <p class="text-blue-100 text-xl">Kelola praktikum dan laporan Anda dengan mudah dan efisien</p>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <!-- Total Laporan -->
        <div class="stat-card p-8" style="--icon-color-1: #3b82f6; --icon-color-2: #1d4ed8;">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stats['total_laporan'] ?? 0; ?></h3>
                    <p class="text-sm text-gray-600 font-medium">Total Laporan</p>
                </div>
                <div class="stat-icon p-5 text-white w-20 h-20 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Sudah Dinilai -->
        <div class="stat-card p-8" style="--icon-color-1: #10b981; --icon-color-2: #059669;">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stats['laporan_dinilai'] ?? 0; ?></h3>
                    <p class="text-sm text-gray-600 font-medium">Sudah Dinilai</p>
                </div>
                <div class="stat-icon p-5 text-white w-20 h-20 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Menunggu Nilai -->
        <div class="stat-card p-8" style="--icon-color-1: #f59e0b; --icon-color-2: #d97706;">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stats['laporan_pending'] ?? 0; ?></h3>
                    <p class="text-sm text-gray-600 font-medium">Menunggu Nilai</p>
                </div>
                <div class="stat-icon p-5 text-white w-20 h-20 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Rata-rata Nilai -->
        <div class="stat-card p-8" style="--icon-color-1: #8b5cf6; --icon-color-2: #7c3aed;">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-4xl font-bold text-gray-800 mb-2"><?php echo $stats['rata_nilai'] ? number_format($stats['rata_nilai'], 1) : '-'; ?></h3>
                    <p class="text-sm text-gray-600 font-medium">Rata-rata Nilai</p>
                </div>
                <div class="stat-icon p-5 text-white w-20 h-20 flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
        <!-- Praktikum yang Diikuti -->
        <div class="content-card shadow-2xl overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-blue-500 to-indigo-600">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                    </svg>
                    Praktikum yang Diikuti
                </h2>
            </div>
            <div class="p-8">
                <?php if ($praktikum_list->num_rows > 0): ?>
                    <div class="space-y-6">
                        <?php while ($row = $praktikum_list->fetch_assoc()): ?>
                            <div class="border border-gray-200 p-6 hover:border-blue-300 transition-all duration-300 bg-gradient-to-r from-gray-50 to-blue-50 transform hover:scale-105">
                                <h3 class="font-bold text-gray-800 mb-3 text-xl"><?php echo htmlspecialchars($row['nama_praktikum']); ?></h3>
                                <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white font-bold text-lg mr-4">
                                        <?php echo strtoupper(substr($row['asisten_nama'], 0, 1)); ?>
                                    </div>
                                    <span class="text-sm text-blue-600 font-medium">Asisten: <?php echo htmlspecialchars($row['asisten_nama']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-16">
                        <div class="w-24 h-24 bg-gradient-to-br from-gray-200 to-gray-300 mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 mb-3">Belum mengikuti praktikum apapun</h3>
                        <p class="text-gray-500 mb-6">Mulai bergabung dengan praktikum yang tersedia</p>
                        <a href="praktikum.php" class="bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white px-8 py-4 font-medium transition-all duration-300 transform hover:scale-105 shadow-lg">
                            Daftar Praktikum
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modul Terbaru -->
        <div class="content-card shadow-2xl overflow-hidden">
            <div class="px-8 py-6 bg-gradient-to-r from-green-500 to-teal-600">
                <h2 class="text-2xl font-bold text-white flex items-center">
                    <svg class="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Modul Terbaru
                </h2>
            </div>
            <div class="p-8">
                <?php if ($recent_modules->num_rows > 0): ?>
                    <div class="space-y-6">
                        <?php while ($row = $recent_modules->fetch_assoc()): ?>
                            <div class="border border-gray-200 p-6 hover:border-green-300 transition-all duration-300 bg-gradient-to-r from-gray-50 to-green-50 transform hover:scale-105">
                                <h3 class="font-bold text-gray-800 mb-2 text-xl"><?php echo htmlspecialchars($row['judul_modul']); ?></h3>
                                <p class="text-sm text-gray-600 mb-4"><?php echo htmlspecialchars($row['nama_praktikum']); ?></p>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center text-sm">
                                        <div class="w-4 h-4 bg-red-500 mr-3"></div>
                                        <span class="text-red-600 font-medium">Deadline: <?php echo date('d M Y, H:i', strtotime($row['deadline'])); ?></span>
                                    </div>
                                    <div class="px-4 py-2 bg-orange-100 text-orange-800 text-xs font-bold">
                                        URGENT
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-16">
                        <div class="w-24 h-24 bg-gradient-to-br from-gray-200 to-gray-300 mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-medium text-gray-900 mb-3">Tidak ada modul aktif</h3>
                        <p class="text-gray-500">Modul akan muncul ketika asisten menambahkan konten baru</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mt-12 content-card shadow-2xl">
        <div class="px-8 py-6 bg-gradient-to-r from-purple-500 to-pink-600">
            <h2 class="text-2xl font-bold text-white flex items-center">
                <svg class="w-8 h-8 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                Aksi Cepat
            </h2>
        </div>
        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <a href="praktikum.php" class="action-card p-10 border-2 border-blue-200 hover:border-blue-300 transition-all duration-400">
                    <div class="text-center">
                        <div class="action-icon w-20 h-20 bg-gradient-to-br from-blue-500 to-indigo-600 mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800 text-xl mb-3">Lihat Praktikum</h3>
                        <p class="text-gray-600">Jelajahi praktikum yang tersedia</p>
                    </div>
                </a>
                
                <a href="laporan.php" class="action-card p-10 border-2 border-green-200 hover:border-green-300 transition-all duration-400">
                    <div class="text-center">
                        <div class="action-icon w-20 h-20 bg-gradient-to-br from-green-500 to-emerald-600 mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800 text-xl mb-3">Upload Laporan</h3>
                        <p class="text-gray-600">Kumpulkan tugas praktikum</p>
                    </div>
                </a>
                
                <a href="nilai.php" class="action-card p-10 border-2 border-purple-200 hover:border-purple-300 transition-all duration-400">
                    <div class="text-center">
                        <div class="action-icon w-20 h-20 bg-gradient-to-br from-purple-500 to-violet-600 mx-auto mb-6 flex items-center justify-center">
                            <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-800 text-xl mb-3">Lihat Nilai</h3>
                        <p class="text-gray-600">Pantau progress penilaian</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once 'templates/footer_mahasiswa.php'; ?>
