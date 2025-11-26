<?php
// TEST SCRIPT - Upload to cPanel and visit this file to test Telegram
header('Content-Type: text/html; charset=UTF-8');

$botToken = '8174653415:AAHrooy08a23wwvICmwsMZX46IT-3w6QIR8';
$chatId = '6375926160';

echo "<h1>Telegram Integration Test (cURL Version)</h1>";
echo "<p>Bot Token: " . substr($botToken, 0, 15) . "...</p>";
echo "<p>Chat ID: " . $chatId . "</p>";
echo "<hr>";

// Check if cURL is available
if (!function_exists('curl_init')) {
    echo "<h2 style='color:red;'>❌ cURL NOT AVAILABLE</h2>";
    echo "<p>Your hosting does not have cURL extension enabled. Contact your hosting provider.</p>";
    exit;
}

echo "<p>✅ cURL extension is available</p>";

$testMessage = "✅ *Test from cPanel (cURL)*\n\n";
$testMessage .= "If you see this message, your Telegram integration is working!\n";
$testMessage .= "Time: " . date('Y-m-d H:i:s');

$url = "https://api.telegram.org/bot{$botToken}/sendMessage";
$postData = http_build_query([
    'chat_id' => $chatId,
    'text' => $testMessage,
    'parse_mode' => 'Markdown'
]);

// Use cURL (works even when allow_url_fopen is disabled)
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($ch);
$error = curl_error($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($error) {
    echo "<h2 style='color:red;'>❌ cURL ERROR</h2>";
    echo "<p>Error: " . htmlspecialchars($error) . "</p>";
} else {
    $response = json_decode($result, true);
    if (isset($response['ok']) && $response['ok'] === true) {
        echo "<h2 style='color:green;'>✅ SUCCESS!</h2>";
        echo "<p>Test message sent to Telegram successfully using cURL!</p>";
        echo "<p>Check your Telegram bot chat (ID: {$chatId}) for the test message.</p>";
        echo "<p>HTTP Code: {$httpCode}</p>";
    } else {
        echo "<h2 style='color:orange;'>⚠️ TELEGRAM API ERROR</h2>";
        echo "<p>HTTP Code: {$httpCode}</p>";
        echo "<p>Response:</p>";
        echo "<pre>" . htmlspecialchars(print_r($response, true)) . "</pre>";
    }
}

echo "<hr>";
echo "<h3>System Information</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><td>PHP Version</td><td>" . phpversion() . "</td></tr>";
echo "<tr><td>allow_url_fopen</td><td>" . (ini_get('allow_url_fopen') ? 'Enabled' : '<strong style=\"color:red;\">Disabled</strong>') . "</td></tr>";
echo "<tr><td>cURL Available</td><td style='color:green;'><strong>YES</strong></td></tr>";
echo "<tr><td>Server Time</td><td>" . date('Y-m-d H:i:s') . "</td></tr>";
echo "</table>";

echo "<hr>";
echo "<p><a href='index.html'>← Back to Application</a></p>";
?>
