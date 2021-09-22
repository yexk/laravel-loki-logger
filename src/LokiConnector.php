<?php

namespace Yexk\LokiLogger;

use Illuminate\Support\Facades\Http;

class LokiConnector
{
    public static function log(string $serverPath, ?string $username, ?string $password, array $logTexts): bool
    {
        $http = Http::withBasicAuth(
            $username ?? '',
            $password ?? ''
        );

        $http->post($serverPath, $logTexts);

        return true;
    }
}
