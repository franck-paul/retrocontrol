<?php

/**
 * @brief retrocontrol, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Oleksandr Syenchuk
 * @author Alain Vagner
 *
 * @copyright Oleksandr Syenchuk, Alain Vagner
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
$this->registerModule(
    'Rétrocontrôle',
    'Trackback validity check',
    'Alain Vagner, Oleksandr Syenchuk',
    '8.0',
    [
        'date'        => '2025-09-07T15:50:33+0200',
        'requires'    => [['core', '2.36']],
        'permissions' => 'My',
        'priority'    => 1001,
        'type'        => 'plugin',
        'settings'    => [
            'info' => 'See antispam filters',
        ],

        'details'    => 'https://open-time.net/?q=retrocontrol',
        'support'    => 'https://github.com/franck-paul/retrocontrol',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/retrocontrol/main/dcstore.xml',
        'license'    => 'gpl2',
    ]
);
