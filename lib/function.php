<?php

/**
 * 判断是否是json
 * @param string $str
 * @return bool
 */
function is_json(string $str): bool
{
    json_decode($str);

    return json_last_error() === JSON_ERROR_NONE;
}

/**
 * @param int $code
 * @param string $message
 * @param $arr
 * @return false|string
 */
function re_json($code = 200, $message = 'ok', $arr = []){
    $reData = [
        'status' => $code,
        'message' => $message,
        'result' => $arr
    ];
    return json_encode($reData);
}