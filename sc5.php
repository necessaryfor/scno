<?php
// Hangi dizinde olduğumuzu belirliyoruz
$root = '/home'; // Başlangıç dizini
$current_dir = isset($_GET['dir']) ? realpath($_GET['dir']) : $root;

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

// URL'de ?up parametresi olup olmadığını kontrol et
if (isset($_GET['up'])) {
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
            <li><a href="?dir=<?php echo dirname($current_dir); ?>">.. (Üst Dizin)</a></li>
        <?php endif; ?>

        <?php foreach ($files as $file): ?>
            <?php if ($file == '.' || $file == '..') continue; ?>
            <?php $file_path = $current_dir . '/' . $file; ?>
            <?php if (is_dir($file_path)): ?>
                <li>[Dizin] <a href="?dir=<?php echo $file_path; ?>"><?php echo $file; ?></a></li>
            <?php else: ?>
                <li><?php echo $file; ?> <?php if (is_image($file)) echo ' <img src="' . $file_path . '" style="width:100px;"/>'; ?></li>
            <?php endif; ?>
        <?php endforeach; ?>
    </ul>

    <!-- Dosya Yükleme Formu -->
    <h2>Dosya Yükle</h2>
    <form action="?up&dir=<?php echo urlencode($current_dir); ?>" method="post" enctype="multipart/form-data">
        <input type="file" name="file">
        <input type="submit" value="Yükle">
    </form>

    </body>
    </html>
    <?php
} else {
    echo "URL'de 'up' parametresi bulunamadı. Dosya yükleme formu görünmeyecek.";
}
?>
