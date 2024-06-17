<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Logs activity only if:
 *  - its type is "error" or higher
 *  - log is set to ON for this specific action
 * @param string $type one of the standard Monolog types
 *          (debug, info, notice, warning, error, critical, alert, emergency)
 * @param string $code the code inside the Shipsecure system
 * @param string $message the message to log
 */
function ssLog(string $type, string $code, string $message)
{
    static $flags = [];
    static $channels = [];

    $type = (strtolower($type) ?: 'undefined');
    $code = strtolower($code);

    if (!array_key_exists($code, $flags)) {
        // we store it statically for better performance
        $param = getParameter('log_'.$code.'_activity');
        if ($param) {
            $flags[$code] = ($param->value != 'N' ? true:false);
            $channels[$code] = Str::lower($param->auxiliary) ?? 'general';
        } else {
            // cannot find what to do? Then we log it
            $flags[$code] = true;
            $channels[$code] = 'general';
        }

    }
    $user = Auth::check() ? Auth::user()->nickname : 'job';

    if (strpos('|error|critical|alert|emergency', $type) || $flags[$code]) {
        Log::channel('daily-'.$channels[$code])->$type('['.$user.' - '.strtoupper($code).']: '.$message);
    }
}

function ssLogDebug(string $code, string $message)
{
    ssLog('debug', $code, $message);
}
function ssLogNotice(string $code, string $message)
{
    ssLog('notice', $code, $message);
}
function ssLogWarning(string $code, string $message)
{
    ssLog('warning', $code, $message);
}
function ssLogAlert(string $code, string $message)
{
    ssLog('alert', $code, $message);
}
function ssLogError(string $code, string $message)
{
    ssLog('error', $code, $message);
}
function ssLogCritical(string $code, string $message)
{
    ssLog('critical', $code, $message);
}
