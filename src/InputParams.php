<?php declare(strict_types=1);

namespace Wolnosciowiec\WebProxy;

final class InputParams
{
    // query
    const QUERY_TOKEN          = '__wp_token';
    const QUERY_CAN_PROCESS    = '__wp_process';
    const QUERY_ONE_TIME_TOKEN = '__wp_one_time_token';
    const QUERY_TARGET_URL     = '__wp_url';

    // headers
    const HEADER_CAN_PROCESS = 'ww-process-output';
    const HEADER_TARGET_URL  = 'ww-target-url';
    const HEADER_TOKEN       = 'ww-token';

    // one-time-token properties
    const ONE_TIME_TOKEN_PROPERTY_URL     = 'url';
    const ONE_TIME_TOKEN_PROPERTY_EXPIRES = 'expires';
    const ONE_TIME_TOKEN_PROCESS          = 'process';
}
