<?php
/** Lead capture (free audit / service / package inquiry) */
require_once __DIR__ . '/_init.php';
api_guard('lead');

$errors = [];
$fullName    = clean_text(input('full_name'), 150);
$business    = clean_text(input('business_name'), 150);
$phone       = clean_text(input('phone'), 40);
$email       = clean_text(input('email'), 150);
$website     = clean_text(input('website_url'), 255);
$city        = clean_text(input('city'), 120);
$service     = clean_text(input('service_needed'), 150);
$budget      = clean_text(input('budget'), 80);
$message     = clean_text(input('message'), 5000);
$source      = clean_text(input('source', 'website'), 80);
$consent     = input('consent');

if ($fullName === '')            $errors['full_name'] = 'Please enter your name.';
if (!valid_phone($phone))        $errors['phone'] = 'Please enter a valid phone number.';
if (!valid_email($email))        $errors['email'] = 'Please enter a valid email address.';
if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) $errors['website_url'] = 'Please enter a valid URL (include https://).';
if (empty($consent))             $errors['consent'] = 'Please provide your consent to continue.';

if (!empty($errors)) {
    json_response(['success' => false, 'message' => 'Please fix the errors below.', 'errors' => $errors], 422);
}

$leadId = DB::insert('leads', [
    'full_name'      => $fullName,
    'business_name'  => $business ?: null,
    'phone'          => $phone ?: null,
    'email'          => $email ?: null,
    'website_url'    => $website ?: null,
    'city'           => $city ?: null,
    'service_needed' => $service ?: null,
    'budget'         => $budget ?: null,
    'message'        => $message ?: null,
    'source'         => $source ?: 'website',
    'status'         => 'new',
    'ip_address'     => client_ip(),
]);

if (!$leadId) {
    json_response(['success' => false, 'message' => 'We could not save your request. Please try again or contact us directly.'], 500);
}

// Store audit detail if this is an audit submission with a website
if ($source === 'free_audit' && $website !== '') {
    DB::insert('audit_requests', [
        'lead_id'     => $leadId,
        'website_url' => $website,
        'goals'       => $message ?: null,
    ]);
}

// Notify admin
$notify = setting('notify_email', setting('contact_email', ''));
if ($notify !== '') {
    $body = '<table style="width:100%;border-collapse:collapse;color:#e8ecf6;">'
        . row_html('Name', $fullName)
        . row_html('Business', $business)
        . row_html('Phone', $phone)
        . row_html('Email', $email)
        . row_html('Website', $website)
        . row_html('City', $city)
        . row_html('Service', $service)
        . row_html('Budget', $budget)
        . row_html('Source', $source)
        . row_html('Message', $message)
        . '</table>';
    @send_mail($notify, 'New Lead: ' . $fullName . ' (' . $source . ')', mail_template('New Lead Received', $body));
}

// Acknowledgement to the lead
if (valid_email($email)) {
    $ack = '<p>Hi ' . e($fullName) . ',</p><p>Thank you for reaching out to ' . e(setting('site_name', SITE_NAME))
        . '. We have received your request and our team will get back to you within one business day.</p>'
        . '<p>Meanwhile, feel free to explore our <a href="' . e(url('/portfolio')) . '" style="color:#6aa1ff;">work</a>.</p>';
    @send_mail($email, 'We received your request — ' . setting('site_name', SITE_NAME), mail_template('Thanks for getting in touch!', $ack));
}

activity_log(null, 'lead_created', 'Source: ' . $source . ', Name: ' . $fullName);

json_response([
    'success'  => true,
    'message'  => 'Thank you! Your request has been received. We\'ll be in touch shortly.',
]);

/** Helper for email rows. */
function row_html(string $label, ?string $value): string
{
    $value = trim((string) $value);
    if ($value === '') return '';
    return '<tr><td style="padding:6px 10px;border-bottom:1px solid #1d2436;color:#9aa6c4;width:120px;">'
        . e($label) . '</td><td style="padding:6px 10px;border-bottom:1px solid #1d2436;">' . nl2br(e($value)) . '</td></tr>';
}
