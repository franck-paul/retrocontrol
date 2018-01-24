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

$__autoload['dcFilterRetrocontrol'] = dirname(__FILE__) . '/class.dc.filter.retrocontrol.php';
$__autoload['retrocontrol']         = dirname(__FILE__) . '/class.retrocontrol.php';
$core->spamfilters[]                = 'dcFilterRetrocontrol';

$core->blog->settings->addNamespace('retrocontrol');
if ($core->blog->settings->retrocontrol->rc_timeoutCheck) {
    $core->addBehavior('coreBlogGetPosts', array('retrocontrol', 'adjustTrackbackURL'));
    $core->addBehavior('publicBeforeTrackbackCreate', array('retrocontrol', 'checkTimeout'));
    $core->url->register('trackback', 'trackback', '^trackback/([0-9]+/[0-9a-z]+)$', array('retrocontrol', 'preTrackback'));
}

class rsExtPostRetrocontrol
{
    public static function getTrackbackLink($rs)
    {
        $ts  = (int) $rs->getTS();
        $key = base_convert((time() - $ts) ^ $ts, 10, 36);
        $chk = substr(md5($rs->post_id . DC_MASTER_KEY . $key), 1, 4);

        return rsExtPost::getTrackbackLink($rs) . '/' . $chk . $key;
    }
}
