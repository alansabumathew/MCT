<?php
declare(strict_types=1);

/** Upload a local file to the configured Drive folder. Returns ['id' => ..., 'url' => ...]. */
function drive_upload(string $path, string $name, string $mime): array
{
    $d = cfg('drive');
    $client = new Google\Client();
    $client->setClientId($d['client_id']);
    $client->setClientSecret($d['client_secret']);
    $client->setScopes([Google\Service\Drive::DRIVE_FILE]);
    $token = $client->fetchAccessTokenWithRefreshToken($d['refresh_token']);
    if (isset($token['error'])) {
        throw new RuntimeException('Drive auth failed: ' . ($token['error_description'] ?? $token['error']));
    }

    $drive = new Google\Service\Drive($client);
    $meta = new Google\Service\Drive\DriveFile(['name' => $name, 'parents' => [$d['folder_id']]]);
    $file = $drive->files->create($meta, [
        'data' => file_get_contents($path),
        'mimeType' => $mime,
        'uploadType' => 'multipart',
        'fields' => 'id,webViewLink',
    ]);
    return ['id' => $file->id, 'url' => $file->webViewLink];
}
