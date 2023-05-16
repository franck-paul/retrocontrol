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

use rsExtPost;

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
