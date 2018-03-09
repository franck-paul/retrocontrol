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

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
    "Rétrocontrôle",                  // Name
    "Trackback validity check",         // Description
    "Alain Vagner, Oleksandr Syenchuk", // Author
    '2.2.6',                            // Version
    array(
        'permissions' => 'usage,contentadmin',                                    // Permissions
        'priority'    => 1001,                                                    // Priority
        'dc_min'      => '2.8',                                                   // Min DC version
        'support'     => 'http://forum.dotclear.org/viewforum.php?id=16',         // Support URL
        'details'     => 'http://plugins.dotaddict.org/dc2/details/retrocontrol', // Doc URL
        'type'        => 'plugin'                                                // Type
    )
);
