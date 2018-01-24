<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of Rétrocontrôle, a plugin for Dotclear 2.
#
# Copyright (c) Oleksandr Syenchuk, Alain Vagner and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) {return;}

$this->registerModule(
    "Rétrocontrôle",                  // Name
    "Trackback validity check",         // Description
    "Alain Vagner, Oleksandr Syenchuk", // Author
    '2.2.6',                            // Version
    array(
        'permissions' => 'usage,contentadmin',                                    // Permissions
        'priority'    => 1001,                                                    // Priority
        'dc_min'      => '2.8' ,                                                  // Min DC version
        'support'     => 'http://forum.dotclear.org/viewforum.php?id=16',         // Support URL
        'details'     => 'http://plugins.dotaddict.org/dc2/details/retrocontrol', // Doc URL
        'type'        => 'plugin'                                                 // Type
    )
);
