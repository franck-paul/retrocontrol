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

$m_version = dcCore::app()->plugins->moduleInfo('retrocontrol', 'version');
$i_version = dcCore::app()->getVersion('retrocontrol');

if (version_compare((string) $i_version, $m_version, '>=')) {
    return;
}

# --INSTALL AND UPDATE PROCEDURES--
try {
    dcCore::app()->blog->settings->addNamespace('retrocontrol');

    # New install / update (just erase settings - but not their values)
    dcCore::app()->blog->settings->retrocontrol->put('rc_sourceCheck', false, 'boolean', 'Check trackback source', false, true);
    dcCore::app()->blog->settings->retrocontrol->put('rc_timeoutCheck', false, 'boolean', 'Use disposable URL for trackbacks', false, true);
    dcCore::app()->blog->settings->retrocontrol->put('rc_recursive', true, 'boolean', 'Recursive filtering while checking source', false, true);
    dcCore::app()->blog->settings->retrocontrol->put('rc_timeout', 300, 'integer', 'Trackback URL time life (in seconds)', false, true);

    # --SETTING NEW VERSION--

    dcCore::app()->setVersion('retrocontrol', $m_version);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
