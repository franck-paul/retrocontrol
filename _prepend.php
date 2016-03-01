<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is Rétrocontrôle, a plugin for Dotclear.              *
 *                                                             *
 *  Copyright (c) 2006-2008                                    *
 *  Oleksandr Syenchuk, Alain Vagner and contributors.         *
 *                                                             *
 *  This is an open source software, distributed under the GNU *
 *  General Public License (version 2) terms and  conditions.  *
 *                                                             *
 *  You should have received a copy of the GNU General Public  *
 *  License along with Rétrocontrôle (see COPYING.txt);        *
 *  if not, write to the Free Software Foundation, Inc.,       *
 *  59 Temple Place, Suite 330, Boston, MA  02111-1307  USA    *
\***************************************************************/

if (!defined('DC_RC_PATH')) { return; }

$__autoload['dcFilterRetrocontrol'] = dirname(__FILE__).'/class.dc.filter.retrocontrol.php';
$__autoload['retrocontrol'] = dirname(__FILE__).'/class.retrocontrol.php';
$core->spamfilters[] = 'dcFilterRetrocontrol';

$core->blog->settings->addNamespace('retrocontrol');
if ($core->blog->settings->retrocontrol->rc_timeoutCheck) {
	$core->addBehavior('coreBlogGetPosts',array('retrocontrol','adjustTrackbackURL'));
	$core->addBehavior('publicBeforeTrackbackCreate',array('retrocontrol','checkTimeout'));
	$core->url->register('trackback','trackback','^trackback/([0-9]+/[0-9a-z]+)$',array('retrocontrol','preTrackback'));
}

class rsExtPostRetrocontrol
{
	public static function getTrackbackLink(&$rs)
	{
		$ts = (int) $rs->getTS();
		$key = base_convert((time() - $ts) ^ $ts,10,36);
		$chk = substr(md5($rs->post_id.DC_MASTER_KEY.$key),1,4);

		return rsExtPost::getTrackbackLink($rs).'/'.$chk.$key;
	}
}
