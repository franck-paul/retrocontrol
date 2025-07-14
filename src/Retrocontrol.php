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
use Dotclear\Database\Cursor;
use Dotclear\Database\MetaRecord;
use Exception;

class Retrocontrol
{
    /**
     * @var array<int, string>
     */
    public array $uHost = [];

    /**
     * @var array<int, string>
     */
    public array $sHost = [];

    /**
     * @var array<int, string>
     */
    public array $uIP = [];

    /**
     * @var array<int, string>
     */
    public array $sIP = [];

    public static function checkTimeout(Cursor $cur): string
    {
        $errmsg = "\n" . 'Invalid trackback. Are you using an expired URL?';

        # Trackback not adjusted or too short key
        if (!App::frontend()->retrocontrol_tbc_key || strlen((string) App::frontend()->retrocontrol_tbc_key) < 5) {
            throw new Exception($errmsg);
        }

        # Timeout setting
        $timeout = My::settings()->rc_timeout;
        $timeout = $timeout ? (int) $timeout : 300;

        # Check key validity
        $chk                                  = substr((string) App::frontend()->retrocontrol_tbc_key, 0, 4);
        $key                                  = substr((string) App::frontend()->retrocontrol_tbc_key, 4);
        App::frontend()->retrocontrol_tbc_key = substr(md5($cur->post_id . App::config()->masterKey() . $key), 1, 4);

        if (App::frontend()->retrocontrol_tbc_key !== $chk) {
            throw new Exception($errmsg);
        }

        # Check key expiration date
        $post     = App::blog()->getPosts(['post_id' => $cur->post_id]);
        $ts       = (int) $post->getTS();
        $curDate  = time() - $ts;
        $refDate  = (int) base_convert($key, 36, 10) ^ $ts;
        $diffDate = $curDate - $refDate;

        if ($diffDate < 1 || $diffDate > $timeout) {
            throw new Exception($errmsg);
        }

        return '';
    }

    /**
     * Get the IP addresses (IPv4 or IPv6) of a host name
     * Simulates gethostbynamel() but with IPv6 support
     *
     * @param      string  $name   The hostname
     *
     * @return     array<string>
     */
    private function gethostbynamelipv6(string $name): array
    {
        $ret = [];
        if ($records = dns_get_record($name, DNS_A | DNS_AAAA)) {
            foreach ($records as $e) {
                $ret[] = $e[$e['type'] === 'A' ? 'ip' : 'ipv6'];
            }
        }

        return $ret;
    }

    /**
     * Get the IPV6 address from a hostname
     *
     * @return string|null If no IPV6 address is found it will return null
     */
    private function gethostbyaddripv6(string $hostname): ?string
    {
        $record = dns_get_record($hostname, DNS_AAAA);

        return $record[0]['ipv6'] ?? null;
    }

    public function checkSource(string $url, ?string $ip = null, bool $recursive = true): bool
    {
        $site = @parse_url($url);
        if (!($site && $url)) {
            # Bad URL => SPAM
            return true;
        }

        $site = $site['host'];
        $ip   = $ip ?: $_SERVER['REMOTE_ADDR'];

        # Initializing search data
        $this->sIP   = $this->gethostbynamelipv6($site) ?: [];
        $this->sHost = [$site, $this->getSLD($site)];
        $this->uIP   = [$ip];
        $this->uHost = [(string) $this->gethostbyaddripv6($this->uIP[0])];

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

    public static function adjustTrackbackURL(MetaRecord $rs): string
    {
        # Override getTrackbackLink method
        $rs->extend(rsExtPostRetrocontrol::class);

        return '';
    }

    public static function preTrackback(string $args): never
    {
        [$post_id, App::frontend()->retrocontrol_tbc_key] = explode('/', $args);

        App::trackback()->receiveTrackback((int) $post_id);

        exit;
    }

    /**
     * @param      array<int, string>       $ip        The IPs
     * @param      array<int, string>       $allhosts  The hosts
     *
     * @return     array<int, string>
     */
    private function searchHost(array $ip, array $allhosts): array
    {
        $res = [];

        if ($ip === []) {
            return $res;
        }

        foreach ($ip as $v) {
            $host = (string) $this->gethostbyaddripv6($v);
            $host = ($host === $v) ? false : $this->getSLD($host);

            if ($host && !in_array($host, $allhosts)) {
                $allhosts[] = $res[] = $host;
            }
        }

        return $res;
    }

    /**
     * @param      array<int, string>       $host    The host
     * @param      array<int, string>       $allips  The IPs
     *
     * @return     array<int, string>
     */
    private function searchIP(array $host, array $allips): array
    {
        $res = [];

        if ($host === []) {
            return $res;
        }

        foreach ($host as $v) {
            $ip = $this->gethostbynamelipv6($v);

            if ($ip === []) {
                continue;
            }

            foreach ($ip as $v) {
                if (!in_array($v, $allips)) {
                    $allips[] = $res[] = $v;
                }
            }
        }

        return $res;
    }

    private function getSLD(string $host): string
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
