
<?php
require_once 'config.php';

// Update passwords untuk demo accounts
$new_password = 'password123';
$hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

$emails = [
    'admin@system.com',
    'asisten1@univ.ac.id', 
    'asisten2@univ.ac.id',
    'budi@student.univ.ac.id',
    'sari@student.univ.ac.id'
];

foreach ($emails as $email) {
    $sql = "UPDATE users SET password = ? WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $hashed_password, $email);
    
    if ($stmt->execute()) {
        echo "Password updated for: $email<br>";
    } else {
        echo "Failed to update password for: $email<br>";
    }
}

echo "<br>All demo passwords updated to: $new_password<br>";
echo "<br><strong>Demo Accounts:</strong><br>";
echo "Asisten: asisten1@univ.ac.id / password123<br>";
echo "Mahasiswa: budi@student.univ.ac.id / password123<br>";
?>
