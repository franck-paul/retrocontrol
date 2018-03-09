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

if (!defined('DC_CONTEXT_ADMIN')) {return;}

$m_version = $core->plugins->moduleInfo('retrocontrol', 'version');
$i_version = $core->getVersion('retrocontrol');

if (version_compare($i_version, $m_version, '>=')) {
    return;
}

# --INSTALL AND UPDATE PROCEDURES--
try
{
    $core->blog->settings->addNamespace('retrocontrol');

    # New install / update (just erase settings - but not their values)
    $core->blog->settings->retrocontrol->put('rc_sourceCheck', false, 'boolean', 'Check trackback source', false, true);
    $core->blog->settings->retrocontrol->put('rc_timeoutCheck', false, 'boolean', 'Use disposable URL for trackbacks', false, true);
    $core->blog->settings->retrocontrol->put('rc_recursive', true, 'boolean', 'Recursive filtering while checking source', false, true);
    $core->blog->settings->retrocontrol->put('rc_timeout', 300, 'integer', 'Trackback URL time life (in seconds)', false, true);

    # --SETTING NEW VERSION--

    $core->setVersion('retrocontrol', $m_version);

    return true;
} catch (Exception $e) {
    $core->error->add($e->getMessage());
}
return false;
