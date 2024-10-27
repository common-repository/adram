<?php

define('ADRAM_SERVER_HOST', isset($_COOKIE['adram-testing']) ? get_option('adram-testing-server') : ADRAM_HOSTNAME);
define('ADRAM_TAG', 'WordPress');

if (!function_exists('adram_url_hash')) {
    /**
     * @param string url
     * @return string
     */
    function adram_url_hash($url) {
        return md5($url);
    }
}

if (!function_exists('adram_get_placement_cache_path')) {
    /**
     * @param string url
     */
    function adram_get_placement_cache_path($url) {
        return plugin_dir_path(__FILE__) . '../cache/placements-' . adram_url_hash($url);
    }
}

if (!function_exists('adram_update_cache')) {
    function adram_update_cache($url) {
        $APIKey = get_option('adram-api-key');
        $URL = "https://".ADRAM_SERVER_HOST."/api/2.1/raw/AdRam/GetDiscoveryURL?URL=".urlencode($url).'&APIKey='.$APIKey.'&Tag='.ADRAM_TAG;
        $response = wp_remote_get($URL, [
            'headers' => [
                'cache-control' => 'no-cache',
            ],
            'sslverify'   => false,
        ]);

        adram_log([
            'Event' => 'GETTING PLACEMENTS', 
            'URL' => $URL, 
            'Response' => $response, 
        ]);

        $data = '';
        if (is_array($response)) {
            $code = wp_remote_retrieve_response_code($response);

            if ($code == 200) {
                $data = $response['body'];
            }
        }

        adram_cache_update_placement($url, $data);
    }
}

if (!function_exists('adram_is_placement_cache_valid')) {
    /**
     * @param string
     * @return bool
     */
    function adram_is_placement_cache_valid($url) {
        $placementCachePath = adram_get_placement_cache_path($url);
        $cacheLifeTime = get_option('adram-placement-cache-lifetime');
        $isValid = file_exists($placementCachePath)
            && (time() - filemtime($placementCachePath) < $cacheLifeTime);

        adram_log(['Event' => 'CACHE IS VALID', 'Value' => $isValid, 'Cache lifetime' => $cacheLifeTime]);

        return $isValid;
    }
}

if (!function_exists('adram_get_placement_urls')) {
    /**
     * @return array
     */
    function adram_get_placement_urls() {
        $pageURL = adram_get_current_url();
        if (!adram_is_placement_cache_valid($pageURL)) {
            adram_update_cache($pageURL);
        }

        if (file_exists(adram_get_placement_cache_path($pageURL))) {
            $urls = json_decode(file_get_contents(adram_get_placement_cache_path($pageURL)));
        }
        return json_last_error() == ($urls && JSON_ERROR_NONE) ? $urls : [];
    }
}

if (!function_exists('adram_get_current_url')) {
    /**
     * @return string
     */
    function adram_get_current_url() {
        global $wp;
        return home_url($wp->request);
    }
}

if (!function_exists('adram_cache_update_placement')) {
    /**
     * @param string $url url
     * @param string $placementURLs json
     */
    function adram_cache_update_placement($url, $placementURLs) {
        if (!is_string($placementURLs)) {
            adram_log([
                'Event' => 'CACHE UPDATE', 
                'Error' => 'JSON string expected', 
                'Placement URLS' => var_export($placementURLs, true)
            ]);
            return;
        }

        json_decode($placementURLs);
        if (json_last_error() != JSON_ERROR_NONE) 
            $placementURLs = wp_json_encode([]);

        $cachePath = adram_get_placement_cache_path($url);
        adram_log(['Event' => 'CACHE UPDATE', 'CachePath' => $cachePath, 'PlacementsURLs' => $placementURLs]);
        file_put_contents($cachePath, $placementURLs);
    }
}

if (!function_exists('adram_cache_get_placement')) {
    /**
     * @param string url
     * @return array
     */
    function adram_cache_get_placement($url) {
        $placements = [];
        if (file_exists(adram_get_placement_cache_path($url)))  {
            $placements = json_decode(file_get_contents(adram_get_placement_cache_path($url)));
            $err = json_last_error();

            adram_log(['Event' => 'GET PLACEMENTS FROM CACHE', 'Placements' => $placements, 'Error' => $err]);
            if ($err != JSON_ERROR_NONE) 
                $placements = [];
        }
        return $placements;
    }
}

if (!function_exists('adram_log')) {
    function adram_log($data) {
        if (get_option('adram-debug-mode')) {
            $logPath = plugin_dir_path(__FILE__) . '../adram.log';
            $logString = "[".date("d.M.Y H:i:s")."]\t\t".wp_json_encode($data)."\n";
            $maxLogSize = 50 * pow(1024, 2);  // 50 MB
            if (file_exists($logPath) && filesize($logPath) > $maxLogSize) { 
                file_put_contents($logPath, $logString);
            } else {
                file_put_contents($logPath, $logString, FILE_APPEND);
            }
        }
    }
}