<?php

/**
 * Strict email: RFC-like check + domain must resolve (MX or A).
 */
function ngc_validate_email_strict(string $email): ?string
{
    $email = trim($email);
    if ($email === '') {
        return 'Email is required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Please enter a valid email address.';
    }
    $domain = strtolower(substr(strrchr($email, '@'), 1) ?: '');
    if ($domain === '' || $domain === 'localhost' || strpos($domain, '..') !== false) {
        return 'Please use a real email domain.';
    }
    if (function_exists('checkdnsrr')) {
        $ok = @checkdnsrr($domain, 'MX') || @checkdnsrr($domain, 'A') || @checkdnsrr($domain, 'AAAA');
        if (!$ok) {
            return 'This email domain cannot receive mail. Please check the address or try another email.';
        }
    }
    return null;
}

/**
 * Philippines mobile: 11 digits, must be 09XXXXXXXXX.
 * Accepts spaces/dashes; accepts +63 or 63 prefix and normalizes to 0...
 */
function ngc_normalize_ph_mobile(string $raw): ?string
{
    $d = preg_replace('/\D+/', '', $raw);
    if ($d === '') {
        return null;
    }
    if (strlen($d) === 12 && substr($d, 0, 2) === '63') {
        $d = '0' . substr($d, 2);
    }
    if (strlen($d) === 11 && substr($d, 0, 2) === '09') {
        return $d;
    }
    return null;
}

function ngc_validate_ph_phone(string $raw): ?string
{
    if (ngc_normalize_ph_mobile($raw) === null) {
        return 'Enter a valid Philippines mobile number: 11 digits starting with 09 (e.g. 0917 123 4567).';
    }
    return null;
}

/**
 * @return string|null error message or null if OK
 */
function ngc_validate_payment_receipt_upload(string $field = 'payment_receipt'): ?string
{
    if (!isset($_FILES[$field])) {
        return 'Please attach a proof of payment image.';
    }
    $f = $_FILES[$field];
    if (!is_array($f) || ($f['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
        return 'Please attach a proof of payment image.';
    }
    if (($f['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return 'Upload failed. Please try a smaller image or a different file.';
    }
    $maxBytes = 2 * 1024 * 1024;
    if (($f['size'] ?? 0) < 1) {
        return 'The uploaded file is empty.';
    }
    if (($f['size'] ?? 0) > $maxBytes) {
        return 'Payment proof must be 2MB or smaller (PNG or JPG).';
    }
    $tmp = $f['tmp_name'] ?? '';
    if ($tmp === '' || !is_uploaded_file($tmp)) {
        return 'Invalid upload. Please try again.';
    }
    $allowed = ['image/jpeg' => true, 'image/png' => true, 'image/webp' => true, 'image/gif' => true];
    $mime = null;
    if (class_exists('finfo')) {
        $fi = new finfo(FILEINFO_MIME_TYPE);
        $mime = $fi->file($tmp) ?: null;
    }
    if ($mime === null || !isset($allowed[$mime])) {
        return 'Payment proof must be an image (JPG, PNG, WebP, or GIF).';
    }
    return null;
}
