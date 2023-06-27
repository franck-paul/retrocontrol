<?php
/**
 * @brief retrocontrol, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\retrocontrol;

use dcCore;
use dcNsProcess;

class Prepend extends dcNsProcess
{
    protected static $init = false; /** @deprecated since 2.27 */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::PREPEND);

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        dcCore::app()->spamfilters[] = AntispamFilterRetrocontrol::class;

        if (dcCore::app()->blog) {
            $settings = dcCore::app()->blog->settings->get(My::id());
            if ($settings->timeoutCheck) {
                dcCore::app()->addBehavior('coreBlogGetPosts', [Retrocontrol::class, 'adjustTrackbackURL']);
                dcCore::app()->addBehavior('publicBeforeTrackbackCreate', [Retrocontrol::class, 'checkTimeout']);
                dcCore::app()->url->register('trackback', 'trackback', '^trackback/([0-9]+/[0-9a-z]+)$', [Retrocontrol::class, 'preTrackback']);
            }
        }

        return true;
    }
}
