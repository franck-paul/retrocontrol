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
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

if (!dcCore::app()->newVersion(basename(__DIR__), dcCore::app()->plugins->moduleInfo(basename(__DIR__), 'version'))) {
    return;
}

# --INSTALL AND UPDATE PROCEDURES--
try {
    # New install / update (just erase settings - but not their values)
    dcCore::app()->blog->settings->retrocontrol->put('rc_sourceCheck', false, 'boolean', 'Check trackback source', false, true);
    dcCore::app()->blog->settings->retrocontrol->put('rc_timeoutCheck', false, 'boolean', 'Use disposable URL for trackbacks', false, true);
    dcCore::app()->blog->settings->retrocontrol->put('rc_recursive', true, 'boolean', 'Recursive filtering while checking source', false, true);
    dcCore::app()->blog->settings->retrocontrol->put('rc_timeout', 300, 'integer', 'Trackback URL time life (in seconds)', false, true);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
