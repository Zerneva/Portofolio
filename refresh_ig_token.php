<?php
// Script to refresh Instagram Long-Lived Access Token
// Run this via Cron Job every 30-50 days.

// Ganti nilai ini dengan token long-lived Anda yang masih aktif
$current_token = 'IGAANHNBHeTvFBZAGJjNnc4ZAENtWTNMektacHlnM2Jrclk5cnZA0RF9HcGgxdkMxT3RiVXEwUm01VTdqZA05TNXhQSi04VEtPODlSamVaWDNhWktmTG1fdVQ0ajAxV0xZAZA2RzYlNMSEZAtVXJQMWJMZA1dSZAVpWOHE5WXRkbGpHUHpxTQZDZD';

// Instagram API Endpoint for refreshing token
$url = "https://graph.instagram.com/refresh_access_token?grant_type=ig_refresh_token&access_token=" . $current_token;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
// Suppress SSL warnings if running locally on Laragon tanpa SSL valid
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
unset($ch);

$data = json_decode($response, true);

if ($httpcode == 200 && isset($data['access_token'])) {
    $new_token = $data['access_token'];
    $expires_in = $data['expires_in'];
    
    echo "<h2>Success! Token Refreshed.</h2>";
    echo "<b>New Token (simpan dan gunakan token ini):</b><br> <textarea style='width:100%; height:80px;'>" . $new_token . "</textarea><br><br>";
    echo "<b>Expires in:</b> " . round($expires_in / 86400) . " hari.<br><br>";
    
    echo "<div style='padding:15px; background:#fff3cd; color:#856404; border:1px solid #ffeeba;'>";
    echo "<b>Action Required:</b><br>";
    echo "1. Copy token baru di atas.<br>";
    echo "2. Paste kembali ke dalam script `fetchInstagram()` di file <b>portofolio.html</b> Anda.<br>";
    echo "3. Update variabel \$current_token di script PHP ini dengan token yang baru agar bulan depan siap di-refresh lagi.";
    echo "</div>";
    
    // NOTE: Dalam sistem production sungguhan yg menggunakan DB, Anda bisa otomatis SAVE 
    // token baru ini ke database (misal MySQL) tanpa harus copy-paste lagi secara manual.
    
} else {
    echo "<h2>Error refreshing token.</h2>";
    echo "<p>Pastikan token yang Anda masukkan masih valid dan belum expired.</p>";
    echo "<pre style='background:#f4f4f4; padding:15px;'>";
    print_r($data);
    echo "</pre>";
}
?>
