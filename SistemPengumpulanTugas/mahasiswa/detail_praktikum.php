
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$praktikum_id = $_GET['id'] ?? 0;

// Check if mahasiswa is registered for this praktikum
$check_sql = "SELECT * FROM pendaftaran WHERE mahasiswa_id = ? AND praktikum_id = ?";
$check_stmt = $conn->prepare($check_sql);
$check_stmt->bind_param("ii", $user_id, $praktikum_id);
$check_stmt->execute();
$check_result = $check_stmt->get_result();

if ($check_result->num_rows == 0) {
    header("Location: praktikum.php");
    exit();
}

// Handle laporan upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_laporan'])) {
    $modul_id = $_POST['modul_id'];
    
    if (isset($_FILES['file_laporan']) && $_FILES['file_laporan']['error'] === 0) {
        $upload_dir = '../uploads/laporan/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . $_FILES['file_laporan']['name'];
        $file_path = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['file_laporan']['tmp_name'], $file_path)) {
            // Check if laporan already exists
            $check_laporan_sql = "SELECT id FROM laporan WHERE mahasiswa_id = ? AND modul_id = ?";
            $check_laporan_stmt = $conn->prepare($check_laporan_sql);
            $check_laporan_stmt->bind_param("ii", $user_id, $modul_id);
            $check_laporan_stmt->execute();
            $check_laporan_result = $check_laporan_stmt->get_result();
            
            if ($check_laporan_result->num_rows > 0) {
                // Update existing laporan
                $update_sql = "UPDATE laporan SET file_laporan = ?, tanggal_upload = NOW() WHERE mahasiswa_id = ? AND modul_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param("sii", $file_name, $user_id, $modul_id);
                $update_stmt->execute();
            } else {
                // Insert new laporan
                $insert_sql = "INSERT INTO laporan (mahasiswa_id, modul_id, file_laporan) VALUES (?, ?, ?)";
                $insert_stmt = $conn->prepare($insert_sql);
                $insert_stmt->bind_param("iis", $user_id, $modul_id, $file_name);
                $insert_stmt->execute();
            }
            
            $success_message = "Laporan berhasil diupload!";
        } else {
            $error_message = "Gagal mengupload file!";
        }
    } else {
        $error_message = "Silakan pilih file untuk diupload!";
    }
}

// Get praktikum details
$praktikum_sql = "SELECT p.*, u.nama as asisten_nama FROM praktikum p 
                  LEFT JOIN users u ON p.asisten_id = u.id 
                  WHERE p.id = ?";
$praktikum_stmt = $conn->prepare($praktikum_sql);
$praktikum_stmt->bind_param("i", $praktikum_id);
$praktikum_stmt->execute();
$praktikum_result = $praktikum_stmt->get_result();
$praktikum = $praktikum_result->fetch_assoc();

// Get modul list with laporan status
$modul_sql = "SELECT m.*, l.file_laporan, l.nilai, l.feedback, l.tanggal_upload
              FROM modul m
              LEFT JOIN laporan l ON m.id = l.modul_id AND l.mahasiswa_id = ?
              WHERE m.praktikum_id = ?
              ORDER BY m.created_at";
$modul_stmt = $conn->prepare($modul_sql);
$modul_stmt->bind_param("ii", $user_id, $praktikum_id);
$modul_stmt->execute();
$modul_result = $modul_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($praktikum['nama_praktikum']); ?> - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <?php include 'templates/header_mahasiswa.php'; ?>
    
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Back Button -->
            <div class="mb-6">
                <a href="dashboard.php" class="text-blue-600 hover:text-blue-500 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali ke Dashboard
                </a>
            </div>

            <!-- Praktikum Header -->
            <div class="bg-white shadow-lg mb-8 p-6">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">
                    <?php echo htmlspecialchars($praktikum['nama_praktikum']); ?>
                </h1>
                <p class="text-gray-600 mb-4">
                    <?php echo htmlspecialchars($praktikum['deskripsi'] ?? 'Tidak ada deskripsi'); ?>
                </p>
                <div class="flex items-center text-sm text-gray-500">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Asisten: <?php echo htmlspecialchars($praktikum['asisten_nama']); ?>
                </div>
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

            <!-- Modul List -->
            <div class="bg-white shadow-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-xl font-bold text-gray-800">Daftar Modul</h2>
                </div>
                <div class="divide-y divide-gray-200">
                    <?php while ($modul = $modul_result->fetch_assoc()): ?>
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-2">
                                        <?php echo htmlspecialchars($modul['judul_modul']); ?>
                                    </h3>
                                    <p class="text-gray-600 mb-4">
                                        <?php echo htmlspecialchars($modul['deskripsi'] ?? 'Tidak ada deskripsi'); ?>
                                    </p>
                                    <div class="flex items-center text-sm text-gray-500 mb-4">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Deadline: <?php echo date('d/m/Y H:i', strtotime($modul['deadline'])); ?>
                                    </div>
                                </div>
                                <div class="ml-6 flex-shrink-0">
                                    <?php if ($modul['file_laporan']): ?>
                                        <span class="bg-green-100 text-green-800 px-3 py-1 text-sm font-medium">
                                            Sudah Upload
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-yellow-100 text-yellow-800 px-3 py-1 text-sm font-medium">
                                            Belum Upload
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Download Materi -->
                            <?php if ($modul['file_modul']): ?>
                                <div class="mb-4">
                                    <a href="../uploads/modul/<?php echo htmlspecialchars($modul['file_modul']); ?>" 
                                       class="text-blue-600 hover:text-blue-500 flex items-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                        </svg>
                                        Download Materi
                                    </a>
                                </div>
                            <?php endif; ?>

                            <!-- Upload Laporan -->
                            <div class="border-t pt-4 mt-4">
                                <h4 class="font-medium text-gray-900 mb-3">Upload Laporan</h4>
                                <form method="POST" enctype="multipart/form-data" class="flex items-center space-x-4">
                                    <input type="hidden" name="modul_id" value="<?php echo $modul['id']; ?>">
                                    <input type="file" name="file_laporan" accept=".pdf,.doc,.docx,.zip" 
                                           class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                    <button type="submit" name="upload_laporan" 
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium transition-colors duration-200">
                                        Upload
                                    </button>
                                </form>
                                
                                <?php if ($modul['file_laporan']): ?>
                                    <div class="mt-3 text-sm text-gray-600">
                                        <p>File yang sudah diupload: <?php echo htmlspecialchars($modul['file_laporan']); ?></p>
                                        <p>Tanggal upload: <?php echo date('d/m/Y H:i', strtotime($modul['tanggal_upload'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Nilai dan Feedback -->
                            <?php if ($modul['nilai'] !== null): ?>
                                <div class="border-t pt-4 mt-4">
                                    <h4 class="font-medium text-gray-900 mb-3">Penilaian</h4>
                                    <div class="bg-blue-50 p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-medium text-gray-700">Nilai:</span>
                                            <span class="text-2xl font-bold text-blue-600"><?php echo $modul['nilai']; ?></span>
                                        </div>
                                        <?php if ($modul['feedback']): ?>
                                            <div>
                                                <span class="font-medium text-gray-700">Feedback:</span>
                                                <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($modul['feedback']); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <?php if ($modul_result->num_rows == 0): ?>
                <div class="bg-white shadow-lg p-12 text-center">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Modul</h3>
                    <p class="text-gray-600">Asisten belum menambahkan modul untuk praktikum ini</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'templates/footer_mahasiswa.php'; ?>
</body>
</html>
