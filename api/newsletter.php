<?php
/** Newsletter subscription */
require_once __DIR__ . '/_init.php';
api_guard('newsletter', 6, 300);

$email = clean_text(input('email'), 150);

if (!valid_email($email)) {
    json_response(['success' => false, 'message' => 'Please enter a valid email address.', 'errors' => ['email' => 'Invalid email.']], 422);
}

$existing = DB::row('SELECT id, status FROM newsletters WHERE email = ?', [$email]);
if ($existing) {
    if ($existing['status'] !== 'subscribed') {
        DB::update('newsletters', ['status' => 'subscribed'], 'id = :id', ['id' => $existing['id']]);
    }
    json_response(['success' => true, 'message' => 'You\'re already on the list — thank you!']);
}

$id = DB::insert('newsletters', ['email' => $email, 'status' => 'subscribed']);
if (!$id) {
    json_response(['success' => false, 'message' => 'Could not subscribe right now. Please try again.'], 500);
}

json_response(['success' => true, 'message' => 'Subscribed! Watch your inbox for growth tips.']);
