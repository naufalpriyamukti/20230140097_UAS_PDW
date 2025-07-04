
<?php
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'asisten') {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Asisten - <?php echo $pageTitle ?? 'Dashboard'; ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');
        
        body {
            font-family: 'Inter', sans-serif;
        }
        
        .glass-effect {
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .card-3d {
            transform-style: preserve-3d;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .card-3d:hover {
            transform: translateY(-8px) rotateX(5deg);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        }
        
        .sidebar-3d {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            box-shadow: 0 20px 40px rgba(31, 41, 55, 0.4);
        }
        
        .nav-item {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .nav-item::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .nav-item:hover::before {
            left: 100%;
        }
        
        .nav-item:hover {
            transform: translateX(8px);
            background: rgba(255, 255, 255, 0.1);
        }
        
        .floating-orb {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.3));
            animation: float 6s ease-in-out infinite;
        }
        
        .floating-orb:nth-child(1) {
            width: 80px;
            height: 80px;
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .floating-orb:nth-child(2) {
            width: 60px;
            height: 60px;
            top: 60%;
            left: 80%;
            animation-delay: 2s;
        }
        
        .floating-orb:nth-child(3) {
            width: 40px;
            height: 40px;
            top: 80%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(180deg); }
        }
        
        .glow-effect {
            box-shadow: 0 0 20px rgba(59, 130, 246, 0.5);
        }
        
        .text-gradient {
            background: linear-gradient(135deg, #1f2937 0%, #374151 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .icon-3d {
            transition: all 0.3s ease;
            filter: drop-shadow(0 4px 8px rgba(0,0,0,0.1));
        }
        
        .icon-3d:hover {
            transform: scale(1.1) rotateY(15deg);
            filter: drop-shadow(0 8px 16px rgba(0,0,0,0.2));
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">
    <div class="flex h-screen relative overflow-hidden">
        <!-- Floating Orbs -->
        <div class="floating-orb"></div>
        <div class="floating-orb"></div>
        <div class="floating-orb"></div>
        
        <!-- Sidebar -->
        <aside class="w-72 sidebar-3d text-white flex flex-col relative z-10">
            <div class="p-8 border-b border-white/20">
                <div class="text-center">
                    <div class="w-16 h-16 mx-auto mb-4 bg-white/20 flex items-center justify-center text-2xl font-bold glow-effect">
                        A
                    </div>
                    <h2 class="text-2xl font-bold mb-2">Panel Asisten</h2>
                    <p class="text-sm text-white/80 mb-1">Administrator Dashboard</p>
                    <div class="bg-white/20 px-4 py-2 text-sm font-medium text-center">
                        <?php echo htmlspecialchars($_SESSION['nama']); ?>
                    </div>
                </div>
            </div>
            
            <nav class="flex-1 p-6">
                <ul class="space-y-3">
                    <?php 
                    $activeClass = 'bg-white/20 border-r-4 border-white text-white transform scale-105';
                    $inactiveClass = 'text-white/80 hover:text-white';
                    ?>
                    
                    <li>
                        <a href="dashboard.php" class="nav-item <?php echo ($activePage == 'dashboard') ? $activeClass : $inactiveClass; ?> flex items-center px-6 py-4 text-sm font-medium transition-all duration-300">
                            <svg class="icon-3d w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                            </svg>
                            Dashboard
                        </a>
                    </li>
                    
                    <li>
                        <a href="kelola_praktikum.php" class="nav-item <?php echo ($activePage == 'kelola_praktikum') ? $activeClass : $inactiveClass; ?> flex items-center px-6 py-4 text-sm font-medium transition-all duration-300">
                            <svg class="icon-3d w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"></path>
                            </svg>
                            Kelola Praktikum
                        </a>
                    </li>
                    
                    <li>
                        <a href="kelola_modul.php" class="nav-item <?php echo ($activePage == 'kelola_modul') ? $activeClass : $inactiveClass; ?> flex items-center px-6 py-4 text-sm font-medium transition-all duration-300">
                            <svg class="icon-3d w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Kelola Modul
                        </a>
                    </li>
                    
                    <li>
                        <a href="laporan_masuk.php" class="nav-item <?php echo ($activePage == 'laporan_masuk') ? $activeClass : $inactiveClass; ?> flex items-center px-6 py-4 text-sm font-medium transition-all duration-300">
                            <svg class="icon-3d w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                            </svg>
                            Laporan Masuk
                        </a>
                    </li>
                    
                    <li>
                        <a href="kelola_user.php" class="nav-item <?php echo ($activePage == 'kelola_user') ? $activeClass : $inactiveClass; ?> flex items-center px-6 py-4 text-sm font-medium transition-all duration-300">
                            <svg class="icon-3d w-6 h-6 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                            </svg>
                            Kelola User
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="p-6 border-t border-white/20">
                <a href="../logout.php" class="nav-item w-full flex items-center justify-center px-6 py-3 text-sm font-medium text-red-200 hover:text-white hover:bg-red-500/20 transition-all duration-300">
                    <svg class="icon-3d w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                    </svg>
                    Logout
                </a>
            </div>
        </aside>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="glass-effect border-b border-white/20 shadow-xl">
                <div class="px-8 py-6">
                    <h1 class="text-3xl font-bold text-gradient"><?php echo $pageTitle ?? 'Dashboard'; ?></h1>
                    <p class="text-gray-600 mt-1">Kelola praktikum dan penilaian mahasiswa</p>
                </div>
            </header>
