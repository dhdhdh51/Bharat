<?php
require_once __DIR__ . '/includes/auth.php';
admin_logout();
secure_session_start();
flash_set('success', 'You have been logged out.');
redirect('admin/login.php');
