<?php
// Copy to config.php and fill in. config.php is gitignored.
return [
    'trust' => [
        'name'    => 'Mahima Charitable Trust',
        'reg_no'  => '74/2012-2013',
        'address' => 'Korah Compound, Dasarahalli (Near Shobha Moon Stone Apartments), H.A Farm Post, Hebbal, Bangalore, Karnataka, India.',
        'email'   => 'mctbangalore@gmail.com',
        'logo'    => __DIR__ . '/assets/Mahima_logo.png',
        '80g'     => 'Contributions to Mahima Charitable Trust are eligible for Income Tax exemption under section 80G (5)(vi) of the Income Tax Act 1961 vide order No.DIT (E) BLR/80G/403/AADTM2051Q/ ITO(E)-2/Vol2012-2013 dt.14-02-2013 of the DIT (E) Bangalore.',
    ],
    // Prefer a path OUTSIDE the web root for the database.
    'db_path' => __DIR__ . '/data/donations.sqlite',

    // Gmail SMTP: needs 2-step verification + an App Password for the trust account.
    'smtp' => [
        'host'      => 'smtp.gmail.com',
        'port'      => 587,
        'username'  => 'mctbangalore@gmail.com',
        'password'  => 'GMAIL_APP_PASSWORD',
        'from'      => 'mctbangalore@gmail.com',
        'from_name' => 'Mahima Charitable Trust',
    ],

    // Google Drive (OAuth). Run tools/get_drive_token.php once to get the refresh token.
    'drive' => [
        'client_id'     => '',
        'client_secret' => '',
        'refresh_token' => '',
        'folder_id'     => '', // ID of the "Donation Screenshots" folder
    ],

    // Admin accounts: username => password_hash. Generate with tools/make_admin_hash.php
    'admins' => [
        'admin' => '',
    ],

    'max_upload_bytes' => 5 * 1024 * 1024,
    'submit_limit_per_hour' => 5, // per IP
];
