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
declare(strict_types=1);

if (isset($this) && is_object($this) && method_exists($this, 'registerModule') && isset($this->id) && is_string($this->id)) {
    $this->registerModule(
        'Rétrocontrôle',
        'Trackback validity check',
        'Alain Vagner, Oleksandr Syenchuk',
        '9.0',
        [
            'date'        => '2026-08-03T10:09:21+0200',
            'requires'    => [['core', '2.39']],
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
}
