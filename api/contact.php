<?php
/** Contact form */
require_once __DIR__ . '/_init.php';
api_guard('contact');

$errors = [];
$name    = clean_text(input('name'), 150);
$email   = clean_text(input('email'), 150);
$phone   = clean_text(input('phone'), 40);
$subject = clean_text(input('subject'), 200);
$message = clean_text(input('message'), 5000);

if ($name === '')         $errors['name'] = 'Please enter your name.';
if (!valid_email($email)) $errors['email'] = 'Please enter a valid email address.';
if ($message === '')      $errors['message'] = 'Please enter a message.';

if (!empty($errors)) {
    json_response(['success' => false, 'message' => 'Please fix the errors below.', 'errors' => $errors], 422);
}

$id = DB::insert('contact_messages', [
    'name'       => $name,
    'email'      => $email,
    'phone'      => $phone ?: null,
    'subject'    => $subject ?: null,
    'message'    => $message,
    'status'     => 'unread',
    'ip_address' => client_ip(),
]);

if (!$id) {
    json_response(['success' => false, 'message' => 'We could not send your message. Please try again.'], 500);
}

$notify = setting('notify_email', setting('contact_email', ''));
if ($notify !== '') {
    $body = '<p><strong>From:</strong> ' . e($name) . ' (' . e($email) . ')</p>'
        . ($phone ? '<p><strong>Phone:</strong> ' . e($phone) . '</p>' : '')
        . ($subject ? '<p><strong>Subject:</strong> ' . e($subject) . '</p>' : '')
        . '<p><strong>Message:</strong></p><p>' . nl2br(e($message)) . '</p>';
    @send_mail($notify, 'New Contact Message: ' . ($subject ?: $name), mail_template('New Contact Message', $body));
}

activity_log(null, 'contact_message', 'From: ' . $name);

json_response(['success' => true, 'message' => 'Thanks for reaching out! We\'ll reply within one business day.']);
