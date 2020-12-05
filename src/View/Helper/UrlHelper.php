<?php

namespace App\View\Helper;

class UrlHelper
{
    protected $base_url;

    public function __construct(string $base_url = "") {
        $this->base_url = $base_url ? $base_url : self::getUrlOrigin();
    }

    public function __invoke(string $path = "/", array $query = []) : string
    {
        $url = sprintf("%s/%s", $this->base_url, trim($path, "/"));
        if (count($query)) {
            $query = http_build_query($query);
            $url = sprintf("%s?%s", $url, $query);
        }
        return $url;
    }

    public static function getUrlOrigin($s = null, $use_forwarded_host = false) : string
    {
        if (is_null($s)) {
            $s = $_SERVER;
        }
        $ssl      = (! empty($s['HTTPS']) && $s['HTTPS'] == 'on');
        $sp       = strtolower($s['SERVER_PROTOCOL']);
        $protocol = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port     = $s['SERVER_PORT'];
        $port     = ((! $ssl && $port=='80') || ($ssl && $port=='443')) ? '' : ':'.$port;
        $host     = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ? $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ? $s['HTTP_HOST'] : null);
        $host     = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        return $protocol . '://' . $host;
    }

    public static function getFullUrl($s = null, $use_forwarded_host = false) : string
    {
        if (is_null($s)) {
            $s = $_SERVER;
        }
        return self::getUrlOrigin($s, $use_forwarded_host) . $s['REQUEST_URI'];
    }
}
