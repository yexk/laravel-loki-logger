<?php

namespace Barexammasters\LaravelLokiLogger;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class LokiLoggerPersister extends Command
{
    protected $signature = 'loki:persist';
    protected $description = 'Persist Loki Log Messages to Loki Server';

    public function handle()
    {
        $file = $file = storage_path(LokiLoggerServiceProvider::LOG_LOCATION);
        if (!file_exists($file)) return;

        $content = file_get_contents($file);
        file_put_contents($file, '');

        $messages = explode("\n", $content);
        if (count($messages) === 0) return;

        $http = Http::withBasicAuth(
            config('lokilogging.loki.username'),
            config('lokilogging.loki.password')
        );
        $path = config('lokilogging.loki.server') . "/loki/api/v1/push";
        foreach ($messages as $message) {
            if ($message === "") continue;
            $data = json_decode($message);
            $resp = $http->post($path, [
                'streams' => [[
                    'stream' => $data->tags,
                    'values' => [[
                        strval($data->time * 1000),
                        $data->message
                    ]]
                ]]
            ]);
        }
    }
}