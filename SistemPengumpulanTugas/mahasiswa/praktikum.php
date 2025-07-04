
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a mahasiswa
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'mahasiswa') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Handle registration to praktikum
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['daftar_praktikum'])) {
    $praktikum_id = $_POST['praktikum_id'];
    
    // Check if already registered
    $check_sql = "SELECT id FROM pendaftaran WHERE mahasiswa_id = ? AND praktikum_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ii", $user_id, $praktikum_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        // Register to praktikum
        $insert_sql = "INSERT INTO pendaftaran (mahasiswa_id, praktikum_id, status) VALUES (?, ?, 'approved')";
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("ii", $user_id, $praktikum_id);
        
        if ($insert_stmt->execute()) {
            $success_message = "Berhasil mendaftar ke praktikum!";
        } else {
            $error_message = "Gagal mendaftar ke praktikum!";
        }
    } else {
        $error_message = "Anda sudah terdaftar di praktikum ini!";
    }
}

// Get all available praktikum
$praktikum_sql = "SELECT p.*, u.nama as asisten_nama, 
                  CASE WHEN pd.id IS NOT NULL THEN 1 ELSE 0 END as is_registered
                  FROM praktikum p
                  LEFT JOIN users u ON p.asisten_id = u.id
                  LEFT JOIN pendaftaran pd ON p.id = pd.praktikum_id AND pd.mahasiswa_id = ?
                  ORDER BY p.nama_praktikum";
$praktikum_stmt = $conn->prepare($praktikum_sql);
$praktikum_stmt->bind_param("i", $user_id);
$praktikum_stmt->execute();
$praktikum_result = $praktikum_stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Praktikum - SIMPRAK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .card-shadow {
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'templates/header_mahasiswa.php'; ?>
    
    <div class="min-h-screen py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Katalog Mata Praktikum</h1>
                <p class="text-gray-600 mt-2">Pilih dan daftar ke mata praktikum yang tersedia</p>
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

            <!-- Praktikum Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php while ($praktikum = $praktikum_result->fetch_assoc()): ?>
                    <div class="bg-white card-shadow overflow-hidden">
                        <div class="p-6">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="text-xl font-semibold text-gray-900 mb-2">
                                        <?php echo htmlspecialchars($praktikum['nama_praktikum']); ?>
                                    </h3>
                                    <p class="text-gray-600 text-sm mb-4">
                                        <?php echo htmlspecialchars($praktikum['deskripsi'] ?? 'Tidak ada deskripsi'); ?>
                                    </p>
                                    <div class="flex items-center text-sm text-gray-500 mb-4">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Asisten: <?php echo htmlspecialchars($praktikum['asisten_nama']); ?>
                                    </div>
                                </div>
                                <?php if ($praktikum['is_registered']): ?>
                                    <span class="bg-green-100 text-green-800 px-3 py-1 text-sm font-medium">
                                        Terdaftar
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="flex items-center justify-between">
                                <?php if ($praktikum['is_registered']): ?>
                                    <a href="detail_praktikum.php?id=<?php echo $praktikum['id']; ?>" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 text-sm font-medium transition-colors duration-200">
                                        Lihat Detail
                                    </a>
                                <?php else: ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="praktikum_id" value="<?php echo $praktikum['id']; ?>">
                                        <button type="submit" name="daftar_praktikum" 
                                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 text-sm font-medium transition-colors duration-200">
                                            Daftar
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

            <?php if ($praktikum_result->num_rows == 0): ?>
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Praktikum</h3>
                    <p class="text-gray-600">Belum ada mata praktikum yang tersedia saat ini</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'templates/footer_mahasiswa.php'; ?>
</body>
</html>
