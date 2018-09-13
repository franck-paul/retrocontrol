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

$__autoload['dcFilterRetrocontrol'] = dirname(__FILE__) . '/class.dc.filter.retrocontrol.php';
$__autoload['retrocontrol']         = dirname(__FILE__) . '/class.retrocontrol.php';
$core->spamfilters[]                = 'dcFilterRetrocontrol';

$core->blog->settings->addNamespace('retrocontrol');
if ($core->blog->settings->retrocontrol->rc_timeoutCheck) {
    $core->addBehavior('coreBlogGetPosts', ['retrocontrol', 'adjustTrackbackURL']);
    $core->addBehavior('publicBeforeTrackbackCreate', ['retrocontrol', 'checkTimeout']);
    $core->url->register('trackback', 'trackback', '^trackback/([0-9]+/[0-9a-z]+)$', ['retrocontrol', 'preTrackback']);
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
