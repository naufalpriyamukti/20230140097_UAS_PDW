
<?php
session_start();

// Jika user sudah login, redirect ke dashboard sesuai role
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'asisten') {
        header("Location: SistemPengumpulanTugas/asisten/dashboard.php");
    } elseif ($_SESSION['role'] == 'mahasiswa') {
        header("Location: SistemPengumpulanTugas/mahasiswa/dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutorial SIMPRAK - Sistem Praktikum</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .card-shadow {
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        .step-number {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }
    </style>
</head>
<body class="font-sans bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <h1 class="text-2xl font-bold text-gray-800">SIMPRAK Tutorial</h1>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="SistemPengumpulanTugas/login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 text-sm font-medium transition-colors duration-200">
                        Login
                    </a>
                    <a href="SistemPengumpulanTugas/register.php" class="border border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-6 py-2 text-sm font-medium transition-colors duration-200">
                        Register
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="gradient-bg text-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl font-bold mb-6">Tutorial Login & Register</h1>
            <p class="text-xl mb-8 max-w-3xl mx-auto">Panduan lengkap untuk menggunakan sistem praktikum SIMPRAK</p>
            <div class="space-x-4">
                <a href="#tutorial" class="bg-white text-blue-600 px-8 py-3 text-lg font-semibold hover:bg-gray-100 transition-colors duration-200">
                    Mulai Tutorial
                </a>
            </div>
        </div>
    </section>

    <!-- Tutorial Section -->
    <section id="tutorial" class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-800 mb-4">Cara Menggunakan SIMPRAK</h2>
                <p class="text-xl text-gray-600">Ikuti langkah-langkah berikut untuk memulai</p>
            </div>
            
            <!-- Step 1: Register -->
            <div class="mb-16">
                <div class="flex items-center mb-8">
                    <div class="step-number text-white w-12 h-12 flex items-center justify-center text-xl font-bold mr-6">1</div>
                    <h3 class="text-3xl font-bold text-gray-800">Registrasi Akun</h3>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <div class="card-shadow bg-white p-8">
                        <h4 class="text-xl font-semibold mb-4 text-gray-800">Untuk Mahasiswa:</h4>
                        <ol class="list-decimal list-inside space-y-3 text-gray-600">
                            <li>Klik tombol <strong>"Register"</strong> di navigation bar</li>
                            <li>Isi form dengan data diri lengkap</li>
                            <li>Pilih role <strong>"Mahasiswa"</strong></li>
                            <li>Masukkan <strong>NIM</strong> yang valid</li>
                            <li>Buat password minimal 6 karakter</li>
                            <li>Klik <strong>"Daftar"</strong></li>
                        </ol>
                        <div class="mt-6">
                            <a href="SistemPengumpulanTugas/register.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 font-medium transition-colors duration-200">
                                Daftar Sebagai Mahasiswa
                            </a>
                        </div>
                    </div>
                    
                    <div class="card-shadow bg-white p-8">
                        <h4 class="text-xl font-semibold mb-4 text-gray-800">Untuk Asisten:</h4>
                        <ol class="list-decimal list-inside space-y-3 text-gray-600">
                            <li>Klik tombol <strong>"Register"</strong> di navigation bar</li>
                            <li>Isi form dengan data diri lengkap</li>
                            <li>Pilih role <strong>"Asisten"</strong></li>
                            <li>NIM tidak perlu diisi untuk asisten</li>
                            <li>Buat password minimal 6 karakter</li>
                            <li>Klik <strong>"Daftar"</strong></li>
                        </ol>
                        <div class="mt-6">
                            <a href="SistemPengumpulanTugas/register.php" class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-2 font-medium transition-colors duration-200">
                                Daftar Sebagai Asisten
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step 2: Login -->
            <div class="mb-16">
                <div class="flex items-center mb-8">
                    <div class="step-number text-white w-12 h-12 flex items-center justify-center text-xl font-bold mr-6">2</div>
                    <h3 class="text-3xl font-bold text-gray-800">Login ke Sistem</h3>
                </div>
                
                <div class="card-shadow bg-white p-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div>
                            <h4 class="text-xl font-semibold mb-4 text-gray-800">Langkah Login:</h4>
                            <ol class="list-decimal list-inside space-y-3 text-gray-600">
                                <li>Klik tombol <strong>"Login"</strong> di navigation bar</li>
                                <li>Masukkan <strong>email</strong> yang telah didaftarkan</li>
                                <li>Masukkan <strong>password</strong> akun Anda</li>
                                <li>Klik <strong>"Masuk"</strong></li>
                                <li>Sistem akan redirect sesuai role:</li>
                                <ul class="list-disc list-inside ml-6 mt-2 space-y-1">
                                    <li>Mahasiswa → Dashboard Mahasiswa</li>
                                    <li>Asisten → Dashboard Asisten</li>
                                </ul>
                            </ol>
                        </div>
                        
                        <div class="bg-blue-50 p-6 border-l-4 border-blue-500">
                            <h5 class="font-semibold text-blue-800 mb-3">Sistem Login:</h5>
                            <div class="space-y-2 text-sm text-blue-700">
                                <p>✓ Registrasi otomatis untuk mahasiswa dan asisten</p>
                                <p>✓ Sistem keamanan password yang aman</p>
                                <p>✓ Dashboard sesuai dengan role pengguna</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="SistemPengumpulanTugas/login.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 font-medium transition-colors duration-200">
                            Login Sekarang
                        </a>
                    </div>
                </div>
            </div>

            <!-- Features Overview -->
            <div class="mb-16">
                <div class="flex items-center mb-8">
                    <div class="step-number text-white w-12 h-12 flex items-center justify-center text-xl font-bold mr-6">3</div>
                    <h3 class="text-3xl font-bold text-gray-800">Fitur yang Tersedia</h3>
                </div>
                
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Fitur Mahasiswa -->
                    <div class="card-shadow bg-green-50 p-8">
                        <h4 class="text-xl font-semibold mb-4 text-green-800">Fitur untuk Mahasiswa:</h4>
                        <ul class="space-y-3 text-green-700">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Mencari Mata Praktikum:</strong> Katalog praktikum yang tersedia</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Mendaftar ke Praktikum:</strong> Daftar praktikum yang diminati</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Melihat Praktikum yang Diikuti:</strong> Dashboard personal praktikum</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Download Materi:</strong> Akses file materi dari asisten</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Upload Laporan:</strong> Kumpulkan tugas praktikum</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-green-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Melihat Nilai:</strong> Cek nilai dan feedback dari asisten</span>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Fitur Asisten -->
                    <div class="card-shadow bg-purple-50 p-8">
                        <h4 class="text-xl font-semibold mb-4 text-purple-800">Fitur untuk Asisten:</h4>
                        <ul class="space-y-3 text-purple-700">
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-purple-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Kelola Mata Praktikum:</strong> CRUD praktikum lengkap</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-purple-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Kelola Modul:</strong> Tambah/edit modul dan upload materi</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-purple-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Lihat Laporan Masuk:</strong> Monitor semua laporan mahasiswa</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-purple-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Beri Nilai:</strong> Evaluasi laporan dengan nilai dan feedback</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-purple-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Kelola User:</strong> CRUD akun mahasiswa dan asisten</span>
                            </li>
                            <li class="flex items-start">
                                <svg class="w-5 h-5 text-purple-500 mt-1 mr-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span><strong>Filter & Search:</strong> Filter laporan berdasarkan kriteria</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Quick Start -->
            <div class="text-center bg-gradient-to-r from-blue-50 to-purple-50 p-12">
                <h3 class="text-2xl font-bold text-gray-800 mb-4">Siap Untuk Memulai?</h3>
                <p class="text-gray-600 mb-8">Pilih peran Anda dan mulai gunakan sistem praktikum SIMPRAK</p>
                <div class="space-x-4">
                    <a href="SistemPengumpulanTugas/register.php" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 text-lg font-semibold transition-colors duration-200">
                        Daftar Sekarang
                    </a>
                    <a href="SistemPengumpulanTugas/login.php" class="border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-8 py-3 text-lg font-semibold transition-colors duration-200">
                        Login
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center">
                <h3 class="text-2xl font-bold mb-4">SIMPRAK</h3>
                <p class="text-gray-400 mb-4">Sistem Informasi Praktikum Universitas</p>
                <p class="text-gray-500">&copy; 2024 SIMPRAK. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>
</html>
