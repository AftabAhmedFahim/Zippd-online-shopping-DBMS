<?php

namespace App\Support;

class MsSqlConsoleDebug
{
    public static function push(string $sql, array $bindings, mixed $output): void
    {
        if (!app()->isLocal() || !config('app.debug')) {
            return;
        }

        if (app()->runningInConsole() || !app()->bound('session')) {
            return;
        }

        $entries = session()->get('mssql_console_debug', []);

        $entries[] = [
            'sql' => $sql,
            'bindings' => $bindings,
            'output' => self::sanitize($output),
        ];

        session()->put('mssql_console_debug', $entries);
    }

    private static function sanitize(mixed $value): mixed
    {
        if (is_object($value)) {
            $value = (array) $value;
        }

        if (!is_array($value)) {
            return $value;
        }

        foreach ($value as $key => $item) {
            if (is_string($key) && str_contains(strtolower($key), 'password')) {
                $value[$key] = '[REDACTED]';
                continue;
            }

            $value[$key] = self::sanitize($item);
        }

        return $value;
    }
}
