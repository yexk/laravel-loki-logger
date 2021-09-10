<?php

namespace Barexammasters\LaravelLokiLogger;

use Illuminate\Support\Facades\Http;

class LokiConnector
{
    public static function Log(string $serverPath, ?string $username, ?string $password, array $logTexts): bool
    {
        $http = Http::withBasicAuth(
            $username ?? '',
            $password ?? ''
        );
        foreach($logTexts as $log)
        {
            $response = $http->post($serverPath, $log);
        }

        return true;
    }
}