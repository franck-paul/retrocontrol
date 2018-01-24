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
