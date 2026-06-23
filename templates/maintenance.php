<?php
/** Maintenance mode page */
if (!headers_sent()) { http_response_code(503); }
$name = function_exists('setting') ? setting('site_name', 'Bharat SEO') : 'Bharat SEO';
$msg  = function_exists('setting') ? setting('maintenance_message', '') : '';
?><!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>We'll Be Right Back — <?= htmlspecialchars($name) ?></title>
<style>
  body{margin:0;font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#0a0e1a;color:#e8ecf6;min-height:100vh;display:grid;place-items:center;text-align:center;padding:24px;}
  .mark{width:64px;height:64px;border-radius:16px;background:linear-gradient(135deg,#4f8cff,#9b5cff);display:grid;place-items:center;font-weight:800;font-size:1.6rem;margin:0 auto 18px;}
  h1{background:linear-gradient(120deg,#6aa1ff,#9b5cff,#2fe6e0);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;}
  p{color:#9aa6c4;max-width:480px;margin-inline:auto;}
</style>
</head>
<body>
  <div>
    <div class="mark">B</div>
    <h1>We'll Be Right Back</h1>
    <p><?= $msg !== '' ? htmlspecialchars($msg) : 'Our site is undergoing scheduled maintenance to serve you better. Please check back shortly.' ?></p>
  </div>
</body>
</html>
