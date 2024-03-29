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

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Process;

class Prepend extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::PREPEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        App::behavior()->addBehaviors([
            'AntispamInitFilters' => static function (ArrayObject $spamfilters) : void {
                $spamfilters->append(AntispamFilterRetrocontrol::class);
            },
        ]);

        if (App::blog()->isDefined()) {
            $settings = My::settings();
            if ($settings->timeoutCheck) {
                App::behavior()->addBehavior('coreBlogGetPosts', Retrocontrol::adjustTrackbackURL(...));
                App::behavior()->addBehavior('publicBeforeTrackbackCreate', Retrocontrol::checkTimeout(...));
                App::url()->register('trackback', 'trackback', '^trackback/([0-9]+/[0-9a-z]+)$', Retrocontrol::preTrackback(...));
            }
        }

        return true;
    }
}
