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

Clearbricks::lib()->autoload([
    'dcFilterRetrocontrol' => __DIR__ . '/class.dc.filter.retrocontrol.php',
    'retrocontrol'         => __DIR__ . '/class.retrocontrol.php',
]);
dcCore::app()->spamfilters[] = 'dcFilterRetrocontrol';

class rsExtPostRetrocontrol
{
    public static function getTrackbackLink($rs)
    {
        $ts  = (int) $rs->getTS();
        $key = base_convert((string) ((time() - $ts) ^ $ts), 10, 36);
        $chk = substr(md5($rs->post_id . DC_MASTER_KEY . $key), 1, 4);

        return rsExtPost::getTrackbackLink($rs) . '/' . $chk . $key;
    }
}

if (dcCore::app()->blog) {
    dcCore::app()->blog->settings->addNamespace('retrocontrol');
    if (dcCore::app()->blog->settings->retrocontrol->rc_timeoutCheck) {
        dcCore::app()->addBehavior('coreBlogGetPosts', [retrocontrol::class, 'adjustTrackbackURL']);
        dcCore::app()->addBehavior('publicBeforeTrackbackCreate', [retrocontrol::class, 'checkTimeout']);
        dcCore::app()->url->register('trackback', 'trackback', '^trackback/([0-9]+/[0-9a-z]+)$', [retrocontrol::class, 'preTrackback']);
    }
}
