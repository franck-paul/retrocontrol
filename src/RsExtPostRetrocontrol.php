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

use Dotclear\App;
use Dotclear\Database\MetaRecord;
use Dotclear\Schema\Extension\Post;

class rsExtPostRetrocontrol
{
    public static function getTrackbackLink(MetaRecord $rs): string
    {
        $ts  = (int) $rs->getTS();
        $key = base_convert((string) ((time() - $ts) ^ $ts), 10, 36);
        $chk = substr(md5($rs->post_id . App::config()->masterKey() . $key), 1, 4);

        return Post::getTrackbackLink($rs) . '/' . $chk . $key;
    }
}
