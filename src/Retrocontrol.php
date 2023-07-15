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
use dcTrackback;
use Exception;

class Retrocontrol
{
    public $uHost = [];
    public $sHost = [];
    public $uIP   = [];
    public $sIP   = [];

    public static function checkTimeout($cur)
    {
        $errmsg = "\n" . 'Invalid trackback. Are you using an expired URL?';

        # Trackback not adjusted or too short key
        if (!dcCore::app()->retrocontrol_tbc_key || strlen(dcCore::app()->retrocontrol_tbc_key) < 5) {
            throw new Exception($errmsg);
        }

        # Timeout setting
        $timeout = dcCore::app()->blog->settings->retrocontrol->rc_timeout;
        $timeout = $timeout ? (int) $timeout : 300;

        # Check key validity
        $chk                                = substr(dcCore::app()->retrocontrol_tbc_key, 0, 4);
        $key                                = substr(dcCore::app()->retrocontrol_tbc_key, 4);
        dcCore::app()->retrocontrol_tbc_key = substr(md5($cur->post_id . DC_MASTER_KEY . $key), 1, 4);

        if (dcCore::app()->retrocontrol_tbc_key !== $chk) {
            throw new Exception($errmsg);
        }

        # Check key expiration date
        $post     = dcCore::app()->blog->getPosts(['post_id' => $cur->post_id]);
        $ts       = (int) $post->getTS();
        $curDate  = time() - $ts;
        $refDate  = (int) base_convert($key, 36, 10) ^ $ts;
        $diffDate = $curDate - $refDate;

        if ($diffDate < 1 || $diffDate > $timeout) {
            throw new Exception($errmsg);
        }
    }

    /**
     * Get the first IP address (IPv4 or IPv6) of a host name
     * Simulates gethostbyname() but with IPv6 support
     *
     * @param      string  $name   The hostname
     *
     * @return     string
     */
    private function gethostbynameipv6(string $name): string
    {
        $addr = $this->gethostbynamelipv6($name);
        if (is_array($addr)) {
            return $addr[0];
        }

        return $name;
    }

    /**
     * Get the IP addresses (IPv4 or IPv6) of a host name
     * Simulates gethostbynamel() but with IPv6 support
     *
     * @param      string  $name   The hostname
     *
     * @return     array|false
     */
    private function gethostbynamelipv6(string $name)
    {
        $ret = [];
        foreach (dns_get_record($name, DNS_A | DNS_AAAA) as $e) {
            $ret[] = $e[$e['type'] === 'A' ? 'ip' : 'ipv6'];
        }

        return count($ret) > 0 ? $ret : false;
    }

    /**
     * Get the IPV6 address from a hostname
     *
     * @param string $hostname
     *
     * @return string|null If no IPV6 address is found it will return null
     */
    private function gethostbyaddripv6(string $hostname): ?string
    {
        $record = dns_get_record($hostname, DNS_AAAA);

        return $record[0]['ipv6'] ?? null;
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
        $this->sIP   = $this->gethostbynamelipv6($site);
        $this->sHost = [$site, $this->getSLD($site)];
        $this->uIP   = [$ip];
        $this->uHost = [$this->gethostbyaddripv6($this->uIP[0])];

        if ($this->sIP && array_intersect($this->uIP, $this->sIP)) {
            return false;
        } elseif (!$recursive) {
            return true;
        }

        # Recursive search
        $sIP = $this->sIP ?: [];
        $uIP = $this->uIP;
        while (true) {
            $sHost = $this->searchHost($sIP, $this->sHost);
            $uHost = $this->searchHost($uIP, $this->uHost);

            if (($sHost && array_intersect($sHost, $this->uHost)) || (
                $uHost && array_intersect($uHost, $this->sHost)
            )) {
                return false;
            }

            $sIP = $this->searchIP($sHost, $this->sIP);
            $uIP = $this->searchIP($uHost, $this->uIP);

            if (($sIP && array_intersect($sIP, $this->uIP)) || (
                $uIP && array_intersect($uIP, $this->sIP)
            )) {
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
        $rs->extend(rsExtPostRetrocontrol::class);
    }

    /**
     * @return never
     */
    public static function preTrackback($args)
    {
        [$post_id, dcCore::app()->retrocontrol_tbc_key] = explode('/', $args);

        (new dcTrackback())->receiveTrackback((int) $post_id);

        exit;
    }

    private function searchHost($ip, $allhosts)
    {
        $res = [];

        if (!$ip) {
            return false;
        }
        foreach ($ip as $v) {
            $host = $this->gethostbyaddripv6($v);
            $host = ($host === $v) ? false : $this->getSLD($host);

            if ($host && !in_array($host, $allhosts)) {
                $allhosts[] = $res[] = $host;
            }
        }

        return empty($res) ? false : $res;
    }

    private function searchIP($host, $allips)
    {
        $res = [];

        if (!$host) {
            return false;
        }
        foreach ($host as $v) {
            $ip = $this->gethostbynamelipv6($v);

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
