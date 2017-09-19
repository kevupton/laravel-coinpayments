<?php

use Kevupton\LaravelCoinpayments\Models\Log;

if (!function_exists('cp_table_prefix')) {
    /**
     * Gets the prefix of the database table.
     *
     * @return string
     */
    function cp_table_prefix() {
        return cp_conf('database_prefix');
    }
}


if (!function_exists('cp_conf')) {
    /**
     * Gets a config value from the config file.
     *
     * @param string $prop the key property
     * @param string $default the default response
     *
     * @return mixed
     */
    function cp_conf($prop, $default = '') {
        return config(COINPAYMENTS_CONFIG . '.' . $prop, $default);
    }
}

if (!function_exists('cp_log_level')) {
    /**
     * Gets the specified log level for the config.
     *
     * @return number
     */
    function cp_log_level() {
        return cp_conf('log_level', Log::LEVEL_NONE);
    }
}

if (!function_exists('cp_log')) {
    /**
     * Logs the request in the database
     *
     * @param mixed $content the xml data received
     * @param null|string $type
     * @param int $level
     * @return Log|null
     * @internal param array $sent the data sent
     */
    function cp_log($content, $type = null, $level = Log::LEVEL_ALL) {
        if (cp_log_level() < $level) return null;

        return Log::create([
            'log' => json_encode($content),
            'type' => $type
        ]);
    }
}