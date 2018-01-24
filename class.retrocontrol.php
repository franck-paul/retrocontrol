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

class retrocontrol
{
    public $uHost = array();
    public $sHost = array();
    public $uIP   = array();
    public $sIP   = array();

    public static function checkTimeout($cur)
    {
        global $tbc_key, $core;

        $errmsg = "\n" . 'Invalid trackback. Are you using an expired URL?';

        # Trackback not adjusted or too short key
        if (!$tbc_key || strlen($tbc_key) < 5) {
            throw new Exception($errmsg);
        }

        # Timeout setting
        $core->blog->settings->addNamespace('retrocontrol');
        $timeout = $core->blog->settings->retrocontrol->rc_timeout;
        $timeout = $timeout ? (int) $timeout : 300;

        # Check key validity
        $chk     = substr($tbc_key, 0, 4);
        $key     = substr($tbc_key, 4);
        $tbc_key = substr(md5($cur->post_id . DC_MASTER_KEY . $key), 1, 4);

        if ($tbc_key !== $chk) {
            throw new Exception($errmsg);
        }

        # Check key expiration date
        $post     = $core->blog->getPosts(array('post_id' => $cur->post_id));
        $ts       = (int) $post->getTS();
        $curDate  = time() - $ts;
        $refDate  = (int) base_convert($key, 36, 10) ^ $ts;
        $diffDate = $curDate - $refDate;

        if ($diffDate < 1 || $diffDate > $timeout) {
            throw new Exception($errmsg);
        }
    }

    public function checkSource($url, $ip = null, $recursive = true)
    {
        $site = @parse_url($url);
        if (!($site && $url)) {
            # Bad URL => SPAM
            return true;
        }
        $site = $site['host'];
        $ip   = $ip ?: $_SERVER['REMOTE_ADDR'];

        # Initializing search data
        $this->sIP   = gethostbynamel($site);
        $this->sHost = array($site, $this->getSLD($site));
        $this->uIP   = array($ip);
        $this->uHost = array(gethostbyaddr($this->uIP[0]));

        if ($this->sIP && array_intersect($this->uIP, $this->sIP)) {
            return false;
        } elseif (!$recursive) {
            return true;
        }

        # Recursive search
        $sIP = ($this->sIP) ? $this->sIP : array();
        $uIP = $this->uIP;
        while (true) {
            $sHost = $this->searchHost($sIP, $this->sHost);
            $uHost = $this->searchHost($uIP, $this->uHost);

            if (($sHost && array_intersect($sHost, $this->uHost)) || (
                $uHost && array_intersect($uHost, $this->sHost))) {
                return false;
            }

            $sIP = $this->searchIP($sHost, $this->sIP);
            $uIP = $this->searchIP($uHost, $this->uIP);

            if (($sIP && array_intersect($sIP, $this->uIP)) || (
                $uIP && array_intersect($uIP, $this->sIP))) {
                return false;
            }

            if (!($uHost || $sHost || $uIP || $sIP)) {
                return true;
            }
        }
    }

    public static function adjustTrackbackURL($rs)
    {
        # Override getTrackbackLink method
        $rs->extend('rsExtPostRetrocontrol');
    }

    public static function preTrackback($args)
    {
        global $tbc_key, $core;

        list($post_id, $tbc_key) = explode('/', $args);

        $tb = new dcTrackback($core);
        $tb->receive($post_id);
        exit;
    }

    private function searchHost($ip, $allhosts)
    {
        $res = array();

        if (!$ip) {return false;}
        foreach ($ip as $v) {
            $host = gethostbyaddr($v);
            $host = ($host === $v) ? false : $this->getSLD($host);

            if ($host && !in_array($host, $allhosts)) {
                $allhosts[] = $res[] = $host;
            }
        }

        return empty($res) ? false : $res;
    }

    private function searchIP($host, $allips)
    {
        $res = array();

        if (!$host) {return false;}
        foreach ($host as $v) {
            $ip = gethostbynamel($v);

            if (!$ip) {
                continue;
            }

            foreach ($ip as $v) {
                if (!in_array($v, $allips)) {
                    $allips[] = $res[] = $v;
                }
            }
        }

        return empty($res) ? false : $res;
    }

    private function getSLD($host)
    {
        $t = strrpos($host, '.');

        # Not in TLD
        if ($t === false) {
            return $host;
        }

        $res = substr($host, 0, $t);
        $t   = strrpos($res, '.');

        # Is already an SLD
        if ($t === false) {
            return $host;
        }

        return substr($host, $t + 1);
    }
}
