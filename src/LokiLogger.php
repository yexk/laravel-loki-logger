<?php

namespace Barexammasters\LaravelLokiLogger;

use Exception;
use Monolog\Handler\HandlerInterface;

class LokiLogger implements HandlerInterface
{
    /** @var resource */
    private $file;
    /** @var boolean */
    private $hasError;
    /** @var array */
    private $context;
    /** @var string */
    private $format;
    /** @var string */
    private $method;

    public function __construct(string $format = '[{level_name}] {message}', array $context = [], string $method = 'instant')
    {
        $this->format = config('lokilogging.format');
        $this->context = config('lokilogging.context');
        $this->method = config('lokilogging.method');

        if($this->method == 'file')
        {
            $file = storage_path(LokiLoggerServiceProvider::LOG_LOCATION);
            if (!file_exists($file)) {
                touch($file);
            }
            $this->file = fopen($file, 'a');
        }
    }

    /**
     * This handler is capable of handling every record
     * @param array $record
     * @return bool
     */
    public function isHandling(array $record): bool
    {
        return true;
    }

    public function handle(array $record): bool
    {
        $this->hasError |= $record['level_name'] === 'ERROR';
        $message = $this->formatString($this->format, $record);
        $tags = array_merge($record['context'], $this->context);
        foreach ($tags as $tag => $value) {
            if (is_string($value)) {
                $tags[$tag] = $this->formatString($value, $record);
            } else {
                unset($tags[$tag]);
            }
        }

        if($this->hasError || $this->method == 'instant')
        {
            return LokiConnector::Log(
                config('lokilogging.loki.server') . "/loki/api/v1/push",
                config('lokilogging.loki.username'),
                config('lokilogging.loki.password'),
                [
                    'streams' => [[
                        'stream' => $tags,
                        'values' => [[
                            strval(now()->getPreciseTimestamp() * 1000),
                            $message
                        ]]
                    ]]
                ]
            );
        }
        else if($this->method == 'file')
        {
            return fwrite($this->file, json_encode([
                'time' => now()->getPreciseTimestamp(),
                'tags' => $tags,
                'message' => $message
            ]) . "\n");
        }
        else {
            throw new Exception('Unrecognized log method');
        }
    }

    public function handleBatch(array $records): void
    {
        foreach ($records as $record) {
            $this->handle($record);
        }
    }

    public function close(): void
    {
        fclose($this->file);
    }

    private function formatString(string $format, array $context): string
    {
        $message = $format;
        foreach ($context as $key => $value) {
            if (!is_string($value)) continue;
            $message = str_replace(
                sprintf('{%s}', $key),
                $value,
                $message
            );
        }
        return $message;
    }
}