<?php
// Script to refresh Instagram Long-Lived Access Token automatically
// Run this via Cron Job/Windows Task Scheduler every 30-50 days.

$token_file = __DIR__ . '/ig_token.json';

// Read current token from JSON file
if (!file_exists($token_file)) {
    die("Error: token file not found.");
}

$json_data = file_get_contents($token_file);
$token_data = json_decode($json_data, true);
$current_token = $token_data['token'] ?? '';

if (empty($current_token)) {
    die("Error: token is empty.");
}

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
    
    // Save new token back to JSON file
    $token_data['token'] = $new_token;
    file_put_contents($token_file, json_encode($token_data, JSON_PRETTY_PRINT));
    
    // Optional: log to file
    $log_msg = "[" . date('Y-m-d H:i:s') . "] Token refreshed successfully. Expires in " . round($expires_in / 86400) . " days.\n";
    file_put_contents(__DIR__ . '/token_refresh_log.txt', $log_msg, FILE_APPEND);

    echo "<h2>Success! Token Refreshed Automatically.</h2>";
    echo "<b>New Token:</b><br> <textarea style='width:100%; height:80px;' readonly>" . $new_token . "</textarea><br><br>";
    echo "<b>Expires in:</b> " . round($expires_in / 86400) . " hari.<br><br>";
    echo "<div style='padding:15px; background:#d4edda; color:#155724; border:1px solid #c3e6cb;'>";
    echo "<b>All done!</b> Token has been automatically saved to <code>ig_token.json</code> and will be used by the frontend. No manual copy-pasting required.";
    echo "</div>";
    
} else {
    $log_msg = "[" . date('Y-m-d H:i:s') . "] Error refreshing token: " . json_encode($data) . "\n";
    file_put_contents(__DIR__ . '/token_refresh_log.txt', $log_msg, FILE_APPEND);

    echo "<h2>Error refreshing token.</h2>";
    echo "<p>Pastikan token yang saat ini ada di ig_token.json masih valid dan belum expired.</p>";
    echo "<pre style='background:#f4f4f4; padding:15px;'>";
    print_r($data);
    echo "</pre>";
}
?>
