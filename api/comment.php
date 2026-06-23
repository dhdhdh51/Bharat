<?php
/** Blog comment submission (moderated) */
require_once __DIR__ . '/_init.php';
api_guard('comment', 5, 300);

$errors = [];
$blogId  = (int) input('blog_id', 0);
$name    = clean_text(input('name'), 120);
$email   = clean_text(input('email'), 150);
$comment = clean_text(input('comment'), 3000);

if ($blogId <= 0) {
    json_response(['success' => false, 'message' => 'Invalid article reference.'], 422);
}
$blogExists = DB::value('SELECT id FROM blogs WHERE id = ? AND status = "published"', [$blogId]);
if (!$blogExists) {
    json_response(['success' => false, 'message' => 'Article not found.'], 404);
}

if ($name === '')          $errors['name'] = 'Please enter your name.';
if (!valid_email($email))  $errors['email'] = 'Please enter a valid email address.';
if ($comment === '')       $errors['comment'] = 'Please enter a comment.';

if (!empty($errors)) {
    json_response(['success' => false, 'message' => 'Please fix the errors below.', 'errors' => $errors], 422);
}

$id = DB::insert('blog_comments', [
    'blog_id' => $blogId,
    'name'    => $name,
    'email'   => $email,
    'comment' => $comment,
    'status'  => 'pending',
]);

if (!$id) {
    json_response(['success' => false, 'message' => 'We could not post your comment. Please try again.'], 500);
}

$notify = setting('notify_email', setting('contact_email', ''));
if ($notify !== '') {
    $body = '<p>A new comment is awaiting moderation.</p><p><strong>' . e($name) . '</strong> (' . e($email) . ')</p><p>' . nl2br(e($comment)) . '</p>';
    @send_mail($notify, 'New Blog Comment (pending approval)', mail_template('New Comment', $body));
}

json_response(['success' => true, 'message' => 'Thanks! Your comment has been submitted and will appear once approved.']);
