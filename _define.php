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
    'Rétrocontrôle',                  // Name
    'Trackback validity check',         // Description
    'Alain Vagner, Oleksandr Syenchuk', // Author
    '2.4',                              // Version
    [
        'requires'    => [['core', '2.19']],                                       // Dependencies
        'permissions' => 'usage,contentadmin',                                     // Permissions
        'priority'    => 1001,                                                     // Priority
        'type'        => 'plugin',                                                 // Type
        'support'     => 'https://github.com/franck-paul/retrocontrol',            // Support URL
        'details'     => 'https://plugins.dotaddict.org/dc2/details/retrocontrol', // Doc URL
        'settings'    => [                                                         // Settings
            'info' => 'See antispam filters'
        ]
    ]
);
