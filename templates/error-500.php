<?php
/** 500 error page (kept dependency-free so it renders even if DB is down) */
if (!headers_sent()) { http_response_code(500); }
?><!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex">
<title>Something Went Wrong</title>
<style>
  body{margin:0;font-family:system-ui,-apple-system,Segoe UI,sans-serif;background:#0a0e1a;color:#e8ecf6;min-height:100vh;display:grid;place-items:center;text-align:center;padding:24px;}
  .code{font-size:clamp(4rem,16vw,8rem);font-weight:800;background:linear-gradient(120deg,#6aa1ff,#9b5cff,#2fe6e0);-webkit-background-clip:text;background-clip:text;-webkit-text-fill-color:transparent;line-height:1;}
  a{display:inline-block;margin-top:18px;padding:13px 26px;border-radius:100px;background:linear-gradient(135deg,#4f8cff,#9b5cff);color:#fff;text-decoration:none;font-weight:600;}
  p{color:#9aa6c4;}
</style>
</head>
<body>
  <div>
    <div class="code">500</div>
    <h1>Something Went Wrong</h1>
    <p>We hit an unexpected error. Our team has been notified. Please try again shortly.</p>
    <a href="/">Back to Home</a>
  </div>
</body>
</html>
