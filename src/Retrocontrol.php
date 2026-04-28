<?php

/**
 * @brief retrocontrol, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul contact@open-time.net
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
     * @var array<string>
     */
    public array $uHost = [];

    /**
     * @var array<string>
     */
    public array $sHost = [];

    /**
     * @var array<string>
     */
    public array $uIP = [];

    /**
     * @var array<string>
     */
    public array $sIP = [];

    public static function checkTimeout(Cursor $cur): string
    {
        $errmsg = "\n" . 'Invalid trackback. Are you using an expired URL?';

        $tbc_key = is_string($tbc_key = App::frontend()->retrocontrol_tbc_key) ? $tbc_key : '';

        // Trackback not adjusted or too short key
        if (strlen($tbc_key) < 5) {
            throw new Exception($errmsg);
        }

        // Timeout setting
        $timeout = is_numeric($timeout = My::settings()->rc_timeout) ? abs((int) $timeout) : 300;

        // Check key validity
        $chk = substr($tbc_key, 0, 4);
        $key = substr($tbc_key, 4);
        $id  = is_numeric($id = $cur->post_id) ? (int) $id : 0;

        App::frontend()->retrocontrol_tbc_key = substr(md5($id . App::config()->masterKey() . $key), 1, 4);

        if (App::frontend()->retrocontrol_tbc_key !== $chk) {
            throw new Exception($errmsg);
        }

        // Check key expiration date
        $post     = App::blog()->getPosts(['post_id' => $id]);
        $ts       = is_numeric($ts = $post->getTS()) ? (int) $ts : 0;
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
        /**
         * @var array<string>
         */
        $ret = [];
        if ($records = dns_get_record($name, DNS_A | DNS_AAAA)) {
            foreach ($records as $record) {
                if (isset($record['type'])) {
                    $key = $record['type'] === 'A' ? 'ip' : 'ipv6';
                    if (isset($record[$key]) && is_string($record[$key])) {
                        $ret[] = $record[$key];
                    }
                }
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
        if (!$record = dns_get_record($hostname, DNS_AAAA)) {
            return null;
        }
        if (isset($record[0]['ipv6']) && is_string($record[0]['ipv6'])) {
            return $record[0]['ipv6'];
        }

        return null;
    }

    public function checkSource(string $url, ?string $ip = null, bool $recursive = true): bool
    {
        $site = @parse_url($url);
        if (!($site && $url)) {
            # Bad URL => SPAM
            return true;
        }

        $site = $site['host'] ?? '';
        if ($site === '') {
            # Bad URL => SPAM
            return true;
        }

        $remote = is_string($remote = $_SERVER['REMOTE_ADDR']) ? $remote : '';
        $ip     = $ip ?: $remote;

        # Initializing search data
        $this->sIP   = $this->gethostbynamelipv6($site);
        $this->sHost = [$site, $this->getSLD($site)];
        $this->uIP   = [$ip];
        $this->uHost = [(string) $this->gethostbyaddripv6($this->uIP[0])];

        if ($this->sIP && array_intersect($this->uIP, $this->sIP)) {
            return false;
        }

        if (!$recursive) {
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
     * @param      array<string>       $ip        The IPs
     * @param      array<string>       $allhosts  The hosts
     *
     * @return     array<string>
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
     * @param      array<string>       $host    The host
     * @param      array<string>       $allips  The IPs
     *
     * @return     array<string>
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
