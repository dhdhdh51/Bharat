<?php
/** Database connection error page (no DB dependency) */
if (!headers_sent()) { http_response_code(503); }
?><!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Service Unavailable</title>
<style>
  body{margin:0;font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#0a0e1a;color:#e8ecf6;min-height:100vh;display:grid;place-items:center;text-align:center;padding:24px;}
  .icon{font-size:3.5rem;}
  p{color:#9aa6c4;max-width:480px;margin-inline:auto;}
  a{display:inline-block;margin-top:18px;padding:13px 26px;border-radius:100px;background:linear-gradient(135deg,#4f8cff,#9b5cff);color:#fff;text-decoration:none;font-weight:600;}
</style>
</head>
<body>
  <div>
    <div class="icon">&#128268;</div>
    <h1>Service Temporarily Unavailable</h1>
    <p>We're unable to reach the database right now. This is usually temporary. Please refresh in a moment.</p>
    <a href="">Retry</a>
  </div>
</body>
</html>
