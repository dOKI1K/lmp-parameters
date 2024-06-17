<?php

use App\Models\User;
use Illuminate\Support\Str;
////////////////////////////////////
use App\Models\Config;
use App\Models\Parameter;
use App\Models\Proposal;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Returns a verbose version of the user_id, assuming the user->name is in the form "firstname lastname".
 * If the name is just one word, it is returned as is.
 * If the user_id is not found, it returns a "???" string.
 *
 * @param $user_id
 * @return string
 */

function nickname($user_id)
{
    static $cache = [];

    if(in_array($user_id, $cache)) {
        $ret = $cache[$user_id];
    } else {
        if ($user = User::where('id', $user_id)->first()) {
            $aux = explode(' ', $user->name);
            switch(count($aux)) {
                case 0:
                    $ret = $user->name;
                    break;
                case 1:
                    $ret = Str::ucfirst($aux[0]);
                    break;
                case 2:
                    $ret = Str::ucfirst($aux[0]).'-'.Str::ucfirst($aux[1][0]);
                    break;
                default:
                    $ret = Str::ucfirst($aux[0]).'-'.Str::ucfirst($aux[2][0]);
            }
            $cache[$user_id] = $ret;
        } else {
            $ret = 'n/a';
        }
    }
    return $ret;
}

/**
 * Returns true if is a valid date, or false if the date is null o void ('0000-00-00')
 * @param $date
 * @return bool
 */
function isNullDate($date)
{
    return
        // truly a null
        $date === null
        // or a malformed date
        || strtotime($date) === false
        // or a '0000-00-00' value
        || strtotime($date) === -62169984000;
}

/**
 * Returns the difference between two dates in a human readable format
 * If the date is null, it returns an empty string
 * @param $date
 * @return string
 */

function diffForHumansNotNull($date)
{
    return $date ? $date->diffForHumans() : '';
}

function ssFileExtensionIsVisual($file)
{
    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return stripos(','.getParameterValue('thumbnail_enabled_extensions'), $ext);
}

function when(Carbon $date, $show_date_too = false)
{
    return $date->diffForHumans()
        .($show_date_too ? ' ('.$date->format('d/m/Y').')' : '');
}

function whoAndWhen($user_id, Carbon $date, $show_date_too = false)
{
    return nickname($user_id).', '.when($date, $show_date_too);
}


// function that receives a subnet and return the ip range
/**
 * Returns the ip range of a subnet
 *
 * @param $cidr string The subnet in the form of "xxx.xxx.xxx.xxx/xx"
 * @param $return_as_ip bool false=(default) returns the range as an array of integers. true=returns the range as an array of ips
 * @return array
 */
function cidr2range($cidr, $return_as_ip = false): array
{
    $cidr = explode('/', $cidr);
    if (count($cidr) != 2) {
        return [null, null];
    }
    $range_start = ip2long($cidr[0]);
    $range_end = $range_start + pow(2, 32-intval($cidr[1])) - 1;
    return $return_as_ip
        ? [long2ip($range_start), long2ip($range_end)]
        : [$range_start, $range_end];
}

/**
 * Returns the cidr of a range of ips
 *
 * @param $range_start int The start of the range
 * @param $range_end int The end of the range
 * @return string The cidr of the range
 */
function range2cidr($range_start, $range_end)
{
    $range_start = ip2long($range_start);
    $range_end = ip2long($range_end);
    $cidr = 32;
    while (($range_start & ~((1 << (32 - $cidr)) - 1)) != ($range_end & ~((1 << (32 - $cidr)) - 1))) {
        $cidr--;
    }
    return $cidr;
}

// get the ip from a cidr using regexp
function cidr2ip($cidr)
{
    return preg_replace('/\/.*$/', '', $cidr);
}

// print bytes count for humans
function human_filesize($bytes, $decimals = 2)
{
    $sz = 'BKMGTP';
    $factor = floor((strlen($bytes) - 1) / 3);
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor];
}

// get real connection and table name based on a model
function getConnectionAndTable($model)
{
    if($model) {
        $table = $model->getTable();
        $connection = $model->getConnectionName();
        return ($connection
                ? config('database.connections.'.$connection.'.database').'.':'')
            .$table;
    } else {
        return null;
    }
}

function human_count($count)
{
    $count = intval($count);
    if ($count < 1000) {
        return $count;
    } elseif ($count < 1000000) {
        return round($count / 1000, 1) . 'K';
    } elseif ($count < 1000000000) {
        return round($count / 1000000, 1) . 'M';
    } else {
        return round($count / 1000000000, 1) . 'B';
    }
}

//get date from database and return it in the format of the configuration
function getDateFrom($date)
{
    // $format = Parameter::where('code', 'date_format')->where('account_id', Auth::user()->account_id)->first()->value ?? 'd/m/Y';
    $format = getParameterValue('date_format') ?? 'd/m/Y';
    return $date ? Carbon::createFromFormat('Y-m-d H:i:s', $date)->format($format) : '';
}

function getCurrentAccountName()
{
    return auth()->check() ? Auth::user()->account->name:config('app.name');
}

function formatPhoneMask($phone, $parameter)
{
    $mask = $parameter;
    $mask = str_split($mask);
    $phone = str_split($phone);
    foreach ($mask as $key => $value) {
        if ($value == '9') {
            $mask[$key] = array_shift($phone);
        }
    }
    $mask = implode('', $mask);
    return $mask;
}

function formatTime12()
{
    $parameter = getParameterValue('TIME_FORMAT', 'h:ia');

    if ($parameter == 'Y') {
        return 'H:i';
    } else {
        return $parameter;
    }
}
