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
        'settings'    => [                                                         // Settings
            'info' => 'See antispam filters'
        ],

        'details'    => 'https://open-time.net/?q=retrocontrol',       // Details URL
        'support'    => 'https://github.com/franck-paul/retrocontrol', // Support URL
        'repository' => 'https://raw.githubusercontent.com/franck-paul/retrocontrol/main/dcstore.xml'
    ]
);
