<?php
// CLI only: php tools/make_admin_hash.php "your-password"
// Paste the output into config.php under 'admins'.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
if (empty($argv[1])) { fwrite(STDERR, "Usage: php tools/make_admin_hash.php <password>\n"); exit(1); }
echo password_hash($argv[1], PASSWORD_DEFAULT), PHP_EOL;
