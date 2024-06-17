<?php

/**
 * Get the device or browser from the user agent string and extract the required info
 *
 * @param $userAgent string The full userAgent string coming from the server
 * @param $what string The type of information to be retrieved: device, browser
 * @return mixed
 */
function getFromUserAgent($userAgent, $what)
{
    $what = strtolower($what);
    $userAgent = strtolower($userAgent);
    switch($what)
    {
        case 'device':
            if (strpos($userAgent, 'android') !== false) {
                return 2;
            } elseif (strpos($userAgent, 'iphone') !== false) {
                return 4;
            } elseif (strpos($userAgent, 'ipad') !== false) {
                return 6;
            } elseif (strpos($userAgent, 'macintosh') !== false) {
                return 5;
            } elseif (strpos($userAgent, 'windows') !== false) {
                return 1;
            } elseif (strpos($userAgent, 'linux') !== false) {
                return 3;
            } elseif(strpos($userAgent, 'tablet') !== false) {
                return 7;
            } elseif(strpos($userAgent, 'bot') !== false) {
                return 8;
            } else {
                return 99;
            }
        case 'browser':
            $browser_info = getBrowserInfo($userAgent);
            return $browser_info;
        default:
            return 'Unknown';
    }
}

/**
 * Get the browser information from the user agent string
 *
 * Returns a browser code listed in the LmpStat model
 *
 * @param $user_agent string The full userAgent string coming from the server
 * @return array the browser code and version. I.e: ['browser_id' => 1, 'version' => '89.0']
 */
function getBrowserInfo($user_agent) {
    // Define an array of known user agent strings for different browsers
    $browser_patterns = array(
        1       => 'firefox\/(\d+\.?\d*|\.\d+)',
        2       => 'chrome\/(\d+\.?\d*|\.\d+)',
        3       => 'version\/(\d+\.?\d*|\.\d+).*safari',
        4       => 'edge\/(\d+\.?\d*|\.\d+)',
        5       => 'msie (\d+\.?\d*|\.\d+)',
        6       => 'opera\/(\d+\.?\d*|\.\d+)',
    );

    $browser_info = array();
//    Log::debug('User agent: '.$user_agent);

    // Loop through each browser pattern and match it against the user agent string
    foreach ($browser_patterns as $browser => $pattern) {
        if (preg_match('/' . $pattern . '/', $user_agent, $matches)) {
            $browser_info['browser_id'] = $browser;
            $browser_info['version'] = $matches[1];
            break;
        }
    }

    // If browser information couldn't be determined, set default values
    if (empty($browser_info)) {
        $browser_info['browser_id'] = 99;
        $browser_info['version'] = 0;
    }

    return $browser_info;
}
