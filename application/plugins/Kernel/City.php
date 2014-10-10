<?php

class Kernel_City
{
    public function __construct()
    {
    }

    public static function findCityFromUrl()
    {
        $city = false;
        $serverName = $_SERVER['SERVER_NAME'];

        $ar = explode('.', $serverName);
        if ($ar[0] === "www") {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: http://' . substr($_SERVER['SERVER_NAME'], 4));
        }

        if (!empty($serverName)) {
            $city = Application_Model_Kernel_City::getByUrl($serverName);
        }

        return $city;
    }

    public static function getUrlForLink($city)
    {
        $cityUrl = 'http://' . SITE_NAME;
        if ($city) {
            $cityUrl = 'http://' . $city->getUrl();
        }

        return $cityUrl;
    }
}