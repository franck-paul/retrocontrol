<?php /* -*- tab-width: 5; indent-tabs-mode: t; c-basic-offset: 5 -*- */
/***************************************************************\
 *  This is Rétrocontrôle, a plugin for Dotclear.              *
 *                                                             *
 *  Copyright (c)                                              *
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

$this->registerModule(
	/* Name */			"Rétrocontrôle",
	/* Description*/		"Trackback validity check",
	/* Author */			"Alain Vagner, Oleksandr Syenchuk",
	/* Version */			'2.2.4',
	array(
		'permissions' =>	'usage,contentadmin',
		'priority' =>		1001,
		'type'		=>		'plugin'
	)
);
