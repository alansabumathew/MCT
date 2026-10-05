<?php
// CLI only, run once: php tools/get_drive_token.php
// Needs client_id / client_secret in config.php (OAuth client of type "Desktop app").
// Prints a refresh token to paste into config.php under drive.refresh_token.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require __DIR__ . '/../vendor/autoload.php';
$cfg = require __DIR__ . '/../config.php';

$client = new Google\Client();
$client->setClientId($cfg['drive']['client_id']);
$client->setClientSecret($cfg['drive']['client_secret']);
$client->setRedirectUri('http://localhost');
$client->setScopes([Google\Service\Drive::DRIVE_FILE]);
$client->setAccessType('offline');
$client->setPrompt('consent');

echo "Open this URL, sign in as the trust's Google account, allow access:\n\n", $client->createAuthUrl(), "\n\n";
echo "The browser will then fail to load http://localhost/?code=...; copy the value after code= and paste it here:\n> ";
$code = trim(fgets(STDIN));
$token = $client->fetchAccessTokenWithAuthCode(urldecode($code));
if (empty($token['refresh_token'])) { fwrite(STDERR, "No refresh token returned: " . json_encode($token) . "\n"); exit(1); }
echo "\nrefresh_token:\n", $token['refresh_token'], "\n";
