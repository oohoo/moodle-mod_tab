<?php

defined('MOODLE_INTERNAL') || die();

$addons = [
    'mod_tab' => [
        'handlers' => [
            'mobile_view' => [
                'displaydata' => [
                    'icon' => $CFG->wwwroot . '/mod/tab/pix/icon.png',
                ],
                'delegate' => 'CoreCourseModuleDelegate',
                'method' => 'mobile_view_activity'
            ]
        ]
    ]
];

