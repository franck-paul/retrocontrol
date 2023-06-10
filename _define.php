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
if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'Rétrocontrôle',
    'Trackback validity check',
    'Alain Vagner, Oleksandr Syenchuk',
    '4.2',
    [
        'requires'    => [['core', '2.26']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]),
        'priority' => 1001,
        'type'     => 'plugin',
        'settings' => [
            'info' => 'See antispam filters',
        ],

        'details'    => 'https://open-time.net/?q=retrocontrol',
        'support'    => 'https://github.com/franck-paul/retrocontrol',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/retrocontrol/master/dcstore.xml',
    ]
);
