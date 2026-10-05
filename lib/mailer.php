<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Send an HTML email from the trust address with the logo embedded inline (cid:logo).
 * $attachment = ['data' => binary string, 'name' => 'file.pdf'] or null.
 * Throws on failure.
 */
function send_mail(string $to, string $toName, string $subject, string $html, ?array $attachment = null): void
{
    $s = cfg('smtp');
    $m = new PHPMailer(true);
    $m->isSMTP();
    $m->Host = $s['host'];
    $m->Port = $s['port'];
    $m->SMTPAuth = true;
    $m->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $m->Username = $s['username'];
    $m->Password = $s['password'];
    $m->CharSet = 'UTF-8';
    $m->setFrom($s['from'], $s['from_name']);
    $m->addReplyTo($s['from'], $s['from_name']);
    $m->addAddress($to, $toName);
    $m->isHTML(true);
    $m->Subject = $subject;
    $m->Body = $html;
    $m->AltBody = trim(html_entity_decode(strip_tags(preg_replace('#<(br|/p|/tr|/h\d)[^>]*>#i', "\n", $html))));

    $logo = cfg('trust')['logo'];
    if (is_file($logo)) {
        $m->addEmbeddedImage($logo, 'logo', 'logo.png', 'base64', 'image/png');
    }
    if ($attachment) {
        $m->addStringAttachment($attachment['data'], $attachment['name'], 'base64', 'application/pdf');
    }
    $m->send();
}
