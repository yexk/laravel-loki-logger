<?php

namespace yexk\LokiLogger;

use Illuminate\Console\Command;

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

        $decodedLogs = array();

        foreach ($messages as $message) {
            if ($message === "") continue;
            $data = json_decode($message);
            $decodedLogs = [
                'streams' => [[
                    'stream' => $data->tags,
                    'values' => [[
                        strval($data->time * 1000),
                        $data->message
                    ]]
                ]]
            ];

            LokiConnector::log(
                config('lokilogging.loki.server') . "/loki/api/v1/push",
                config('lokilogging.loki.username'),
                config('lokilogging.loki.password'),
                $decodedLogs
            );
        }
    }
}
