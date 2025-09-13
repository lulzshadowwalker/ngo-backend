<?php

return [
    'post-created' => [
        'title' => 'New post from :organization',
        'body' => ':organization has published a new post: ":title"',
    ],
    'program-created' => [
        'title' => 'New program from :organization',
        'body' => ':organization has launched a new program: ":title"',
    ],
    'opportunity-created' => [
        'title' => 'New opportunity from :organization :program',
        'body' => ':organization has posted a new opportunity in :program: ":title"',
    ],
    'application-status-changed' => [
        'title' => 'Application :status',
        'approved' => [
            'body' => 'Congratulations! Your application for ":opportunity" at :organization has been approved.',
        ],
        'rejected' => [
            'body' => 'We regret to inform you that your application for ":opportunity" at :organization has been rejected.',
        ],
    ],
];
