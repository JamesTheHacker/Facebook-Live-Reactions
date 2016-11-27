<?php

return [
    'POST_ID'       => '',
    'ACCESS_TOKEN'  => '',
    'APP_ID'        => '',
    'APP_SECRET'    => '',
    'VIDEO_BG'      => './images/background.jpg',
    'KEYWORD'       => 'share',
    'FRAME_RATE'    => 5,
    'REACTION_SETTINGS' => [
        'REACTION_PADDING' => 12,
        'FONT' => [
            'FAMILY' => './fonts/MyriadPro-Bold.otf',
            'SIZE' => 36,
            'COLOR' => '#FFF'
        ],
        'REACTIONS' => [
            'LIKE' => [
                'XPOS' => 95,
                'YPOS' => 320,
            ],
            'LOVE' => [
                'XPOS' => 239,
                'YPOS' => 320,
            ],
            'HAHA' => [
                'XPOS' => 385,
                'YPOS' => 320,
            ],
            'WOW' => [
                'XPOS' => 530,
                'YPOS' => 320,
            ],
            'SAD' => [
                'XPOS' => 0,
                'YPOS' => 0,
            ],
            'ANGRY' => [
                'XPOS' => 0,
                'YPOS' => 0,
            ],
        ],
    ],
    'SHOUTOUT_SETTINGS' => [
        'SHOUTOUT_TEXT' => [
            'XPOS' => 97,
            'YPOS' => 428,
            'FONT' => [
                'FAMILY' => './fonts/Futura.ttc',
                'SIZE' => 20,
                'COLOR' => '#FFF'
            ]
        ],
        'PROFILE_IMAGE' => [
            'WIDTH' => 65,
            'HEIGHT' => 65,
            'XPOS' => 14,
            'YPOS' => 10
        ],
        'DEFAULT_SHOUTOUT_NAME' => 'John Doe',
        'DEFAULT_SHOUTOUT' => 'Thanks for your share!',
    ],
];
