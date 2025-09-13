<?php

return [
    'post-created' => [
        'title' => 'منشور جديد من :organization',
        'body' => ':organization قام بنشر منشور جديد: ":title"',
    ],
    'program-created' => [
        'title' => 'برنامج جديد من :organization',
        'body' => ':organization أطلق برنامجًا جديدًا: ":title"',
    ],
    'opportunity-created' => [
        'title' => 'فرصة جديدة من :organization :program',
        'body' => ':organization نشر فرصة جديدة في :program: ":title"',
    ],
    'application-status-changed' => [
        'title' => 'الطلب :status',
        'approved' => [
            'body' => 'تهانينا! تم قبول طلبك لـ ":opportunity" في :organization.',
        ],
        'rejected' => [
            'body' => 'نأسف لإعلامك أنه تم رفض طلبك لـ ":opportunity" في :organization.',
        ],
    ],
];
