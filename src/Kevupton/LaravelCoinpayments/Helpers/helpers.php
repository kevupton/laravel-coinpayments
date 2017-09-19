<?php

use Kevupton\LaravelCoinpayments\Models\Log;

if (!function_exists('cp_prefix')) {
    /**
     * Gets the prefix of the database table.
     *
     * @return string
     */
    function cp_prefix() {
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


if (!function_exists('cp_log')) {
    /**
     * Logs the request in the database
     *
     * @param string $content the xml data received
     * @param null|string $type
     * @return Log
     * @internal param array $sent the data sent
     */
    function cp_log($content, $type = null) {
        return Log::create([
            'log' => $content,
            'type' => $type
        ]);
    }
}