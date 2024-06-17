<?php

use App\Models\Parameter;
use Illuminate\Support\Facades\Log;

/**
 * Gets a record from the Parameters table
 *
 * ToDo: we should provide also "category" as input, in order to avoid collisions
 *
 * @param string $code the Code to be retrieved
 * @return mixed
 */
function getParameter(string $code)
{
    static $log_parameters_activity;

    if (!$log_parameters_activity) {
        $log_parameters_activity = Parameter::where('code', 'log_parameters_activity')->pluck('value')->first() ?? 'Y';
    }

    $param = Parameter::where('code', $code)->first();
    if ($log_parameters_activity == 'Y') {
        if (!$param) {
            Log::alert('Parameter "'.$code.'" not found.');
        } else {
            Log::debug('Parameter with code "'.$param->code.'" retrieved with value "'.$param->value.'"');
        }
    }
    return $param;
}

/**
 * Returns a parameter value retrieved from the Parameters table
 *
 * ToDo: we should provide also "category" as input, in order to avoid collisions
 *
 * @param string $code the Code to be retrieved
 * @param string $default a default value, in case the parameter is not present
 * @return mixed
 */
function getParameterValue(string $code, $default = '')
{
    if (($param = getParameter($code)) && $param->value) {
        return $param->value;
    } else {
        return $default;
    }
}

/**
 * Updates a parameter value into Parameters table
 *
 * @param string $code the Code to be updated
 * @param string $value the value to be updated
 * @return bool
 */
function updateParameterValue(string $code, $value = '')
{
    $ret = false;

    if ($param = Parameter::where('code', $code)->first()) {
        $param->value = $value;
        $ret = $param->update();
    } else {
        Log::alert('Parameter "'.$code.'" not found for update.');
    }
    return $ret;
}
